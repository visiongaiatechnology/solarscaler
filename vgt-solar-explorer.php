<?php
/**
 * Plugin Name: VGT Solar Explorer [DIAMANT EDITION]
 * Description: High-Fidelity Solar Data Visualization System. Powered by VGT Shadow Net.
 * Version: 4.1.0
 * Author: VisionGaiaTechnology
 * Requires PHP: 8.0
 * License:     AGPL-3.0-or-later
 * License URI: https://www.gnu.org/licenses/agpl-3.0.html
 * * VGT OMEGA PROTOCOL: This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or (at your option) 
 * any later version.
 */

if (!defined('ABSPATH')) exit;

define('VGT_SOLAR_VERSION', '4.1.0');
define('VGT_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('VGT_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once VGT_PLUGIN_PATH . 'includes/vgt-backend.php';

class VGTSolarBoot {
    public function __construct() {
        $backend = new VGT_Backend();
        
        register_activation_hook(__FILE__, [$this, 'system_init']);
        add_action('wp_enqueue_scripts', [$this, 'inject_vgt_engine']);
        add_shortcode('vgt_solar_engine', [$this, 'render_mount_point']);
        add_action('admin_enqueue_scripts', [$this, 'inject_admin_assets']);
        
        // CRON ARCHITECTURE
        add_filter('cron_schedules', [$this, 'add_cron_intervals']);
        // FIX: Self-Healing Cron Check on Init (Ensure Cron exists always)
        add_action('init', [$this, 'verify_cron_integrity']);

        // REWRITE & PROXY LOGIC (SECURE)
        add_action('init', [$this, 'register_storage_rewrites']);
        add_filter('query_vars', [$this, 'register_query_vars']);
        add_action('template_redirect', [$this, 'handle_storage_proxy']);
    }

    /**
     * VGT REWRITE LOGIC
     * Maps /storage/vgt-solar-cache/X to the internal upload directory
     */
    public function register_storage_rewrites() {
        add_rewrite_rule(
            '^storage/vgt-solar-cache/(.*)$',
            'index.php?vgt_storage_file=$matches[1]',
            'top'
        );
    }

    public function register_query_vars($vars) {
        $vars[] = 'vgt_storage_file';
        return $vars;
    }

    /**
     * SECURITY CORE: STRICT JAIL PROXY
     * Prevents Directory Traversal via Realpath Validation
     */
    public function handle_storage_proxy() {
        $file_req = get_query_var('vgt_storage_file');
        
        if ($file_req) {
            $upload = wp_upload_dir();
            $base_dir = $upload['basedir'] . '/vgt-solar-cache';
            
            // 1. Construct Target Path
            $requested_path = $base_dir . '/' . $file_req;

            // 2. Resolve Path (Canonize) - Returns FALSE if file does not exist
            $real_path = realpath($requested_path);
            $real_base = realpath($base_dir);

            // 3. SECURITY CHECK: JAIL VALIDATION
            // Ensure the resolved path starts with the resolved base directory
            if ($real_path && $real_base && strpos($real_path, $real_base) === 0 && file_exists($real_path) && !is_dir($real_path)) {
                
                // Content Type Detection
                $ext = strtolower(pathinfo($real_path, PATHINFO_EXTENSION));
                $mime = 'application/octet-stream';
                if ($ext === 'jpg' || $ext === 'jpeg') $mime = 'image/jpeg';
                if ($ext === 'json') $mime = 'application/json';

                // Headers for High-Performance Caching
                header('Content-Type: ' . $mime);
                header('Content-Length: ' . filesize($real_path));
                header('Cache-Control: public, max-age=31536000'); // 1 Year Cache
                header('Access-Control-Allow-Origin: *'); // CORS allowed
                
                // Serve File
                readfile($real_path);
                exit; 
            } else {
                // Access Denied or File Not Found
                status_header(404);
                echo 'VGT SECURITY: FILE NOT FOUND OR ACCESS DENIED';
                exit;
            }
        }
    }

    public function add_cron_intervals($schedules) {
        $schedules['vgt_10min'] = [
            'interval' => 600, // 10 Minutes
            'display'  => __('VGT High-Frequency (10min)')
        ];
        return $schedules;
    }

    /**
     * SELF-HEALING CRON LOGIC
     * Runs on every init to ensure the heartbeat is alive.
     */
    public function verify_cron_integrity() {
        if (!wp_next_scheduled('vgt_solar_cron_update')) {
            wp_schedule_event(time(), 'vgt_10min', 'vgt_solar_cron_update');
        }
    }

    public function system_init() {
        $upload = wp_upload_dir();
        $cache_dir = $upload['basedir'] . '/vgt-solar-cache';
        
        if (!file_exists($cache_dir)) {
            wp_mkdir_p($cache_dir);
            
            // SERVER POWER BOOST
            $htaccess_content = "Options -Indexes\n";
            $htaccess_content .= "<IfModule mod_expires.c>\n";
            $htaccess_content .= "ExpiresActive On\n";
            $htaccess_content .= "ExpiresByType image/jpeg \"access plus 1 year\"\n";
            $htaccess_content .= "</IfModule>\n";
            
            file_put_contents($cache_dir . '/.htaccess', $htaccess_content);
        }

        $this->register_storage_rewrites();
        flush_rewrite_rules();
        
        $this->verify_cron_integrity();
    }

    public function inject_admin_assets($hook) {
        if ($hook === 'toplevel_page_vgt-solar-dashboard') {
            wp_enqueue_style('vgt-admin-css', VGT_PLUGIN_URL . 'assets/css/vgt-admin.css', [], VGT_SOLAR_VERSION);
            wp_enqueue_script('vgt-admin-js', VGT_PLUGIN_URL . 'assets/js/vgt-admin.js', [], VGT_SOLAR_VERSION, true);
        }
    }

    public function inject_vgt_engine() {
        wp_enqueue_script('vgt-tailwind', 'https://cdn.tailwindcss.com', [], null, false);
        wp_enqueue_script('vgt-solar-core', VGT_PLUGIN_URL . 'assets/js/vgt-core.js', [], VGT_SOLAR_VERSION, true);
        wp_enqueue_style('vgt-solar-css', VGT_PLUGIN_URL . 'assets/css/vgt-diamond.css', [], VGT_SOLAR_VERSION);

        wp_localize_script('vgt-solar-core', 'VGT_CONFIG', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('vgt_solar_ops'),
            'default_channel' => '171'
        ]);
    }

    public function render_mount_point() {
        ob_start();
        include VGT_PLUGIN_PATH . 'includes/dashboard/layout.php';
        return ob_get_clean();
    }
}

new VGTSolarBoot();
?>