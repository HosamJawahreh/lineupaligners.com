<?php

namespace App\Console\Commands;

use Illuminate\Foundation\Console\ServeCommand as BaseServeCommand;

use function Illuminate\Support\php_binary;

class ServeCommand extends BaseServeCommand
{
    /**
     * @return array<int, string>
     */
    protected function serverCommand(): array
    {
        $server = file_exists(base_path('server.php'))
            ? base_path('server.php')
            : base_path('vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php');

        return [
            php_binary(),
            '-d', 'upload_max_filesize=128M',
            '-d', 'post_max_size=132M',
            '-d', 'max_execution_time=300',
            '-d', 'memory_limit=256M',
            '-S',
            $this->host().':'.$this->port(),
            $server,
        ];
    }
}
