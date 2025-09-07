<?php

namespace App\Http\Controllers\Service;

use Exception;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Str;

class FileService
{
    public function multipleUploadFile($request, $fieldName, $fileStore, $userId)
    {
        if (!$request->hasFile($fieldName)) {
            return [];
        }

        $filePaths = [];
        $files = is_array($request->file($fieldName))
            ? $request->file($fieldName)
            : [$request->file($fieldName)];

        foreach ($files as $file) {
            $originalName = $file->getClientOriginalName();
            $mime = $file->getMimeType();

            $correctExtension = match ($mime) {
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                default      => throw new Exception('Unsupported file type'),
            };

            $nameWithoutExtension = pathinfo($originalName, PATHINFO_FILENAME);
            $encryptedName = base64_encode($nameWithoutExtension);
            $encryptedNameWithExtension = $encryptedName . '.' . $correctExtension;

            // store inside public/products/{userId}
            $pathDestination = public_path($fileStore . '/' . $userId);
            if (!file_exists($pathDestination)) {
                mkdir($pathDestination, 0777, true);
            }

            if (file_exists($pathDestination . '/' . $encryptedNameWithExtension)) {
                $uuid = Str::uuid()->toString();
                $encryptedNameWithExtension = $uuid . '.' . $correctExtension;
            }

            $file->move($pathDestination, $encryptedNameWithExtension);

            // 👇 return path relative to public (include $fileStore)
            $filePaths[$originalName] = $fileStore . '/' . $userId . '/' . $encryptedNameWithExtension;
        }

        return $filePaths;
    }

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

    public function appendBaseUrl($path, $baseUrl)
    {
        // Check if the path is already a full URL
        if (!filter_var($path, FILTER_VALIDATE_URL)) {
            // If not, append the base URL
            return url($baseUrl . '/' . $path);
        }
        return $path;
    }

    public function imageDisplay($image, $path)
    {
        // Decode the JSON product image field
        $product_images = json_decode($image, true);

        // Initialize the variable to hold the URL of the first image
        $first_image_url = null;

        // Check if the decoded value is an array and has images
        if (is_array($product_images) && count($product_images) > 0) {
            // Process each image and append base URL
            foreach ($product_images as $key => $product_img) {
                // Append base URL to the image path
                $product_images[$key] = $this->appendBaseUrl(asset($product_img), $path);
            }

            // Retrieve the URL of the first image if there are images
            $first_image_url =  null ? null : reset($product_images);
        }

        return $first_image_url;
    }

    public function multipleDisplayUploadFile($path, $filePath)
    {
        if (isset($path)) {
            if (is_string($path)) {
                // Decode JSON string into an associative array
                $uploads = json_decode($path, true);

                // Handle decoding errors
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $uploads = [];
                }
            } else {
                // Ensure $path is an array if it's already decoded
                $uploads = is_array($path) ? $path : [];
            }

            // Append the base URL to each upload path
            foreach ($uploads as $filename => &$uploadPath) {
                $uploadPath = $this->appendBaseUrl(asset($uploadPath), $filePath);
            }
        } else {
            $uploads = [];
        }

        // Return the modified uploads array
        return $uploads;
    }

    public function multipleImageDisplaySingleImage($images)
    {
        $images = is_array($images) ? $images : json_decode($images, true);

        if (empty($images)) {
            return null;
        }

        foreach ($images as $key => $img) {
            $images[$key] = $this->appendBaseUrl($img, null);
        }

        return reset($images); // first image
    }
}
