<div class="glass-panel rounded-2xl p-5 border-l-4 border-l-red-500 relative overflow-hidden">
    <div class="flex justify-between items-center mb-4 relative z-10">
       <span class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Flare Aktivität (72h)</span>
       <i class="ph-fill ph-fire text-red-500 animate-pulse"></i>
    </div>
    
    <div class="grid grid-cols-2 gap-4 relative z-10">
        <!-- MAX FLARE -->
        <div class="flex flex-col">
            <span class="text-[9px] text-slate-500 uppercase mb-1">Maximal</span>
            <div id="flare-history-max" class="text-2xl font-display font-bold text-white hud-text">--</div>
        </div>

        <!-- LAST FLARE -->
        <div class="flex flex-col text-right border-l border-white/10 pl-4">
            <span class="text-[9px] text-slate-500 uppercase mb-1">Letzter Event</span>
            <div id="flare-history-last-class" class="text-xl font-display font-bold text-white">--</div>
            <div id="flare-history-last-time" class="text-[9px] font-mono text-slate-400 mt-1">--:--</div>
        </div>
    </div>

    <!-- GRAPHIC BG -->
    <div class="absolute -right-4 -bottom-4 opacity-10 pointer-events-none">
        <i class="ph-fill ph-chart-line-up text-6xl text-white"></i>
    </div>
</div>