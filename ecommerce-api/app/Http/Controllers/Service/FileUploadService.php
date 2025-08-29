<?php
namespace App\Http\Controllers\Service;

use Illuminate\Support\Str;

class FileUploadService
{
    public function uploadFile($file, $directory, $existingFileName = null)
    {
        if ($file) {
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $nameWithoutExtension = str_replace("." . $extension, "", $originalName);
            $encryptedName = base64_encode($nameWithoutExtension);
            $encryptedNameWithExtension = $encryptedName . '.' . $extension;

            if (file_exists(public_path($directory . '/' . $encryptedNameWithExtension))) {
                $uuid = Str::uuid()->toString();
                $encryptedNameWithExtension = $uuid . '.' . $extension;
            }

            // Move the file
            $file->move(public_path($directory), $encryptedNameWithExtension);

            // Delete old file if exists
            if (!empty($existingFileName)) {
                $existingFilePath = public_path($directory . '/' . $existingFileName);
                if (file_exists($existingFilePath)) {
                    unlink($existingFilePath);
                }
            }

            return $encryptedNameWithExtension;
        }

        return null;
    }
}
