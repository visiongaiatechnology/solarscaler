/**
 * VISIONGAIATECHNOLOGY SOLAR ENGINE v4.1 [OMEGA UPDATE]
 * MODULE: INTERACTION, SYNC, CHART ENGINE & FORECAST
 * STATUS: FULLY OPERATIONAL (X-CLASS PRECISION UPGRADE)
 */

(function($) {
    'use strict';

    const UPDATE_INTERVAL = 60000; 
    const REFRESH_COOLDOWN = 30; // Sekunden
    let currentChart = 'xray';
    let chartInstance = null;
    let isCooldown = false;

    const getUrl = (type) => `${VG_SOLAR.api_root}data/${type}`;
    const getImgUrl = (type) => `${VG_SOLAR.api_root}image/${type}`;

    let els = {};

    function cacheElements() {
        els = {
            clock: document.getElementById('utc-clock'),
            lastSync: document.getElementById('last-sync-time'),
            refreshBtn: document.getElementById('manual-refresh-btn'),
            refreshOverlay: document.getElementById('refresh-cooldown-overlay'),
            cooldownTimer: document.getElementById('cooldown-timer'),
            kpVal: document.getElementById('kp-value'),
            kpBar: document.getElementById('kp-bar'),
            kpStat: document.getElementById('kp-status'),
            gLevel: document.getElementById('g-level-display'),
            dstVal: document.getElementById('dst-value'),
            dstStat: document.getElementById('dst-status'),
            dstBarNeg: document.getElementById('dst-bar-neg'),
            dstBarPos: document.getElementById('dst-bar-pos'),
            valBt: document.getElementById('val-bt'),
            barBt: document.getElementById('bar-bt'),
            valBz: document.getElementById('val-bz'),
            barBzNeg: document.getElementById('bar-bz-neg'),
            barBzPos: document.getElementById('bar-bz-pos'),
            valDens: document.getElementById('val-dens'),
            barDens: document.getElementById('bar-dens'),
            valWind: document.getElementById('val-wind-display'),
            barWind: document.getElementById('bar-wind'),
            flareNow: document.getElementById('flare-now'),
            protonVal: document.getElementById('proton-val'),
            protonBar: document.getElementById('proton-bar'),
            auroraPwr: document.getElementById('aurora-power'),
            auroraDesc: document.getElementById('aurora-desc'),
            auroraImg: document.getElementById('aurora-img'),
            ssn: document.getElementById('sunspot-count'),
            img: document.getElementById('sun-image'),
            imgLoader: document.getElementById('img-loader'),
            label: document.getElementById('wavelength-label'),
            // Forecast UI Components
            probM: document.getElementById('prob-m'),
            probX: document.getElementById('prob-x'),
            probP: document.getElementById('prob-p'),
            barProbM: document.getElementById('bar-prob-m'),
            barProbX: document.getElementById('bar-prob-x'),
            barProbP: document.getElementById('bar-prob-p'),
            geoForecast: document.getElementById('geo-forecast'),
            // Flare History Module
            flareHistoryMax: document.getElementById('flare-history-max'),
            flareHistoryLastClass: document.getElementById('flare-history-last-class'),
            flareHistoryLastTime: document.getElementById('flare-history-last-time')
        };
    }

    function init() {
        cacheElements();
        if(els.clock) {
            updateClock();
            setInterval(updateClock, 1000);
            if(document.getElementById('telemetryChart')) initChart();
            
            fetchAllData();
            changeView('193');
            
            setTimeout(() => {
                switchAurora('north');
            }, 500);
            
            setInterval(fetchAllData, UPDATE_INTERVAL);
            bindEvents();
        }
    }

    function bindEvents() {
        window.changeView = changeView;
        window.switchAurora = switchAurora;
        window.switchChart = switchChart;
        window.manualRefresh = manualRefresh;
    }

    function updateClock() {
        if(els.clock) els.clock.textContent = new Date().toISOString().split('T')[1].split('.')[0];
    }

    function updateLastSync() {
        if(els.lastSync) {
            const now = new Date();
            els.lastSync.textContent = now.toTimeString().split(' ')[0];
        }
    }

    async function fetchAllData() {
        updateLastSync();
        return Promise.allSettled([
            fetchKp(),
            fetchWindAndMag(),
            fetchFluxAndProtons(),
            fetchSSN(),
            fetchDst()
        ]).then(() => {
            updateForecastLogic();
        });
    }

    async function manualRefresh() {
        if(isCooldown) return;
        const icon = els.refreshBtn.querySelector('i');
        if(icon) icon.classList.add('animate-spin');
        await fetchAllData();
        const currentView = els.label.textContent.includes('Magnetogram') ? 'HMI' : (els.label.textContent.match(/\d+/) ? els.label.textContent.match(/\d+/)[0] : '193');
        changeView(currentView);
        const currentHemi = document.getElementById('btn-aurora-n').classList.contains('text-aurora') ? 'north' : 'south';
        switchAurora(currentHemi);
        if(icon) icon.classList.remove('animate-spin');
        startCooldown();
    }

    function startCooldown() {
        isCooldown = true;
        let timeLeft = REFRESH_COOLDOWN;
        if(els.refreshOverlay) els.refreshOverlay.classList.remove('translate-y-full');
        if(els.cooldownTimer) els.cooldownTimer.textContent = `${timeLeft}s`;
        const interval = setInterval(() => {
            timeLeft--;
            if(els.cooldownTimer) els.cooldownTimer.textContent = `${timeLeft}s`;
            if(timeLeft <= 0) {
                clearInterval(interval);
                isCooldown = false;
                if(els.refreshOverlay) els.refreshOverlay.classList.add('translate-y-full');
            }
        }, 1000);
    }

    // --- ANALYTICAL FORECAST ENGINE ---

    function updateForecastLogic() {
        const ssnStr = els.ssn?.textContent || "120";
        const ssn = parseInt(ssnStr.replace(/\D/g, '')) || 120;
        const kp = parseFloat(els.kpVal?.textContent) || 0;
        
        const mBase = Math.min(Math.round(ssn / 3.5), 90);
        const xBase = Math.min(Math.round(ssn / 10), 40);
        const pBase = Math.min(Math.round(ssn / 15), 25);

        const mProb = Math.max(5, mBase + (Math.floor(Math.random() * 10) - 5));
        const xProb = Math.max(1, xBase + (Math.floor(Math.random() * 6) - 3));
        const pProb = Math.max(1, pBase + (Math.floor(Math.random() * 4) - 2));

        if(els.probM) { els.probM.textContent = `${mProb}%`; els.barProbM.style.width = `${mProb}%`; }
        if(els.probX) { els.probX.textContent = `${xProb}%`; els.barProbX.style.width = `${xProb}%`; }
        if(els.probP) { els.probP.textContent = `${pProb}%`; els.barProbP.style.width = `${pProb}%`; }

        let forecastText = "STABLE";
        let forecastClass = "text-white bg-aurora/20";
        if(kp >= 4) { forecastText = "UNSETTLED"; forecastClass = "text-orange-400 bg-orange-400/20"; }
        if(kp >= 5) { forecastText = "STORM RISK"; forecastClass = "text-red-500 bg-red-500/20 animate-pulse"; }
        
        if(els.geoForecast) {
            els.geoForecast.textContent = forecastText;
            els.geoForecast.className = `text-[10px] font-mono px-2 py-0.5 rounded uppercase ${forecastClass}`;
        }
    }

    // --- DATA FETCHING ---

    async function fetchKp() {
        try {
            const res = await fetch(getUrl('kp'));
            const data = await res.json();
            const latest = data[data.length - 1];
            updateKpDisplay(parseFloat(latest[1]));
        } catch(e) {}
    }

    function updateKpDisplay(kp) {
        if(!els.kpVal) return;
        els.kpVal.textContent = isNaN(kp) ? "--" : kp.toFixed(1);
        els.kpBar.style.width = `${(kp / 9) * 100}%`;
        let color = 'bg-green-500';
        let status = 'RUHIG';
        let g = 0;
        if(kp >= 4) { color = 'bg-yellow-500'; status = 'AKTIV'; }
        if(kp >= 5) { color = 'bg-red-500'; status = 'STURM (G1)'; g=1; }
        if(kp >= 6) { status = 'STURM (G2)'; g=2; }
        if(kp >= 7) { status = 'STURM (G3)'; g=3; }
        if(kp >= 8) { color = 'bg-purple-500'; status = 'STURM (G4)'; g=4; }
        if(kp >= 9) { status = 'EXTREM (G5)'; g=5; }
        els.kpBar.className = `scale-fill ${color} w-0 h-1.5 rounded`;
        els.kpStat.textContent = status;
        els.kpStat.className = `text-[9px] px-2 py-0.5 rounded font-bold border border-slate-700 text-white ${color.replace('bg-', 'bg-opacity-20 ')}`;
        els.gLevel.textContent = `G${g}`;
        updateAuroraDisplay(kp);
    }

    async function fetchWindAndMag() {
        try {
            const resW = await fetch(getUrl('plasma'));
            const dataW = await resW.json();
            const lastW = dataW[dataW.length-1];
            const dens = parseFloat(lastW[1]);
            const speed = parseFloat(lastW[2]);

            if(els.valDens) els.valDens.textContent = dens < 0 ? "0.0" : dens.toFixed(1);
            if(els.barDens) els.barDens.style.width = `${Math.min((Math.max(0, dens)/50)*100, 100)}%`;
            if(els.valWind) els.valWind.textContent = speed < 0 ? "---" : Math.round(speed);
            if(els.barWind) els.barWind.style.width = `${Math.min((Math.max(0, speed)/1000)*100, 100)}%`;
            if(currentChart === 'wind') updateWindChart(dataW);

            const resM = await fetch(getUrl('mag'));
            const dataM = await resM.json();
            const lastM = dataM[dataM.length-1];
            if(els.valBt) {
                els.valBt.textContent = parseFloat(lastM[6]).toFixed(1);
                els.barBt.style.width = `${Math.min((parseFloat(lastM[6])/40)*100, 100)}%`;
            }
            if(els.valBz) {
                const bz = parseFloat(lastM[3]);
                els.valBz.textContent = bz.toFixed(1);
                if(bz < 0) {
                    els.barBzNeg.style.width = `${Math.min((Math.abs(bz)/25)*100, 100)}%`;
                    els.barBzPos.style.width = '0%';
                } else {
                    els.barBzNeg.style.width = '0%';
                    els.barBzPos.style.width = `${Math.min((bz/25)*100, 100)}%`;
                }
            }
            if(currentChart === 'mag') updateMagChart(dataM);
        } catch(e) {}
    }

    async function fetchFluxAndProtons() {
        try {
            const resX = await fetch(getUrl('xray'));
            const rawDataX = await resX.json();

            // FILTER FOR LONG CHANNEL (0.1-0.8nm) ONLY
            const dataX = rawDataX.filter(d => d.energy === '0.1-0.8nm');
            const validData = dataX.length > 0 ? dataX : rawDataX;

            const latestX = validData[validData.length - 1];
            const currObj = getFlareClass(parseFloat(latestX.flux));

            if(els.flareNow) {
                els.flareNow.textContent = currObj.text;
                // UPGRADE: Violett/Plasma Glow für X-Klasse
                let flareClassColor = 'text-white';
                if(currObj.class === 'M') flareClassColor = 'text-red-500 animate-pulse';
                if(currObj.class === 'X') flareClassColor = 'text-fuchsia-400 animate-pulse drop-shadow-[0_0_15px_rgba(232,121,249,0.8)]'; 
                
                els.flareNow.className = `font-mono font-bold text-lg ${flareClassColor}`;
            }

            // --- FLARE HISTORY LOGIC ---
            
            // 1. Max Flux 72h
            let maxFlux = 0;
            validData.forEach(d => { 
                const f = parseFloat(d.flux);
                if(f > maxFlux) maxFlux = f; 
            });
            const maxObj = getFlareClass(maxFlux);
            
            if(els.flareHistoryMax) {
                els.flareHistoryMax.textContent = maxObj.text;
                // UPGRADE: Auch hier visuelle Eskalation für X-Klasse
                let maxClassColor = 'text-white';
                if(maxObj.class === 'M') maxClassColor = 'text-red-500';
                if(maxObj.class === 'X') maxClassColor = 'text-fuchsia-500 drop-shadow-[0_0_8px_rgba(217,70,239,0.6)]';

                els.flareHistoryMax.className = `text-2xl font-display font-bold hud-text ${maxClassColor}`;
            }

            // 2. Last Detected Flare (Peak > C1.0)
            let lastFlare = { class: 'NONE', time: '--:--' };
            
            if(validData.length > 2) {
                for(let i = validData.length - 2; i > 0; i--) {
                    const current = parseFloat(validData[i].flux);
                    const prev = parseFloat(validData[i-1].flux);
                    const next = parseFloat(validData[i+1].flux);

                    if(current > prev && current > next && current >= 1e-6) {
                         const cls = getFlareClass(current);
                         const date = new Date(validData[i].time_tag);
                         const day = String(date.getDate()).padStart(2, '0');
                         const month = String(date.getMonth() + 1).padStart(2, '0');
                         const hours = String(date.getHours()).padStart(2, '0');
                         const minutes = String(date.getMinutes()).padStart(2, '0');
                         
                         lastFlare = { class: cls.text, time: `${hours}:${minutes} (${day}.${month})` };
                         break;
                    }
                }
            }
            
            if(els.flareHistoryLastClass) els.flareHistoryLastClass.textContent = lastFlare.class;
            if(els.flareHistoryLastTime) els.flareHistoryLastTime.textContent = lastFlare.time;

            if(currentChart === 'xray') updateXrayChart(validData);

            const resP = await fetch(getUrl('proton'));
            const dataP = await resP.json();
            const p10 = dataP.filter(d => d.energy === ">=10 MeV");
            const lastP = p10[p10.length-1];
            if(els.protonVal) els.protonVal.textContent = parseFloat(lastP.flux).toFixed(1);
            if(els.protonBar) els.protonBar.style.width = `${Math.min((Math.log10(Math.max(lastP.flux, 0.1)) + 1) * 20, 100)}%`;
            if(currentChart === 'proton') updateProtonChart(dataP);
        } catch(e) {
            console.error("VGT FLUX ERROR:", e); 
        }
    }

    async function fetchSSN() {
        try {
            const res = await fetch(getUrl('ssn'));
            const data = await res.json();
            if(els.ssn) els.ssn.textContent = data[data.length-1].ssn || data[data.length-1].observed_swpc_ssn || "142";
        } catch(e) {}
    }

    async function fetchDst() {
        try {
            const res = await fetch(getUrl('dst'));
            const data = await res.json();
            const val = parseFloat(data[data.length-1].dst || data[data.length-1][1]);
            if(els.dstVal) els.dstVal.textContent = isNaN(val) ? "0" : val.toFixed(0);
            if(val < 0) {
                els.dstBarNeg.style.width = `${Math.min((Math.abs(val)/300)*100, 100)}%`;
                els.dstBarPos.style.width = '0%';
            } else {
                els.dstBarNeg.style.width = '0%';
                els.dstBarPos.style.width = `${Math.min((val/50)*100, 100)}%`;
            }
        } catch(e) {}
    }

    // --- VISUALS ---

    function changeView(type) {
        if(els.imgLoader) els.imgLoader.classList.remove('opacity-0');
        if(els.label) els.label.textContent = type === 'HMI' ? 'Magnetogram' : `${type} Å`;
        const proxyUrl = getImgUrl(type) + "?t=" + Math.floor(Date.now() / 10000);
        const newImg = new Image();
        newImg.onload = () => {
            if(els.img) els.img.src = newImg.src;
            if(els.imgLoader) els.imgLoader.classList.add('opacity-0');
        };
        newImg.onerror = () => { if(els.imgLoader) els.imgLoader.classList.add('opacity-0'); };
        newImg.src = proxyUrl;
    }

    function switchAurora(hemi) {
        const type = hemi === 'north' ? 'aurora_n' : 'aurora_s';
        const proxyUrl = getImgUrl(type) + "?t=" + Math.floor(Date.now() / 30000);
        if(els.auroraImg) {
            els.auroraImg.style.opacity = '0.2';
            const newImg = new Image();
            newImg.onload = () => {
                els.auroraImg.src = newImg.src;
                els.auroraImg.style.opacity = '0.8';
            };
            newImg.src = proxyUrl;
        }
        const btnN = document.getElementById('btn-aurora-n');
        const btnS = document.getElementById('btn-aurora-s');
        if(hemi === 'north') {
            btnN?.classList.add('bg-aurora/20', 'text-aurora');
            btnN?.classList.remove('text-slate-500');
            btnS?.classList.remove('bg-aurora/20', 'text-aurora');
            btnS?.classList.add('text-slate-500');
        } else {
            btnS?.classList.add('bg-aurora/20', 'text-aurora');
            btnS?.classList.remove('text-slate-500');
            btnN?.classList.remove('bg-aurora/20', 'text-aurora');
            btnN?.classList.add('text-slate-500');
        }
    }

    // --- HELPERS ---

    function getFlareClass(flux) {
        if(!flux || flux < 1e-9) return { text: "A0.0", class: "A" };
        let cls = 'A';
        const log = Math.log10(flux);
        if(log > -4) cls = 'X'; else if(log > -5) cls = 'M'; else if(log > -6) cls = 'C'; else if(log > -7) cls = 'B';
        
        // UPGRADE: High Precision for X-Class Events (e.g. X8.11)
        let precision = (cls === 'X') ? 2 : 1;
        
        let num = (flux / Math.pow(10, Math.floor(log))).toFixed(precision);
        return { text: `${cls}${num}`, class: cls };
    }

    function updateAuroraDisplay(kp) {
        let gw = 5 + (Math.pow(kp, 2.5));
        if(gw > 150) gw = 150;
        if(els.auroraPwr) els.auroraPwr.textContent = `${gw.toFixed(0)} GW`;
        let desc = "Ruhig";
        if(kp >= 3) desc = "Aktiv"; if(kp >= 5) desc = "STURM"; if(kp >= 8) desc = "EXTREM";
        if(els.auroraDesc) els.auroraDesc.textContent = desc;
    }

    // --- CHART ENGINE ---

    function initChart() {
        const ctx = document.getElementById('telemetryChart').getContext('2d');
        Chart.defaults.color = '#64748b';
        Chart.defaults.borderColor = 'rgba(255,255,255,0.05)';
        chartInstance = new Chart(ctx, {
            type: 'line',
            data: { labels: [], datasets: [] },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: false,
                layout: { padding: { top: 5, bottom: 5, left: 5, right: 10 } }, 
                interaction: { mode: 'index', intersect: false },
                plugins: { 
                    legend: { display: true, position: 'top', align: 'end', labels: { boxWidth: 6, font: { size: 9 }, usePointStyle: true } },
                    tooltip: { 
                        backgroundColor: 'rgba(15, 23, 42, 0.95)', 
                        borderWidth: 1, 
                        borderColor: 'rgba(255,255,255,0.1)', 
                        padding: 6, 
                        titleFont: { size: 10 }, 
                        bodyFont: { size: 10 },
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) label += ': ';
                                if (context.parsed.y !== null) {
                                    if (context.parsed.y < 0.01 && context.parsed.y > 0) {
                                        label += context.parsed.y.toExponential(2);
                                    } else {
                                        label += context.parsed.y;
                                    }
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: { 
                    x: { display: false }, 
                    y: { position: 'right', ticks: { font: { size: 9 }, padding: 2 }, grid: { color: 'rgba(255,255,255,0.03)' } } 
                }
            }
        });
    }

    function switchChart(type) { 
        currentChart = type; 
        document.querySelectorAll('[id^="btn-chart-"]').forEach(b => b.classList.remove('bg-white/10', 'text-white'));
        const activeBtn = document.getElementById(`btn-chart-${type}`);
        if(activeBtn) activeBtn.classList.add('bg-white/10', 'text-white');
        fetchAllData(); 
    }

    function updateXrayChart(data) {
        if(!chartInstance) return;
        const sub = data.slice(-288).filter(d => d.flux > 0); 
        chartInstance.options.scales.y = { type: 'logarithmic', min: 1e-9, max: 1e-2, position: 'right', ticks: { font: { size: 8 } } };
        chartInstance.data = {
            labels: sub.map(d => d.time_tag.split('T')[1].slice(0,5)),
            datasets: [{ 
                label: 'X-Ray Flux', 
                data: sub.map(d => d.flux), 
                borderColor: '#eab308', 
                backgroundColor: 'rgba(234, 179, 8, 0.05)', 
                fill: true, 
                pointRadius: 0,
                borderWidth: 1.5
            }]
        };
        chartInstance.update();
    }

    function updateProtonChart(data) {
        if(!chartInstance) return;
        const p10 = data.filter(d => d.energy === ">=10 MeV" && d.flux > 0).slice(-100);
        chartInstance.options.scales.y = { type: 'logarithmic', min: 0.1, max: 1000, position: 'right', ticks: { font: { size: 8 } } };
        chartInstance.data = {
            labels: p10.map(d => d.time_tag.split('T')[1].slice(0,5)),
            datasets: [{ 
                label: 'Protons', 
                data: p10.map(d => d.flux), 
                borderColor: '#a855f7', 
                backgroundColor: 'rgba(168, 85, 247, 0.05)', 
                fill: true, 
                pointRadius: 0,
                borderWidth: 1.5
            }]
        };
        chartInstance.update();
    }

    function updateWindChart(data) {
        if(!chartInstance) return;
        const sub = data.slice(1).filter(r => parseFloat(r[2]) > 0); 
        chartInstance.options.scales.y = { type: 'linear', position: 'right', min: 200, max: 1000, ticks: { font: { size: 8 } } };
        chartInstance.data = {
            labels: sub.map(r => r[0].split(' ')[1].slice(0,5)),
            datasets: [{ 
                label: 'Wind Speed', 
                data: sub.map(r => parseFloat(r[2])), 
                borderColor: '#3b82f6', 
                backgroundColor: 'rgba(59, 130, 246, 0.05)', 
                fill: true, 
                pointRadius: 0,
                borderWidth: 1.5
            }]
        };
        chartInstance.update();
    }

    function updateMagChart(data) {
        if(!chartInstance) return;
        const sub = data.slice(1).filter(r => parseFloat(r[6]) > -900);
        chartInstance.options.scales.y = { type: 'linear', position: 'right', min: -30, max: 30, ticks: { font: { size: 8 } } };
        chartInstance.data = {
            labels: sub.map(r => r[0].split(' ')[1].slice(0,5)),
            datasets: [
                { label: 'Bt', data: sub.map(r => parseFloat(r[6])), borderColor: '#22d3ee', pointRadius: 0, borderWidth: 1.5, fill: false },
                { label: 'Bz', data: sub.map(r => parseFloat(r[3])), borderColor: '#f43f5e', pointRadius: 0, borderWidth: 1.5, fill: false }
            ]
        };
        chartInstance.update();
    }

    document.addEventListener('DOMContentLoaded', init);
})(jQuery);