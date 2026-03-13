<div class="glass-panel rounded-2xl p-5 border-l-4 border-l-purple-500 relative overflow-hidden">
    <div class="flex justify-between items-center mb-2 relative z-10">
       <span class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Dst Index</span>
       <span id="dst-status" class="text-[9px] px-2 py-0.5 bg-slate-800 rounded text-slate-300 font-bold border border-slate-700">LOADING</span>
    </div>
    
    <div class="flex justify-between items-end mb-1 z-10 relative">
        <div id="dst-value" class="text-3xl font-display font-bold text-white hud-text">--</div>
        <div class="text-[9px] text-slate-500 mb-1 font-mono">nT (0 = Normal)</div>
    </div>

    <!-- Split Bar -->
    <div class="w-full bg-slate-800 h-2 rounded-full overflow-hidden flex relative z-10">
        <div class="w-1/2 h-full flex justify-end border-r border-slate-700">
            <div id="dst-bar-neg" class="h-full bg-red-500 w-0 transition-all duration-1000"></div>
        </div>
        <div class="w-1/2 h-full flex justify-start">
            <div id="dst-bar-pos" class="h-full bg-green-500 w-0 transition-all duration-1000"></div>
        </div>
    </div>
</div>