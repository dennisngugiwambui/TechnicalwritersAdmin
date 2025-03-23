@extends('admin.app')

@section('title', 'Edit Writer')

@section('page-title', 'Edit Writer Profile')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h1 class="text-xl font-medium text-gray-900">Edit Writer Profile</h1>
                <a href="{{ route('admin.writers.show', $writer->id) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Profile
                </a>
            </div>
        </div>
        
        <form method="POST" action="{{ route('admin.writers.update', $writer->id) }}" enctype="multipart/form-data" class="p-6 space-y-6">
            @csrf
            @method('PUT')
            
            @if ($errors->any())
                <div class="bg-red-50 p-4 rounded-md mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-red-400"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">There were {{ $errors->count() }} errors with your submission</h3>
                            <div class="mt-2 text-sm text-red-700">
                                <ul class="list-disc pl-5 space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Basic Information -->
                <div class="space-y-6">
                    <h2 class="text-lg font-medium text-gray-900">Basic Information</h2>
                    
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $writer->name) }}" class="mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required>
                    </div>
                    
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                        <input type="email" name="email" id="email" value="{{ old('email', $writer->email) }}" class="mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required>
                    </div>
                    
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                        <input type="text" name="phone" id="phone" value="{{ old('phone', $writer->phone) }}" class="mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                    </div>
                    
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">Account Status</label>
                        <select name="status" id="status" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                            <option value="active" {{ old('status', $writer->status) == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="suspended" {{ old('status', $writer->status) == 'suspended' ? 'selected' : '' }}>Suspended</option>
                            <option value="pending" {{ old('status', $writer->status) == 'pending' ? 'selected' : '' }}>Pending Verification</option>
                            <option value="inactive" {{ old('status', $writer->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="profile_picture" class="block text-sm font-medium text-gray-700">Profile Picture</label>
                        <div class="mt-1 flex items-center">
                            <div class="mr-4">
                                @if($writer->profile_picture)
                                    <img src="{{ asset($writer->profile_picture) }}" alt="{{ $writer->name }}" class="h-16 w-16 object-cover rounded-full">
                                @else
                                    <div class="h-16 w-16 rounded-full bg-primary-100 flex items-center justify-center text-primary-600 text-xl font-bold">
                                        {{ strtoupper(substr($writer->name, 0, 1)) }}
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1">
                                <input type="file" name="profile_picture" id="profile_picture" class="mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                <p class="mt-1 text-xs text-gray-500">Upload a new profile picture (leave empty to keep current)</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Writer Profile -->
                <div class="space-y-6">
                    <h2 class="text-lg font-medium text-gray-900">Professional Details</h2>
                    
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700">Title/Position</label>
                        <input type="text" name="title" id="title" value="{{ old('title', $writer->writerProfile->title ?? '') }}" class="mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                    </div>
                    
                    <div>
                        <label for="education_level" class="block text-sm font-medium text-gray-700">Education Level</label>
                        <select name="education_level" id="education_level" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                            <option value="">Select Education Level</option>
                            <option value="High School" {{ old('education_level', $writer->writerProfile->education_level ?? '') == 'High School' ? 'selected' : '' }}>High School</option>
                            <option value="Associate's Degree" {{ old('education_level', $writer->writerProfile->education_level ?? '') == "Associate's Degree" ? 'selected' : '' }}>Associate's Degree</option>
                            <option value="Bachelor's Degree" {{ old('education_level', $writer->writerProfile->education_level ?? '') == "Bachelor's Degree" ? 'selected' : '' }}>Bachelor's Degree</option>
                            <option value="Master's Degree" {{ old('education_level', $writer->writerProfile->education_level ?? '') == "Master's Degree" ? 'selected' : '' }}>Master's Degree</option>
                            <option value="Doctorate" {{ old('education_level', $writer->writerProfile->education_level ?? '') == 'Doctorate' ? 'selected' : '' }}>Doctorate</option>
                            <option value="Other" {{ old('education_level', $writer->writerProfile->education_level ?? '') == 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="experience_years" class="block text-sm font-medium text-gray-700">Years of Experience</label>
                        <input type="number" min="0" max="50" name="experience_years" id="experience_years" value="{{ old('experience_years', $writer->writerProfile->experience_years ?? '') }}" class="mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                    </div>
                    
                    <div>
                        <label for="areas_of_expertise" class="block text-sm font-medium text-gray-700">Areas of Expertise</label>
                        <input type="text" name="areas_of_expertise" id="areas_of_expertise" value="{{ old('areas_of_expertise', $writer->writerProfile->areas_of_expertise ?? '') }}" class="mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        <p class="mt-1 text-xs text-gray-500">Separate different areas with commas</p>
                    </div>
                    
                    <div>
                        <label for="commission_rate" class="block text-sm font-medium text-gray-700">Commission Rate</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <input type="number" min="0" max="1" step="0.01" name="commission_rate" id="commission_rate" value="{{ old('commission_rate', $writer->writerProfile->commission_rate ?? config('app.default_writer_commission_rate', 0.70)) }}" class="focus:ring-primary-500 focus:border-primary-500 block w-full pr-12 sm:text-sm border-gray-300 rounded-md">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">%</span>
                            </div>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Enter as a decimal (e.g., 0.7 for 70%)</p>
                    </div>
                </div>
            </div>
            
            <!-- Bio -->
            <div>
                <label for="bio" class="block text-sm font-medium text-gray-700">Biography</label>
                <textarea name="bio" id="bio" rows="4" class="mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">{{ old('bio', $writer->writerProfile->bio ?? '') }}</textarea>
            </div>
            
            <div class="pt-5">
                <div class="flex justify-end">
                    <a href="{{ route('admin.writers.show', $writer->id) }}" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 mr-3">
                        Cancel
                    </a>
                    <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        Save Changes
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection