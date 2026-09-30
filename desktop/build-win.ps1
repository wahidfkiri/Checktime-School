# Build de l'installateur .exe en gardant TOUS les caches/temp sur D:
# (utile quand le disque C: est plein). Lancer depuis le dossier "desktop".
#
# Note: on n'utilise PAS $ErrorActionPreference='Stop' car npm/electron-builder
# écrivent des avertissements sur stderr, que PowerShell 5.1 transforme sinon en
# erreur fatale. On vérifie les vrais codes de sortie ($LASTEXITCODE) à la place.
$ErrorActionPreference = 'Continue'
$root  = $PSScriptRoot
$cache = Join-Path $root '.cache'
New-Item -ItemType Directory -Force -Path $cache, (Join-Path $cache 'tmp') | Out-Null

# Redirige tous les emplacements qui, par défaut, écrivent sur C:
$env:ELECTRON_CACHE          = Join-Path $cache 'electron'
$env:ELECTRON_BUILDER_CACHE  = Join-Path $cache 'electron-builder'
$env:npm_config_cache        = Join-Path $cache 'npm'
$env:TMP                     = Join-Path $cache 'tmp'
$env:TEMP                    = Join-Path $cache 'tmp'

Write-Host "==> Caches rediriges vers $cache" -ForegroundColor Cyan

Write-Host "==> npm install" -ForegroundColor Cyan
& npm install --no-audit --no-fund
if ($LASTEXITCODE -ne 0) { Write-Host "npm install a echoue (code $LASTEXITCODE)" -ForegroundColor Red; exit $LASTEXITCODE }

Write-Host "==> electron-builder (NSIS)" -ForegroundColor Cyan
& npm run dist
if ($LASTEXITCODE -ne 0) { Write-Host "electron-builder a echoue (code $LASTEXITCODE)" -ForegroundColor Red; exit $LASTEXITCODE }

Write-Host "`n==> Termine. Installateur dans: $(Join-Path $root 'dist')" -ForegroundColor Green
