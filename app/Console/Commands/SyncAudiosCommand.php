<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use phpseclib3\Net\SFTP;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'audios:sync-remote', description: 'Sincroniza storage/app/public/audios al servidor de producción vía SFTP (password)')]
class SyncAudiosCommand extends Command
{
    protected $signature = 'audios:sync-remote '
        . '{--dry-run : Lista archivos sin subir}'
        . '{--local= : Directorio local de audios (por defecto storage/app/public/audios)}';

    protected $description = 'Sincroniza storage/app/public/audios al servidor de producción vía SFTP (password)';

    public function handle(): int
    {
        $host   = config('deploy.ssh_host', env('DEPLOY_SSH_HOST'));
        $port   = (int) (config('deploy.ssh_port', env('DEPLOY_SSH_PORT', 22)));
        $user   = config('deploy.ssh_user', env('DEPLOY_SSH_USER'));
        $pass   = config('deploy.ssh_password', env('DEPLOY_SSH_PASSWORD'));
        $target = rtrim(config('deploy.target_dir', env('DEPLOY_TARGET_DIR', '/var/www/audios_app')), '/');

        if (!$host || !$user || !$pass || !$target) {
            $this->error('Faltan variables DEPLOY_* en .env');
            return self::FAILURE;
        }

        $local = $this->option('local') ?: storage_path('app/public/audios');
        if (!is_dir($local)) {
            $this->error("No existe el directorio local: {$local}");
            return self::FAILURE;
        }

        $remoteBase = $target . '/storage/app/public/audios';

        $this->info("Conectando a {$host}:{$port} como {$user}...");
        $sftp = new SFTP($host, $port, 30);
        if (!$sftp->login($user, $pass)) {
            $this->error('Login SFTP falló');
            return self::FAILURE;
        }

        $this->ensureRemoteDir($sftp, $remoteBase);

        $dry = (bool) $this->option('dry-run');
        $uploaded = 0;

        $rii = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($local, \FilesystemIterator::SKIP_DOTS));
        foreach ($rii as $file) {
            if ($file->isDir()) continue;
            $localPath  = $file->getPathname();
            $relPath    = ltrim(str_replace('\\', '/', substr($localPath, strlen($local))), '/');
            $remotePath = $remoteBase . '/' . $relPath;
            $remoteDir  = dirname($remotePath);
            $this->ensureRemoteDir($sftp, $remoteDir);

            if ($dry) {
                $this->line("[dry] subir: {$localPath} -> {$remotePath}");
                continue;
            }

            if (!$sftp->put($remotePath, $localPath, SFTP::SOURCE_LOCAL_FILE)) {
                $this->error("Fallo al subir: {$localPath}");
                return self::FAILURE;
            }
            $uploaded++;
        }

        $this->info($dry ? 'Dry-run completado.' : "Archivos subidos: {$uploaded}");
        return self::SUCCESS;
    }

    private function ensureRemoteDir(SFTP $sftp, string $path): void
    {
        $parts = explode('/', trim($path, '/'));
        $accum = '';
        foreach ($parts as $part) {
            $accum .= '/' . $part;
            if (!$sftp->is_dir($accum)) {
                $sftp->mkdir($accum);
            }
        }
    }
}
