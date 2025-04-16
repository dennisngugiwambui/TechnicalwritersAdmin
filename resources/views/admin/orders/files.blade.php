@extends('admin.app')

@section('content')
<div class="mb-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <nav class="flex" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('admin.dashboard') }}" class="text-gray-700 hover:text-primary-600 inline-flex items-center">
                        <i class="fas fa-home mr-2"></i>
                        Dashboard
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-400 mx-2 text-sm"></i>
                        <a href="{{ route('admin.orders.index') }}" class="text-gray-700 hover:text-primary-600">
                            Orders
                        </a>
                    </div>
                </li>
                <li>
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-400 mx-2 text-sm"></i>
                        <a href="{{ route('admin.orders.show', $order->id) }}" class="text-gray-700 hover:text-primary-600">
                            Order #{{ $order->id }}
                        </a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-400 mx-2 text-sm"></i>
                        <span class="text-gray-500">Files</span>
                    </div>
                </li>
            </ol>
        </nav>
    </div>
</div>

<div class="bg-white rounded-lg shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex justify-between items-center">
            <h3 class="text-lg font-medium text-gray-900">Files for Order #{{ $order->id }}: {{ $order->title }}</h3>
            <div class="flex space-x-2">
                <button onclick="document.getElementById('upload-modal').classList.remove('hidden')" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                    <i class="fas fa-upload mr-1.5"></i> Upload Files
                </button>
                
                @if(count($files) > 0)
                <form action="{{ route('admin.files.download-multiple') }}" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="file_ids" value="{{ json_encode($files->pluck('id')) }}">
                    <button type="submit" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-download mr-1.5"></i> Download All
                    </button>
                </form>
                @endif
                
                <a href="{{ route('admin.orders.show', $order->id) }}" class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                    <i class="fas fa-arrow-left mr-1.5"></i> Back to Order
                </a>
            </div>
        </div>
    </div>
    
    <div class="p-6">
        @if(session('success'))
            <div class="mb-4 bg-green-50 border-l-4 border-green-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-green-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-green-700">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif
        
        @if(count($files) > 0)
            <div class="flex flex-col">
                <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                    <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                        <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            File
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Description
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Uploaded By
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Date
                                        </th>
                                        <th scope="col" class="relative px-6 py-3">
                                            <span class="sr-only">Actions</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($files as $file)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-10 w-10 flex items-center justify-center">
                                                        @php
                                                            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                                                            $iconClass = 'fas fa-file text-gray-400';
                                                            
                                                            if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'bmp'])) {
                                                                $iconClass = 'fas fa-file-image text-blue-500';
                                                            } elseif (in_array($extension, ['doc', 'docx', 'odt', 'rtf'])) {
                                                                $iconClass = 'fas fa-file-word text-blue-600';
                                                            } elseif (in_array($extension, ['xls', 'xlsx', 'csv'])) {
                                                                $iconClass = 'fas fa-file-excel text-green-600';
                                                            } elseif ($extension === 'pdf') {
                                                                $iconClass = 'fas fa-file-pdf text-red-600';
                                                            } elseif (in_array($extension, ['zip', 'rar', '7z', 'tar', 'gz'])) {
                                                                $iconClass = 'fas fa-file-archive text-yellow-600';
                                                            } elseif (in_array($extension, ['ppt', 'pptx'])) {
                                                                $iconClass = 'fas fa-file-powerpoint text-orange-600';
                                                            }
                                                        @endphp
                                                        <i class="{{ $iconClass }} text-xl"></i>
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900">
                                                            {{ $file['name'] }}
                                                        </div>
                                                        <div class="text-xs text-gray-500">
                                                            {{ $file['size'] }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ $file['description'] ?? 'No description' }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ $file['uploaded_by'] }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $file['uploaded_at'] }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <a href="{{ route('admin.files.download', $file['id']) }}" class="text-primary-600 hover:text-primary-900 mr-4">
                                                    <i class="fas fa-download"></i> Download
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No files</h3>
                <p class="mt-1 text-sm text-gray-500">Get started by uploading a file for this order.</p>
                <div class="mt-6">
                    <button type="button" onclick="document.getElementById('upload-modal').classList.remove('hidden')" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        <i class="fas fa-upload mr-2"></i>
                        Upload Files
                    </button>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Upload Modal -->
<div id="upload-modal" class="fixed inset-0 z-50 overflow-auto bg-gray-900 bg-opacity-50 hidden">
    <div class="relative p-8 bg-white max-w-md m-auto flex-col flex rounded-lg shadow-lg mt-20">
        <div class="flex justify-between items-center mb-6">
            <h4 class="text-xl font-medium text-gray-900">Upload Files</h4>
            <button onclick="document.getElementById('upload-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-500">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form action="{{ route('admin.orders.upload-files') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="order_id" value="{{ $order->id }}">
            
            <div class="mb-4">
                <label for="file_description" class="block text-sm font-medium text-gray-700 mb-1">File Description</label>
                <input type="text" id="file_description" name="file_description" class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm" value="{{ old('file_description') }}" required>
            </div>
            
            <div class="mb-6">
                <label for="upload_files" class="block text-sm font-medium text-gray-700 mb-1">Upload Files</label>
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                    <div class="space-y-1 text-center">
                        <i class="fas fa-upload text-gray-400 text-3xl mb-2"></i>
                        <div class="flex text-sm text-gray-600">
                            <label for="upload_files" class="relative cursor-pointer bg-white rounded-md font-medium text-primary-600 hover:text-primary-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary-500">
                                <span>Upload files</span>
                                <input id="upload_files" name="files[]" type="file" class="sr-only" multiple required>
                            </label>
                            <p class="pl-1">or drag and drop</p>
                        </div>
                        <p class="text-xs text-gray-500">
                            All file types supported, up to 99MB each
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="flex justify-end">
                <button type="button" onclick="document.getElementById('upload-modal').classList.add('hidden')" class="mr-3 px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                    Upload Files
                </button>
            </div>
        </form>
    </div>
</div>
@endsection