<?php
/**
 * Plugin Name: VisionGaia Solar Command Center (OMEGA)
 * Description: High-Fidelity Solar Telemetry. Modular. Proxied. DSGVO-Compliant.
 * Version: 3.7.0 (OMEGA - ISOLATION FIX)
 * Author: VisionGaiaTechnology
 */

if (!defined('ABSPATH')) exit;

class VisionGaiaSolarCommand {

    private $cache_time = 180; 
    private $upload_dir;

    public function __construct() {
        add_shortcode('visiongaia_solar_command', array($this, 'render_dashboard'));
        add_action('wp_enqueue_scripts', array($this, 'register_assets'));
        add_action('rest_api_init', array($this, 'register_rest_routes'));
        
        $upload_info = wp_upload_dir();
        $this->upload_dir = $upload_info['basedir'] . '/visiongaia_cache';
        if (!file_exists($this->upload_dir)) {
            wp_mkdir_p($this->upload_dir);
        }
    }

    public function register_assets() {
        wp_register_script('vg-tailwind', 'https://cdn.tailwindcss.com', [], null, false);
        wp_register_script('vg-phosphor', 'https://unpkg.com/@phosphor-icons/web', [], null, false);
        wp_register_script('vg-chartjs', 'https://cdn.jsdelivr.net/npm/chart.js', [], null, false);
        wp_register_style('vg-solar-css', plugins_url('assets/css/vg-solar-skin.css', __FILE__), [], '3.7.0');
        wp_register_script('vg-solar-js', plugins_url('assets/js/vg-solar-engine.js', __FILE__), ['jquery', 'vg-chartjs'], '3.7.0', true);
        
        wp_localize_script('vg-solar-js', 'VG_SOLAR', [
            'api_root' => esc_url_raw(rest_url('visiongaia/v1/')),
            'nonce' => wp_create_nonce('wp_rest')
        ]);
    }

    public function render_dashboard() {
        wp_enqueue_script('vg-tailwind');
        wp_enqueue_script('vg-phosphor');
        wp_enqueue_script('vg-chartjs');
        wp_enqueue_style('vg-solar-css');
        wp_enqueue_script('vg-solar-js');
        
        // WICHTIG: Tailwind Konfiguration injizieren um Theme-Konflikte zu lösen
        $this->inject_tailwind_config();

        ob_start();
        $path = plugin_dir_path(__FILE__) . 'templates/';
        include $path . 'dashboard-main.php';
        return ob_get_clean();
    }

    private function inject_tailwind_config() {
        ?>
        <script>
            // VISIONGAIATECHNOLOGY OMEGA ISOLATION PROTOCOL
            tailwind.config = {
                important: '#vg-sun-root', 
                corePlugins: { 
                    preflight: false // DEAKTIVIERT DEN GLOBALEN RESET (FIX FÜR HEADER/FOOTER)
                },
                theme: {
                    extend: {
                        fontFamily: {
                            sans: ['Inter', 'sans-serif'],
                            mono: ['JetBrains Mono', 'monospace'],
                            display: ['Space Grotesk', 'sans-serif'],
                        },
                        colors: {
                            sun: { 400: '#facc15', 500: '#eab308', 600: '#ca8a04', glow: '#f59e0b', 900: '#713f12' },
                            void: '#050505',
                            aurora: '#34d399',
                            mag: '#22d3ee',
                        },
                        animation: { 'scan': 'scan 8s linear infinite' },
                        keyframes: {
                            scan: { '0%': { transform: 'translateY(-100%)' }, '100%': { transform: 'translateY(100%)' } }
                        }
                    }
                }
            }
        </script>
        <?php
    }

    public function register_rest_routes() {
        register_rest_route('visiongaia/v1', '/data/(?P<type>[a-zA-Z0-9_-]+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_proxied_data'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('visiongaia/v1', '/image/(?P<type>[a-zA-Z0-9_-]+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_proxied_image'),
            'permission_callback' => '__return_true'
        ));
    }

    public function get_proxied_data($data) {
        $type = $data['type'];
        $transient_key = 'vg_data_' . $type;
        $cached = get_transient($transient_key);

        if ($cached !== false) return rest_ensure_response(json_decode($cached));

        $urls = [
            'plasma' => 'https://services.swpc.noaa.gov/products/solar-wind/plasma-2-hour.json',
            'mag'    => 'https://services.swpc.noaa.gov/products/solar-wind/mag-2-hour.json',
            'xray'   => 'https://services.swpc.noaa.gov/json/goes/primary/xrays-3-day.json',
            'proton' => 'https://services.swpc.noaa.gov/json/goes/primary/integral-protons-6-hour.json',
            'kp'     => 'https://services.swpc.noaa.gov/products/noaa-planetary-k-index.json',
            'ssn'    => 'https://services.swpc.noaa.gov/json/solar-cycle/observed-solar-cycle-indices.json',
            'dst'    => 'https://services.swpc.noaa.gov/products/kyoto-dst.json'
        ];

        if (!isset($urls[$type])) return new WP_Error('404');
        $response = wp_remote_get($urls[$type], ['timeout' => 15]);
        if (is_wp_error($response)) return new WP_Error('500');

        $body = wp_remote_retrieve_body($response);
        set_transient($transient_key, $body, $this->cache_time);
        return rest_ensure_response(json_decode($body));
    }

    public function get_proxied_image($data) {
        $type = $data['type'];
        $filename = 'vg_img_' . $type . '.jpg';
        $filepath = $this->upload_dir . '/' . $filename;
        if (file_exists($filepath) && (time() - filemtime($filepath) < $this->cache_time)) {
            $this->serve_image($filepath);
            exit;
        }
        $urls = [
            '193' => 'https://sdo.gsfc.nasa.gov/assets/img/latest/latest_1024_0193.jpg',
            '171' => 'https://sdo.gsfc.nasa.gov/assets/img/latest/latest_1024_0171.jpg',
            '304' => 'https://sdo.gsfc.nasa.gov/assets/img/latest/latest_1024_0304.jpg',
            '131' => 'https://sdo.gsfc.nasa.gov/assets/img/latest/latest_1024_0131.jpg',
            'HMI' => 'https://sdo.gsfc.nasa.gov/assets/img/latest/latest_1024_HMII.jpg',
            'aurora_n' => 'https://services.swpc.noaa.gov/images/animations/ovation/north/latest.jpg',
            'aurora_s' => 'https://services.swpc.noaa.gov/images/animations/ovation/south/latest.jpg'
        ];
        if (!isset($urls[$type])) exit;
        $response = wp_remote_get($urls[$type], ['timeout' => 20, 'sslverify' => false]);
        if (is_wp_error($response)) { if(file_exists($filepath)) { $this->serve_image($filepath); exit; } exit; }
        $body = wp_remote_retrieve_body($response);
        file_put_contents($filepath, $body);
        $this->serve_image($filepath);
        exit;
    }

    private function serve_image($filepath) {
        header('Content-Type: image/jpeg');
        header('Content-Length: ' . filesize($filepath));
        header('Cache-Control: public, max-age=' . $this->cache_time);
        readfile($filepath);
    }
}
new VisionGaiaSolarCommand();