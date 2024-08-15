<?php

namespace Diatria\LaravelInstant\Console\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;

class MakeServiceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "make:li-service {model}";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Create a new Laravel Instant service class";

    protected $targetDirectory;

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        // Mengambil argument yang dituliskan di command, biasanya berisi "{directory}/{service name}"
        $argument = $this->argument("model");

        // Mengambil nama file dari argument yang dituliskan
        $serviceName = $this->getFileName($argument);

        // Mengambil directory dari argument yang dituliskan
        $this->targetDirectory = $this->getDirectory($argument);

        // Target File Service nantinya disimpan
        $targetPath = app_path(
            $this->getDirectoryService() . "/" . $serviceName . ".php"
        );

        // Replace Content
        $stubContent = $this->replaceContent($serviceName);

        // Membuat folder jika tidak ditemukan
        $this->createFolderIfNotExists(app_path($this->getDirectoryService()));

        // Menyimpan file service di app/Services
        file_put_contents($targetPath, $stubContent);

        $this->info("Service {$serviceName} created successfully.");
    }

    public function getDirectory($commandArgs)
    {
        $split = explode("/", $commandArgs);
        array_splice($split, -1, 1); // Menghapus nama file dan menyisakan path nya saja
        return join("/", $split);
    }

    public function getDirectoryService()
    {
        return "Services/" . $this->targetDirectory;
    }

    public function getFileName($commandArgs)
    {
        $split = explode("/", $commandArgs);
        return array_splice($split, -1, 1)[0]; // Mengambil nama file saja
    }

    public function replaceContent($serviceName)
    {
        $nameOnly = str_replace("Service", "", $serviceName);

        // prettier-ignore
        $modelNamespace = str_replace("/", "\\", $this->targetDirectory) . "\\" . $nameOnly;
        if (class_exists("App\Models\\" . $modelNamespace)) {
            $modelClass = app("App\Models\\" . $modelNamespace);
            $columns = json_encode($modelClass->getFillable());
        }

        // File path stub service
        $stubPath = __DIR__ . "/../../../stubs/service.stub";

        // Replace variable
        $stubContent = file_get_contents($stubPath);
        $stubContent = str_replace(
            "{Namespace}",
            str_replace("/", "\\", $this->getDirectoryService()),
            $stubContent
        );
        $stubContent = str_replace("{Class}", $serviceName, $stubContent);
        $stubContent = str_replace("{Model}", $nameOnly, $stubContent);
        $stubContent = str_replace(
            "{ModelNamespace}",
            $modelNamespace,
            $stubContent
        );
        $stubContent = str_replace(
            "{Columns}",
            $columns ?? json_encode([]),
            $stubContent
        );
        $stubContent = str_replace(
            "{PaginationPath}",
            Str::slug($this->targetDirectory . "/" . $nameOnly, "-", "en", [
                "/" => "",
            ]),
            $stubContent
        );

        // Remove double slash "\\"
        $stubContent = str_replace('\\\\', '\\', $stubContent);

        return $stubContent;
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
