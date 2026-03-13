<?php if (!defined('ABSPATH')) exit; ?>

<header class="h-16 bg-black/80 backdrop-blur-md border-b border-white/10 flex justify-between items-center px-8 z-50 relative">
    
    <!-- LEFT: BRANDING -->
    <div class="flex items-center gap-6">
        <a href="/mediacenter" class="text-xs text-gray-500 hover:text-[#ff4d00] transition-colors uppercase tracking-widest flex items-center gap-2 group">
            <span class="group-hover:-translate-x-1 transition-transform">←</span>
            Mediacenter
        </a>
        
        <div class="h-4 w-px bg-white/20"></div>

        <div class="flex flex-col">
            <h1 class="text-lg font-bold italic leading-none m-0">
                SOLAR<span class="text-[#ff4d00]">SCALER</span>
            </h1>
            <span class="text-[0.6rem] tracking-[0.2em] text-gray-500 uppercase">VGT Observatory Module BETA V1.0</span>
        </div>
    </div>

    <!-- RIGHT: TELEMETRY & INFO -->
    <div class="flex items-center gap-4 md:gap-6">
        
        <!-- LEGAL LINKS (Desktop) -->
        <nav class="hidden md:flex items-center gap-4 text-[0.6rem] uppercase tracking-widest text-gray-500">
            <a href="/impressum" class="hover:text-white transition-colors">Impressum</a>
            <a href="/datenschutz" class="hover:text-white transition-colors">Datenschutz</a>
        </nav>

        <div class="h-4 w-px bg-white/20 hidden md:block"></div>

        <!-- SOLAR COMMAND LINK (NEW) -->
        <a href="/sun-livetracker" class="hidden md:block text-[0.65rem] border border-white/20 px-3 py-1 rounded text-gray-400 hover:text-white hover:border-[#ff4d00] hover:bg-[#ff4d00]/10 transition-all uppercase tracking-wider no-underline">
            Solar Command
        </a>

        <!-- DATA RIGHTS -->
        <button id="vgt-copyright-trigger" class="text-[0.65rem] border border-white/20 px-3 py-1 rounded text-gray-400 hover:text-white hover:border-[#ff4d00] hover:bg-[#ff4d00]/10 transition-all uppercase tracking-wider">
            Data Rights
        </button>

        <!-- CLOCK -->
        <div class="text-right hidden md:block min-w-[80px]">
            <span class="block text-[0.6rem] text-gray-500 uppercase tracking-wider">UTC Time</span>
            <span id="vgt-live-time" class="text-sm font-bold text-[#ff4d00] font-mono">--:--:--</span>
        </div>

        <!-- STATUS -->
        <div class="flex items-center gap-2 border border-white/10 px-3 py-1.5 rounded-full bg-white/5">
            <span class="w-2 h-2 rounded-full bg-green-500 shadow-[0_0_10px_#22c55e] animate-pulse"></span>
            <span class="text-xs font-bold tracking-wide hidden sm:inline">ONLINE</span>
        </div>
    </div>
</header>

<!-- COPYRIGHT MODAL -->
<div id="vgt-modal-backdrop" class="fixed inset-0 bg-black/90 backdrop-blur-sm z-[999] hidden flex items-center justify-center opacity-0 transition-opacity duration-300">
    <div id="vgt-modal-content" class="bg-[#111] border border-white/20 p-8 rounded-xl max-w-md w-full shadow-[0_0_50px_rgba(255,77,0,0.1)] transform scale-95 transition-transform duration-300">
        <h3 class="text-xl text-white font-bold mb-4 border-b border-white/10 pb-2 flex justify-between items-center">
            <span>DATA SOURCES</span>
            <span class="text-[#ff4d00] text-xs tracking-widest">LEGAL</span>
        </h3>
        
        <div class="text-gray-400 text-sm space-y-4 font-mono">
            <p>
                Imagery courtesy of <strong class="text-white">NASA/SDO</strong> and the AIA, EVE, and HMI science teams.
            </p>
            <p>
                Processed via <strong class="text-white">JHelioviewer</strong> (ESA/NASA) and VisionGaiaTechnology caching layers.
            </p>
            <div class="grid grid-cols-2 gap-2 mt-4 pt-4 border-t border-white/10">
                <a href="/impressum" class="text-xs text-[#ff4d00] hover:underline">>> IMPRESSUM</a>
                <a href="/datenschutz" class="text-xs text-[#ff4d00] hover:underline">>> DATENSCHUTZ</a>
            </div>
        </div>

        <div class="mt-8 flex justify-end">
            <button id="vgt-modal-close" class="bg-[#ff4d00] hover:bg-[#ff6a00] text-black font-bold py-2 px-6 rounded transition-colors text-xs uppercase tracking-wider">
                ACKNOWLEDGE
            </button>
        </div>
    </div>
</div>