<?php if (!defined('ABSPATH')) exit; ?>

<div class="wrap">
    <div class="vgt-dashboard">
        
        <!-- HEADER -->
        <header class="vgt-header">
            <div class="vgt-title">
                <h1>VGT ORBITAL <span>UPLINK</span></h1>
                <span class="vgt-subtitle">VERSION <?php echo VGT_SOLAR_VERSION; ?> // OMEGA ARCHITECTURE</span>
            </div>
            <div class="vgt-status-badge">
                SYSTEM ACTIVE
            </div>
        </header>

        <!-- TELEMETRY GRID -->
        <div class="vgt-metrics">
            <div class="vgt-metric-card">
                <span class="vgt-label">CACHE UTILIZATION</span>
                <span class="vgt-value"><?php echo $cache_size_mb; ?> MB</span>
            </div>
            <div class="vgt-metric-card">
                <span class="vgt-label">LAST ORBIT SYNC</span>
                <span class="vgt-value"><?php echo $last_sync_fmt; ?></span>
            </div>
            <div class="vgt-metric-card">
                <span class="vgt-label">TIMELINE HORIZON</span>
                <span class="vgt-value">12 HOURS</span>
            </div>
            <div class="vgt-metric-card">
                <span class="vgt-label">SYNC INTERVAL</span>
                <span class="vgt-value success">10 MIN</span>
            </div>
        </div>

        <!-- MAIN CONTROL ROOM -->
        <div class="vgt-control-room">
            
            <!-- LEFT: TERMINAL -->
            <div class="vgt-terminal-wrapper">
                <div class="vgt-terminal-header">
                    // VGT_SECURE_SHELL_v4.5 // ROOT ACCESS GRANTED
                </div>
                <div id="vgt-console-output">
                    <span class="console-line info">> WAITING FOR COMMAND INPUT...</span>
                </div>
            </div>

            <!-- RIGHT: CONTROLS -->
            <aside class="vgt-actions">
                <div>
                    <h3 style="color:white; margin:0 0 10px 0; font-size: 0.9rem;">PROTOCOL SELECTION</h3>
                    <p class="vgt-info-text">
                        Select sync depth. Standard retrieves latest data. Hard Reset rebuilds timeline (6h).
                    </p>
                </div>

                <!-- STANDARD SYNC -->
                <button id="vgt-manual-sync-btn" class="vgt-btn-large">
                    STANDARD SYNC (LATEST)
                </button>

                <!-- HARD RESET (NEW) -->
                <button id="vgt-hard-reset-btn" class="vgt-btn-large" style="border-color: #ff0044; color: #ff0044;">
                    HARD RESET (FETCH 6H)
                </button>

                <div style="margin-top:auto;">
                    <p class="vgt-info-text" style="opacity: 0.5;">
                        SERVER TIME: <?php echo date('H:i:s T'); ?><br>
                        PHP MEMORY: <?php echo size_format(memory_get_usage()); ?>
                    </p>
                </div>
            </aside>

        </div>
    </div>
</div>