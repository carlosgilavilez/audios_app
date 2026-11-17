param(
    [string] $RepoRoot = (Split-Path -Parent $PSScriptRoot),
    [string] $CodexSkillsRoot = (Join-Path $env:USERPROFILE '.codex\skills'),
    [string] $PythonExecutable = (Join-Path $env:USERPROFILE '.cache\codex-runtimes\codex-primary-runtime\dependencies\python\python.exe'),
    [string] $YamlModuleRoot = (Join-Path $env:TEMP 'palma-skill-validation'),
    [switch] $Sync
)

$ErrorActionPreference = 'Stop'
$palmaNames = @(
    'iglesiapalma-audios-proyecto',
    'iglesiapalma-audios-mantenimiento',
    'iglesiapalma-audios-actualizar'
)
$palmaValidator = Join-Path $CodexSkillsRoot '.system\skill-creator\scripts\quick_validate.py'
if (-not (Test-Path -LiteralPath $PythonExecutable -PathType Leaf)) {
    throw 'No se encuentra Python; indicar -PythonExecutable con el runtime disponible.'
}
if (-not (Test-Path -LiteralPath $palmaValidator -PathType Leaf)) {
    throw 'No se encuentra quick_validate.py de skill-creator en el directorio de skills.'
}

# Use the existing optional PyYAML directory; do not install dependencies here.
$palmaRunner = 'import sys,runpy; p=sys.argv.pop(1); sys.path.insert(0,p) if p else None; sys.argv=sys.argv[1:]; runpy.run_path(sys.argv[0],run_name="__main__")'
$palmaYamlPath = if (Test-Path -LiteralPath (Join-Path $YamlModuleRoot 'yaml\__init__.py') -PathType Leaf) { $YamlModuleRoot } else { '' }
foreach ($palmaName in $palmaNames) {
    $palmaSource = Join-Path $RepoRoot ('skills\' + $palmaName)
    & $PythonExecutable -c $palmaRunner $palmaYamlPath $palmaValidator $palmaSource
    if ($LASTEXITCODE -ne 0) {
        throw "No se validó $palmaName. Si falta PyYAML, indicar -YamlModuleRoot con su directorio existente. No se ha sincronizado ninguna skill."
    }
}

$palmaCopied = 0
if ($Sync) {
    foreach ($palmaName in $palmaNames) {
        $palmaSource = (Resolve-Path -LiteralPath (Join-Path $RepoRoot ('skills\' + $palmaName))).Path
        $palmaDestination = Join-Path $CodexSkillsRoot $palmaName
        foreach ($palmaFile in (Get-ChildItem -LiteralPath $palmaSource -Recurse -File)) {
            $palmaRelative = $palmaFile.FullName.Substring($palmaSource.Length + 1)
            $palmaTarget = Join-Path $palmaDestination $palmaRelative
            New-Item -ItemType Directory -Force -Path (Split-Path -Parent $palmaTarget) | Out-Null
            Copy-Item -LiteralPath $palmaFile.FullName -Destination $palmaTarget -Force
            if ((Get-FileHash -LiteralPath $palmaFile.FullName).Hash -ne (Get-FileHash -LiteralPath $palmaTarget).Hash) {
                throw "La copia no coincide: $palmaTarget"
            }
            $palmaCopied++
        }
    }
}
[pscustomobject]@{
    ValidatedSkills = $palmaNames.Count
    Mode = $(if ($Sync) { 'sync' } else { 'validate' })
    CopiedFilesWithMatchingHashes = $palmaCopied
} | ConvertTo-Json -Compress
