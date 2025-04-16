<?php

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use ZipArchive;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileController extends Controller
{
    /**
     * Download a file for admin users
     * 
     * @param  int  $file
     * @return \Illuminate\Http\Response
     */
    public function adminDownload($file)
    {
        // Find the file
        $fileModel = File::findOrFail($file);
        
        // Check if file exists in storage
        if (!Storage::disk('public')->exists($fileModel->path)) {
            return back()->with('error', 'File not found in storage');
        }
        
        // Return file download
        return Storage::disk('public')->download($fileModel->path, $fileModel->original_name ?? $fileModel->name);
    }
    
    /**
     * Download multiple files as a ZIP for admin users
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function adminDownloadMultiple(Request $request)
    {
        $request->validate([
            'file_ids' => 'required'
        ]);
        
        // Get file IDs from request
        // Handle both array and JSON string
        $fileIds = $request->file_ids;
        if (is_string($fileIds)) {
            $fileIds = json_decode($fileIds, true);
        }
        
        // Make sure we have file IDs
        if (empty($fileIds)) {
            return back()->with('error', 'No files specified for download');
        }
        
        return $this->createAndDownloadZip($fileIds);
    }
    
    /**
     * Download a file for writer users
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function writerDownload($id)
    {
        // Find the file
        $file = File::findOrFail($id);
        
        // Check user permissions - writer can only download files from orders assigned to them
        $order = $file->fileable_type === 'App\Models\Order' ? $file->fileable : null;
        
        if (!$order || ($order->writer_id != Auth::id() && Auth::user()->usertype !== 'admin')) {
            return back()->with('error', 'You do not have permission to download this file');
        }
        
        // Check if file exists in storage
        if (!Storage::disk('public')->exists($file->path)) {
            return back()->with('error', 'File not found in storage');
        }
        
        // Return file download
        return Storage::disk('public')->download($file->path, $file->original_name ?? $file->name);
    }
    
    /**
     * Download multiple files as a ZIP for writer users
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function writerDownloadMultiple(Request $request)
    {
        $request->validate([
            'file_ids' => 'required'
        ]);
        
        // Get file IDs from request
        // Handle both array and JSON string
        $fileIds = $request->file_ids;
        if (is_string($fileIds)) {
            $fileIds = json_decode($fileIds, true);
        }
        
        // Make sure we have file IDs
        if (empty($fileIds)) {
            return back()->with('error', 'No files specified for download');
        }
        
        // Verify that the writer has access to all the files
        $files = File::whereIn('id', $fileIds)->get();
        
        foreach ($files as $file) {
            $order = $file->fileable_type === 'App\Models\Order' ? $file->fileable : null;
            
            if (!$order || ($order->writer_id != Auth::id() && Auth::user()->usertype !== 'admin')) {
                return back()->with('error', 'You do not have permission to download one or more of these files');
            }
        }
        
        return $this->createAndDownloadZip($fileIds);
    }
    
    /**
     * Generic download method for all user types
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function download($id)
    {
        // Find the file
        $file = File::findOrFail($id);
        
        // Check user permissions if this is a writer
        if (Auth::user()->usertype === 'writer') {
            $order = $file->fileable_type === 'App\Models\Order' ? $file->fileable : null;
            
            if (!$order || ($order->writer_id != Auth::id() && Auth::user()->usertype !== 'admin')) {
                return back()->with('error', 'You do not have permission to download this file');
            }
        }
        
        // Check if file exists in storage
        if (!Storage::disk('public')->exists($file->path)) {
            return back()->with('error', 'File not found in storage');
        }
        
        // Return file download
        return Storage::disk('public')->download($file->path, $file->original_name ?? $file->name);
    }
    
    /**
     * Create and download a ZIP file of the specified files
     *
     * @param  array  $fileIds
     * @return \Illuminate\Http\Response
     */
    private function createAndDownloadZip($fileIds)
    {
        // Get the files
        $files = File::whereIn('id', $fileIds)->get();
        
        if ($files->isEmpty()) {
            return back()->with('error', 'No files found to download');
        }
        
        // Create a temporary zip file
        $zipFileName = 'order-files-' . time() . '.zip';
        $tempPath = storage_path('app/public/temp');
        
        // Create temp directory if it doesn't exist
        if (!file_exists($tempPath)) {
            mkdir($tempPath, 0755, true);
        }
        
        $zipPath = $tempPath . '/' . $zipFileName;
        
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
            return back()->with('error', 'Cannot create zip file');
        }
        
        // Add files to the zip
        foreach ($files as $file) {
            if (Storage::disk('public')->exists($file->path)) {
                $fileContent = Storage::disk('public')->get($file->path);
                $filename = $file->original_name ?? $file->name;
                $zip->addFromString($filename, $fileContent);
            }
        }
        
        $zip->close();
        
        // Return the zip file and delete after sending
        return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
    }
}