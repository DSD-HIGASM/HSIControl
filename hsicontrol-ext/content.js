// Configuración base
const HSI_CONTROL_URL = 'https://hsi.sdhigasanmartin.qzz.io';
const INSTITUTION_ID = 484; 

// Función principal que orquesta la extracción
async function exportPatientToHSIControl(patientId) {
    try {
        // 1. Obtener completedata
        const completedRes = await fetch(`/api/patient/${patientId}/completedata`);
        const completedData = await completedRes.json();
        
        const personId = completedData.person?.id;
        if (!personId) throw new Error("No se encontró personId en HSI");

        // 2. Obtener personalInformation
        const personalRes = await fetch(`/api/person/${personId}/personalInformation`);
        const personalData = await personalRes.json();

        // 3. Obtener user info
        let userData = {};
        try {
            const userRes = await fetch(`/api/users/institution/${INSTITUTION_ID}/person/${personId}`);
            if (userRes.ok) userData = await userRes.json();
        } catch (e) { console.warn("El usuario no tiene credenciales en HSI", e); }

        // 4. Obtener roles (solo si hay user.id)
        let rolesData = [];
        if (userData && userData.id) {
            try {
                console.log("Consultando roles para userID:", userData.id);
                const rolesRes = await fetch(`/api/user-role/institution/${INSTITUTION_ID}/user/${userData.id}`);
                
                if (rolesRes.ok) {
                    rolesData = await rolesRes.json();
                    console.log("Roles obtenidos:", rolesData);
                } else {
                    console.error("Error en API de roles. Status:", rolesRes.status);
                }
            } catch (e) { 
                console.error("Error crítico multimedia en roles:", e); 
            }
        } else {
            console.warn("No se pudo consultar roles porque no hay userData.id");
        }

        // Ejecutar envío según configuración
        chrome.storage.sync.get(['syncMode', 'apiToken'], (config) => {
            const mode = config.syncMode || 'GET';
            
            if (mode === 'GET') {
                executeGetFlow(completedData, personalData, userData, rolesData);
            } else {
                executePostFlow(completedData, personalData, userData, rolesData, mode, config.apiToken);
            }
        });

    } catch (error) {
        console.error("Error en HSIControl Sync:", error);
        showToast("Error al extraer datos de HSI: " + error.message, "error");
    }
}

// Lógica para el Modo GET (Redirección directa)
function executeGetFlow(completed, personal, user, roles) {
    const dni = completed.identificationNumber || completed.person?.identificationNumber;
    const firstName = completed.firstName || completed.person?.firstName;
    const lastName = completed.lastName || completed.person?.lastName;
    const gender = completed.gender?.description || completed.person?.gender?.description || 'x';

    const params = new URLSearchParams({
        dni: dni,
        first_name: firstName,
        last_name: lastName,
        gender: gender.toLowerCase(),
        completed: btoa(unescape(encodeURIComponent(JSON.stringify(completed)))),
        personal:  btoa(unescape(encodeURIComponent(JSON.stringify(personal)))),
        user:      btoa(unescape(encodeURIComponent(JSON.stringify(user)))),
        roles:     btoa(unescape(encodeURIComponent(JSON.stringify(roles))))
    });

    const url = `${HSI_CONTROL_URL}/agentes/importar-rapido?${params.toString()}`;
    window.open(url, '_blank');
}

// Lógica para el Modo POST (Conexión por API segura)
async function executePostFlow(completed, personal, user, roles, mode, token) {
    if (!token) {
        showToast("Configurá tu Token de API en la extensión para usar este modo.", "error");
        return;
    }

    const apiUrl = `${HSI_CONTROL_URL}/api/hsi-sync`;

    try {
        const response = await fetch(apiUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify({
                mode: mode,
                completed: completed,
                personal: personal,
                user: user,
                roles: roles
            })
        });

        const result = await response.json();

        if (response.ok && result.status === 'success') {
            showToast("Agente enviado con éxito a la bandeja de pendientes.", "success");
        } else {
            console.error("Error devuelto por la API:", result);
            showToast(`Error de sincronización: ${result.message || 'Error desconocido'}`, "error");
        }

    } catch (error) {
        console.error("Error crítico de red al conectar con HSIControl:", error);
        showToast("No se pudo conectar con el servidor de HSIControl.", "error");
    }
}

