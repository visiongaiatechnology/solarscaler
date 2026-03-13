<!-- 
    VG-SOLAR: FORECAST & PROBABILITY MODULE 
    Füllt den Raum unterhalb der 380px Graphs
-->
<div class="glass-panel rounded-2xl p-5 flex flex-col gap-4 border-t-2 border-t-aurora/30">
    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest flex justify-between items-center">
        <span>Vorhersage & Wahrscheinlichkeit</span>
        <i class="ph-fill ph-crystal-ball text-aurora"></i>
    </h3>

    <div class="grid grid-cols-3 gap-4">
        <!-- M-Class Probability -->
        <div class="bg-slate-900/40 p-3 rounded-xl border border-white/5 text-center">
            <div class="text-[9px] text-slate-500 uppercase mb-1">M-Class Flare</div>
            <div id="prob-m" class="text-xl font-display font-bold text-orange-400">--%</div>
            <div class="w-full bg-slate-800 h-1 mt-2 rounded-full overflow-hidden">
                <div id="bar-prob-m" class="h-full bg-orange-500 w-0 transition-all duration-1000"></div>
            </div>
        </div>

        <!-- X-Class Probability -->
        <div class="bg-slate-900/40 p-3 rounded-xl border border-white/5 text-center">
            <div class="text-[9px] text-slate-500 uppercase mb-1">X-Class Flare</div>
            <div id="prob-x" class="text-xl font-display font-bold text-red-500">--%</div>
            <div class="w-full bg-slate-800 h-1 mt-2 rounded-full overflow-hidden">
                <div id="bar-prob-x" class="h-full bg-red-600 w-0 transition-all duration-1000"></div>
            </div>
        </div>

        <!-- Proton Event -->
        <div class="bg-slate-900/40 p-3 rounded-xl border border-white/5 text-center">
            <div class="text-[9px] text-slate-500 uppercase mb-1">Proton Event</div>
            <div id="prob-p" class="text-xl font-display font-bold text-purple-400">--%</div>
            <div class="w-full bg-slate-800 h-1 mt-2 rounded-full overflow-hidden">
                <div id="bar-prob-p" class="h-full bg-purple-500 w-0 transition-all duration-1000"></div>
            </div>
        </div>
    </div>

    <div class="flex justify-between items-center bg-aurora/5 p-2 rounded-lg border border-aurora/10">
        <span class="text-[10px] text-aurora font-bold uppercase tracking-tight">Geomagnetische Prognose (24h)</span>
        <span id="geo-forecast" class="text-[10px] text-white font-mono bg-aurora/20 px-2 py-0.5 rounded">STABLE</span>
    </div>
</div>