document.addEventListener('DOMContentLoaded', () => {
    const syncMode = document.getElementById('syncMode');
    const apiToken = document.getElementById('apiToken');
    const saveBtn = document.getElementById('saveBtn');
    const status = document.getElementById('status');

    // Cargar configuración previa
    chrome.storage.sync.get(['syncMode', 'apiToken'], (items) => {
        if (items.syncMode) syncMode.value = items.syncMode;
        if (items.apiToken) apiToken.value = items.apiToken;
    });

    // Guardar configuración
    saveBtn.addEventListener('click', () => {
        chrome.storage.sync.set({
            syncMode: syncMode.value,
            apiToken: apiToken.value
        }, () => {
            status.style.display = 'block';
            setTimeout(() => { status.style.display = 'none'; }, 2000);
        });
    });
});