# CheckTime École — Application de bureau (Electron)

Enveloppe desktop Windows qui ouvre **https://school.checktime.bj/login** dans une
fenêtre native, avec installateur `.exe`.

## Prérequis
- Node.js 18+ et npm (testé avec Node 22 / npm 11)
- Windows 64 bits

## Développement (lancer sans compiler)
```bash
npm install
npm start
```

## Générer l'installateur .exe
```bash
npm install
npm run dist
```
L'installateur est produit dans `dist/` :
`CheckTime École Setup 1.0.0.exe`

> ⚠️ Le disque **C:** doit avoir un peu d'espace libre : Electron met ses caches
> dans `%LOCALAPPDATA%` (sur C:). Pour tout garder sur D:, lancez le build avec le
> script `build-win.ps1` fourni (il redirige caches et temp vers `desktop\.cache`).

## Personnalisation
- URL cible : constante `APP_URL` dans `src/main.js`
- Icône : `build/icon.png` (256×256) et `build/icon.ico`
- Nom / version / éditeur : `package.json`

## Notes techniques
- La session (cookies de connexion) est conservée entre les lancements
  (`partition: persist:checktime-school`).
- Les liens externes s'ouvrent dans le navigateur par défaut ; la navigation
  interne (school.checktime.bj) reste dans l'application.
- Une page de repli s'affiche si le serveur est injoignable (hors ligne).
- Une seule instance de l'application peut tourner à la fois.
