[CmdletBinding()]
param(
    [ValidateRange(1, 65535)][int]$Port = 8000
)

$ErrorActionPreference = 'Stop'
$webRoot = Split-Path -Parent $PSScriptRoot
$router = Join-Path $webRoot 'vendor\laravel\framework\src\Illuminate\Foundation\resources\server.php'
if (-not (Test-Path -LiteralPath $router)) { throw 'Install backend dependencies before starting the server.' }

# Laravel still enforces 5 MB/photo, 30 MB/video and 6+2 attachments.
# 90 MB is the maximum payload; 96 MB leaves room for multipart overhead.
# These flags apply only to this local process, not the machine's global php.ini.
Push-Location -LiteralPath (Join-Path $webRoot 'public')
try {
    & php -d upload_max_filesize=30M -d post_max_size=96M -d max_file_uploads=20 -S "127.0.0.1:$Port" $router
    if ($LASTEXITCODE -ne 0) { throw 'Local server failed. Check whether this port is already in use.' }
} finally {
    Pop-Location
}