// --- SISTEMA DE TOAST NOTIFICACIONES ---
function showToast(message, type = 'success') {
    const existingToast = document.getElementById('hsi-sync-toast');
    if (existingToast) existingToast.remove();

    const toast = document.createElement('div');
    toast.id = 'hsi-sync-toast';
    
    const bgColor = type === 'success' ? '#0ea5e9' : '#db2777';
    
    toast.innerHTML = `
        <div style="display: flex; align-items: center; gap: 10px;">
            ${type === 'success' 
                ? '<svg style="width:18px;height:18px" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>'
                : '<svg style="width:18px;height:18px" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>'
            }
            <span style="font-family: system-ui, sans-serif; font-size: 13px; font-weight: 600;">${message}</span>
        </div>
    `;

    toast.style.cssText = `
        position: fixed;
        bottom: 24px;
        right: 24px;
        z-index: 999999;
        background-color: ${bgColor};
        color: white;
        padding: 12px 18px;
        border-radius: 8px;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        opacity: 0;
        transform: translateY(20px);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    `;

    document.body.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '1';
        toast.style.transform = 'translateY(0)';
    }, 50);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(20px)';
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

// --- INYECCIÓN EN EL HEADER ---
function injectButton() {
    const isProfileUrl = window.location.href.includes('/pacientes/profile/');
    const existingBtn = document.getElementById('btn-hsi-sync');

    // Si no es la URL del perfil, nos aseguramos de borrar el botón si quedó de otra pantalla
    if (!isProfileUrl) {
        if (existingBtn) existingBtn.remove();
        return;
    }

    // Si ya está puesto y es la URL correcta, no hacemos nada
    if (existingBtn) return;

    const topHeader = document.querySelector('header .navbar-right') || document.querySelector('nav') || document.body;

    const btn = document.createElement('button');
    btn.id = 'btn-hsi-sync';
    
    btn.innerHTML = `
        <svg style="width:14px;height:14px;stroke-width:2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"></path>
        </svg>
        Sincronizar Padrón
    `;
    
    const isFloatingTop = topHeader === document.body;
    btn.style.cssText = `
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px; 
        background-color: #0ea5e9; 
        color: white; 
        border: none; 
        border-radius: 6px; 
        font-family: system-ui, -apple-system, sans-serif;
        font-size: 12px;
        font-weight: 700; 
        cursor: pointer; 
        transition: all 0.2s;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        ${isFloatingTop ? 'position: absolute; top: 75px; right: 20px; z-index: 9999;' : 'margin-left: 12px;'}
    `;
    
    btn.addEventListener('mouseenter', () => btn.style.backgroundColor = '#0284c7');
    btn.addEventListener('mouseleave', () => btn.style.backgroundColor = '#0ea5e9');

    btn.addEventListener('click', () => {
        const match = window.location.href.match(/profile\/(\d+)/); 
        if (match && match[1]) {
            exportPatientToHSIControl(match[1]);
        } else {
            showToast("No se detectó un ID de paciente en la URL actual.", "error");
        }
    });

    if (isFloatingTop) {
        topHeader.appendChild(btn);
    } else {
        topHeader.insertBefore(btn, topHeader.firstChild);
    }
}

// --- OBSERVADOR DE NAVEGACIÓN UNIFICADO ---
let lastUrl = location.href; 
new MutationObserver(() => {
    const url = location.href;
    if (url !== lastUrl) {
        lastUrl = url;
        // Ejecutamos siempre para ponerlo o sacarlo dinámicamente
        setTimeout(injectButton, 600);
    }
}).observe(document, {subtree: true, childList: true});

// Ejecución al tiro en la carga inicial
setTimeout(injectButton, 600);