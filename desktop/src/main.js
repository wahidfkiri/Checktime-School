'use strict';

const { app, BrowserWindow, Menu, shell, dialog } = require('electron');
const path = require('path');

// ---------------------------------------------------------------------------
// Configuration
// ---------------------------------------------------------------------------
const APP_URL = 'https://school.checktime.bj/login';
const APP_ORIGIN = new URL(APP_URL).origin; // https://school.checktime.bj
const APP_NAME = 'CheckTime École';

app.setName(APP_NAME);

// Une seule instance de l'application à la fois.
const gotTheLock = app.requestSingleInstanceLock();
if (!gotTheLock) {
  app.quit();
}

let mainWindow = null;

// ---------------------------------------------------------------------------
// Fenêtre principale
// ---------------------------------------------------------------------------
function createWindow() {
  mainWindow = new BrowserWindow({
    width: 1280,
    height: 800,
    minWidth: 960,
    minHeight: 600,
    title: APP_NAME,
    icon: path.join(__dirname, '..', 'build', 'icon.png'),
    backgroundColor: '#0e1726',
    show: false,
    autoHideMenuBar: true,
    webPreferences: {
      contextIsolation: true,
      nodeIntegration: false,
      // Conserve la session (cookies de connexion) entre deux lancements.
      partition: 'persist:checktime-school',
      spellcheck: true
    }
  });

  mainWindow.once('ready-to-show', () => mainWindow.show());

  loadApp();

  // Ouvre les liens externes (autre domaine) dans le navigateur par défaut,
  // et garde la navigation interne dans la fenêtre.
  mainWindow.webContents.setWindowOpenHandler(({ url }) => {
    if (isInternal(url)) {
      mainWindow.loadURL(url);
    } else {
      shell.openExternal(url);
    }
    return { action: 'deny' };
  });

  mainWindow.webContents.on('will-navigate', (event, url) => {
    if (!isInternal(url)) {
      event.preventDefault();
      shell.openExternal(url);
    }
  });

  // Page de repli en cas d'échec de chargement (hors ligne, serveur down…).
  mainWindow.webContents.on('did-fail-load', (event, errorCode, errorDescription, validatedURL, isMainFrame) => {
    // -3 = ERR_ABORTED (navigation annulée volontairement) → on ignore.
    if (!isMainFrame || errorCode === -3) return;
    showOfflinePage(errorDescription || 'Impossible de contacter le serveur.');
  });

  mainWindow.on('closed', () => {
    mainWindow = null;
  });
}

function isInternal(url) {
  try {
    return new URL(url).origin === APP_ORIGIN;
  } catch (_) {
    return false;
  }
}

function loadApp() {
  mainWindow.loadURL(APP_URL).catch(() => {
    showOfflinePage('Impossible de contacter le serveur.');
  });
}

function showOfflinePage(message) {
  const html = `<!doctype html>
  <html lang="fr"><head><meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>${APP_NAME} — Hors ligne</title>
  <style>
    :root { color-scheme: dark; }
    * { box-sizing: border-box; }
    body { margin:0; min-height:100vh; display:flex; align-items:center; justify-content:center;
      font-family: Segoe UI, system-ui, sans-serif; background:#0e1726; color:#e6eaf2; text-align:center; padding:24px; }
    .card { max-width:460px; }
    h1 { font-size:20px; margin:0 0 8px; }
    p { color:#9aa4b8; line-height:1.5; margin:0 0 20px; }
    code { color:#c3cee0; font-size:12px; }
    button { background:#3b5bdb; color:#fff; border:0; padding:11px 22px; border-radius:8px;
      font-size:14px; cursor:pointer; }
    button:hover { background:#2f4bc0; }
  </style></head>
  <body><div class="card">
    <h1>Connexion impossible</h1>
    <p>${APP_NAME} n'a pas pu joindre <code>${APP_ORIGIN}</code>.<br>Vérifiez votre connexion Internet.</p>
    <p><code>${String(message).replace(/</g, '&lt;')}</code></p>
    <button onclick="location.replace('${APP_URL}')">Réessayer</button>
  </div></body></html>`;
  mainWindow.loadURL('data:text/html;charset=utf-8,' + encodeURIComponent(html));
}

// ---------------------------------------------------------------------------
// Menu applicatif (français, minimal)
// ---------------------------------------------------------------------------
function buildMenu() {
  const template = [
    {
      label: 'Fichier',
      submenu: [
        { label: 'Recharger', accelerator: 'CmdOrCtrl+R', click: () => loadApp() },
        { type: 'separator' },
        { label: 'Quitter', accelerator: 'CmdOrCtrl+Q', role: 'quit' }
      ]
    },
    {
      label: 'Navigation',
      submenu: [
        { label: 'Précédent', accelerator: 'Alt+Left', click: () => mainWindow && mainWindow.webContents.canGoBack() && mainWindow.webContents.goBack() },
        { label: 'Suivant', accelerator: 'Alt+Right', click: () => mainWindow && mainWindow.webContents.canGoForward() && mainWindow.webContents.goForward() },
        { label: "Page d'accueil", click: () => loadApp() }
      ]
    },
    {
      label: 'Affichage',
      submenu: [
        { label: 'Zoom avant', role: 'zoomIn' },
        { label: 'Zoom arrière', role: 'zoomOut' },
        { label: 'Zoom normal', role: 'resetZoom' },
        { type: 'separator' },
        { label: 'Plein écran', role: 'togglefullscreen' }
      ]
    },
    {
      label: 'Aide',
      submenu: [
        {
          label: 'À propos',
          click: () => {
            dialog.showMessageBox(mainWindow, {
              type: 'info',
              title: 'À propos',
              message: APP_NAME,
              detail: `Version ${app.getVersion()}\n${APP_ORIGIN}\n\n© 2026 Novatis Sarl`,
              buttons: ['OK']
            });
          }
        }
      ]
    }
  ];
  Menu.setApplicationMenu(Menu.buildFromTemplate(template));
}

// ---------------------------------------------------------------------------
// Cycle de vie
// ---------------------------------------------------------------------------
app.on('second-instance', () => {
  if (mainWindow) {
    if (mainWindow.isMinimized()) mainWindow.restore();
    mainWindow.focus();
  }
});

app.whenReady().then(() => {
  buildMenu();
  createWindow();

  app.on('activate', () => {
    if (BrowserWindow.getAllWindows().length === 0) createWindow();
  });
});

app.on('window-all-closed', () => {
  if (process.platform !== 'darwin') app.quit();
});
