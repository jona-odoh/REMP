<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use ZipArchive;

class BackupSystemCommand extends Command
{
    protected $signature = 'ksmct:backup {--output= : Custom backup directory path}';

    protected $description = 'Create an on-premise backup archive containing a MySQL dump and uploaded files.';

    public function handle(): int
    {
        $timestamp = now()->format('Ymd_His');
        $backupDir = $this->option('output') ?: storage_path('app/backups');
        File::ensureDirectoryExists($backupDir);

        $sqlFile = "{$backupDir}/ksmct_db_{$timestamp}.sql";
        $zipFile = "{$backupDir}/ksmct_backup_{$timestamp}.zip";

        $connection = config('database.default');
        $dbConfig = config("database.connections.{$connection}");

        if ($connection === 'mysql') {
            $this->info('Dumping MySQL database...');

            $command = [
                'mysqldump',
                '--single-transaction',
                '--routines',
                '--triggers',
                '--default-character-set=utf8mb4',
                '-h', (string) ($dbConfig['host'] ?? '127.0.0.1'),
                '-P', (string) ($dbConfig['port'] ?? '3306'),
                '-u', (string) ($dbConfig['username'] ?? 'root'),
                (string) ($dbConfig['database'] ?? 'ksmct_db'),
            ];

            $env = [];
            if (! empty($dbConfig['password'])) {
                $env['MYSQL_PWD'] = (string) $dbConfig['password'];
            }

            $process = new Process($command, null, $env, null, 300);
            $process->run();

            if (! $process->isSuccessful()) {
                $this->error('mysqldump failed: '.$process->getErrorOutput());

                return self::FAILURE;
            }

            File::put($sqlFile, $process->getOutput());
        } else {
            $this->warn("Active DB connection is '{$connection}' (not mysql). Skipping mysqldump and archiving storage only.");
        }

        $this->info('Packaging database dump and uploaded files into ZIP archive...');

        $zip = new ZipArchive;
        if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            $this->error("Unable to create archive at {$zipFile}");

            return self::FAILURE;
        }

        if (File::exists($sqlFile)) {
            $zip->addFile($sqlFile, 'database/'.basename($sqlFile));
        }

        $uploadsDir = storage_path('app/private');
        if (File::isDirectory($uploadsDir)) {
            foreach (File::allFiles($uploadsDir) as $file) {
                $relativePath = 'uploads/'.ltrim(str_replace($uploadsDir, '', $file->getRealPath()), DIRECTORY_SEPARATOR);
                $zip->addFile($file->getRealPath(), str_replace('\\', '/', $relativePath));
            }
        }

        $zip->close();

        if (File::exists($sqlFile)) {
            File::delete($sqlFile);
        }

        $this->info("Backup completed successfully: {$zipFile}");

        return self::SUCCESS;
    }
}
