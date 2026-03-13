<div class="glass-panel rounded-2xl p-1 flex flex-col relative overflow-hidden group aspect-square">
    <div class="absolute top-4 left-4 z-20 flex gap-2">
        <span class="bg-black/70 backdrop-blur px-2 py-1 rounded text-[10px] font-bold text-sun-400 border border-sun-500/30">SDO AIA</span>
        <span id="wavelength-label" class="bg-black/70 backdrop-blur px-2 py-1 rounded text-[10px] font-bold text-white border border-white/20">193 Å</span>
    </div>
    
    <div class="absolute bottom-16 left-4 z-20">
        <div class="text-[9px] text-slate-400 uppercase tracking-wider mb-0.5">Sonnenflecken (SSN)</div>
        <div class="text-2xl font-display font-bold text-white flex items-baseline gap-1">
            <span id="sunspot-count">--</span>
        </div>
    </div>

    <!-- Image Container mit Proxy Source (via JS injected) -->
    <div class="relative w-full h-full bg-black rounded-xl overflow-hidden flex items-center justify-center">
        <img id="sun-image" src="" class="w-full h-full object-cover scale-105 group-hover:scale-110 transition-transform duration-[30s] ease-linear" alt="Sun Live">
        <div id="img-loader" class="absolute inset-0 flex items-center justify-center bg-black z-10 transition-opacity duration-500">
            <div class="w-16 h-16 border-2 border-slate-800 border-t-sun-500 rounded-full animate-spin"></div>
        </div>
        <div class="absolute inset-0 bg-gradient-to-b from-transparent via-sun-500/5 to-transparent h-[10%] w-full animate-scan pointer-events-none"></div>
    </div>

    <!-- Controls -->
    <div class="absolute bottom-4 left-1/2 -translate-x-1/2 z-20 flex bg-black/80 backdrop-blur rounded-full p-1 border border-white/10 gap-1 shadow-xl">
        <button onclick="changeView('193')" class="w-8 h-8 rounded-full bg-[#c99534] border border-white/20 hover:scale-110 hover:border-white transition-all shadow-[0_0_10px_#c99534]" title="AIA 193 (Korona)"></button>
        <button onclick="changeView('171')" class="w-8 h-8 rounded-full bg-[#e8c347] border border-white/20 hover:scale-110 hover:border-white transition-all shadow-[0_0_10px_#e8c347]" title="AIA 171 (Ruhig)"></button>
        <button onclick="changeView('304')" class="w-8 h-8 rounded-full bg-[#f26e3f] border border-white/20 hover:scale-110 hover:border-white transition-all shadow-[0_0_10px_#f26e3f]" title="AIA 304 (Filamente)"></button>
        <button onclick="changeView('131')" class="w-8 h-8 rounded-full bg-[#3fa8f2] border border-white/20 hover:scale-110 hover:border-white transition-all shadow-[0_0_10px_#3fa8f2]" title="AIA 131 (Flares)"></button>
        <button onclick="changeView('HMI')" class="w-8 h-8 rounded-full bg-[#d66b22] border border-white/20 hover:scale-110 hover:border-white transition-all shadow-[0_0_10px_#d66b22] flex items-center justify-center" title="HMI Magnetogram">
            <i class="ph-bold ph-magnet text-black text-xs"></i>
        </button>
    </div>
</div>