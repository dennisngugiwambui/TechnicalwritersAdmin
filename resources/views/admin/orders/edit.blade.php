@extends('admin.app')

@section('title', 'Edit Order #' . $order->id)

@section('page-title', 'Edit Order #' . $order->id)

@section('content')
<div class="bg-white rounded-lg shadow-sm overflow-hidden">
    <div class="p-6 border-b border-gray-200">
        <div class="flex justify-between items-center">
            <h3 class="text-lg font-medium text-gray-900">Edit Order #{{ $order->id }}</h3>
            <div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                @if($order->status == 'available')
                    bg-blue-100 text-blue-800
                @elseif(in_array($order->status, ['confirmed', 'in_progress', 'done', 'delivered']))
                    bg-orange-100 text-orange-800
                @elseif($order->status == 'revision')
                    bg-red-100 text-red-800
                @elseif(in_array($order->status, ['completed', 'paid', 'finished']))
                    bg-green-100 text-green-800
                @elseif($order->status == 'dispute')
                    bg-yellow-100 text-yellow-800
                @elseif($order->status == 'cancelled')
                    bg-gray-100 text-gray-800
                @else
                    bg-gray-100 text-gray-800
                @endif
                ">
                    {{ ucfirst($order->status) }}
                </span>
            </div>
        </div>
        <p class="mt-1 text-sm text-gray-500">Created {{ $order->created_at->format('M d, Y h:i A') }}</p>
    </div>

    <form action="{{ route('admin.orders.update', $order->id) }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Left Column -->
            <div class="space-y-6">
                <!-- Basic Order Information -->
                <div>
                    <h4 class="text-base font-medium text-gray-900 mb-4">Basic Information</h4>
                    
                    <!-- Order Title -->
                    <div class="mb-4">
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Order Title <span class="text-red-600">*</span></label>
                        <input type="text" name="title" id="title" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm @error('title') border-red-300 text-red-900 placeholder-red-300 focus:border-red-500 focus:ring-red-500 @enderror" value="{{ old('title', $order->title) }}" required>
                        @error('title')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Type of Service -->
                    <div class="mb-4">
                        <label for="type_of_service" class="block text-sm font-medium text-gray-700 mb-1">Type of Service <span class="text-red-600">*</span></label>
                        <select name="type_of_service" id="type_of_service" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm @error('type_of_service') border-red-300 text-red-900 focus:border-red-500 focus:ring-red-500 @enderror" required>
                            <option value="">Select service type</option>
                            @foreach($serviceTypes as $type)
                                <option value="{{ $type }}" {{ old('type_of_service', $order->type_of_service) == $type ? 'selected' : '' }}>{{ $type }}</option>
                            @endforeach
                        </select>
                        @error('type_of_service')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Academic Discipline -->
                    <div class="mb-4">
                        <label for="discipline" class="block text-sm font-medium text-gray-700 mb-1">Academic Discipline <span class="text-red-600">*</span></label>
                        <select name="discipline" id="discipline" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm @error('discipline') border-red-300 text-red-900 focus:border-red-500 focus:ring-red-500 @enderror" required>
                            <option value="">Select discipline</option>
                            @foreach($disciplines as $discipline)
                                <option value="{{ $discipline }}" {{ old('discipline', $order->discipline) == $discipline ? 'selected' : '' }}>{{ $discipline }}</option>
                            @endforeach
                        </select>
                        @error('discipline')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Task Size (Pages) -->
                    <div class="mb-4">
                        <label for="task_size" class="block text-sm font-medium text-gray-700 mb-1">Task Size (Pages) <span class="text-red-600">*</span></label>
                        <input type="number" name="task_size" id="task_size" min="1" step="1" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm @error('task_size') border-red-300 text-red-900 placeholder-red-300 focus:border-red-500 focus:ring-red-500 @enderror" value="{{ old('task_size', $order->task_size) }}" required>
                        @error('task_size')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Each page is approximately 275 words, double-spaced</p>
                    </div>
                    
                    <!-- Software Required (if any) -->
                    <div class="mb-4">
                        <label for="software" class="block text-sm font-medium text-gray-700 mb-1">Software Required (if any)</label>
                        <input type="text" name="software" id="software" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" value="{{ old('software', $order->software) }}">
                        <p class="mt-1 text-xs text-gray-500">Specify any software needed (e.g., SPSS, Matlab, etc.)</p>
                    </div>
                </div>
                
                <!-- Order Timeline -->
                <div>
                    <h4 class="text-base font-medium text-gray-900 mb-4">Timeline & Pricing</h4>
                    
                    <!-- Deadline -->
                    <div class="mb-4">
                        <label for="deadline" class="block text-sm font-medium text-gray-700 mb-1">Deadline <span class="text-red-600">*</span></label>
                        <input type="datetime-local" name="deadline" id="deadline" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm @error('deadline') border-red-300 text-red-900 placeholder-red-300 focus:border-red-500 focus:ring-red-500 @enderror" value="{{ old('deadline', $order->deadline ? $order->deadline->format('Y-m-d\TH:i') : '') }}" required>
                        @error('deadline')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Price -->
                    <div class="mb-4">
                        <label for="price" class="block text-sm font-medium text-gray-700 mb-1">Price ($) <span class="text-red-600">*</span></label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">$</span>
                            </div>
                            <input type="number" name="price" id="price" min="1" step="0.01" class="pl-7 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm @error('price') border-red-300 text-red-900 placeholder-red-300 focus:border-red-500 focus:ring-red-500 @enderror" value="{{ old('price', $order->price) }}" required>
                        </div>
                        @error('price')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                
                <!-- Client Information -->
                <div>
                    <h4 class="text-base font-medium text-gray-900 mb-4">Client Information</h4>
                    
                    <!-- Client Name -->
                    <div class="mb-4">
                        <label for="client_name" class="block text-sm font-medium text-gray-700 mb-1">Client Name</label>
                        <input type="text" name="client_name" id="client_name" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" value="{{ old('client_name', $order->client_name) }}">
                    </div>
                    
                    <!-- Client Email -->
                    <div class="mb-4">
                        <label for="client_email" class="block text-sm font-medium text-gray-700 mb-1">Client Email</label>
                        <input type="email" name="client_email" id="client_email" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm @error('client_email') border-red-300 text-red-900 placeholder-red-300 focus:border-red-500 focus:ring-red-500 @enderror" value="{{ old('client_email', $order->client_email) }}">
                        @error('client_email')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Client Comments -->
                    <div class="mb-4">
                        <label for="customer_comments" class="block text-sm font-medium text-gray-700 mb-1">Client Comments</label>
                        <textarea name="customer_comments" id="customer_comments" rows="3" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">{{ old('customer_comments', $order->customer_comments) }}</textarea>
                        <p class="mt-1 text-xs text-gray-500">Any specific client notes or requirements not covered in the instructions</p>
                    </div>
                </div>
            </div>
            
            <!-- Right Column -->
            <div class="space-y-6">
                <!-- Detailed Instructions -->
                <div>
                    <h4 class="text-base font-medium text-gray-900 mb-4">Detailed Instructions</h4>
                    
                    <div class="mb-4">
                        <label for="instructions" class="block text-sm font-medium text-gray-700 mb-1">Instructions <span class="text-red-600">*</span></label>
                        <textarea name="instructions" id="instructions" rows="10" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm @error('instructions') border-red-300 text-red-900 placeholder-red-300 focus:border-red-500 focus:ring-red-500 @enderror" required>{{ old('instructions', $order->instructions) }}</textarea>
                        @error('instructions')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Provide detailed instructions for the order, including requirements, formatting, citation style, etc.</p>
                    </div>
                </div>
                
                <!-- Current Files -->
                @if(count($order->files) > 0)
                <div>
                    <h4 class="text-base font-medium text-gray-900 mb-4">Current Files</h4>
                    <div class="mb-4">
                        <div class="bg-gray-50 rounded-lg p-4">
                            <ul class="divide-y divide-gray-200">
                                @foreach($order->files as $file)
                                <li class="py-3 flex justify-between items-center">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 bg-gray-100 rounded-md flex items-center justify-center">
                                            @if(in_array(pathinfo($file->name, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'gif']))
                                                <i class="fas fa-file-image text-blue-500"></i>
                                            @elseif(in_array(pathinfo($file->name, PATHINFO_EXTENSION), ['doc', 'docx']))
                                                <i class="fas fa-file-word text-blue-700"></i>
                                            @elseif(in_array(pathinfo($file->name, PATHINFO_EXTENSION), ['xls', 'xlsx']))
                                                <i class="fas fa-file-excel text-green-700"></i>
                                            @elseif(in_array(pathinfo($file->name, PATHINFO_EXTENSION), ['pdf']))
                                                <i class="fas fa-file-pdf text-red-700"></i>
                                            @elseif(in_array(pathinfo($file->name, PATHINFO_EXTENSION), ['zip', 'rar']))
                                                <i class="fas fa-file-archive text-yellow-700"></i>
                                            @else
                                                <i class="fas fa-file text-gray-700"></i>
                                            @endif
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-gray-900">{{ $file->name }}</p>
                                            <p class="text-xs text-gray-500">
                                                {{ number_format($file->size / 1024, 2) }} KB
                                                <span class="mx-1">•</span>
                                                {{ $file->created_at->format('M d, Y') }}
                                            </p>
                                        </div>
                                    </div>
                                    <a href="{{ route('files.download', $file->id) }}" class="text-primary-600 hover:text-primary-900 text-sm font-medium">
                                        <i class="fas fa-download"></i> Download
                                    </a>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                @endif
                
                <!-- File Uploads -->
                <div>
                    <h4 class="text-base font-medium text-gray-900 mb-4">Upload Additional Files</h4>
                    
                    <div class="mb-4">
                        <label for="files" class="block text-sm font-medium text-gray-700 mb-2">Upload Files</label>
                        
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-gray-600 justify-center">
                                    <label for="files" class="relative cursor-pointer bg-white rounded-md font-medium text-primary-600 hover:text-primary-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary-500">
                                        <span>Upload files</span>
                                        <input id="files" name="files[]" type="file" class="sr-only" multiple>
                                    </label>
                                    <p class="pl-1">or drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500">
                                    PDF, DOC, DOCX, XLS, XLSX, JPG, PNG up to 10MB each
                                </p>
                            </div>
                        </div>
                        
                        <div class="mt-2">
                            <ul id="file-list" class="list-disc list-inside text-sm text-gray-600"></ul>
                        </div>
                        
                        @error('files')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        @error('files.*')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                
                <!-- Writer Assignment -->
                <div>
                    <h4 class="text-base font-medium text-gray-900 mb-4">Writer Assignment</h4>
                    
                    <div class="mb-4">
                        <label for="writer_id" class="block text-sm font-medium text-gray-700 mb-1">Assign to Writer</label>
                        <select name="writer_id" id="writer_id" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                            <option value="">Unassigned</option>
                            @foreach($writers as $writer)
                                <option value="{{ $writer->id }}" {{ old('writer_id', $order->writer_id) == $writer->id ? 'selected' : '' }}>
                                    {{ $writer->name }} (Rating: {{ number_format($writer->rating ?? 0, 1) }})
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Changing the writer will notify both the old and new writers.</p>
                    </div>
                </div>
                
                <!-- Order Status -->
                <div>
                    <h4 class="text-base font-medium text-gray-900 mb-4">Order Status</h4>
                    
                    <div class="mb-4">
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-600">*</span></label>
                        <select name="status" id="status" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm @error('status') border-red-300 text-red-900 focus:border-red-500 focus:ring-red-500 @enderror" required>
                            <option value="available" {{ old('status', $order->status) == 'available' ? 'selected' : '' }}>Available</option>
                            <option value="confirmed" {{ old('status', $order->status) == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                            <option value="in_progress" {{ old('status', $order->status) == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="done" {{ old('status', $order->status) == 'done' ? 'selected' : '' }}>Done</option>
                            <option value="delivered" {{ old('status', $order->status) == 'delivered' ? 'selected' : '' }}>Delivered</option>
                            <option value="revision" {{ old('status', $order->status) == 'revision' ? 'selected' : '' }}>Revision</option>
                            <option value="completed" {{ old('status', $order->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="dispute" {{ old('status', $order->status) == 'dispute' ? 'selected' : '' }}>Dispute</option>
                            <option value="cancelled" {{ old('status', $order->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                        @error('status')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <div class="mt-2">
                            <label for="status_notes" class="block text-sm font-medium text-gray-700 mb-1">Status Change Notes</label>
                            <textarea name="status_notes" id="status_notes" rows="2" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">{{ old('status_notes') }}</textarea>
                            <p class="mt-1 text-xs text-gray-500">Provide reason for status change (optional)</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Form Actions -->
        <div class="pt-6 border-t border-gray-200 flex justify-end space-x-3">
            <a href="{{ route('admin.orders.show', $order->id) }}" class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                Cancel
            </a>
            <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                Update Order
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // File upload preview
        const fileInput = document.getElementById('files');
        const fileList = document.getElementById('file-list');
        
        fileInput.addEventListener('change', function(e) {
            // Clear the list
            fileList.innerHTML = '';
            
            // Add each file to the list
            const files = e.target.files;
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                const fileSize = (file.size / 1024).toFixed(2); // Convert to KB
                
                const listItem = document.createElement('li');
                listItem.className = 'mb-1';
                listItem.textContent = `${file.name} (${fileSize} KB)`;
                
                fileList.appendChild(listItem);
            }
        });
        
        // Status change handling
        const statusSelect = document.getElementById('status');
        const writerSelect = document.getElementById('writer_id');
        
        statusSelect.addEventListener('change', function() {
            const status = this.value;
            
            // If status is changed to available, clear writer
            if (status === 'available' && writerSelect.value) {
                if (confirm('Changing status to Available will remove the assigned writer. Continue?')) {
                    writerSelect.value = '';
                } else {
                    // Revert status change
                    this.value = '{{ $order->status }}';
                }
            }
            
            // If status is changed to confirmed/in_progress but no writer is assigned
            if ((status === 'confirmed' || status === 'in_progress') && !writerSelect.value) {
                alert('Please assign a writer first before changing status to ' + status);
                // Revert status change
                this.value = '{{ $order->status }}';
            }
        });
        
        // Writer change handling
        writerSelect.addEventListener('change', function() {
            const writerId = this.value;
            const status = statusSelect.value;
            
            // If writer is assigned but status is 'available'
            if (writerId && status === 'available') {
                // Ask if status should be updated to 'confirmed'
                if (confirm('Do you want to update the status to Confirmed since you are assigning a writer?')) {
                    statusSelect.value = 'confirmed';
                }
            }
            
            // If writer is removed but status is not 'available' or 'cancelled'
            if (!writerId && status !== 'available' && status !== 'cancelled') {
                // Ask if status should be updated to 'available'
                if (confirm('Do you want to update the status to Available since you are removing the writer?')) {
                    statusSelect.value = 'available';
                }
            }
        });
    });
</script>
@endpush