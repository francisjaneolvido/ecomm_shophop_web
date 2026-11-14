[CmdletBinding()]
param(
    [ValidateRange(1, 65535)][int]$Port = 8000
)

& (Join-Path $PSScriptRoot 'serve-local.ps1') -Port $Port
