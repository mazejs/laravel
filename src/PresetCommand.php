<?php

namespace Maze;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class PresetCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maze:preset
        {--i|ignore : Ignore exporting views}
        {--f|force : Overwrite existing views by default}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Preset maze with vue js and tailwind css';

    /**
     * The views that need to be exported.
     *
     * @var array
     */
    protected $views = [
        'auth/login.stub' => 'auth/login.blade.php',
        'auth/passwords/confirm.stub' => 'auth/passwords/confirm.blade.php',
        'auth/passwords/email.stub' => 'auth/passwords/email.blade.php',
        'auth/passwords/reset.stub' => 'auth/passwords/reset.blade.php',
        'auth/register.stub' => 'auth/register.blade.php',
        'auth/verify.stub' => 'auth/verify.blade.php',
        'errors/401.stub' => 'errors/401.blade.php',
        'errors/403.stub' => 'errors/403.blade.php',
        'errors/404.stub' => 'errors/404.blade.php',
        'errors/419.stub' => 'errors/419.blade.php',
        'errors/429.stub' => 'errors/429.blade.php',
        'errors/500.stub' => 'errors/500.blade.php',
        'errors/503.stub' => 'errors/503.blade.php',
        'layouts/app.stub' => 'layouts/app.blade.php',
        'layouts/error.stub' => 'layouts/error.blade.php',
        'layouts/guest.stub' => 'layouts/guest.blade.php',
        'layouts/navbar/brand.stub' => 'layouts/navbar/brand.blade.php',
        'layouts/navbar/left.stub' => 'layouts/navbar/left.blade.php',
        'layouts/navbar/right.stub' => 'layouts/navbar/right.blade.php',
        'layouts/sidebar/menu.stub' => 'layouts/sidebar/menu.blade.php',
        'layouts/sidebar/notification.stub' => 'layouts/sidebar/notification.blade.php',
        'home.stub' => 'home.blade.php',
        'welcome.stub' => 'welcome.blade.php'
    ];

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->filesystem = new Filesystem;

        $this->updatePackages();
        $this->ensureDirectoriesExist();
        $this->exportViews();
        $this->cleanSassDirectory();
        $this->exportSass();
        $this->cleanJsDirectory();
        $this->exportJs();
    }

    /**
     * Create the directories for the files.
     *
     * @return void
     */
    protected function ensureDirectoriesExist()
    {
        $directories = ['auth/passwords', 'errors', 'layouts', 'layouts/navbar', 'layouts/sidebar'];

        if ($this->option('ignore'))
            return;

        foreach ($directories as $item) {
            if (! is_dir($directory = $this->getViewPath($item))) {
                mkdir($directory, 0755, true);
            }
        }
    }

    /**
     * Export the authentication views.
     *
     * @return void
     */
    protected function exportViews()
    {
        if ($this->option('ignore'))
            return;

        foreach ($this->views as $key => $value) {
            if (file_exists($view = $this->getViewPath($value)) && ! $this->option('force')) {
                if (! $this->confirm("The [{$value}] view already exists. Do you want to replace it?")) {
                    continue;
                }
            }

            copy(__DIR__ . '/stubs/views/' . $key, $view);
        }

        $this->info('Views files scaffold successfully.');
    }

    /**
     * Remove all files/folders from sass directory.
     *
     * @return void
     */
    protected function cleanSassDirectory()
    {
        $this->filesystem->cleanDirectory(resource_path('sass'));
    }

    /**
     * Export all sass stubs files.
     *
     * @return void
     */
    protected function exportSass()
    {
        copy(__DIR__ . '/stubs/sass/app.stub', resource_path('sass') . '/app.scss');

        $this->info('Sass files scaffold successfully.');
    }

    /**
     * Remove all files/folders from javascript directory.
     *
     * @return void
     */
    protected function cleanJsDirectory()
    {
        $this->filesystem->cleanDirectory(resource_path('js'));
    }

    /**
     * Export all javascript stubs files.
     *
     * @return void
     */
    protected function exportJs()
    {
        copy(__DIR__ . '/stubs/js/webpack.stub',  base_path() . '/webpack.mix.js');
        copy(__DIR__ . '/stubs/js/tailwind.stub', base_path() . '/tailwind.config.js');
        copy(__DIR__ . '/stubs/js/app.stub', resource_path('js') . '/app.js');
        copy(__DIR__ . '/stubs/js/bootstrap.stub', resource_path('js') . '/bootstrap.js');

        $this->info('Javascript files scaffold successfully.');
    }

    /**
     * Get full view path relative to the application's configured view path.
     *
     * @param  string  $path
     * @return string
     */
    protected function getViewPath($path)
    {
        return implode(DIRECTORY_SEPARATOR, [
            config('view.paths')[0] ?? resource_path('views'), $path,
        ]);
    }

    /**
     * Change the "package.json" file.
     *
     * @return void
     */
    protected static function updatePackages()
    {
        if (! file_exists(base_path('package.json'))) {
            return;
        }

        $packages = json_decode(file_get_contents(base_path('package.json')), true);

        $packages['devDependencies'] = [
            'axios' => '^0.19',
            'cross-env' => '^7.0',
            'laravel-mix' => '^5.0.1',
            'resolve-url-loader' => '^2.3.1',
            'sass' => '^1.20.1',
            'sass-loader' => '^8.0.0',
            'vue-template-compiler' => '^2.6.11'
        ];

        $packages['dependencies'] = [
            'mazejs' => '^0.1.0',
            'moment' => '^2.26.0',
            'tailwindcss' => '^1.4.6',
            'vue' => '^2.6.11'
        ];

        file_put_contents(
            base_path('package.json'),
            json_encode($packages, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT).PHP_EOL
        );
    }
}
