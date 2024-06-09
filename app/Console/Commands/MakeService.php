<?php

namespace App\Console\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;

class MakeService extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:service {model}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new service class';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $modelParam = $this->argument('model');
        $model = $modelParam;
        $modelLowerCase = Str::lower($model);
        $className = "{$model}Service";
        $columns = json_encode([]);
        if (class_exists("App\Models\\$modelParam")) {
            $modelClass = app("App\Models\\$modelParam");
            $columns = json_encode($modelClass->getFillable(), JSON_PRETTY_PRINT);
        }

        $content = <<<CONTENT
        <?php
        namespace App\Services;
    
        use App\Models\\$model;
        use App\Traits\InstantServiceTrait;

        class $className {
            use InstantServiceTrait;

            /**
             * Class model yang digunakan
             * 
             * @var App\Models\\$model
             */
            protected \$model;

            /**
             * Path pagination
             * 
             * @var string
             */
            protected \$paginationPath = '/$modelLowerCase/table';

            /**
             * List kolom yang akan ditampilkan
             * 
             * @var array
             */
            protected \$columns = $columns;

            /**
             * List kolom yang required ketika akan menyimpan data
             * 
             * @var array
             */
            protected \$columnsRequired = $columns;
            
            /**
             * @param App\Models\\$model \$model class
             */
            public function initModel()
            {
                \$this->model = (new $model());
                return \$this;
            }
        }
        CONTENT;

        // Check direktori Service
        if (!is_dir('app/Services')) mkdir('app/Services');

        fwrite(fopen("app/Services/{$className}.php", 'w'), $content);
    }
}
