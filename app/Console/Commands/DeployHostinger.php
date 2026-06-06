<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Support\PublicStorageUrl;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'lineup:deploy-hostinger')]
class DeployHostinger extends Command
{
    protected $signature = 'lineup:deploy-hostinger {--skip-cache : Skip Laravel cache clear}';

    protected $description = 'Prepare Hostinger/shared hosting: upload dirs, storage access, symlinks, permissions, cache';

    /** @var list<string> */
    private array $uploadDirs = [
        'profiles',
        'settings',
        'website',
        'website/images',
        'website/videos',
    ];

    public function handle(): int
    {
        $this->components->info('LineUp Hostinger deploy setup');

        $this->ensureUploadDirectories();
        $this->ensureStorageHtaccess();
        $this->fixPermissions();

        Artisan::call('lineup:link-public', [], $this->output);

        if (! $this->option('skip-cache')) {
            Artisan::call('config:clear');
            Artisan::call('view:clear');
            Artisan::call('cache:clear');
            $this->components->info('Laravel caches cleared.');
        }

        $this->runDiagnostics();

        return self::SUCCESS;
    }

    private function ensureUploadDirectories(): void
    {
        $base = storage_path('app/public');

        foreach ($this->uploadDirs as $dir) {
            $path = $base.'/'.$dir;

            if (! is_dir($path) && ! mkdir($path, 0755, true) && ! is_dir($path)) {
                $this->components->error("Could not create directory: {$path}");

                continue;
            }

            $this->line("OK  {$path}");
        }
    }

    private function ensureStorageHtaccess(): void
    {
        $htaccess = storage_path('app/public/.htaccess');
        $contents = <<<'HTACCESS'
<IfModule mod_authz_core.c>
    Require all granted
</IfModule>
<IfModule !mod_authz_core.c>
    Order allow,deny
    Allow from all
</IfModule>

Options -Indexes
HTACCESS;

        if (is_file($htaccess) && file_get_contents($htaccess) === $contents) {
            $this->line("OK  {$htaccess}");

            return;
        }

        if (file_put_contents($htaccess, $contents) === false) {
            $this->components->error("Could not write {$htaccess}");

            return;
        }

        @chmod($htaccess, 0644);
        $this->components->info("Wrote {$htaccess}");
    }

    private function fixPermissions(): void
    {
        $paths = [
            storage_path('app/public'),
            storage_path('framework'),
            storage_path('logs'),
            base_path('bootstrap/cache'),
        ];

        foreach ($paths as $path) {
            if (! is_dir($path)) {
                continue;
            }

            @chmod($path, 0755);
            $this->applyPermissionsRecursive($path);
        }

        $this->components->info('Storage permissions updated (755 dirs, 644 files).');
    }

    private function applyPermissionsRecursive(string $directory): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            @chmod($item->getPathname(), $item->isDir() ? 0755 : 0644);
        }
    }

    private function runDiagnostics(): void
    {
        $this->newLine();
        $this->components->info('Diagnostics');

        $this->checkSymlink(public_path('storage'), storage_path('app/public'), 'public/storage');
        $this->checkSymlink(public_path('assets'), base_path('assets'), 'public/assets');

        foreach (['profiles', 'settings', 'website'] as $folder) {
            $dir = storage_path('app/public/'.$folder);
            $count = is_dir($dir) ? count(glob($dir.'/*') ?: []) : 0;
            $status = $count > 0 ? "{$count} file(s)" : 'EMPTY — re-upload images in admin or copy from local';

            $this->line("  storage/app/public/{$folder}: {$status}");
        }

        $logoPath = '';

        try {
            $logoPath = trim((string) Setting::get('logo', ''));
        } catch (\Throwable) {
            $this->components->warn('  Could not read settings (database unavailable). Skipping logo check.');
        }

        if ($logoPath !== '') {
            $accessible = PublicStorageUrl::isPubliclyAccessible($logoPath);
            $this->line('  Logo setting: '.($accessible ? 'OK' : 'MISSING on disk — re-upload in Settings → Branding'));
        }

        $appUrl = rtrim((string) config('app.url'), '/');

        if (! str_starts_with($appUrl, 'https://')) {
            $this->components->warn('Set APP_URL=https://lineupaligner.com and APP_ENV=production in .env, then run: php artisan config:cache');
        } elseif (config('app.env') !== 'production') {
            $this->components->warn('Set APP_ENV=production in .env for live HTTPS cookies and URL forcing.');
        } else {
            $this->line('  HTTPS config: APP_URL uses https, production mode active.');
        }

        $this->newLine();
        $this->line('Test in browser:');
        $this->line('  '.$appUrl.'/assets/smiliz/images/logo.svg');
        $this->line('  '.$appUrl.'/storage/settings/ (should not 403)');
        $this->newLine();
        $this->components->warn('Uploaded files are NOT in git. After deploy, re-upload logo/photos in admin OR copy storage/app/public/* from your computer.');
    }

    private function checkSymlink(string $link, string $target, string $label): void
    {
        if (is_link($link) && realpath($link) === realpath($target)) {
            $this->line("  {$label}: OK");

            return;
        }

        if (is_link($link)) {
            $this->components->warn("  {$label}: broken symlink — run: bash scripts/hostinger-link.sh");

            return;
        }

        if (file_exists($link)) {
            $this->components->warn("  {$label}: exists but is not a symlink — run: bash scripts/hostinger-link.sh");

            return;
        }

        $this->components->warn("  {$label}: missing — run: bash scripts/hostinger-link.sh");
    }
}
