<?php

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileController extends Controller
{
        /**
         * Download a file.
         *
         * @param  \App\Models\File  $file
         * @return \Symfony\Component\HttpFoundation\StreamedResponse
         */
        public function download(File $file)
        {
            // Check if user has permission to download this file
            // This is a basic check - you might want to add more specific permission checks
            if (!auth()->check()) {
                abort(403, 'Unauthorized');
            }
            
            // Check if file exists in storage
            if (!Storage::exists($file->path)) {
                return redirect()->back()->with('error', 'File not found in storage.');
            }
            
            // Get file content type
            $fileContents = Storage::get($file->path);
            $mimeType = Storage::mimeType($file->path);
            
            // Create response with proper headers
            $response = new StreamedResponse(function() use ($fileContents) {
                echo $fileContents;
            }, 200, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'attachment; filename="' . $file->name . '"',
                'Content-Length' => strlen($fileContents),
            ]);
            
            return $response;
        }
        
        /**
         * Display a file (for images, PDFs, etc. that can be displayed in browser).
         *
         * @param  \App\Models\File  $file
         * @return \Illuminate\Http\Response
         */
        public function display(File $file)
        {
            // Check if user has permission to view this file
            if (!auth()->check()) {
                abort(403, 'Unauthorized');
            }
            
            // Check if file exists in storage
            if (!Storage::exists($file->path)) {
                return redirect()->back()->with('error', 'File not found in storage.');
            }
            
            // Get file content and mime type
            $fileContents = Storage::get($file->path);
            $mimeType = Storage::mimeType($file->path);
            
            // Return as inline response
            return response($fileContents, 200, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'inline; filename="' . $file->name . '"',
            ]);
        }
        
        /**
         * Delete a file.
         *
         * @param  \App\Models\File  $file
         * @return \Illuminate\Http\RedirectResponse
         */
        public function destroy(File $file)
        {
            // Check if user has permission to delete this file
            // Add more specific permission checks as needed
            if (!auth()->check() || !auth()->user()->isAdmin()) {
                abort(403, 'Unauthorized');
            }
            
            // Delete file from storage if it exists
            if (Storage::exists($file->path)) {
                Storage::delete($file->path);
            }
            
            // Delete the file record
            $file->delete();
            
            return redirect()->back()->with('success', 'File deleted successfully.');
        }
        
        /**
         * Upload a file for an order.
         *
         * @param  \Illuminate\Http\Request  $request
         * @return \Illuminate\Http\RedirectResponse
         */
        public function upload(Request $request)
        {
            // Validate the request
            $request->validate([
                'fileable_id' => 'required|integer',
                'fileable_type' => 'required|string',
                'files.*' => 'required|file|max:10240', // Max 10MB
            ]);
            
            // Get the model type and ID
            $fileableType = $request->fileable_type;
            $fileableId = $request->fileable_id;
            
            // Check if the model exists
            $model = $fileableType::find($fileableId);
            if (!$model) {
                return redirect()->back()->with('error', 'Cannot attach files to non-existent item.');
            }
            
        // Process file uploads
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $uploadedFile) {
                // Generate a unique filename
                $originalName = $uploadedFile->getClientOriginalName();
                $extension = $uploadedFile->getClientOriginalExtension();
                $fileName = pathinfo($originalName, PATHINFO_FILENAME);
                $uniqueName = $fileName . '_' . uniqid() . '.' . $extension;
                
                // Store the file using Laravel's storage system
                $path = $uploadedFile->storeAs(
                    'uploads/' . strtolower(class_basename($fileableType)), 
                    $uniqueName
                );
                
                // Create file record in database
                $file = new File();
                $file->name = $originalName;
                $file->path = $path;
                $file->size = $uploadedFile->getSize();
                $file->fileable_id = $fileableId;
                $file->fileable_type = $fileableType;
                $file->uploaded_by = auth()->id();
                $file->save();
            }
            
            return redirect()->back()->with('success', 'Files uploaded successfully.');
        }
        
        return redirect()->back()->with('error', 'No files were selected for upload.');
    }
}