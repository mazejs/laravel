<?php

namespace Maze;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class FilterCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:filter
        {name : The name of query filter}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new model query filter';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function handle()
    {
        $this->createFolder();

        $this->createFilter();

        $this->info('Filter created successfully.');
    }

    /**
     * Replace the class name for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     * @return string
     */
    protected function replaceClass($stub, $name)
    {
        return str_replace(['DummyClass', '{{ class }}', '{{class}}'], $name, $stub);
    }

    /**
     * Create folder Filters in case it don't exists.
     *
     */
    protected function createFolder()
    {
        if (! is_dir($directory = app_path('Filters'))) {
            mkdir($directory, 0755, true);
        }
    }

    /**
     * Create Filter with the given name
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function createFilter()
    {
        $filesystem = new Filesystem;
        $name = $this->argument('name');

        $filesystem->put(
            base_path("./app/Filters/{$name}.php"),
            $this->replaceClass($stub = $filesystem->get(__DIR__. '/stubs/filter.stub'), $name)
        );
    }
}
