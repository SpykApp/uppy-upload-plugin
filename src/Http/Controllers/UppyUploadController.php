<?php

namespace SpykApp\UppyUpload\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UppyUploadController extends Controller
{
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file',
            'chunk_index' => 'required|integer|min:0',
            'total_chunks' => 'required|integer|min:1',
            'upload_id' => 'required|string',
            'filename' => 'required|string',
            'disk' => 'nullable|string',
            'directory' => 'nullable|string',
        ]);

        $disk = $request->input('disk', config('uppy-upload.disk', 'public'));
        $directory = $request->input('directory', config('uppy-upload.directory', 'uploads'));
        $uploadId = preg_replace('/[^a-zA-Z0-9_\-]/', '', $request->input('upload_id'));
        $chunkIndex = (int) $request->input('chunk_index');
        $totalChunks = (int) $request->input('total_chunks');
        $originalFilename = $request->input('filename');

        $directory = str_replace(['..', "\0"], '', $directory);

        $chunkDir = sys_get_temp_dir() . '/uppy_chunks/' . $uploadId;
        if (!is_dir($chunkDir)) {
            mkdir($chunkDir, 0755, true);
        }

        $request->file('file')->move($chunkDir, "chunk_{$chunkIndex}");

        $uploadedChunks = count(glob($chunkDir . '/chunk_*'));

        if ($uploadedChunks >= $totalChunks) {
            $finalPath = $this->mergeAndUpload($chunkDir, $totalChunks, $disk, $directory, $originalFilename);
            $this->cleanupChunkDir($chunkDir);

            $storage = Storage::disk($disk);

            return response()->json([
                'success' => true,
                'path' => $finalPath,
                'url' => $storage->url($finalPath),
                'filename' => basename($finalPath),
                'original_filename' => $originalFilename,
                'size' => $storage->size($finalPath),
                'mime_type' => $storage->mimeType($finalPath),
                'completed' => true,
            ]);
        }

        return response()->json([
            'success' => true,
            'completed' => false,
            'uploaded_chunks' => $uploadedChunks,
            'total_chunks' => $totalChunks,
            'progress' => round(($uploadedChunks / $totalChunks) * 100, 2),
        ]);
    }

    protected function mergeAndUpload(string $chunkDir, int $totalChunks, string $disk, string $directory, string $originalFilename): string
    {
        $extension = pathinfo($originalFilename, PATHINFO_EXTENSION);
        $basename = Str::slug(pathinfo($originalFilename, PATHINFO_FILENAME));
        $uniqueName = $basename . '-' . Str::random(10) . '.' . $extension;
        $finalPath = rtrim($directory, '/') . '/' . $uniqueName;

        $mergedTempFile = $chunkDir . '/merged_' . $uniqueName;
        $out = fopen($mergedTempFile, 'wb');

        if ($out === false) {
            throw new \RuntimeException("Cannot create merged temp file: {$mergedTempFile}");
        }

        for ($i = 0; $i < $totalChunks; $i++) {
            $chunkPath = $chunkDir . "/chunk_{$i}";

            if (!file_exists($chunkPath)) {
                fclose($out);
                throw new \RuntimeException("Missing chunk {$i} for upload");
            }

            $in = fopen($chunkPath, 'rb');
            if ($in === false) {
                fclose($out);
                throw new \RuntimeException("Cannot read chunk {$i}");
            }

            while (!feof($in)) {
                fwrite($out, fread($in, 8192));
            }

            fclose($in);
        }

        fclose($out);

        Storage::disk($disk)->putFileAs($directory, new \Illuminate\Http\File($mergedTempFile), $uniqueName);

        @unlink($mergedTempFile);

        return $finalPath;
    }

    protected function cleanupChunkDir(string $chunkDir): void
    {
        if (!is_dir($chunkDir)) {
            return;
        }

        foreach (glob($chunkDir . '/*') as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }

        @rmdir($chunkDir);
    }

    public function uploadSingle(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file',
            'filename' => 'required|string',
            'disk' => 'nullable|string',
            'directory' => 'nullable|string',
        ]);

        $disk = $request->input('disk', config('uppy-upload.disk', 'public'));
        $directory = $request->input('directory', config('uppy-upload.directory', 'uploads'));
        $originalFilename = $request->input('filename');

        $directory = str_replace(['..', "\0"], '', $directory);

        $storage = Storage::disk($disk);

        $extension = pathinfo($originalFilename, PATHINFO_EXTENSION);
        $basename = Str::slug(pathinfo($originalFilename, PATHINFO_FILENAME));
        $uniqueName = $basename . '-' . Str::random(10) . '.' . $extension;
        $finalPath = rtrim($directory, '/') . '/' . $uniqueName;

        $storage->putFileAs($directory, $request->file('file'), $uniqueName);

        return response()->json([
            'success' => true,
            'path' => $finalPath,
            'url' => $storage->url($finalPath),
            'filename' => $uniqueName,
            'original_filename' => $originalFilename,
            'size' => $storage->size($finalPath),
            'mime_type' => $storage->mimeType($finalPath),
            'completed' => true,
        ]);
    }

    public function delete(Request $request): JsonResponse
    {
        $request->validate([
            'path' => 'required|string',
            'disk' => 'nullable|string',
        ]);

        $disk = $request->input('disk', config('uppy-upload.disk', 'public'));
        $path = str_replace(['..', "\0"], '', $request->input('path'));

        $storage = Storage::disk($disk);

        if ($storage->exists($path)) {
            $storage->delete($path);
            return response()->json(['success' => true, 'message' => __('uppy-upload::uppy.file_deleted')]);
        }

        return response()->json(['success' => false, 'message' => __('uppy-upload::uppy.file_not_found')], 404);
    }
}
