<?php

namespace Diatria\LaravelInstant\Console\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class MakeControllerCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = "make:li-controller {name}";

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = "Create a new Laravel Instant controller class";

	protected $targetDirectory;

	/**
	 * Execute the console command.
	 */
	public function handle(): void
	{
		// Mengambil argument yang dituliskan di command, biasanya berisi "{directory}/{service name}"
		$argument = $this->argument("name");
		$explodingArgs = explode('/', $argument);
		$path = implode('/', Arr::take($explodingArgs, count($explodingArgs) - 1));
		$name = str_replace('Controller', '', $explodingArgs[count($explodingArgs) - 1]); // UserController => User
		$namespace = $this->backslash('App\Http\\' . $path);
		$namespaceModel = 'App\Models\\' . $name;
		$namespaceService = $this->backslash('App\Services\\' . $path . '\\' . $name) . 'Service';
		
		// File path stub service
        $stubPath = __DIR__ . "/../../../stubs/controller.stub";

        // Replace variable
        $stubContent = file_get_contents($stubPath);

		// Membuat folder jika tidak ditemukan
        $this->createFolderIfNotExists(app_path(
			"Http/Controllers/" . $path
		));

		// Target File Service nantinya disimpan
        $targetPath = app_path(
            "Http/Controllers/" . $path . '/' . $name . "Controller" . ".php"
        );

		$stubContent = str_replace(
			[
				'{{ name }}',
				'{{ name_lc }}',
				'{{ namespace }}',
				'{{ namespace_model }}',
				'{{ namespace_service }}',
				'\\\\',
				'\\;'
			],
			[
				$name,
				Str::snake($name),
				$namespace,
				$namespaceModel,
				$namespaceService,
				'\\',
				';'
			],
			$stubContent
		);

		file_put_contents($targetPath, $stubContent);

		// $this->info(json_encode($explodingArgs));
		$this->info($namespaceService);
		$this->info("Controller created successfully.");
	}

	public function backslash($string): string
	{
		return str_replace('/', '\\', $string);
	}

	public function createFolderIfNotExists($targetDir)
    {
        // Cek apakah folder sudah ada
        if (!is_dir($targetDir)) {
            // Buat folder jika tidak ada
            mkdir($targetDir, 0755, true);
        }
    }
}
