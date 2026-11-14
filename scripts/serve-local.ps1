[CmdletBinding()]
param(
    [ValidateRange(1, 65535)][int]$Port = 8000
)

$ErrorActionPreference = 'Stop'
$webRoot = Split-Path -Parent $PSScriptRoot
$router = Join-Path $webRoot 'vendor\laravel\framework\src\Illuminate\Foundation\resources\server.php'
if (-not (Test-Path -LiteralPath $router)) { throw 'Install backend dependencies before starting the server.' }

# Seller inventory allows 8 photos at 5 MiB each plus one 30 MiB MP4.
# Buyer reviews allow 6 photos and 2 videos, so 96 MiB covers the larger form.
# PHP flags apply to this server process only; Laravel still validates each file.
Push-Location -LiteralPath (Join-Path $webRoot 'public')
try {
    & php -d upload_max_filesize=30M -d post_max_size=96M -d max_file_uploads=20 -S "127.0.0.1:$Port" $router
    if ($LASTEXITCODE -ne 0) { throw 'Local server failed. Check whether this port is already in use.' }
} finally {
    Pop-Location
}
