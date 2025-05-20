<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CleanupDarkMode extends Command
{
    protected $signature = 'cleanup:dark-mode';
    protected $description = 'Menghapus semua class Tailwind dark:* dari file blade.';

    public function handle()
    {
        $this->info('Membersihkan class dark:* dari file blade...');

        $files = File::allFiles(resource_path('views'));

        foreach ($files as $file) {
            if ($file->getExtension() === 'php') {
                $content = File::get($file->getRealPath());

                // Hapus semua class dark:* pakai regex
                $newContent = preg_replace('/\bdark:[^\s"\']+/', '', $content);

                if ($newContent !== $content) {
                    File::put($file->getRealPath(), $newContent);
                    $this->line("✔ Dibersihkan: {$file->getFilename()}");
                }
            }
        }

        $this->info('Selesai!');
        return Command::SUCCESS;
    }
}
