<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class DownloadService
{
    /**
     * Download a file from user's input
     *
     * @param  string $storageType  e.g. s3
     * @param  string $fileType     e.g. application/json, application/text
     * @param  string $fileName
     * @return file
     */
    public function downloadFile($storageType, $fileType, $fileName)
    {
        $filePath = Storage::disk($storageType)->url($fileName);
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=" . basename($filePath));
        header("Content-Type: " . $fileType);
        return file_get_contents($filePath);
    }
}