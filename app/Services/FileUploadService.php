<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FileUploadService
{
    /**
     * Allowed file types
     */
    protected $allowedMimes = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'image/jpeg',
        'image/png',
    ];

    /**
     * Maximum file size in bytes (2MB)
     */
    protected $maxFileSize = 2097152;

    /**
     * Upload and validate a file
     */
    public function uploadFile(UploadedFile $file, string $directory = 'uploads'): array
    {
        try {
            // Validate file
            $this->validateFile($file);

            // Generate a unique filename
            $filename = $this->generateUniqueFilename($file);

            // Store the file
            $path = $file->storeAs($directory, $filename, 'public');

            // Return success response
            return [
                'success' => true,
                'path' => $path,
                'filename' => $filename,
                'original_name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
            ];
        } catch (\Exception $e) {
            Log::error('File upload failed: ' . $e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Validate the uploaded file
     */
    protected function validateFile(UploadedFile $file): void
    {
        // Check file size
        if ($file->getSize() > $this->maxFileSize) {
            throw new \Exception('File size exceeds the maximum allowed size of ' . $this->maxFileSize . ' bytes.');
        }

        // Check file type
        if (!in_array($file->getMimeType(), $this->allowedMimes)) {
            throw new \Exception('File type is not allowed. Allowed types: ' . implode(', ', $this->allowedMimes));
        }

        // Check if file was uploaded successfully
        if (!$file->isValid()) {
            throw new \Exception('File upload failed.');
        }
    }

    /**
     * Generate a unique filename
     */
    protected function generateUniqueFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $basename = Str::random(32);

        return $basename . '.' . $extension;
    }

    /**
     * Delete a file
     */
    public function deleteFile(string $path): bool
    {
        try {
            if (Storage::disk('public')->exists($path)) {
                return Storage::disk('public')->delete($path);
            }

            return false;
        } catch (\Exception $e) {
            Log::error('File deletion failed: ' . $e->getMessage());
            return false;
        }
    }
}
