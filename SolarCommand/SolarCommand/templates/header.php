<?php if (!defined('ABSPATH')) exit; ?>

<header class="border-b border-slate-800 bg-[#050505]/90 backdrop-blur z-50 sticky top-0 shadow-lg shadow-black/50 w-full">
    <div class="flex justify-between items-center px-4 md:px-6 py-3">
        <!-- Logo Area -->
        <a href="/mediacenter" class="flex items-center gap-3 group text-slate-400 hover:text-white transition-colors relative z-10 no-underline">
            <img src="https://visiongaia.de/wp-content/uploads/2025/01/cropped-testbild-1.png" alt="VisionGaia" class="h-8 w-auto group-hover:rotate-180 transition-transform duration-700 drop-shadow-[0_0_8px_rgba(234,179,8,0.5)]">
            <div class="hidden sm:flex flex-col leading-none">
                <span class="text-[10px] font-bold tracking-widest uppercase text-sun-500 mb-0.5 block">VisionGaia</span>
                <span class="text-xs font-medium flex items-center gap-1 group-hover:-translate-x-1 transition-transform">
                    <i class="ph-bold ph-caret-left"></i> Mediacenter
                </span>
            </div>
        </a>
        
        <!-- Center Title & Last Update -->
        <div class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 text-center pointer-events-none w-full max-w-[300px]">
            <h1 class="text-sm md:text-lg font-bold tracking-[0.3em] uppercase text-white m-0 font-display hud-text">SOLAR COMMAND</h1>
            <div class="text-[9px] text-slate-500 uppercase tracking-tighter mt-1">
                Letztes Update: <span id="last-sync-time" class="text-sun-500/80">--:--:--</span>
            </div>
            <div class="text-[9px] text-slate-500 uppercase tracking-tighter mt-1">
                Datenquellen: NOAA SWPC & NASA SDO. Echtzeit-Telemetrie aufbereitet durch VisionGaia Intelligence.
            </div>
        </div>

        <!-- System Controls -->
        <div class="flex items-center gap-4 relative z-20">
            
            <!-- SOLARSCALER BUTTON (NEW) -->
            <a href="/solarscaler" class="hidden md:flex items-center gap-2 bg-slate-900 border border-slate-700 hover:border-sun-500/50 px-3 py-1.5 rounded-md transition-all group overflow-hidden relative no-underline">
                <i class="ph-bold ph-sun-horizon text-slate-400 group-hover:text-sun-400 transition-colors"></i>
                <span class="text-[10px] font-bold text-slate-300 uppercase group-hover:text-white transition-colors">SolarScaler</span>
            </a>

            <!-- Manual Refresh Button -->
            <button id="manual-refresh-btn" onclick="manualRefresh()" class="hidden md:flex items-center gap-2 bg-slate-900 border border-slate-700 hover:border-sun-500/50 px-3 py-1.5 rounded-md transition-all group overflow-hidden relative">
                <i class="ph-bold ph-arrows-clockwise text-slate-400 group-hover:text-sun-400 group-hover:rotate-180 transition-transform duration-500"></i>
                <span id="refresh-text" class="text-[10px] font-bold text-slate-300 uppercase">Synchronisieren</span>
                <div id="refresh-cooldown-overlay" class="absolute inset-0 bg-slate-900/90 flex items-center justify-center translate-y-full transition-transform duration-300">
                    <span id="cooldown-timer" class="text-[10px] font-mono text-sun-500">30s</span>
                </div>
            </button>

            <div class="hidden md:flex flex-col items-end leading-none">
                <span class="text-[9px] text-slate-500 uppercase font-bold tracking-wider">System Zeit (UTC)</span>
                <span id="utc-clock" class="text-xs font-mono text-sun-400">00:00:00</span>
            </div>
        </div>
    </div>
    
    <!-- ALERT TICKER -->
    <div class="bg-sun-900/10 border-t border-b border-sun-500/10 h-6 flex items-center overflow-hidden relative">
        <div class="absolute left-0 bg-sun-500/20 px-2 h-full flex items-center text-[9px] font-bold text-sun-500 z-10">STATUS</div>
        <div class="whitespace-nowrap animate-[marquee_20s_linear_infinite] text-[10px] font-mono text-sun-400/80 px-4 flex gap-8" id="alert-ticker">
            <span>SYSTEM INITIALIZED...</span>
            <span>DATA STREAMS ACTIVE...</span>
            <span>SECURE PROXY ENCRYPTED...</span>
        </div>
    </div>
</header>