<div class="glass-panel rounded-2xl p-5">
    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4 border-b border-slate-700 pb-2">
        Interplanetare Daten
    </h3>
    
    <div class="flex flex-col gap-5">
        <!-- Bt -->
        <div>
            <div class="flex justify-between items-end mb-1">
                <span class="text-[10px] text-slate-500 uppercase">Bt (Total)</span>
                <div class="flex items-baseline gap-1">
                    <span id="val-bt" class="font-mono font-bold text-white text-lg">--</span>
                    <span class="text-[9px] text-slate-600">nT</span>
                </div>
            </div>
            <div class="w-full bg-slate-800 h-1.5 rounded-full overflow-hidden">
                <div id="bar-bt" class="h-full bg-mag w-0 transition-all duration-1000"></div>
            </div>
        </div>

        <!-- Bz -->
        <div>
            <div class="flex justify-between items-end mb-1">
                <span class="text-[10px] text-slate-500 uppercase">Bz (Süd/Nord)</span>
                <div class="flex items-baseline gap-1">
                    <span id="val-bz" class="font-mono font-bold text-white text-lg">--</span>
                    <span class="text-[9px] text-slate-600">nT</span>
                </div>
            </div>
            <div class="w-full bg-slate-800 h-1.5 rounded-full overflow-hidden flex relative">
                <div class="absolute left-1/2 top-0 bottom-0 w-[1px] bg-white/20 z-10"></div>
                <div class="w-1/2 h-full flex justify-end">
                    <div id="bar-bz-neg" class="h-full bg-red-500 w-0 transition-all duration-1000"></div>
                </div>
                <div class="w-1/2 h-full flex justify-start">
                    <div id="bar-bz-pos" class="h-full bg-green-500 w-0 transition-all duration-1000"></div>
                </div>
            </div>
        </div>

        <!-- Wind -->
        <div>
            <div class="flex justify-between items-end mb-1">
                <span class="text-[10px] text-slate-500 uppercase">Wind Geschw.</span>
                <div class="flex items-baseline gap-1">
                    <span id="val-wind-display" class="font-mono font-bold text-white text-lg">--</span>
                    <span class="text-[9px] text-slate-600">km/s</span>
                </div>
            </div>
            <div class="w-full bg-slate-800 h-1.5 rounded-full overflow-hidden">
                <div id="bar-wind" class="h-full bg-blue-500 w-0 transition-all duration-1000"></div>
            </div>
        </div>
        
        <!-- Density -->
        <div>
            <div class="flex justify-between items-end mb-1">
                <span class="text-[10px] text-slate-500 uppercase">Dichte</span>
                <div class="flex items-baseline gap-1">
                    <span id="val-dens" class="font-mono font-bold text-white text-lg">--</span>
                    <span class="text-[9px] text-slate-600">p/cm³</span>
                </div>
            </div>
            <div class="w-full bg-slate-800 h-1.5 rounded-full overflow-hidden">
                <div id="bar-dens" class="h-full bg-sun-500 w-0 transition-all duration-1000"></div>
            </div>
        </div>
    </div>
</div>