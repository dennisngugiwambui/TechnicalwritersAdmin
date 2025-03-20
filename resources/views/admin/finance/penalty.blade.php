@extends('admin.app')

@section('title', 'Apply Writer Penalty')

@section('page-title', 'Apply Writer Penalty')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Writer Penalty Form</h3>
        </div>
        
        <form action="{{ route('admin.finance.penalty.store') }}" method="POST" class="p-6">
            @csrf
            
            @if ($errors->any())
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">There were errors with your submission:</h3>
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
            
            <div class="space-y-6">
                <!-- Writer Selection -->
                <div>
                    <label for="user_id" class="block text-sm font-medium text-gray-700">Select Writer <span class="text-red-500">*</span></label>
                    <select id="user_id" name="user_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" required>
                        <option value="">-- Select Writer --</option>
                        @foreach ($writers as $writer)
                            <option value="{{ $writer->id }}" {{ old('user_id') == $writer->id ? 'selected' : '' }}>
                                {{ $writer->name }} ({{ $writer->email }})
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Amount -->
                <div>
                    <label for="amount" class="block text-sm font-medium text-gray-700">Penalty Amount (USD) <span class="text-red-500">*</span></label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">$</span>
                        </div>
                        <input type="number" step="0.01" min="0.01" name="amount" id="amount" value="{{ old('amount') }}" class="focus:ring-primary-500 focus:border-primary-500 block w-full pl-7 pr-12 sm:text-sm border-gray-300 rounded-md" placeholder="0.00" required>
                    </div>
                    <p class="mt-1 text-sm text-gray-500">Note: Penalty amount cannot exceed writer's available balance.</p>
                </div>
                
                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Penalty Reason <span class="text-red-500">*</span></label>
                    <textarea id="description" name="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" placeholder="Explain the reason for this penalty..." required>{{ old('description') }}</textarea>
                </div>
                
                <div class="pt-4 flex justify-end space-x-3">
                    <a href="{{ route('admin.finance.transactions') }}" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        Cancel
                    </a>
                    <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        Apply Penalty
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection