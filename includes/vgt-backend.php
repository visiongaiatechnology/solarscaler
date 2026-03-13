<?php
if (!defined('ABSPATH')) exit;

/**
 * VGT BACKEND INTELLIGENCE [DIAMANT EDITION v4.8 - GHOST NODE]
 * Features: DSGVO Proxy, Raw cURL Failsafe, Polite Throttling, Referer Spoofing
 */
class VGT_Backend {
    private $cache_dir;
    private $channels = ['171', '193', '304', 'HMI'];
    const MAX_SLOTS = 72; // 12 Hours @ 10min Interval

    public function __construct() {
        $upload = wp_upload_dir();
        $this->cache_dir = $upload['basedir'] . '/vgt-solar-cache';

        add_action('wp_ajax_vgt_manual_sync', [$this, 'handle_manual_sync']);
        add_action('wp_ajax_vgt_hard_reset', [$this, 'handle_hard_reset']); 
        add_action('wp_ajax_vgt_frontend_refresh', [$this, 'handle_frontend_refresh']);
        add_action('wp_ajax_nopriv_vgt_frontend_refresh', [$this, 'handle_frontend_refresh']);

        add_action('admin_menu', [$this, 'register_admin_panel']);
        add_action('vgt_solar_cron_update', [$this, 'execute_pipeline']);
    }

    // --- PIPELINES ---

    public function execute_pipeline() {
        $log = [];
        @set_time_limit(300); 
        
        update_option('vgt_last_sync_attempt', time());

        foreach ($this->channels as $channel) {
            $log[$channel] = $this->process_single_latest($channel);
            // POLITE THROTTLING: 1 Sekunde Pause zwischen den Kanälen
            sleep(1); 
        }
        
        update_option('vgt_last_sync', time());
        return $log;
    }

    public function execute_deep_sync($limit = 18) {
        $log = [];
        @set_time_limit(600); 
        foreach ($this->channels as $channel) {
            $this->backfill_from_archive($channel, $limit);
            $res = $this->process_single_latest($channel);
            $log[$channel] = $res;
            
            // POLITE THROTTLING
            sleep(1);
        }
        update_option('vgt_last_sync', time());
        return $log;
    }

    // --- CORE LOGIC ---

    /**
     * VGT GHOST HEADERS
     * Täuscht einen legitimen menschlichen Besucher vor (Legal Polite Scraping)
     */
    private function get_stealth_args() {
        return [
            'timeout'    => 60,
            'sslverify'  => false,
            'reject_unsafe_urls' => false,
            'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
            'headers'    => [
                'Accept'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.9,de;q=0.8',
                'Referer'         => 'https://sdo.gsfc.nasa.gov/data/', // WICHTIG: Umgeht Hotlink-Protection!
                'Connection'      => 'keep-alive'
            ]
        ];
    }

    /**
     * VGT KINETIC FALLBACK FETCHER (Hardened cURL)
     */
    private function fetch_raw_data($url) {
        $args = $this->get_stealth_args();
        $response = wp_remote_get($url, $args);
        
        // 1. WP HTTP API (Standard Weg)
        if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
            return [
                'body' => wp_remote_retrieve_body($response),
                'last_modified' => wp_remote_retrieve_header($response, 'last-modified')
            ];
        }

        // 2. KINETIC FALLBACK: Raw cURL
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            
            // Baue Header-Array für cURL
            $curl_headers = [];
            foreach ($args['headers'] as $key => $value) {
                $curl_headers[] = "$key: $value";
            }

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            curl_setopt($ch, CURLOPT_USERAGENT, $args['user-agent']);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $curl_headers);
            curl_setopt($ch, CURLOPT_HEADER, true); 
            
            // Versuche IPv4 zu erzwingen, falls IPv6 geblockt ist (oder umgekehrt)
            if (defined('CURLOPT_IPRESOLVE') && defined('CURL_IPRESOLVE_V4')) {
                curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
            }
            
            $response_raw = curl_exec($ch);
            $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            if ($status === 200 && $response_raw !== false) {
                $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
                $header_str = substr($response_raw, 0, $header_size);
                $body = substr($response_raw, $header_size);
                curl_close($ch);

                $lm = '';
                if (preg_match('/Last-Modified:\s*(.*)/i', $header_str, $matches)) {
                    $lm = trim($matches[1]);
                }

                return [
                    'body' => $body,
                    'last_modified' => $lm
                ];
            }
            curl_close($ch);
        }

        return false; 
    }

    private function backfill_from_archive($channel, $limit = 18) {
        $map = ['171' => '0171', '193' => '0193', '304' => '0304', 'HMI' => 'HMIBC'];
        $code = $map[$channel] ?? '0171';
        $res = '2048';
        
        $date_path = gmdate('Y/m/d'); 
        $base_url = "https://sdo.gsfc.nasa.gov/assets/img/browse/" . $date_path;
        
        $data = $this->fetch_raw_data($base_url);
        if (!$data || empty($data['body'])) return;

        $html = $data['body'];
        
        $pattern = '/href="(\d{8}_\d{6}_' . $res . '_' . $code . '\.jpg)"/';
        preg_match_all($pattern, $html, $matches);

        if (empty($matches[1])) return;

        $files_found = array_unique($matches[1]);
        sort($files_found);
        
        $files_to_fetch = array_slice($files_found, -$limit); 

        foreach ($files_to_fetch as $nasa_filename) {
            $parts = explode('_', $nasa_filename);
            if (count($parts) < 2) continue;
            
            $dt_str = substr($parts[0], 0, 4) . '-' . substr($parts[0], 4, 2) . '-' . substr($parts[0], 6, 2) . ' ' . 
                      substr($parts[1], 0, 2) . ':' . substr($parts[1], 2, 2) . ':' . substr($parts[1], 4, 2);
            
            $ts = strtotime($dt_str . ' UTC');
            $file_url = $base_url . '/' . $nasa_filename;
            
            $this->download_and_store($channel, $file_url, $ts);

            // POLITE THROTTLING: 0.5 Sekunden Pause zwischen Massen-Downloads
            usleep(500000); 
        }
    }

    private function process_single_latest($channel) {
        $map = ['171' => '0171', '193' => '0193', '304' => '0304', 'HMI' => 'HMIBC'];
        $code = $map[$channel] ?? '0171';
        $source_url = "https://sdo.gsfc.nasa.gov/assets/img/latest/latest_2048_{$code}.jpg";

        $channel_dir = $this->cache_dir . '/' . $channel;
        if (!file_exists($channel_dir)) {
            if (!wp_mkdir_p($channel_dir)) return 'ERR_FS_PERM_DIR';
        }
        
        $temp_file = $channel_dir . '/temp_latest_' . uniqid() . '.jpg';

        $data = $this->fetch_raw_data($source_url);
        if (!$data || empty($data['body'])) return 'ERR_DL_NASA_BLOCKED';

        if (file_put_contents($temp_file, $data['body']) === false) {
            return 'ERR_FS_WRITE_BODY';
        }

        $ts = !empty($data['last_modified']) ? strtotime($data['last_modified']) : time();

        return $this->download_and_store($channel, $temp_file, $ts, true);
    }

    private function download_and_store($channel, $source, $timestamp, $is_temp_file = false) {
        $channel_dir = $this->cache_dir . '/' . $channel;
        if (!file_exists($channel_dir)) wp_mkdir_p($channel_dir);

        $target_filename = $timestamp . '.jpg';
        $target_path = $channel_dir . '/' . $target_filename;
        
        if (file_exists($target_path)) {
            if ($is_temp_file) @unlink($source);
            $this->update_manifest_data($channel, $target_filename, $timestamp);
            return 'SKIP_EXISTS';
        }

        if (!$is_temp_file) {
            $temp_dl = $channel_dir . '/temp_' . md5($source) . '.jpg';
            
            $data = $this->fetch_raw_data($source);
            if (!$data || empty($data['body'])) return 'ERR_DL_ARCHIVE';

            if (file_put_contents($temp_dl, $data['body']) === false) {
                return 'ERR_FS_WRITE_ARCHIVE';
            }
            
            $source = $temp_dl;
            $is_temp_file = true;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $source);
        finfo_close($finfo);

        if ($mime !== 'image/jpeg' && $mime !== 'image/jpg') {
            @unlink($source);
            return 'ERR_SECURITY_MIME_REJECTED';
        }

        if (filesize($source) < 10000) { @unlink($source); return 'ERR_SIZE'; }

        rename($source, $target_path);
        $this->update_manifest_data($channel, $target_filename, $timestamp);
        return 'SAVED';
    }

    private function update_manifest_data($channel, $filename, $timestamp) {
        $manifest_file = $this->cache_dir . '/' . $channel . '/timeline.json';
        $data = [];
        if (file_exists($manifest_file)) {
            $data = json_decode(file_get_contents($manifest_file), true) ?: [];
        }

        $exists = false;
        foreach($data as $entry) { if($entry['ts'] == $timestamp) { $exists = true; break; } }

        if (!$exists) {
            $site_url = get_site_url();
            $file_url = $site_url . '/wp-content/uploads/vgt-solar-cache/' . $channel . '/' . $filename; 
            $data[] = [
                'ts' => $timestamp,
                'time' => gmdate('H:i', $timestamp) . ' UTC', 
                'date' => gmdate('d.m', $timestamp),
                'url' => $file_url,
                'file' => $filename 
            ];
        }

        usort($data, function($a, $b) { return $a['ts'] - $b['ts']; });

        if (count($data) > self::MAX_SLOTS) {
            $remove_count = count($data) - self::MAX_SLOTS;
            $remove_entries = array_slice($data, 0, $remove_count);
            $data = array_slice($data, $remove_count);
            foreach ($remove_entries as $item) {
                $p = $this->cache_dir . '/' . $channel . '/' . $item['file'];
                if (file_exists($p)) @unlink($p);
            }
        }
        file_put_contents($manifest_file, wp_json_encode($data));
    }

    // --- HANDLERS ---

    public function handle_manual_sync() {
        if (!current_user_can('manage_options')) wp_send_json_error('PERM_FAIL');
        $log = $this->execute_deep_sync(18); 
        wp_send_json_success(['log' => $log, 'time' => date('H:i:s')]);
    }

    public function handle_hard_reset() {
        if (!current_user_can('manage_options')) wp_send_json_error('PERM_FAIL');
        
        foreach($this->channels as $channel) {
            $m = $this->cache_dir . '/' . $channel . '/timeline.json';
            if(file_exists($m)) @unlink($m);
        }

        $log = $this->execute_deep_sync(40); 
        wp_send_json_success(['log' => $log, 'time' => date('H:i:s')]);
    }

    public function handle_frontend_refresh() {
        $this->execute_pipeline(); 
        wp_send_json_success(['message' => 'SYNC_COMPLETE']);
    }

    public function register_admin_panel() {
        add_menu_page('VGT Solar', 'VGT Uplink', 'manage_options', 'vgt-solar-dashboard', [$this, 'render_dashboard_ui'], 'dashicons-superhero', 3);
    }

    public function render_dashboard_ui() {
        $last_sync = get_option('vgt_last_sync', 'NEVER');
        $last_sync_fmt = ($last_sync === 'NEVER') ? 'PENDING' : date('d.m.Y H:i:s', $last_sync);
        
        $cache_size = 0;
        if(file_exists($this->cache_dir)) {
            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->cache_dir)) as $file) {
                if ($file->isFile()) {
                    $cache_size += $file->getSize();
                }
            }
        }
        $cache_size_mb = number_format($cache_size / 1048576, 2);
        
        if (defined('VGT_PLUGIN_PATH')) {
            include VGT_PLUGIN_PATH . 'includes/admin/dashboard-view.php';
        } else {
            echo '<div class="wrap"><h2>VGT Solar Node - Dashboard missing (VGT_PLUGIN_PATH not defined)</h2></div>';
        }
    }
}
?>