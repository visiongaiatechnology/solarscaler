<?php if (!defined('ABSPATH')) exit; ?>

<!-- VGT IMMERSIVE VIEWPORT -->
<!-- FIX: Removed h-screen, added h-[85vh] and max/min constraints. Added max-w-full. -->
<div id="vgt-solar-wrapper" class="relative w-full h-[85vh] min-h-[900px] max-h-screen bg-[#050505] text-white overflow-hidden flex flex-col font-mono select-none max-w-full rounded-xl shadow-2xl border border-white/10 mx-auto">
    
    <!-- BACKGROUND GRID (CSS Rendered) -->
    <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI0MCIgaGVpZ2h0PSI0MCIgdmlld0JveD0iMCAwIDQwIDQwIj48ZyBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxnIGZpbGw9IiMzMzMiIGZpbGwtb3BhY2l0eT0iMC4xIj48cGF0aCBkPSJNMCAwaDQwdjQwSDBWMHptMjAgMjBoMjB2MjBIMjAyMHoiLz48cGF0aCBkPSJNMCAwaDIwdjIwSDBWMHptMjAgMjBoMjB2MjBIMjAyMHoiLz48L2c+PC9nPjwvc3ZnPg==')] opacity-20 pointer-events-none z-0"></div>

    <!-- HEADER -->
    <?php include VGT_PLUGIN_PATH . 'includes/dashboard/header.php'; ?>

    <!-- MAIN STAGE -->
    <main class="flex-1 relative z-10 flex items-center justify-center bg-radial-gradient overflow-hidden">
        <?php include VGT_PLUGIN_PATH . 'includes/dashboard/scaler.php'; ?>
    </main>

</div>