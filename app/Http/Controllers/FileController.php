<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Services\FileUploadService;

class FileController extends Controller
{
    protected $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }

    /**
     * Display the file management interface.
     */
    public function index()
    {
        // Get all files uploaded by the user
        $files = Storage::disk('public')->allFiles('uploads');

        // Filter files by user (in a real app, you would store user_id with files)
        $fileDetails = [];
        foreach ($files as $file) {
            $fileDetails[] = [
                'name' => basename($file),
                'path' => $file,
                'size' => Storage::disk('public')->size($file),
                'modified' => Storage::disk('public')->lastModified($file),
                'url' => Storage::url($file),
            ];
        }

        return view('files.index', compact('fileDetails'));
    }

    /**
     * Upload a new file.
     */
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:2048', // 2MB max
        ]);

        if ($request->hasFile('file')) {
            $uploadResult = $this->fileUploadService->uploadFile($request->file('file'), 'uploads');

            if ($uploadResult['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'File uploaded successfully.',
                    'file' => $uploadResult,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $uploadResult['error'],
                ], 422);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'No file provided.',
        ], 422);
    }

    /**
     * Download a file.
     */
    public function download($filename)
    {
        $path = 'uploads/' . $filename;

        if (!Storage::disk('public')->exists($path)) {
            abort(404);
        }

        return response()->download(storage_path('app/public/' . $path));
    }

    /**
     * Delete a file.
     */
    public function destroy($filename)
    {
        $path = 'uploads/' . $filename;

        if (!Storage::disk('public')->exists($path)) {
            return response()->json([
                'success' => false,
                'message' => 'File not found.',
            ], 404);
        }

        if (Storage::disk('public')->delete($path)) {
            return response()->json([
                'success' => true,
                'message' => 'File deleted successfully.',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to delete file.',
        ], 500);
    }

    /**
     * Preview a file.
     */
    public function preview($filename)
    {
        $path = 'uploads/' . $filename;

        if (!Storage::disk('public')->exists($path)) {
            abort(404);
        }

        // Get file content
        $content = Storage::disk('public')->get($path);

        // Determine MIME type from file extension
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $mimeType = $this->getMimeTypeFromExtension($extension);

        // For images, return the image directly
        if (strpos($mimeType, 'image/') === 0) {
            return response($content)->header('Content-Type', $mimeType);
        }

        // For PDFs, return the PDF content
        if ($mimeType === 'application/pdf') {
            return response($content)->header('Content-Type', $mimeType);
        }

        // For text files, return the content as text
        if (strpos($mimeType, 'text/') === 0) {
            return response($content)->header('Content-Type', 'text/plain');
        }

        // For other file types, show a preview page
        return view('files.preview', compact('filename', 'mimeType', 'content'));
    }

    /**
     * Get MIME type from file extension
     */
    private function getMimeTypeFromExtension($extension)
    {
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'pdf' => 'application/pdf',
            'txt' => 'text/plain',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ];

        return $mimeTypes[strtolower($extension)] ?? 'application/octet-stream';
    }
}
