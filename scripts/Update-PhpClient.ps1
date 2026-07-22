<#
.SYNOPSIS
    Refreshes the committed CloudHub OpenAPI spec and verifies the hand-written PHP client.

.DESCRIPTION
    Unlike the Java client, this PHP client is HAND-WRITTEN (no code generator). This script does
    the two mechanical halves of a sync; the human/AI edit in between is driven by the
    `update-cloudhub-php-client` skill.

    Pipeline stages:
      1. Build the CloudHub .NET Site (so its assemblies exist).
      2. Capture its OpenAPI spec headlessly -> openapi/cloudhub.json   (Swashbuckle CLI).
         => `git diff openapi/cloudhub.json` now shows exactly what changed upstream.
      -- (manual) apply the spec changes to app/Cloudhub/*.php, guided by the skill --
      3. Verify: `php -l` every source file, then `composer install` + `vendor/bin/phpunit`.

    Typical use:
      pwsh scripts/Update-PhpClient.ps1 -SpecOnly            # capture, then review the diff
      pwsh scripts/Update-PhpClient.ps1 -SkipCloudHubBuild   # after editing: re-capture + verify
      pwsh scripts/Update-PhpClient.ps1 -VerifyOnly          # just lint + test the current code

.PARAMETER CloudHubRepo
    Path to the CloudHub .NET repo. Defaults to a sibling folder named "cloudhub".

.PARAMETER Configuration
    .NET build configuration (Debug/Release). Default: Debug.

.PARAMETER SpecOnly
    Capture the spec only; skip verification.

.PARAMETER SkipCloudHubBuild
    Reuse already-built CloudHub assemblies (skip stage 1).

.PARAMETER VerifyOnly
    Skip build + capture; only lint + test the current PHP code.

.PARAMETER SkipVerify
    Capture the spec but skip the PHP lint/test stage.

.EXAMPLE
    pwsh scripts/Update-PhpClient.ps1 -SpecOnly
.EXAMPLE
    pwsh scripts/Update-PhpClient.ps1 -CloudHubRepo ..\cloudhub -Configuration Release
#>
[CmdletBinding()]
param(
    [string] $CloudHubRepo,
    [ValidateSet('Debug', 'Release')] [string] $Configuration = 'Debug',
    [switch] $SpecOnly,
    [switch] $SkipCloudHubBuild,
    [switch] $VerifyOnly,
    [switch] $SkipVerify
)

$ErrorActionPreference = 'Stop'
Set-StrictMode -Version Latest

function Write-Step($msg) { Write-Host "`n==> $msg" -ForegroundColor Cyan }
function Warn($msg)       { Write-Host "WARNING: $msg" -ForegroundColor Yellow }
function Fail($msg)       { Write-Error $msg; exit 1 }

# --- Resolve paths -----------------------------------------------------------
$RepoRoot = Split-Path -Parent $PSScriptRoot
Write-Step "PHP client repo: $RepoRoot"
$SpecPath = Join-Path $RepoRoot 'openapi\cloudhub.json'

# --- Capture the spec (stages 1-2), unless VerifyOnly ------------------------
if (-not $VerifyOnly) {
    if (-not $CloudHubRepo) { $CloudHubRepo = Join-Path (Split-Path -Parent $RepoRoot) 'cloudhub' }
    $resolved = Resolve-Path -LiteralPath $CloudHubRepo -ErrorAction SilentlyContinue
    $CloudHubRepo = if ($resolved) { $resolved.Path } else { $null }
    if (-not $CloudHubRepo) { Fail "CloudHub repo not found. Pass -CloudHubRepo <path>." }
    Write-Host "CloudHub repo:   $CloudHubRepo"

    $SiteCsproj = Join-Path $CloudHubRepo 'Site\Lacuna.Cloudhub.Site.csproj'
    if (-not (Test-Path $SiteCsproj)) { Fail "Site project not found at $SiteCsproj" }

    # Stage 1: build CloudHub
    if (-not $SkipCloudHubBuild) {
        Write-Step "Building CloudHub Site ($Configuration)"
        dotnet build $SiteCsproj -c $Configuration --nologo -v minimal
        if ($LASTEXITCODE -ne 0) { Fail "CloudHub build failed (exit $LASTEXITCODE)." }
    } else {
        Write-Step "Skipping CloudHub build (-SkipCloudHubBuild)"
    }

    # Stage 2: capture the spec
    Write-Step "Locating built Site assembly"
    $SiteDll = Get-ChildItem -Path (Join-Path $CloudHubRepo "Site\bin\$Configuration") -Recurse -Filter 'Lacuna.Cloudhub.Site.dll' -ErrorAction SilentlyContinue |
        Sort-Object LastWriteTime -Descending | Select-Object -First 1
    if (-not $SiteDll) { Fail "Could not find Lacuna.Cloudhub.Site.dll under Site\bin\$Configuration. Build first (omit -SkipCloudHubBuild)." }
    Write-Host "Assembly: $($SiteDll.FullName)"

    Push-Location $RepoRoot
    try {
        Write-Step "Restoring the Swashbuckle CLI tool"
        dotnet tool restore
        if ($LASTEXITCODE -ne 0) { Fail "dotnet tool restore failed." }

        Write-Step "Capturing OpenAPI spec -> openapi/cloudhub.json"
        New-Item -ItemType Directory -Force -Path (Split-Path $SpecPath) | Out-Null
        dotnet swagger tofile --output $SpecPath $SiteDll.FullName v1
        if ($LASTEXITCODE -ne 0) { Fail "Spec capture failed. (Doc name 'v1' must match the Swashbuckle SwaggerDoc id in Site/Startup.cs.)" }
        Write-Host "Spec written: $((Get-Item $SpecPath).Length) bytes"
    }
    finally { Pop-Location }

    if ($SpecOnly) {
        Write-Step "Done (spec only). Review upstream changes with:"
        Write-Host "  git -C `"$RepoRoot`" diff -- openapi/cloudhub.json"
        Write-Host "Then hand-apply the changes to app/Cloudhub/*.php (see the update-cloudhub-php-client skill)."
        exit 0
    }
}

if ($SkipVerify) { Write-Step "Skipping verification (-SkipVerify)"; exit 0 }

# --- Stage 3: verify the PHP code -------------------------------------------
Write-Step "Verifying the PHP client"
$php      = Get-Command php      -ErrorAction SilentlyContinue
$composer = Get-Command composer -ErrorAction SilentlyContinue

if (-not $php) {
    Warn "php not found on PATH - skipping lint + tests. Install PHP 7.4+ to enable verification."
    exit 0
}

# php -l every source (and test) file
Write-Step "php -l (syntax check)"
$lintFailed = $false
Get-ChildItem -Path (Join-Path $RepoRoot 'app'), (Join-Path $RepoRoot 'tests') -Recurse -Filter '*.php' -ErrorAction SilentlyContinue | ForEach-Object {
    & $php.Source -l $_.FullName
    if ($LASTEXITCODE -ne 0) { $lintFailed = $true }
}
if ($lintFailed) { Fail "php -l reported syntax errors." }

# composer install + phpunit
if ($composer) {
    Write-Step "composer install"
    Push-Location $RepoRoot
    try {
        & $composer.Source install --no-interaction --no-progress
        if ($LASTEXITCODE -ne 0) { Warn "composer install failed; skipping tests." }
        else {
            $phpunit = Join-Path $RepoRoot 'vendor\bin\phpunit'
            if (Test-Path $phpunit) {
                Write-Step "vendor/bin/phpunit"
                & $php.Source $phpunit
                if ($LASTEXITCODE -ne 0) { Fail "phpunit reported failures." }
            } else {
                Warn "vendor/bin/phpunit not present (require-dev not installed?) - skipping tests."
            }
        }
    }
    finally { Pop-Location }
} else {
    Warn "composer not found on PATH - ran php -l only. Install Composer to run the unit tests."
}

Write-Step "Done."
Write-Host "Review upstream API changes:  git -C `"$RepoRoot`" diff -- openapi/cloudhub.json"
Write-Host "Review code changes:          git -C `"$RepoRoot`" status"
Write-Host "Remember to bump the version constraint in README.md and tag the release (the skill does this)."
