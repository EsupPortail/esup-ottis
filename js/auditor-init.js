/**
 * auditor-init.js
 * Initialisation spécifique pour auditor.php
 * Doit être chargé comme module ES après conference-legacy.js
 */

import { initChatAlert } from './lib/chat/alert.js';
import { loadVoicesWhenAvailable } from './lib/conference/index.js';

// Get user name from DOM
const userName = document.getElementById('AuditorName') ? document.getElementById('AuditorName').value : window.randomname;

// Initialize chat alert
if (typeof initChatAlert === 'function') {
    initChatAlert(userName);
} else {
    console.error('[Chat Alert] initChatAlert function not found!');
}

// Wait for DOM to be fully loaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        initializeAuditor();
    });
} else {
    initializeAuditor();
}

function initializeAuditor() {
    // Initialize the application
    console.log('[AUDITOR DEBUG] DOM fully loaded, calling Init()');
    if (typeof Init === 'function') {
        Init();
    }

    // Load voices when available
    if (typeof loadVoicesWhenAvailable === 'function') {
        setTimeout(loadVoicesWhenAvailable, 100);
    } else {
        console.error('[AUDITOR DEBUG] loadVoicesWhenAvailable function not found!');
    }
}
