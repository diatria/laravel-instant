<?php
namespace Diatria\LaravelInstant\Utils;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUpload
{
    /**
     * Upload file ke storage.
     *
     * @param UploadedFile $file
     * @param string $path
     * @param string $disk
     * @return string
     */
    public static function upload(
        UploadedFile $file,
        string $path = 'uploads',
        string $disk = 'public'
    ): string {
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();

        return $file->storeAs($path, $filename, $disk);
    }

    /**
     * Hapus file jika ada.
     *
     * @param string|null $filePath
     * @param string $disk
     * @return bool
     */
    public static function delete(?string $filePath, string $disk = 'public'): bool
    {
        if (!$filePath) {
            return false;
        }

        if (Storage::disk($disk)->exists($filePath)) {
            return Storage::disk($disk)->delete($filePath);
        }

        return false;
    }

    /**
     * Replace file lama dengan file baru.
     *
     * @param UploadedFile $newFile
     * @param string|null $oldFile
     * @param string $path
     * @param string $disk
     * @return string
     */
    public static function replace(
        UploadedFile $newFile,
        ?string $oldFile,
        string $path = 'uploads',
        string $disk = 'public'
    ): string {
        self::delete($oldFile, $disk);

        return self::upload($newFile, $path, $disk);
    }

    /**
     * Mendapatkan URL file.
     *
     * @param string|null $filePath
     * @param string $disk
     * @return string|null
     */
    public static function url(?string $filePath, string $disk = 'public'): ?string
    {
        if (!$filePath) {
            return null;
        }

        return Storage::disk($disk)->url($filePath);
    }
}
