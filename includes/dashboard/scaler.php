<?php if (!defined('ABSPATH')) exit; ?>

<div id="vgt-scaler-root" class="w-full h-full relative group flex flex-col">
    
    <!-- ZOOM STAGE -->
    <div id="vgt-sun-stage-container" class="flex-1 relative overflow-hidden flex items-center justify-center cursor-grab active:cursor-grabbing bg-transparent">
        
        <!-- TRANSFORM TARGET -->
        <!-- FIX: Added w-full h-full to prevent 0x0 collapse -->
        <div id="vgt-sun-stage" class="absolute top-0 left-0 w-full h-full flex items-center justify-center pointer-events-none will-change-transform origin-[0_0]">
            
            <!-- LOADER -->
            <div id="vgt-loader" class="absolute inset-0 flex items-center justify-center z-50 pointer-events-none">
                <div class="flex flex-col items-center gap-4 bg-black/80 p-6 rounded-2xl backdrop-blur border border-[#ff4d00]/30 shadow-[0_0_50px_rgba(255,77,0,0.2)]">
                    <div class="w-12 h-12 border-2 border-[#ff4d00] border-t-transparent rounded-full animate-spin"></div>
                    <span class="text-[#ff4d00] text-xs font-mono tracking-[0.3em] animate-pulse">UPLINK ACTIVE</span>
                </div>
            </div>

            <!-- DUAL BUFFER SYSTEM (PING-PONG) -->
            <!-- FIX: Removed 'inset-0'. Using Flexbox centering from parent. -->
            
            <!-- Buffer A -->
            <img id="vgt-buffer-a" src="" alt="Solar Layer A" 
                 class="absolute h-[85vh] w-auto max-w-none object-contain rounded-full shadow-[0_0_100px_rgba(255,77,0,0.15)] transition-opacity duration-300 select-none draggable-none z-10 opacity-0 pointer-events-none">
            
            <!-- Buffer B -->
            <img id="vgt-buffer-b" src="" alt="Solar Layer B" 
                 class="absolute h-[85vh] w-auto max-w-none object-contain rounded-full shadow-[0_0_100px_rgba(255,77,0,0.15)] transition-opacity duration-300 select-none draggable-none z-0 opacity-0 pointer-events-none">

        </div>

    </div>

    <!-- CONTROL DECK -->
    <div class="h-auto py-4 bg-black/80 backdrop-blur-xl border-t border-white/10 flex flex-wrap items-center px-4 md:px-8 gap-4 md:gap-6 z-30 relative shrink-0">
        
        <!-- WAVELENGTHS -->
        <div class="flex flex-col items-center gap-1">
             <span class="text-[0.6rem] text-gray-500 uppercase tracking-widest hidden md:block">Wavelength</span>
             <div class="flex gap-1 bg-white/5 p-1 rounded-lg border border-white/10">
                <button class="vgt-wave-btn active text-xs px-2 py-1 transition-all hover:bg-white/10" data-wave="171">171</button>
                <button class="vgt-wave-btn text-xs px-2 py-1 transition-all hover:bg-white/10" data-wave="193">193</button>
                <button class="vgt-wave-btn text-xs px-2 py-1 transition-all hover:bg-white/10" data-wave="304">304</button>
                <button class="vgt-wave-btn text-xs px-2 py-1 transition-all hover:bg-white/10" data-wave="HMI">HMI</button>
             </div>
        </div>

        <!-- SLIDER & PLAYBACK -->
        <div class="flex-1 flex flex-col gap-2 min-w-[150px]">
            <div class="flex justify-between items-end">
                <span class="text-[0.6rem] text-[#ff4d00] uppercase tracking-widest">History Loop</span>
                <span id="vgt-timeline-date" class="text-xs font-mono text-white">LIVE</span>
            </div>
            
            <div class="flex items-center gap-3">
                <button id="vgt-play-trigger" class="text-gray-400 hover:text-white transition-colors focus:outline-none disabled:opacity-50" title="Start Loop">
                    <svg id="vgt-icon-play" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4l12 6-12 6z"/></svg>
                    <svg id="vgt-icon-pause" class="w-5 h-5 hidden text-[#ff4d00]" fill="currentColor" viewBox="0 0 20 20"><path d="M5 4h3v12H5V4zm7 0h3v12h-3V4z"/></svg>
                </button>

                <input type="range" id="vgt-timeline-slider" min="0" max="100" value="100" disabled
                       class="flex-1 h-1 bg-gray-800 rounded-lg appearance-none cursor-pointer accent-[#ff4d00] hover:accent-white transition-all">
            </div>
        </div>

        <!-- REFRESH -->
        <div class="border-l border-white/10 pl-4 flex flex-col items-center gap-2">
            <button id="vgt-refresh-trigger" class="group relative flex items-center justify-center w-8 h-8 rounded-full border border-white/20 hover:border-[#ff4d00] hover:bg-[#ff4d00]/10 transition-all" title="Force Update">
                <svg class="w-3 h-3 text-gray-400 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
            </button>
            <span id="vgt-refresh-status" class="text-[0.5rem] text-gray-500 uppercase tracking-wider hidden md:block">Update</span>
        </div>
    </div>

    <!-- ZOOM OSD -->
    <div class="absolute top-4 right-4 text-right pointer-events-none z-30 mix-blend-difference">
        <span class="block text-[0.6rem] text-gray-400 uppercase tracking-widest mb-1">ZOOM</span>
        <span id="vgt-zoom-value" class="block text-2xl font-bold text-[#ff4d00] font-mono leading-none">0.85x</span>
    </div>
</div>

<style>
/* Custom Slider Style */
#vgt-timeline-slider::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    width: 12px;
    height: 12px;
    background: #ff4d00;
    border-radius: 50%;
    cursor: pointer;
    box-shadow: 0 0 15px rgba(255, 77, 0, 0.8);
    border: 2px solid #000;
}
#vgt-timeline-slider::-moz-range-thumb {
    width: 12px;
    height: 12px;
    background: #ff4d00;
    border-radius: 50%;
    cursor: pointer;
    box-shadow: 0 0 15px rgba(255, 77, 0, 0.8);
    border: 2px solid #000;
}
</style>