<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'lineup:link-public')]
class LinkPublicPaths extends Command
{
    protected $signature = 'lineup:link-public {--force : Replace existing paths}';

    protected $description = 'Link public/storage and public/assets for shared hosting (Hostinger-safe)';

    /** @var array<string, string> */
    private array $links = [];

    public function handle(): int
    {
        $this->links = [
            public_path('storage') => storage_path('app/public'),
            public_path('assets') => base_path('assets'),
        ];

        $failed = false;

        foreach ($this->links as $link => $target) {
            if (! $this->linkPath($link, $target)) {
                $failed = true;
            }
        }

        if ($failed) {
            $this->newLine();
            $this->warn('PHP could not create symlinks on this host. Run over SSH:');
            $this->newLine();
            $this->line('  cd '.public_path());
            $this->line('  ln -s ../storage/app/public storage');
            $this->line('  ln -s ../assets assets');
            $this->newLine();
            $this->line('Or: bash scripts/hostinger-link.sh');

            return self::FAILURE;
        }

        $this->info('Public paths linked successfully.');

        return self::SUCCESS;
    }

    private function linkPath(string $link, string $target): bool
    {
        if (! is_dir($target)) {
            $this->error("Target missing: {$target}");

            return false;
        }

        if (file_exists($link) || is_link($link)) {
            if (is_link($link) && realpath($link) === realpath($target)) {
                $this->line("OK  {$link} -> {$target}");

                return true;
            }

            if (! $this->option('force')) {
                $this->warn("Skip {$link} (already exists). Use --force to replace.");

                return true;
            }

            if (is_link($link)) {
                unlink($link);
            } elseif (is_dir($link)) {
                $this->error("Cannot replace directory: {$link}");

                return false;
            } else {
                unlink($link);
            }
        }

        if (@symlink($target, $link)) {
            $this->line("Linked {$link} -> {$target}");

            return true;
        }

        $this->error("Could not link {$link} (symlink/exec disabled in PHP).");

        return false;
    }
}
