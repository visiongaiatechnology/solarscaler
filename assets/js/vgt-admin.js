/**
 * VGT ADMIN CONSOLE CONTROLLER
 */
document.addEventListener('DOMContentLoaded', () => {
    const btnSync = document.getElementById('vgt-manual-sync-btn');
    const btnReset = document.getElementById('vgt-hard-reset-btn'); // NEW
    const consoleOut = document.getElementById('vgt-console-output');

    if (!consoleOut) return;

    const log = (msg, type = 'normal') => {
        const line = document.createElement('span');
        line.className = `console-line ${type}`;
        line.innerHTML = `> ${msg}`;
        consoleOut.appendChild(line);
        consoleOut.scrollTop = consoleOut.scrollHeight;
    };

    const runCommand = (action, label) => {
        const activeBtn = action === 'vgt_hard_reset' ? btnReset : btnSync;
        if(!activeBtn) return;

        activeBtn.disabled = true;
        activeBtn.innerText = 'EXECUTING...';
        consoleOut.innerHTML = ''; 

        log(`INITIALIZING ${label} PROTOCOL...`);
        
        const data = new FormData();
        data.append('action', action);
        
        fetch(ajaxurl, { method: 'POST', body: data })
        .then(r => r.json())
        .then(res => {
            if(res.success) {
                log('--------------------------------');
                log('DATA PACKET RECEIVED.');
                
                Object.keys(res.data.log).forEach(channel => {
                    const status = res.data.log[channel];
                    log(`CHANNEL [${channel}]: ${status}`, 'success');
                });

                log('--------------------------------');
                log('SEQUENCE COMPLETE.', 'success');
                activeBtn.innerText = 'DONE';
                setTimeout(() => { activeBtn.disabled = false; activeBtn.innerText = label; }, 3000);
            } else {
                log('ERROR: ' + JSON.stringify(res.data), 'error');
                activeBtn.disabled = false;
            }
        })
        .catch(err => {
            log('NETWORK FAILURE.', 'error');
            console.error(err);
            activeBtn.disabled = false;
        });
    };

    if(btnSync) btnSync.addEventListener('click', () => runCommand('vgt_manual_sync', 'STANDARD SYNC'));
    if(btnReset) btnReset.addEventListener('click', () => runCommand('vgt_hard_reset', 'HARD RESET'));
});