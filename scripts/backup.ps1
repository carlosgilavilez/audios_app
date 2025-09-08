Param(
  [string]$ProjectRoot = (Resolve-Path "$PSScriptRoot\..\").Path
)

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

Push-Location $ProjectRoot
try {
  $ts = Get-Date -Format 'yyyyMMdd-HHmmss'
  $backupDir = Join-Path $ProjectRoot 'storage\backups'
  New-Item -ItemType Directory -Force -Path $backupDir | Out-Null

  # 1) Backup .env
  $envPath = Join-Path $ProjectRoot '.env'
  if (Test-Path $envPath) {
    Copy-Item $envPath (Join-Path $backupDir ".env.$ts") -Force
    Write-Host "[backup] .env copied to storage/backups/.env.$ts"
  } else {
    Write-Warning "[backup] .env not found"
  }

  # 2) Try DB dump with mysqldump (if available)
  if (Test-Path $envPath) {
    $envLines = Get-Content $envPath | Where-Object { $_ -match '^(DB_HOST|DB_PORT|DB_DATABASE|DB_USERNAME|DB_PASSWORD)=' }
    $envMap = @{}
    foreach ($line in $envLines) {
      $parts = $line -split '=', 2
      if ($parts.Count -eq 2) { $envMap[$parts[0]] = $parts[1].Trim('"') }
    }

    $mysqldump = Get-Command mysqldump -ErrorAction SilentlyContinue
    if ($mysqldump) {
      $outSql = Join-Path $backupDir ("db-{0}-{1}.sql" -f $envMap['DB_DATABASE'],$ts)
      Write-Host "[backup] Dumping DB $($envMap['DB_DATABASE']) to $outSql"
      & $mysqldump.Path -h $envMap['DB_HOST'] -P ($envMap['DB_PORT'] ?? '3306') -u $envMap['DB_USERNAME'] --password=$envMap['DB_PASSWORD'] $envMap['DB_DATABASE'] | Set-Content -Path $outSql -Encoding Ascii
    } else {
      Write-Warning "[backup] mysqldump not found. Skipping DB dump."
    }
  }

  # 3) Optional: zip public storage
  $pubDir = Join-Path $ProjectRoot 'storage\app\public'
  if (Test-Path $pubDir) {
    $zipPath = Join-Path $backupDir ("storage-public-{0}.zip" -f $ts)
    Add-Type -AssemblyName 'System.IO.Compression.FileSystem'
    [System.IO.Compression.ZipFile]::CreateFromDirectory($pubDir, $zipPath)
    Write-Host "[backup] Zipped public storage to $zipPath"
  }

  Write-Host "[backup] Done. Files in: $backupDir"
}
finally {
  Pop-Location
}

