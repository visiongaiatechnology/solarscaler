<?php
/**
 * VG-SOLAR: DASHBOARD MAIN LAYOUT
 * VERSION: 3.8.0 (FLARE MODULE ADDED)
 */
if (!defined('ABSPATH')) exit;
$template_path = plugin_dir_path(__FILE__);
?>

<div id="vg-sun-root">
    <!-- Background Layer -->
    <div class="absolute inset-0 pointer-events-none z-0 w-full h-full">
        <div class="scanlines"></div>
        <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/stardust.png')] opacity-20"></div>
        <div class="absolute top-[-20%] left-1/2 -translate-x-1/2 w-[80vw] h-[80vw] bg-sun-500/5 rounded-full blur-[150px]"></div>
    </div>

    <?php include $template_path . 'header.php'; ?>

    <main class="flex-grow p-4 md:p-6 relative z-10 max-w-[1800px] mx-auto w-full flex flex-col gap-6">
        <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 w-full">
            
            <!-- LEFT -->
            <div class="xl:col-span-4 flex flex-col gap-6">
                <?php include $template_path . 'visuals/sun-imager.php'; ?>
                <?php include $template_path . 'visuals/aurora-scope.php'; ?>
            </div>

            <!-- CENTER -->
            <div class="xl:col-span-5 flex flex-col gap-6">
                <?php include $template_path . 'telemetry/scales.php'; ?>
                
                <div class="flex flex-col h-[380px]">
                    <?php include $template_path . 'telemetry/graphs.php'; ?>
                </div>
                
                <?php include $template_path . 'telemetry/forecast.php'; ?>
            </div>

            <!-- RIGHT -->
            <div class="xl:col-span-3 flex flex-col gap-6">
                <?php include $template_path . 'data/kp-index.php'; ?>
                <?php include $template_path . 'data/dst-index.php'; ?>
                <?php include $template_path . 'data/wind-mag.php'; ?>
                <!-- NEW FLARE MODULE -->
                <?php include $template_path . 'data/flare-history.php'; ?>
            </div>
        </div>
    </main>

    <?php include $template_path . 'footer.php'; ?>
</div>