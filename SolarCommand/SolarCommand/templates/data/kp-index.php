<div class="glass-panel rounded-2xl p-5 border-l-4 border-l-sun-500 relative overflow-hidden">
     <div class="absolute right-0 top-0 p-4 opacity-10">
        <i class="ph-fill ph-globe text-6xl text-white"></i>
     </div>
    <div class="flex justify-between items-center mb-2 relative z-10">
        <span class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Kp Index</span>
        <span id="kp-status" class="text-[9px] px-2 py-0.5 bg-slate-800 rounded text-slate-300 font-bold border border-slate-700">WAITING</span>
    </div>
    <div class="flex items-end gap-3 relative z-10">
        <div id="kp-value" class="text-5xl font-display font-bold text-white hud-text">--</div>
        <div class="flex flex-col mb-1.5">
             <span class="text-xs text-slate-500 font-mono">/ 9.0</span>
             <span class="text-[10px] text-sun-500 font-bold" id="g-level-display">G0</span>
        </div>
    </div>
    <div class="scale-bar mt-4 bg-slate-800 relative z-10">
        <div id="kp-bar" class="scale-fill bg-green-500 w-0 h-1.5 rounded"></div>
    </div>
</div>