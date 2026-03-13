<div class="glass-panel rounded-2xl p-4 flex flex-col gap-2 relative overflow-hidden aspect-square group">
    <div class="flex justify-between items-start z-20">
        <h3 class="text-xs font-bold text-aurora-text uppercase tracking-widest flex items-center gap-2">
            <i class="ph-fill ph-sparkle text-aurora"></i> Polarlichtoval
        </h3>
        <div class="flex bg-black/50 rounded p-0.5 border border-white/10">
            <button onclick="switchAurora('north')" id="btn-aurora-n" class="px-2 py-0.5 text-[9px] font-bold uppercase rounded bg-aurora/20 text-aurora transition-all">Nord</button>
            <button onclick="switchAurora('south')" id="btn-aurora-s" class="px-2 py-0.5 text-[9px] font-bold uppercase rounded text-slate-500 hover:text-white transition-all">Süd</button>
        </div>
    </div>
    
    <div class="absolute inset-0 z-0 flex items-center justify-center overflow-hidden">
        <img id="aurora-img" src="" class="w-full h-full object-cover opacity-80 group-hover:opacity-100 transition-all duration-500 scale-105" alt="Aurora Map">
    </div>
    
    <div class="relative z-10 mt-auto bg-black/60 backdrop-blur-md p-3 rounded-xl border border-white/10 shadow-lg">
         <div class="flex justify-between items-end">
             <div>
                <div class="text-[9px] text-slate-400 uppercase">Hemispheric Power</div>
                <div class="text-xl font-mono font-bold text-white" id="aurora-power">-- GW</div>
             </div>
             <div class="text-right">
                 <div class="text-[9px] text-slate-400 uppercase">Sichtbarkeit</div>
                 <div class="text-[10px] text-aurora font-bold" id="aurora-desc">Berechnung...</div>
             </div>
         </div>
    </div>
</div>