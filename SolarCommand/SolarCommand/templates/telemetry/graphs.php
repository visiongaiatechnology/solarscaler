<!-- 
    VG-SOLAR: MODULAR GRAPH COMPONENT 
    FIX: Vertikale Höhe reduziert von 340px auf 260px
-->
<div class="glass-panel rounded-2xl p-4 flex flex-col h-[380px] relative overflow-hidden">
    <div class="flex justify-between items-center mb-3 flex-wrap gap-2 relative z-10">
        <div class="flex gap-1.5 p-1 bg-slate-900/80 rounded-lg border border-white/5">
            <button onclick="switchChart('xray')" id="btn-chart-xray" class="px-3 py-1 rounded text-[10px] font-bold uppercase transition-all bg-white/10 text-white shadow-sm">X-Ray</button>
            <button onclick="switchChart('mag')" id="btn-chart-mag" class="px-3 py-1 rounded text-[10px] font-bold uppercase transition-all text-slate-400 hover:text-white">Mag</button>
            <button onclick="switchChart('proton')" id="btn-chart-proton" class="px-3 py-1 rounded text-[10px] font-bold uppercase transition-all text-slate-400 hover:text-white">Proton</button>
            <button onclick="switchChart('wind')" id="btn-chart-wind" class="px-3 py-1 rounded text-[10px] font-bold uppercase transition-all text-slate-400 hover:text-white">Wind</button>
        </div>
        <div class="flex items-center gap-2 text-[9px] text-slate-500 font-mono tracking-widest">
            <span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse shadow-[0_0_5px_#ef4444]"></span> LIVE
        </div>
    </div>

    <!-- Die Chart-Höhe wird nun exakt durch den Parent-Container begrenzt -->
    <div class="relative w-full h-full flex-grow">
        <canvas id="telemetryChart"></canvas>
    </div>
</div>