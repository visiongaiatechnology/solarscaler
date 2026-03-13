/**
 * VGT SOLAR CORE ENGINE [DIAMANT EDITION v4.7 - REQUEST ANIMATION FRAME]
 * Features: Static JSON, Auto-Genesis, Image Decoding API, Hardware Accelerated Loop
 */

document.addEventListener('DOMContentLoaded', () => {
    const CONFIG = window.VGT_CONFIG || {};

    class VGTSolarEngine {
        constructor() {
            // State
            this.state = {
                transform: { x: 0, y: 0, scale: 0.85 },
                isDragging: false,
                dragStart: { x: 0, y: 0 },
                wavelength: CONFIG.default_channel || '171',
                timelineData: [],
                imageCache: new Map(),
                isLoading: false,
                fetchController: null,
                isRecovering: false,
                activeBuffer: 'A', 
                isPlaying: false,
                
                // Animation Logic (RAF)
                lastFrameTime: 0,
                frameInterval: 150, // ms per frame target
                animationFrameId: null
            };

            // DOM
            this.dom = {
                container: document.getElementById('vgt-sun-stage-container'),
                stage: document.getElementById('vgt-sun-stage'),
                bufferA: document.getElementById('vgt-buffer-a'),
                bufferB: document.getElementById('vgt-buffer-b'),
                zoomValue: document.getElementById('vgt-zoom-value'),
                slider: document.getElementById('vgt-timeline-slider'),
                timeLabel: document.getElementById('vgt-timeline-date'),
                playBtn: document.getElementById('vgt-play-trigger'),
                iconPlay: document.getElementById('vgt-icon-play'),
                iconPause: document.getElementById('vgt-icon-pause'),
                loader: document.getElementById('vgt-loader'),
                buttons: document.querySelectorAll('.vgt-wave-btn'),
                refreshBtn: document.getElementById('vgt-refresh-trigger'),
                clock: document.getElementById('vgt-live-time'),
                modalTrigger: document.getElementById('vgt-copyright-trigger'),
                modalBackdrop: document.getElementById('vgt-modal-backdrop'),
                modalContent: document.getElementById('vgt-modal-content'),
                modalClose: document.getElementById('vgt-modal-close')
            };

            if (!this.dom.stage) return;
            this.init();
        }

        init() {
            this.bindEvents();
            this.dom.bufferA.style.opacity = '0';
            this.dom.bufferB.style.opacity = '0';
            
            // Bound Animation Loop for RAF
            this.animationLoop = this.animationLoop.bind(this);

            this.loadChannelData(this.state.wavelength);
            this.updateTransform(); 
            this.startSystemClock(); 
            console.log('VGT SYSTEM: VISUAL CORE ONLINE (HW ACCELERATED).');
        }

        bindEvents() {
            const { container } = this.dom;
            container.addEventListener('mousedown', (e) => {
                this.state.isDragging = true;
                this.state.dragStart = { x: e.clientX - this.state.transform.x, y: e.clientY - this.state.transform.y };
                container.style.cursor = 'grabbing';
            });

            window.addEventListener('mousemove', (e) => {
                if (!this.state.isDragging) return;
                e.preventDefault();
                this.state.transform.x = e.clientX - this.state.dragStart.x;
                this.state.transform.y = e.clientY - this.state.dragStart.y;
                this.updateTransform();
            });

            window.addEventListener('mouseup', () => {
                this.state.isDragging = false;
                this.dom.container.style.cursor = 'grab';
            });

            container.addEventListener('wheel', (e) => this.handleZoom(e), { passive: false });

            this.dom.buttons.forEach(btn => {
                btn.addEventListener('click', (e) => {
                    this.stopPlayback();
                    this.dom.buttons.forEach(b => {
                        b.classList.remove('active', 'bg-[#ff4d00]', 'text-black');
                        b.classList.add('text-gray-400');
                    });
                    e.currentTarget.classList.add('active', 'bg-[#ff4d00]', 'text-black');
                    e.currentTarget.classList.remove('text-gray-400');
                    this.loadChannelData(e.currentTarget.dataset.wave);
                });
            });

            this.dom.slider.addEventListener('input', (e) => {
                this.stopPlayback(); 
                this.renderFrame(parseInt(e.target.value));
            });

            if(this.dom.playBtn) this.dom.playBtn.addEventListener('click', () => this.togglePlayback());
            this.dom.refreshBtn.addEventListener('click', () => this.triggerRefresh());

            if (this.dom.modalTrigger) this.dom.modalTrigger.addEventListener('click', () => this.toggleModal(true));
            if (this.dom.modalClose) this.dom.modalClose.addEventListener('click', () => this.toggleModal(false));
            if (this.dom.modalBackdrop) this.dom.modalBackdrop.addEventListener('click', (e) => { if(e.target === this.dom.modalBackdrop) this.toggleModal(false); });
        }

        async loadChannelData(wavelength, isRetry = false) {
            if (this.state.fetchController) this.state.fetchController.abort();
            this.state.fetchController = new AbortController();
            const signal = this.state.fetchController.signal;

            this.state.wavelength = wavelength;
            this.setLoading(true);
            this.state.imageCache.clear();
            
            const staticUrl = `/storage/vgt-solar-cache/${wavelength}/timeline.json?nocache=${Date.now()}`;

            try {
                const response = await fetch(staticUrl, { signal });
                
                if (!response.ok) {
                    if (response.status === 404 && !isRetry && !this.state.isRecovering) {
                        console.warn('VGT: MANIFEST 404. AUTO-HEALING...');
                        this.state.isRecovering = true;
                        await this.triggerRefresh(true); 
                        this.state.isRecovering = false;
                        return this.loadChannelData(wavelength, true); 
                    }
                    throw new Error('MANIFEST_MISSING');
                }
                
                let data;
                try { data = await response.json(); } catch (e) { throw new Error('INVALID_JSON'); }

                if (Array.isArray(data) && data.length > 0) {
                    this.state.timelineData = data;
                    this.dom.slider.disabled = false;
                    this.dom.slider.max = data.length - 1;
                    
                    const latestIndex = data.length - 1;
                    this.dom.slider.value = latestIndex;
                    
                    await this.renderFrame(latestIndex);
                    
                    this.preloadSequence(data);
                } else {
                    this.throwError("NO SIGNAL");
                }
            } catch (err) {
                if (err.name !== 'AbortError') {
                    console.error('VGT ERROR:', err);
                    if(!this.state.isRecovering) this.throwError("OFFLINE");
                }
            }
            if (!signal.aborted) this.setLoading(false);
        }

        throwError(msg) {
            this.dom.slider.disabled = true;
            this.dom.timeLabel.textContent = msg;
        }

        async renderFrame(index) {
            if (!this.state.timelineData[index]) return;
            const frame = this.state.timelineData[index];
            
            const targetBuffer = this.state.activeBuffer === 'A' ? this.dom.bufferB : this.dom.bufferA;
            const currentBuffer = this.state.activeBuffer === 'A' ? this.dom.bufferA : this.dom.bufferB;
            
            let sourceToUse = frame.url;
            if (this.state.imageCache.has(index)) {
                const cachedImg = this.state.imageCache.get(index);
                if (cachedImg.complete && cachedImg.naturalWidth > 0) sourceToUse = cachedImg.src;
            }

            if (targetBuffer.src === sourceToUse && targetBuffer.style.opacity === '1') {
                return;
            }

            const performSwap = () => {
                targetBuffer.style.zIndex = '10';
                targetBuffer.style.opacity = '1';
                currentBuffer.style.zIndex = '0';
                currentBuffer.style.opacity = '0';
                this.state.activeBuffer = this.state.activeBuffer === 'A' ? 'B' : 'A';
                this.dom.timeLabel.innerHTML = `${frame.date} <span class="text-[#ff4d00] font-bold tracking-widest">${frame.time}</span>`;
            };

            targetBuffer.src = sourceToUse;

            try {
                if (targetBuffer.decode) {
                    await targetBuffer.decode();
                } else {
                    await new Promise((resolve) => {
                        if (targetBuffer.complete) resolve();
                        else targetBuffer.onload = resolve;
                    });
                }
                performSwap();
            } catch (err) {
                console.warn('VGT Decode Error', err);
                performSwap();
            }
        }

        preloadSequence(data) {
            const count = data.length;
            const shouldLoadAll = count < 60;
            const prioritySet = data.slice(-20).reverse();
            
            const loadList = (list) => {
                list.forEach((frame) => {
                    const idx = data.indexOf(frame);
                    if(this.state.imageCache.has(idx)) return; 

                    const img = new Image();
                    img.src = frame.url;
                    this.state.imageCache.set(idx, img);
                });
            };

            loadList(prioritySet);

            if(shouldLoadAll) {
                setTimeout(() => {
                     const remaining = data.filter(f => !prioritySet.includes(f));
                     loadList(remaining);
                }, 1000); 
            }
        }

        togglePlayback() { this.state.isPlaying ? this.stopPlayback() : this.startPlayback(); }

        startPlayback() {
            if (this.state.timelineData.length < 2) return;
            this.state.isPlaying = true;
            this.dom.iconPlay.classList.add('hidden');
            this.dom.iconPause.classList.remove('hidden');

            let currentIdx = parseInt(this.dom.slider.value);
            if (currentIdx >= this.dom.slider.max) currentIdx = 0;
            this.dom.slider.value = currentIdx;

            // Reset Delta Timer
            this.state.lastFrameTime = performance.now();
            
            // Start Loop
            this.state.animationFrameId = requestAnimationFrame(this.animationLoop);
        }

        animationLoop(timestamp) {
            if (!this.state.isPlaying) return;

            // Calculate Delta Time
            const elapsed = timestamp - this.state.lastFrameTime;

            if (elapsed > this.state.frameInterval) {
                // UPDATE FRAME
                this.state.lastFrameTime = timestamp - (elapsed % this.state.frameInterval); // Correction for drift

                let idx = parseInt(this.dom.slider.value);
                let nextIdx = idx + 1;
                
                if (nextIdx > this.dom.slider.max) nextIdx = 0;
                
                this.dom.slider.value = nextIdx;
                this.renderFrame(nextIdx);
            }

            // Continue Loop
            this.state.animationFrameId = requestAnimationFrame(this.animationLoop);
        }

        stopPlayback() {
            this.state.isPlaying = false;
            this.dom.iconPlay.classList.remove('hidden');
            this.dom.iconPause.classList.add('hidden');
            
            if (this.state.animationFrameId) {
                cancelAnimationFrame(this.state.animationFrameId);
                this.state.animationFrameId = null;
            }
        }

        handleZoom(e) {
            e.preventDefault();
            const factor = 1 + (e.deltaY > 0 ? -0.1 : 0.1);
            let { x, y, scale } = this.state.transform;
            const rect = this.dom.container.getBoundingClientRect();
            const mouseX = e.clientX - rect.left;
            const mouseY = e.clientY - rect.top;
            const newScale = Math.min(Math.max(0.1, scale * factor), 20);
            x = mouseX - (mouseX - x) * (newScale / scale);
            y = mouseY - (mouseY - y) * (newScale / scale);
            this.state.transform = { x, y, scale: newScale };
            this.updateTransform();
        }

        updateTransform() {
            const { x, y, scale } = this.state.transform;
            this.dom.stage.style.transform = `translate(${x}px, ${y}px) scale(${scale})`;
            this.dom.zoomValue.textContent = scale.toFixed(2) + 'x';
        }

        async triggerRefresh(silent = false) {
            this.stopPlayback();
            
            if(!silent) {
                this.dom.refreshBtn.classList.add('animate-spin');
                this.dom.refreshBtn.style.color = '#ff4d00';
            }
            
            const formData = new FormData();
            formData.append('action', 'vgt_frontend_refresh');
            formData.append('nonce', CONFIG.nonce);
            
            try {
                await fetch(CONFIG.ajax_url, { method: 'POST', body: formData });
                return new Promise(resolve => setTimeout(() => {
                     if(!silent) {
                         this.dom.refreshBtn.classList.remove('animate-spin');
                         this.dom.refreshBtn.style.color = '';
                         this.loadChannelData(this.state.wavelength);
                     }
                     resolve();
                }, 2000));
            } catch (e) { 
                if(!silent) {
                    this.dom.refreshBtn.classList.remove('animate-spin');
                    this.dom.refreshBtn.style.color = 'red';
                }
            }
        }

        setLoading(loading) {
            this.state.isLoading = loading;
            this.dom.loader.style.display = loading ? 'flex' : 'none';
        }
        
        startSystemClock() {
            const updateTime = () => {
                if(!this.dom.clock) return;
                const now = new Date();
                this.dom.clock.textContent = now.toLocaleTimeString('en-GB', { 
                    timeZone: 'UTC', hour12: false, hour: '2-digit', minute: '2-digit', second: '2-digit' 
                });
            };
            updateTime(); setInterval(updateTime, 1000);
        }
        
        toggleModal(show) {
            const { modalBackdrop, modalContent } = this.dom;
            if(!modalBackdrop) return;
            if(show) {
                modalBackdrop.classList.remove('hidden');
                setTimeout(() => { 
                    modalBackdrop.classList.remove('opacity-0');
                    modalContent.classList.remove('scale-95');
                }, 10);
            } else {
                modalBackdrop.classList.add('opacity-0');
                modalContent.classList.add('scale-95');
                setTimeout(() => { modalBackdrop.classList.add('hidden'); }, 300);
            }
        }
    }

    new VGTSolarEngine();
});