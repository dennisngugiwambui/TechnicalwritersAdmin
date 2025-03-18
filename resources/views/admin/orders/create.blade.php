@extends('admin.app')


@section('content')
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Order Details</h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">Fill in all required information to create a new order.</p>
        </div>
        
        <form action="{{ route('admin.orders.store') }}" method="POST" enctype="multipart/form-data" class="divide-y divide-gray-200">
            @csrf
            
            <!-- Order Information Section -->
            <div class="px-4 py-5 sm:p-6">
                <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                    <div class="sm:col-span-4">
                        <label for="title" class="block text-sm font-medium text-gray-700">Order Title <span class="text-red-500">*</span></label>
                        <div class="mt-1">
                            <input type="text" name="title" id="title" class="shadow-sm focus:ring-primary-500 focus:border-primary-500 block w-full sm:text-sm border-gray-300 rounded-md @error('title') border-red-300 @enderror" value="{{ old('title') }}" required>
                            @error('title')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="sm:col-span-3">
                        <label for="discipline" class="block text-sm font-medium text-gray-700">Subject/Discipline <span class="text-red-500">*</span></label>
                        <div class="mt-1">
                            <select id="discipline" name="discipline" class="shadow-sm focus:ring-primary-500 focus:border-primary-500 block w-full sm:text-sm border-gray-300 rounded-md @error('discipline') border-red-300 @enderror" required>
                                <option value="">Select a discipline</option>
                                <option value="Programming" {{ old('discipline') == 'Programming' ? 'selected' : '' }}>Programming</option>
                                <option value="Data Analysis" {{ old('discipline') == 'Data Analysis' ? 'selected' : '' }}>Data Analysis</option>
                                <option value="Business" {{ old('discipline') == 'Business' ? 'selected' : '' }}>Business</option>
                                <option value="Nursing" {{ old('discipline') == 'Nursing' ? 'selected' : '' }}>Nursing</option>
                                <option value="Science" {{ old('discipline') == 'Science' ? 'selected' : '' }}>Science</option>
                                <option value="Engineering" {{ old('discipline') == 'Engineering' ? 'selected' : '' }}>Engineering</option>
                                <option value="Arts" {{ old('discipline') == 'Arts' ? 'selected' : '' }}>Arts</option>
                                <option value="Social Sciences" {{ old('discipline') == 'Social Sciences' ? 'selected' : '' }}>Social Sciences</option>
                                <option value="Other" {{ old('discipline') == 'Other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('discipline')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="sm:col-span-3">
                        <label for="type_of_service" class="block text-sm font-medium text-gray-700">Type of Service <span class="text-red-500">*</span></label>
                        <div class="mt-1">
                            <select id="type_of_service" name="type_of_service" class="shadow-sm focus:ring-primary-500 focus:border-primary-500 block w-full sm:text-sm border-gray-300 rounded-md @error('type_of_service') border-red-300 @enderror" required>
                                <option value="">Select service type</option>
                                <option value="Essay" {{ old('type_of_service') == 'Essay' ? 'selected' : '' }}>Essay</option>
                                <option value="Research Paper" {{ old('type_of_service') == 'Research Paper' ? 'selected' : '' }}>Research Paper</option>
                                <option value="Assignment" {{ old('type_of_service') == 'Assignment' ? 'selected' : '' }}>Assignment</option>
                                <option value="Programming" {{ old('type_of_service') == 'Programming' ? 'selected' : '' }}>Programming</option>
                                <option value="Case Study" {{ old('type_of_service') == 'Case Study' ? 'selected' : '' }}>Case Study</option>
                                <option value="Presentation" {{ old('type_of_service') == 'Presentation' ? 'selected' : '' }}>Presentation</option>
                                <option value="Other" {{ old('type_of_service') == 'Other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('type_of_service')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="task_size" class="block text-sm font-medium text-gray-700">Task Size/Pages <span class="text-red-500">*</span></label>
                        <div class="mt-1">
                            <input type="number" name="task_size" id="task_size" min="1" class="shadow-sm focus:ring-primary-500 focus:border-primary-500 block w-full sm:text-sm border-gray-300 rounded-md @error('task_size') border-red-300 @enderror" value="{{ old('task_size', 1) }}" required>
                            @error('task_size')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="price" class="block text-sm font-medium text-gray-700">Price (USD) <span class="text-red-500">*</span></label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">$</span>
                            </div>
                            <input type="number" name="price" id="price" step="0.01" min="1" class="focus:ring-primary-500 focus:border-primary-500 block w-full pl-7 pr-12 sm:text-sm border-gray-300 rounded-md @error('price') border-red-300 @enderror" placeholder="0.00" value="{{ old('price') }}" required>
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">USD</span>
                            </div>
                        </div>
                        @error('price')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="deadline" class="block text-sm font-medium text-gray-700">Deadline <span class="text-red-500">*</span></label>
                        <div class="mt-1">
                            <input type="datetime-local" name="deadline" id="deadline" class="shadow-sm focus:ring-primary-500 focus:border-primary-500 block w-full sm:text-sm border-gray-300 rounded-md @error('deadline') border-red-300 @enderror" value="{{ old('deadline') }}" required>
                            @error('deadline')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="sm:col-span-6">
                        <label for="instructions" class="block text-sm font-medium text-gray-700">Instructions <span class="text-red-500">*</span></label>
                        <div class="mt-1">
                            <textarea id="instructions" name="instructions" rows="6" class="shadow-sm focus:ring-primary-500 focus:border-primary-500 block w-full sm:text-sm border border-gray-300 rounded-md @error('instructions') border-red-300 @enderror" required>{{ old('instructions') }}</textarea>
                            @error('instructions')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <p class="mt-2 text-sm text-gray-500">Detailed instructions for the writer to complete the order.</p>
                    </div>
                </div>
            </div>

            <!-- File Upload Section -->
            <div class="px-4 py-5 sm:p-6">
                <div>
                    <h3 class="text-lg font-medium leading-6 text-gray-900">Attachments</h3>
                    <p class="mt-1 text-sm text-gray-500">Upload files required for this order (optional).</p>
                </div>

                <div class="mt-4" x-data="{ files: [] }">
                    <div class="flex items-center justify-center w-full">
                        <label for="file-upload" class="flex flex-col items-center justify-center w-full h-64 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                <svg class="w-10 h-10 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                                <p class="mb-2 text-sm text-gray-500"><span class="font-semibold">Click to upload</span> or drag and drop</p>
                                <p class="text-xs text-gray-500">PDF, DOCX, ZIP, etc. (Max 10MB per file)</p>
                            </div>
                            <input id="file-upload" type="file" name="files[]" class="hidden" multiple @change="files = [...$event.target.files]" />
                        </label>
                    </div>
                    
                    <div class="mt-4" x-show="files.length > 0">
                        <h4 class="text-sm font-medium text-gray-700">Selected Files:</h4>
                        <ul class="mt-2 divide-y divide-gray-200 border border-gray-200 rounded-md">
                            <template x-for="(file, index) in files" :key="index">
                                <li class="pl-3 pr-4 py-3 flex items-center justify-between text-sm">
                                    <div class="w-0 flex-1 flex items-center">
                                        <svg class="flex-shrink-0 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M8 4a3 3 0 00-3 3v4a3 3 0 006 0V7a1 1 0 112 0v4a5 5 0 01-10 0V7a5 5 0 0110 0v4a1 1 0 11-2 0V7a3 3 0 00-3-3z" clip-rule="evenodd" />
                                        </svg>
                                        <span class="ml-2 flex-1 w-0 truncate" x-text="file.name"></span>
                                    </div>
                                    <div class="ml-4 flex-shrink-0 flex items-center space-x-4">
                                        <span class="text-xs text-gray-500" x-text="(file.size / 1024).toFixed(2) + ' KB'"></span>
                                        <button type="button" class="text-red-500 hover:text-red-700" @click="files = files.filter((_, i) => i !== index)">
                                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </li>
                            </template>
                        </ul>
                    </div>
                    
                    @error('files.*')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Writer Assignment Section -->
            <div class="px-4 py-5 sm:p-6">
                <div>
                    <h3 class="text-lg font-medium leading-6 text-gray-900">Writer Assignment</h3>
                    <p class="mt-1 text-sm text-gray-500">Assign this order directly to a writer or make it available for bidding.</p>
                </div>

                <div class="mt-4">
                    <div class="flex items-center mb-4">
                        <input id="assignment_type_available" name="assignment_type" type="radio" class="focus:ring-primary-500 h-4 w-4 text-primary-600 border-gray-300" value="available" {{ old('assignment_type', 'available') == 'available' ? 'checked' : '' }}>
                        <label for="assignment_type_available" class="ml-3 block text-sm font-medium text-gray-700">
                            Make available for writers
                        </label>
                    </div>
                    <div class="flex items-center">
                        <input id="assignment_type_assign" name="assignment_type" type="radio" class="focus:ring-primary-500 h-4 w-4 text-primary-600 border-gray-300" value="assign" {{ old('assignment_type') == 'assign' ? 'checked' : '' }}>
                        <label for="assignment_type_assign" class="ml-3 block text-sm font-medium text-gray-700">
                            Assign to specific writer
                        </label>
                    </div>

                    <div class="mt-4" x-data="{ showWriterSelect: {{ old('assignment_type') == 'assign' ? 'true' : 'false' }} }">
                        <div x-show="showWriterSelect">
                            <label for="writer_id" class="block text-sm font-medium text-gray-700">Select Writer</label>
                            <select id="writer_id" name="writer_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md">
                                <option value="">-- Select a writer --</option>
                                @foreach($writers ?? [] as $writer)
                                    <option value="{{ $writer->id }}" {{ old('writer_id') == $writer->id ? 'selected' : '' }}>
                                        {{ $writer->name }} - {{ $writer->writerProfile->writer_id ?? 'No ID' }} (Rating: {{ number_format($writer->rating ?? 0, 1) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const assignmentTypeAvailable = document.getElementById('assignment_type_available');
                                const assignmentTypeAssign = document.getElementById('assignment_type_assign');
                                
                                assignmentTypeAvailable.addEventListener('change', function() {
                                    if (this.checked) {
                                        document.querySelector('[x-data]').__x.$data.showWriterSelect = false;
                                    }
                                });
                                
                                assignmentTypeAssign.addEventListener('change', function() {
                                    if (this.checked) {
                                        document.querySelector('[x-data]').__x.$data.showWriterSelect = true;
                                    }
                                });
                            });
                        </script>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                <a href="{{ route('admin.orders.index') }}" class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                    Cancel
                </a>
                <button type="submit" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                    Create Order
                </button>
            </div>
        </form>
    </div>

<script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.min.js" defer></script>

@endsection