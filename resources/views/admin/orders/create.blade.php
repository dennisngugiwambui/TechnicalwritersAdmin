@extends('admin.app')



@section('content')
<div class="bg-white rounded-lg shadow-sm overflow-hidden">
    <div class="p-6 border-b border-gray-200">
        <h3 class="text-lg font-medium text-gray-900">Order Details</h3>
        <p class="mt-1 text-sm text-gray-500">Complete the form below to create a new order in the system.</p>
    </div>

    <form action="{{ route('admin.orders.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Left Column -->
            <div class="space-y-6">
                <!-- Basic Order Information -->
                <div>
                    <h4 class="text-base font-medium text-gray-900 mb-4">Basic Information</h4>
                    
                    <!-- Order Title -->
                    <div class="mb-4">
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Order Title <span class="text-red-600">*</span></label>
                        <input type="text" name="title" id="title" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm @error('title') border-red-300 text-red-900 placeholder-red-300 focus:border-red-500 focus:ring-red-500 @enderror" value="{{ old('title') }}" required>
                        @error('title')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Type of Service -->
                    <div class="mb-4">
                        <label for="type_of_service" class="block text-sm font-medium text-gray-700 mb-1">Type of Service <span class="text-red-600">*</span></label>
                        <select name="type_of_service" id="type_of_service" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm @error('type_of_service') border-red-300 text-red-900 focus:border-red-500 focus:ring-red-500 @enderror" required>
                            <option value="">Select service type</option>
                            <option value="Essay" {{ old('type_of_service') == 'Essay' ? 'selected' : '' }}>Essay</option>
                            <option value="Research Paper" {{ old('type_of_service') == 'Research Paper' ? 'selected' : '' }}>Research Paper</option>
                            <option value="Case Study" {{ old('type_of_service') == 'Case Study' ? 'selected' : '' }}>Case Study</option>
                            <option value="Dissertation" {{ old('type_of_service') == 'Dissertation' ? 'selected' : '' }}>Dissertation</option>
                            <option value="Term Paper" {{ old('type_of_service') == 'Term Paper' ? 'selected' : '' }}>Term Paper</option>
                            <option value="Thesis" {{ old('type_of_service') == 'Thesis' ? 'selected' : '' }}>Thesis</option>
                            <option value="Coursework" {{ old('type_of_service') == 'Coursework' ? 'selected' : '' }}>Coursework</option>
                            <option value="Assignment" {{ old('type_of_service') == 'Assignment' ? 'selected' : '' }}>Assignment</option>
                            <option value="Book Review" {{ old('type_of_service') == 'Book Review' ? 'selected' : '' }}>Book Review</option>
                            <option value="Article Review" {{ old('type_of_service') == 'Article Review' ? 'selected' : '' }}>Article Review</option>
                            <option value="Lab Report" {{ old('type_of_service') == 'Lab Report' ? 'selected' : '' }}>Lab Report</option>
                            <option value="Other" {{ old('type_of_service') == 'Other' ? 'selected' : '' }}>Other</option>
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
                            <option value="Business" {{ old('discipline') == 'Business' ? 'selected' : '' }}>Business</option>
                            <option value="Mathematics" {{ old('discipline') == 'Mathematics' ? 'selected' : '' }}>Mathematics</option>
                            <option value="Science" {{ old('discipline') == 'Science' ? 'selected' : '' }}>Science</option>
                            <option value="History" {{ old('discipline') == 'History' ? 'selected' : '' }}>History</option>
                            <option value="Technology" {{ old('discipline') == 'Technology' ? 'selected' : '' }}>Technology</option>
                            <option value="Engineering" {{ old('discipline') == 'Engineering' ? 'selected' : '' }}>Engineering</option>
                            <option value="Literature" {{ old('discipline') == 'Literature' ? 'selected' : '' }}>Literature</option>
                            <option value="Art" {{ old('discipline') == 'Art' ? 'selected' : '' }}>Art</option>
                            <option value="Law" {{ old('discipline') == 'Law' ? 'selected' : '' }}>Law</option>
                            <option value="Psychology" {{ old('discipline') == 'Psychology' ? 'selected' : '' }}>Psychology</option>
                            <option value="Sociology" {{ old('discipline') == 'Sociology' ? 'selected' : '' }}>Sociology</option>
                            <option value="Economics" {{ old('discipline') == 'Economics' ? 'selected' : '' }}>Economics</option>
                            <option value="Other" {{ old('discipline') == 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('discipline')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Task Size (Pages) -->
                    <div class="mb-4">
                        <label for="task_size" class="block text-sm font-medium text-gray-700 mb-1">Task Size (Pages) <span class="text-red-600">*</span></label>
                        <input type="number" name="task_size" id="task_size" min="1" step="1" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm @error('task_size') border-red-300 text-red-900 placeholder-red-300 focus:border-red-500 focus:ring-red-500 @enderror" value="{{ old('task_size') }}" required>
                        @error('task_size')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Each page is approximately 275 words, double-spaced</p>
                    </div>
                    
                    <!-- Software Required (if any) -->
                    <div class="mb-4">
                        <label for="software" class="block text-sm font-medium text-gray-700 mb-1">Software Required (if any)</label>
                        <input type="text" name="software" id="software" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" value="{{ old('software') }}">
                        <p class="mt-1 text-xs text-gray-500">Specify any software needed (e.g., SPSS, Matlab, etc.)</p>
                    </div>
                </div>
                
                <!-- Order Timeline -->
                <div>
                    <h4 class="text-base font-medium text-gray-900 mb-4">Timeline & Pricing</h4>
                    
                    <!-- Deadline -->
                    <div class="mb-4">
                        <label for="deadline" class="block text-sm font-medium text-gray-700 mb-1">Deadline <span class="text-red-600">*</span></label>
                        <input type="datetime-local" name="deadline" id="deadline" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm @error('deadline') border-red-300 text-red-900 placeholder-red-300 focus:border-red-500 focus:ring-red-500 @enderror" value="{{ old('deadline') ?? date('Y-m-d\TH:i', strtotime('+3 days')) }}" required>
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
                            <input type="number" name="price" id="price" min="1" step="0.01" class="pl-7 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm @error('price') border-red-300 text-red-900 placeholder-red-300 focus:border-red-500 focus:ring-red-500 @enderror" value="{{ old('price') }}" required>
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
                        <input type="text" name="client_name" id="client_name" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" value="{{ old('client_name') }}">
                    </div>
                    
                    <!-- Client Email -->
                    <div class="mb-4">
                        <label for="client_email" class="block text-sm font-medium text-gray-700 mb-1">Client Email</label>
                        <input type="email" name="client_email" id="client_email" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm @error('client_email') border-red-300 text-red-900 placeholder-red-300 focus:border-red-500 focus:ring-red-500 @enderror" value="{{ old('client_email') }}">
                        @error('client_email')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Client Comments -->
                    <div class="mb-4">
                        <label for="customer_comments" class="block text-sm font-medium text-gray-700 mb-1">Client Comments</label>
                        <textarea name="customer_comments" id="customer_comments" rows="3" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">{{ old('customer_comments') }}</textarea>
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
                        <textarea name="instructions" id="instructions" rows="10" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm @error('instructions') border-red-300 text-red-900 placeholder-red-300 focus:border-red-500 focus:ring-red-500 @enderror" required>{{ old('instructions') }}</textarea>
                        @error('instructions')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Provide detailed instructions for the order, including requirements, formatting, citation style, etc.</p>
                    </div>
                </div>
                
                <!-- File Uploads -->
                <div>
                    <h4 class="text-base font-medium text-gray-900 mb-4">File Attachments</h4>
                    
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
                        <label for="writer_id" class="block text-sm font-medium text-gray-700 mb-1">Assign to Writer (Optional)</label>
                        <select name="writer_id" id="writer_id" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                            <option value="">Leave unassigned (available for bidding)</option>
                            @foreach($writers as $writer)
                                <option value="{{ $writer->id }}" {{ old('writer_id') == $writer->id ? 'selected' : '' }}>
                                    {{ $writer->name }} (Rating: {{ number_format($writer->rating ?? 0, 1) }})
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500">If unassigned, the order will be visible to all qualified writers.</p>
                    </div>
                </div>
                
                <!-- Order Status -->
                <div>
                    <h4 class="text-base font-medium text-gray-900 mb-4">Order Status</h4>
                    
                    <div class="mb-4">
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Initial Status <span class="text-red-600">*</span></label>
                        <select name="status" id="status" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm @error('status') border-red-300 text-red-900 focus:border-red-500 focus:ring-red-500 @enderror" required>
                            <option value="available" {{ old('status') == 'available' ? 'selected' : '' }}>Available (visible to writers)</option>
                            <option value="hidden" {{ old('status') == 'hidden' || old('status') == '' ? 'selected' : '' }}>Hidden (draft mode)</option>
                        </select>
                        @error('status')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Form Actions -->
        <div class="pt-6 border-t border-gray-200 flex justify-end space-x-3">
            <a href="{{ route('admin.orders.index') }}" class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                Cancel
            </a>
            <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                Create Order
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
        
        // Price calculation based on task size and deadline
        const taskSizeInput = document.getElementById('task_size');
        const deadlineInput = document.getElementById('deadline');
        const priceInput = document.getElementById('price');
        
        // Only auto-calculate if price is empty or the user hasn't manually set it
        let priceManuallySet = false;
        
        if (!priceInput.value) {
            calculatePrice();
        }
        
        taskSizeInput.addEventListener('input', function() {
            if (!priceManuallySet) {
                calculatePrice();
            }
        });
        
        deadlineInput.addEventListener('change', function() {
            if (!priceManuallySet) {
                calculatePrice();
            }
        });
        
        priceInput.addEventListener('input', function() {
            priceManuallySet = true;
        });
        
        function calculatePrice() {
            const taskSize = parseInt(taskSizeInput.value) || 0;
            const deadlineDate = new Date(deadlineInput.value);
            const currentDate = new Date();
            
            const timeDiff = deadlineDate - currentDate;
            const daysDiff = Math.max(1, Math.ceil(timeDiff / (1000 * 60 * 60 * 24)));
            
            // Base price calculation
            let basePrice = 20; // Base price per page
            
            // Urgency multiplier
            let urgencyMultiplier = 1;
            if (daysDiff < 1) {
                urgencyMultiplier = 2.5; // Same day
            } else if (daysDiff < 2) {
                urgencyMultiplier = 2.0; // 1 day
            } else if (daysDiff < 3) {
                urgencyMultiplier = 1.5; // 2 days
            } else if (daysDiff < 5) {
                urgencyMultiplier = 1.3; // 3-4 days
            } else if (daysDiff < 7) {
                urgencyMultiplier = 1.1; // 5-6 days
            }
            
            // Volume discount
            let volumeDiscount = 1;
            if (taskSize > 50) {
                volumeDiscount = 0.8; // 20% discount
            } else if (taskSize > 30) {
                volumeDiscount = 0.85; // 15% discount
            } else if (taskSize > 15) {
                volumeDiscount = 0.9; // 10% discount
            } else if (taskSize > 5) {
                volumeDiscount = 0.95; // 5% discount
            }
            
            const calculatedPrice = taskSize * basePrice * urgencyMultiplier * volumeDiscount;
            
            // Round to 2 decimal places
            priceInput.value = calculatedPrice.toFixed(2);
        }
    });
</script>
@endpush