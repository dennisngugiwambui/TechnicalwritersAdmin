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
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mx-2 text-sm"></i>
                            <span class="text-gray-500">Order #{{ $order->id }}</span>
                        </div>
                    </li>
                </ol>
            </nav>
            
            <div class="flex space-x-2">
                <a href="{{ route('admin.orders.edit', $order->id) }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <i class="fas fa-edit mr-2"></i> Edit Order
                </a>
                <div x-data="{ open: false }">
                    <button @click="open = !open" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        <i class="fas fa-cog mr-2"></i> Actions <i class="fas fa-chevron-down ml-2"></i>
                    </button>
                    <div x-show="open" @click.away="open = false" class="origin-top-right absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50 divide-y divide-gray-100">
                        <!-- Status actions based on current status -->
                        <div class="py-1" role="none">
                            @if($order->status != 'available')
                                <form action="{{ route('admin.orders.make-available', $order->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="text-left w-full block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">
                                        <i class="fas fa-clipboard-list text-blue-500 mr-2"></i> Make Available
                                    </button>
                                </form>
                            @endif
                            
                            @if(in_array($order->status, ['available', 'dispute', 'cancelled']))
                                <button @click="$refs.assignOrderModal.style.display='block'" class="text-left w-full block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">
                                    <i class="fas fa-user-check text-green-500 mr-2"></i> Assign to Writer
                                </button>
                            @endif
                            
                            @if(in_array($order->status, ['done', 'delivered', 'revision']))
                                <button @click="$refs.completeOrderModal.style.display='block'" class="text-left w-full block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">
                                    <i class="fas fa-check-circle text-green-500 mr-2"></i> Mark as Complete
                                </button>
                            @endif
                            
                            @if(in_array($order->status, ['done', 'delivered', 'completed']))
                                <button @click="$refs.revisionModal.style.display='block'" class="text-left w-full block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">
                                    <i class="fas fa-sync-alt text-red-500 mr-2"></i> Request Revision
                                </button>
                            @endif
                            
                            @if(!in_array($order->status, ['completed', 'paid', 'finished', 'cancelled']))
                               <button @click="$refs.disputeModal.style.display='block'" class="text-left w-full block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">
                                   <i class="fas fa-exclamation-triangle text-yellow-500 mr-2"></i> Mark as Disputed
                               </button>
                               
                               <button @click="$refs.cancelOrderModal.style.display='block'" class="text-left w-full block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">
                                   <i class="fas fa-ban text-gray-500 mr-2"></i> Cancel Order
                               </button>
                           @endif
                       </div>
                       
                       <!-- Communications -->
                       <div class="py-1" role="none">
                           <button @click="$refs.messageModal.style.display='block'" class="text-left w-full block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">
                               <i class="fas fa-comment text-blue-500 mr-2"></i> Send Message
                           </button>
                           <button @click="$refs.uploadFilesModal.style.display='block'" class="text-left w-full block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">
                               <i class="fas fa-file-upload text-green-500 mr-2"></i> Upload Files
                           </button>
                       </div>
                   </div>
               </div>
           </div>
       </div>
   </div>
   
   <!-- Order Information Sections -->
   <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
       <!-- Left Column: Order Details -->
       <div class="lg:col-span-2 space-y-6">
           <!-- Order Summary Card -->
           <div class="bg-white rounded-lg shadow-sm overflow-hidden">
               <div class="p-6 border-b border-gray-200">
                   <div class="flex justify-between items-start">
                       <div>
                           <h3 class="text-lg font-medium text-gray-900">{{ $order->title }}</h3>
                           <div class="mt-1 flex flex-wrap gap-2">
                               <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                   {{ $order->type_of_service }}
                               </span>
                               <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                   {{ $order->discipline }}
                               </span>
                               <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                   {{ $order->task_size }} pages
                               </span>
                               
                               @if($order->status == 'available')
                                   <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                       Available
                                   </span>
                               @elseif(in_array($order->status, ['confirmed', 'in_progress', 'done', 'delivered']))
                                   <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                       {{ ucfirst($order->status) }}
                                   </span>
                               @elseif($order->status == 'revision')
                                   <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                       Revision
                                   </span>
                               @elseif(in_array($order->status, ['completed', 'paid', 'finished']))
                                   <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                       {{ ucfirst($order->status) }}
                                   </span>
                               @elseif($order->status == 'dispute')
                                   <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                       Dispute
                                   </span>
                               @elseif($order->status == 'cancelled')
                                   <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                       Cancelled
                                   </span>
                               @endif
                           </div>
                       </div>
                       <div class="text-right">
                           <div class="text-xl font-bold text-gray-900">${{ number_format($order->price, 2) }}</div>
                           <div class="text-sm text-gray-500">Order #{{ $order->id }}</div>
                           <div class="text-sm text-gray-500">Created: {{ $order->created_at->format('M d, Y') }}</div>
                       </div>
                   </div>
               </div>
               
               <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                   <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Order Timeline</h4>
               </div>
               
               <div class="p-6">
                   <div class="flex items-center mb-4">
                       <div class="w-1/4 sm:w-1/5">
                           <span class="text-sm font-medium text-gray-900">Created</span>
                       </div>
                       <div class="flex-1">
                           <div class="text-sm text-gray-900">{{ $order->created_at->format('M d, Y h:i A') }}</div>
                           <div class="text-xs text-gray-500">By: {{ $order->creator->name ?? 'System' }}</div>
                       </div>
                   </div>
                   
                   <div class="flex items-center mb-4">
                       <div class="w-1/4 sm:w-1/5">
                           <span class="text-sm font-medium text-gray-900">Deadline</span>
                       </div>
                       <div class="flex-1">
                           <div class="text-sm text-gray-900">{{ $order->deadline->format('M d, Y h:i A') }}</div>
                           <div class="text-xs text-gray-500">
                               @if($order->deadline->isPast())
                                   <span class="text-red-600">Overdue by {{ $order->deadline->diffForHumans() }}</span>
                               @else
                                   <span class="{{ $order->deadline->diffInHours() < 24 ? 'text-yellow-600' : 'text-green-600' }}">
                                       {{ $order->deadline->diffForHumans() }} remaining
                                   </span>
                               @endif
                           </div>
                       </div>
                   </div>
                   
                   @if($order->writer)
                   <div class="flex items-center mb-4">
                       <div class="w-1/4 sm:w-1/5">
                           <span class="text-sm font-medium text-gray-900">Writer</span>
                       </div>
                       <div class="flex-1 flex items-center">
                           <div class="flex-shrink-0 h-8 w-8 mr-3">
                               @if($order->writer->profile_picture)
                                   <img class="h-8 w-8 rounded-full object-cover" src="{{ asset($order->writer->profile_picture) }}" alt="{{ $order->writer->name }}">
                               @else
                                   <div class="h-8 w-8 rounded-full bg-primary-100 flex items-center justify-center">
                                       <span class="text-primary-600 font-medium text-sm">{{ strtoupper(substr($order->writer->name, 0, 1)) }}</span>
                                   </div>
                               @endif
                           </div>
                           <div>
                               <div class="text-sm text-gray-900">{{ $order->writer->name }}</div>
                               <div class="flex items-center text-xs text-gray-500">
                                   @for($i = 1; $i <= 5; $i++)
                                       @if($i <= round($order->writer->rating ?? 0))
                                           <i class="fas fa-star text-yellow-400 text-xs"></i>
                                       @else
                                           <i class="far fa-star text-yellow-400 text-xs"></i>
                                       @endif
                                   @endfor
                                   <span class="ml-1">{{ number_format($order->writer->rating ?? 0, 1) }}</span>
                               </div>
                           </div>
                       </div>
                   </div>
                   @endif
                   
                   @if($order->completed_at)
                   <div class="flex items-center mb-4">
                       <div class="w-1/4 sm:w-1/5">
                           <span class="text-sm font-medium text-gray-900">Completed</span>
                       </div>
                       <div class="flex-1">
                           <div class="text-sm text-gray-900">{{ $order->completed_at->format('M d, Y h:i A') }}</div>
                           <div class="text-xs text-gray-500">
                               {{ $order->deadline->diffInHours($order->completed_at) < 0 ? 'Completed on time' : 'Completed late by ' . $order->deadline->diffForHumans($order->completed_at) }}
                           </div>
                       </div>
                   </div>
                   @endif
                   
                   @if($order->disputed_at)
                   <div class="flex items-center mb-4">
                       <div class="w-1/4 sm:w-1/5">
                           <span class="text-sm font-medium text-gray-900">Disputed</span>
                       </div>
                       <div class="flex-1">
                           <div class="text-sm text-gray-900">{{ $order->disputed_at->format('M d, Y h:i A') }}</div>
                           <div class="text-xs text-gray-500">
                               Reason: {{ $order->dispute_reason }}
                           </div>
                       </div>
                   </div>
                   @endif
                   
                   @if($order->cancelled_at)
                   <div class="flex items-center">
                       <div class="w-1/4 sm:w-1/5">
                           <span class="text-sm font-medium text-gray-900">Cancelled</span>
                       </div>
                       <div class="flex-1">
                           <div class="text-sm text-gray-900">{{ $order->cancelled_at->format('M d, Y h:i A') }}</div>
                           <div class="text-xs text-gray-500">
                               Reason: {{ $order->cancellation_reason }}
                           </div>
                       </div>
                   </div>
                   @endif
               </div>
           </div>
           
           <!-- Order Instructions Card -->
           <div class="bg-white rounded-lg shadow-sm overflow-hidden">
               <div class="px-6 py-4 border-b border-gray-200">
                   <h3 class="text-lg font-medium text-gray-900">Order Instructions</h3>
               </div>
               <div class="p-6">
                   @if($order->instructions)
                       <div class="prose max-w-none">
                           {!! nl2br(e($order->instructions)) !!}
                       </div>
                   @else
                       <p class="text-gray-500 italic">No detailed instructions provided.</p>
                   @endif
               </div>
           </div>
           
           <!-- Files Card -->
           <div class="bg-white rounded-lg shadow-sm overflow-hidden">
               <div class="px-6 py-4 border-b border-gray-200">
                   <div class="flex justify-between items-center">
                       <h3 class="text-lg font-medium text-gray-900">Files</h3>
                       <button @click="$refs.uploadFilesModal.style.display='block'" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                           <i class="fas fa-upload mr-1.5"></i> Upload Files
                       </button>
                   </div>
               </div>
               
               <div class="overflow-hidden">
                   @if(count($order->files) > 0)
                       <table class="min-w-full divide-y divide-gray-200">
                           <thead class="bg-gray-50">
                               <tr>
                                   <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">File</th>
                                   <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                   <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Uploaded By</th>
                                   <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                   <th scope="col" class="relative px-6 py-3">
                                       <span class="sr-only">Actions</span>
                                   </th>
                               </tr>
                           </thead>
                           <tbody class="bg-white divide-y divide-gray-200">
                               @foreach($order->files as $file)
                                   <tr>
                                       <td class="px-6 py-4 whitespace-nowrap">
                                           <div class="flex items-center">
                                               <div class="flex-shrink-0 h-8 w-8 bg-gray-100 rounded-md flex items-center justify-center">
                                                   @if(in_array(pathinfo($file->filename, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'gif']))
                                                       <i class="fas fa-file-image text-blue-500"></i>
                                                   @elseif(in_array(pathinfo($file->filename, PATHINFO_EXTENSION), ['doc', 'docx']))
                                                       <i class="fas fa-file-word text-blue-700"></i>
                                                   @elseif(in_array(pathinfo($file->filename, PATHINFO_EXTENSION), ['xls', 'xlsx']))
                                                       <i class="fas fa-file-excel text-green-700"></i>
                                                   @elseif(in_array(pathinfo($file->filename, PATHINFO_EXTENSION), ['pdf']))
                                                       <i class="fas fa-file-pdf text-red-700"></i>
                                                   @elseif(in_array(pathinfo($file->filename, PATHINFO_EXTENSION), ['zip', 'rar']))
                                                       <i class="fas fa-file-archive text-yellow-700"></i>
                                                   @else
                                                       <i class="fas fa-file text-gray-700"></i>
                                                   @endif
                                               </div>
                                               <div class="ml-4">
                                                   <div class="text-sm font-medium text-gray-900">{{ $file->filename }}</div>
                                                   <div class="text-xs text-gray-500">{{ number_format($file->filesize / 1024, 2) }} KB</div>
                                               </div>
                                           </div>
                                       </td>
                                       <td class="px-6 py-4 whitespace-nowrap">
                                           <div class="text-sm text-gray-900">{{ $file->file_description }}</div>
                                       </td>
                                       <td class="px-6 py-4 whitespace-nowrap">
                                           <div class="text-sm text-gray-900">{{ $file->user->name ?? 'Unknown' }}</div>
                                           <div class="text-xs text-gray-500">{{ $file->user->role ?? '' }}</div>
                                       </td>
                                       <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                           {{ $file->created_at->format('M d, Y') }}
                                       </td>
                                       <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                           <a href="{{ route('files.download', $file->id) }}" class="text-primary-600 hover:text-primary-900">
                                               <i class="fas fa-download"></i> Download
                                           </a>
                                       </td>
                                   </tr>
                               @endforeach
                           </tbody>
                       </table>
                   @else
                       <div class="p-6 text-center">
                           <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-gray-100">
                               <i class="fas fa-file-alt text-gray-400"></i>
                           </div>
                           <h3 class="mt-2 text-sm font-medium text-gray-900">No files yet</h3>
                           <p class="mt-1 text-sm text-gray-500">Get started by uploading a file.</p>
                           <div class="mt-4">
                               <button @click="$refs.uploadFilesModal.style.display='block'" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                   <i class="fas fa-upload mr-2"></i> Upload Files
                               </button>
                           </div>
                       </div>
                   @endif
               </div>
           </div>
           
           <!-- Status History Card -->
           <div class="bg-white rounded-lg shadow-sm overflow-hidden">
               <div class="px-6 py-4 border-b border-gray-200">
                   <h3 class="text-lg font-medium text-gray-900">Status History</h3>
               </div>
               <div class="overflow-hidden">
                   @if(count($statusHistory) > 0)
                       <table class="min-w-full divide-y divide-gray-200">
                           <thead class="bg-gray-50">
                               <tr>
                                   <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                   <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                   <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Changed By</th>
                                   <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                               </tr>
                           </thead>
                           <tbody class="bg-white divide-y divide-gray-200">
                               @foreach($statusHistory as $log)
                                   <tr>
                                       <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                           {{ $log->created_at->format('M d, Y h:i A') }}
                                       </td>
                                       <td class="px-6 py-4 whitespace-nowrap">
                                           @if($log->status == 'available')
                                               <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                   Available
                                               </span>
                                           @elseif(in_array($log->status, ['confirmed', 'in_progress', 'done', 'delivered']))
                                               <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-orange-100 text-orange-800">
                                                   {{ ucfirst($log->status) }}
                                               </span>
                                           @elseif($log->status == 'revision')
                                               <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                   Revision
                                               </span>
                                           @elseif(in_array($log->status, ['completed', 'paid', 'finished']))
                                               <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                   {{ ucfirst($log->status) }}
                                               </span>
                                           @elseif($log->status == 'dispute')
                                               <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                   Dispute
                                               </span>
                                           @elseif($log->status == 'cancelled')
                                               <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                   Cancelled
                                               </span>
                                           @else
                                               <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                   {{ ucfirst($log->status) }}
                                               </span>
                                           @endif
                                       </td>
                                       <td class="px-6 py-4 whitespace-nowrap">
                                           <div class="text-sm text-gray-900">{{ $log->changer->name ?? 'System' }}</div>
                                           <div class="text-xs text-gray-500">{{ $log->changer->role ?? '' }}</div>
                                       </td>
                                       <td class="px-6 py-4">
                                           <div class="text-sm text-gray-900">{{ $log->notes ?? 'No notes' }}</div>
                                       </td>
                                   </tr>
                               @endforeach
                           </tbody>
                       </table>
                   @else
                       <div class="p-6 text-center">
                           <p class="text-gray-500 italic">No status history available.</p>
                       </div>
                   @endif
               </div>
           </div>
       </div>
       
       <!-- Right Column: Messages and Client Info -->
       <div class="space-y-6">
           <!-- Client Info Card -->
           <div class="bg-white rounded-lg shadow-sm overflow-hidden">
               <div class="px-6 py-4 border-b border-gray-200">
                   <h3 class="text-lg font-medium text-gray-900">Client Information</h3>
               </div>
               <div class="p-6">
                   @if($order->client_name || $order->client_email)
                       <div class="mb-4">
                           @if($order->client_name)
                               <p class="text-sm text-gray-900"><span class="font-medium">Name:</span> {{ $order->client_name }}</p>
                           @endif
                           
                           @if($order->client_email)
                               <p class="text-sm text-gray-900 mt-1"><span class="font-medium">Email:</span> {{ $order->client_email }}</p>
                           @endif
                       </div>
                   @else
                       <p class="text-gray-500 italic">No client information available.</p>
                   @endif
                   
                   @if($order->client_id)
                       <div class="mt-4 pt-4 border-t border-gray-200">
                           <a href="" class="inline-flex items-center text-primary-600 hover:text-primary-900">
                               <i class="fas fa-external-link-alt mr-1.5"></i> View Client Profile
                           </a>
                       </div>
                   @endif
               </div>
           </div>
           
           <!-- Messages Card -->
           <div class="bg-white rounded-lg shadow-sm overflow-hidden">
               <div class="px-6 py-4 border-b border-gray-200">
                   <div class="flex justify-between items-center">
                       <h3 class="text-lg font-medium text-gray-900">Messages</h3>
                       <button @click="$refs.messageModal.style.display='block'" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                           <i class="fas fa-paper-plane mr-1.5"></i> Send Message
                       </button>
                   </div>
               </div>
               
               <div class="p-6 max-h-96 overflow-y-auto">
                   @if(count($order->messages) > 0)
                       <div class="space-y-4">
                           @foreach($order->messages as $message)
                               <div class="flex">
                                   <div class="flex-shrink-0 mr-3">
                                       @if($message->user && $message->user->profile_picture)
                                           <img class="h-10 w-10 rounded-full object-cover" src="{{ asset($message->user->profile_picture) }}" alt="{{ $message->user->name }}">
                                       @else
                                           <div class="h-10 w-10 rounded-full bg-{{ $message->message_type == 'client' ? 'blue' : ($message->message_type == 'writer' ? 'green' : ($message->message_type == 'system' ? 'gray' : ($message->message_type == 'revision' ? 'red' : ($message->message_type == 'dispute' ? 'yellow' : 'primary')))) }}-100 flex items-center justify-center">
                                               <span class="text-{{ $message->message_type == 'client' ? 'blue' : ($message->message_type == 'writer' ? 'green' : ($message->message_type == 'system' ? 'gray' : ($message->message_type == 'revision' ? 'red' : ($message->message_type == 'dispute' ? 'yellow' : 'primary')))) }}-600 font-medium text-sm">
                                                   @if($message->user)
                                                       {{ strtoupper(substr($message->user->name, 0, 1)) }}
                                                   @else
                                                       {{ $message->message_type == 'client' ? 'C' : ($message->message_type == 'writer' ? 'W' : 'S') }}
                                                   @endif
                                               </span>
                                           </div>
                                       @endif
                                   </div>
                                   <div class="flex-1 bg-gray-50 rounded-lg p-4">
                                       <div class="flex items-center justify-between mb-2">
                                           <div>
                                               <span class="font-medium text-gray-900">
                                                   @if($message->user)
                                                       {{ $message->user->name }}
                                                   @else
                                                       {{ $message->message_type == 'client' ? 'Client' : ($message->message_type == 'writer' ? 'Writer' : 'System') }}
                                                   @endif
                                               </span>
                                               <span class="ml-2 text-xs text-gray-500">{{ $message->created_at->format('M d, Y h:i A') }}</span>
                                           </div>
                                           <div>
                                               <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize bg-{{ $message->message_type == 'client' ? 'blue' : ($message->message_type == 'writer' ? 'green' : ($message->message_type == 'system' ? 'gray' : ($message->message_type == 'revision' ? 'red' : ($message->message_type == 'dispute' ? 'yellow' : 'primary')))) }}-100 text-{{ $message->message_type == 'client' ? 'blue' : ($message->message_type == 'writer' ? 'green' : ($message->message_type == 'system' ? 'gray' : ($message->message_type == 'revision' ? 'red' : ($message->message_type == 'dispute' ? 'yellow' : 'primary')))) }}-800">
                                                   {{ $message->message_type }}
                                               </span>
                                           </div>
                                       </div>
                                       
                                       @if($message->title)
                                           <h4 class="text-sm font-medium text-gray-900 mb-1">{{ $message->title }}</h4>
                                       @endif
                                       
                                       <div class="text-sm text-gray-700 whitespace-pre-wrap">{!! nl2br(e($message->message)) !!}</div>
                                       
                                       @if(!$message->read_at && $message->receiver_id == Auth::id())
                                           <div class="mt-2 text-xs text-red-600">
                                               <i class="fas fa-circle mr-1"></i> Unread
                                           </div>
                                       @elseif($message->read_at)
                                           <div class="mt-2 text-xs text-gray-500">
                                               Read: {{ $message->read_at->format('M d, Y h:i A') }}
                                           </div>
                                       @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center">
                            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-gray-100">
                                <i class="fas fa-comment-alt text-gray-400"></i>
                            </div>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No messages</h3>
                            <p class="mt-1 text-sm text-gray-500">Start the conversation by sending a message.</p>
                            <div class="mt-4">
                                <button @click="$refs.messageModal.style.display='block'" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                    <i class="fas fa-paper-plane mr-2"></i> Send Message
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Payment Details Card -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Payment Details</h3>
                </div>
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-sm font-medium text-gray-500">Order Price:</span>
                        <span class="text-sm font-medium text-gray-900">${{ number_format($order->price, 2) }}</span>
                    </div>
                    
                    @if($order->discount_amount > 0)
                        <div class="flex justify-between items-center mb-4">
                            <span class="text-sm font-medium text-gray-500">Discount:</span>
                            <span class="text-sm font-medium text-red-600">-${{ number_format($order->discount_amount, 2) }}</span>
                        </div>
                    @endif
                    
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-sm font-medium text-gray-500">Writer Payment:</span>
                        <span class="text-sm font-medium text-gray-900">${{ number_format($order->writer_payment ?? ($order->price * 0.7), 2) }}</span>
                    </div>
                    
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-sm font-medium text-gray-500">Platform Fee:</span>
                        <span class="text-sm font-medium text-gray-900">${{ number_format($order->price - ($order->writer_payment ?? ($order->price * 0.7)), 2) }}</span>
                    </div>
                    
                    <div class="pt-4 border-t border-gray-200">
                        <div class="flex justify-between items-center font-medium">
                            <span class="text-gray-900">Payment Status:</span>
                            @if($order->payment_status == 'paid')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-1"></i> Paid
                                </span>
                            @elseif($order->payment_status == 'pending')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    <i class="fas fa-clock mr-1"></i> Pending
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    <i class="fas fa-times-circle mr-1"></i> Not Paid
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modals -->
    <!-- Assign Order Modal -->
    <div x-ref="assignOrderModal" class="fixed inset-0 z-50 overflow-auto bg-gray-900 bg-opacity-50 hidden" style="display: none;">
        <div class="relative p-8 bg-white max-w-md m-auto flex-col flex rounded-lg shadow-lg mt-20">
            <div class="flex justify-between items-center mb-6">
                <h4 class="text-xl font-medium text-gray-900">Assign to Writer</h4>
                <button @click="$refs.assignOrderModal.style.display='none'" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form action="{{ route('admin.orders.assign', $order->id) }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="writer_id" class="block text-sm font-medium text-gray-700 mb-1">Select Writer</label>
                    <select id="writer_id" name="writer_id" class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                        <option value="">Select a writer...</option>
                        @foreach($writers as $writer)
                            <option value="{{ $writer->id }}" {{ (old('writer_id') == $writer->id || $order->writer_id == $writer->id) ? 'selected' : '' }}>
                                {{ $writer->name }} (Rating: {{ number_format($writer->rating ?? 0, 1) }})
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="mb-6">
                    <label for="message" class="block text-sm font-medium text-gray-700 mb-1">Assignment Message (Optional)</label>
                    <textarea id="message" name="message" rows="3" class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm">{{ old('message') }}</textarea>
                    <p class="mt-1 text-xs text-gray-500">This message will be sent to the writer along with the assignment notification.</p>
                </div>
                
                <div class="flex justify-end">
                    <button type="button" @click="$refs.assignOrderModal.style.display='none'" class="mr-3 px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        Assign Order
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Complete Order Modal -->
    <div x-ref="completeOrderModal" class="fixed inset-0 z-50 overflow-auto bg-gray-900 bg-opacity-50 hidden" style="display: none;">
        <div class="relative p-8 bg-white max-w-md m-auto flex-col flex rounded-lg shadow-lg mt-20">
            <div class="flex justify-between items-center mb-6">
                <h4 class="text-xl font-medium text-gray-900">Mark Order as Complete</h4>
                <button @click="$refs.completeOrderModal.style.display='none'" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form action="{{ route('admin.orders.complete', $order->id) }}" method="POST">
                @csrf
                <div class="mb-6">
                    <label for="completion_notes" class="block text-sm font-medium text-gray-700 mb-1">Completion Notes (Optional)</label>
                    <textarea id="completion_notes" name="completion_notes" rows="3" class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm">{{ old('completion_notes') }}</textarea>
                    <p class="mt-1 text-xs text-gray-500">These notes will be visible to the writer and logged in the order history.</p>
                </div>
                
                <div class="flex justify-end">
                    <button type="button" @click="$refs.completeOrderModal.style.display='none'" class="mr-3 px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        Mark as Complete
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Revision Request Modal -->
    <div x-ref="revisionModal" class="fixed inset-0 z-50 overflow-auto bg-gray-900 bg-opacity-50 hidden" style="display: none;">
        <div class="relative p-8 bg-white max-w-md m-auto flex-col flex rounded-lg shadow-lg mt-20">
            <div class="flex justify-between items-center mb-6">
                <h4 class="text-xl font-medium text-gray-900">Request Revision</h4>
                <button @click="$refs.revisionModal.style.display='none'" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form action="{{ route('admin.orders.request-revision', $order->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-4">
                    <label for="revision_instructions" class="block text-sm font-medium text-gray-700 mb-1">Revision Instructions</label>
                    <textarea id="revision_instructions" name="revision_instructions" rows="4" class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm" required>{{ old('revision_instructions') }}</textarea>
                </div>
                
                <div class="mb-4">
                    <label for="revision_deadline" class="block text-sm font-medium text-gray-700 mb-1">Revision Deadline</label>
                    <input type="datetime-local" id="revision_deadline" name="revision_deadline" class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm" value="{{ old('revision_deadline', now()->addDays(1)->format('Y-m-d\TH:i')) }}">
                </div>
                
                <div class="mb-6">
                    <label for="files" class="block text-sm font-medium text-gray-700 mb-1">Attach Files (Optional)</label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                        <div class="space-y-1 text-center">
                            <i class="fas fa-upload text-gray-400 text-3xl mb-2"></i>
                            <div class="flex text-sm text-gray-600">
                                <label for="files" class="relative cursor-pointer bg-white rounded-md font-medium text-primary-600 hover:text-primary-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary-500">
                                    <span>Upload files</span>
                                    <input id="files" name="files[]" type="file" class="sr-only" multiple>
                                </label>
                                <p class="pl-1">or drag and drop</p>
                            </div>
                            <p class="text-xs text-gray-500">
                                PNG, JPG, PDF, DOC, DOCX, XLS, XLSX up to 10MB
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end">
                    <button type="button" @click="$refs.revisionModal.style.display='none'" class="mr-3 px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        Request Revision
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Dispute Modal -->
    <div x-ref="disputeModal" class="fixed inset-0 z-50 overflow-auto bg-gray-900 bg-opacity-50 hidden" style="display: none;">
        <div class="relative p-8 bg-white max-w-md m-auto flex-col flex rounded-lg shadow-lg mt-20">
            <div class="flex justify-between items-center mb-6">
                <h4 class="text-xl font-medium text-gray-900">Mark Order as Disputed</h4>
                <button @click="$refs.disputeModal.style.display='none'" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form action="{{ route('admin.orders.dispute', $order->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-4">
                    <label for="dispute_reason" class="block text-sm font-medium text-gray-700 mb-1">Dispute Reason</label>
                    <textarea id="dispute_reason" name="dispute_reason" rows="4" class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm" required>{{ old('dispute_reason') }}</textarea>
                </div>
                
                <div class="mb-6">
                    <label for="files" class="block text-sm font-medium text-gray-700 mb-1">Attach Evidence (Optional)</label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                        <div class="space-y-1 text-center">
                            <i class="fas fa-upload text-gray-400 text-3xl mb-2"></i>
                            <div class="flex text-sm text-gray-600">
                                <label for="files" class="relative cursor-pointer bg-white rounded-md font-medium text-primary-600 hover:text-primary-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary-500">
                                    <span>Upload files</span>
                                    <input id="files" name="files[]" type="file" class="sr-only" multiple>
                                </label>
                                <p class="pl-1">or drag and drop</p>
                            </div>
                            <p class="text-xs text-gray-500">
                                PNG, JPG, PDF, DOC, DOCX, XLS, XLSX up to 10MB
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end">
                    <button type="button" @click="$refs.disputeModal.style.display='none'" class="mr-3 px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                        Mark as Disputed
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Cancel Order Modal -->
    <div x-ref="cancelOrderModal" class="fixed inset-0 z-50 overflow-auto bg-gray-900 bg-opacity-50 hidden" style="display: none;">
        <div class="relative p-8 bg-white max-w-md m-auto flex-col flex rounded-lg shadow-lg mt-20">
            <div class="flex justify-between items-center mb-6">
                <h4 class="text-xl font-medium text-gray-900">Cancel Order</h4>
                <button @click="$refs.cancelOrderModal.style.display='none'" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form action="{{ route('admin.orders.cancel', $order->id) }}" method="POST">
                @csrf
                <div class="mb-6">
                    <label for="cancellation_reason" class="block text-sm font-medium text-gray-700 mb-1">Cancellation Reason</label>
                    <textarea id="cancellation_reason" name="cancellation_reason" rows="4" class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm" required>{{ old('cancellation_reason') }}</textarea>
                </div>
                
                <div class="flex justify-end">
                    <button type="button" @click="$refs.cancelOrderModal.style.display='none'" class="mr-3 px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        Back
                    </button>
                    <button type="submit" class="px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                        Cancel Order
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Send Message Modal -->
    <div x-ref="messageModal" class="fixed inset-0 z-50 overflow-auto bg-gray-900 bg-opacity-50 hidden" style="display: none;">
        <div class="relative p-8 bg-white max-w-md m-auto flex-col flex rounded-lg shadow-lg mt-20">
            <div class="flex justify-between items-center mb-6">
                <h4 class="text-xl font-medium text-gray-900">Send Message</h4>
                <button @click="$refs.messageModal.style.display='none'" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form action="{{ route('admin.messages.send-as-support', $order->id) }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="message_type" class="block text-sm font-medium text-gray-700 mb-1">Message Type</label>
                    <select id="message_type" name="message_type" class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                        <option value="admin">Send as Admin</option>
                        <option value="client">Send as Client</option>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label for="message_title" class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                    <input type="text" id="message_title" name="title" class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm" value="{{ old('title') }}">
                </div>
                
                <div class="mb-6">
                    <label for="message_content" class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                    <textarea id="message_content" name="message" rows="4" class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm" required>{{ old('message') }}</textarea>
                </div>
                
                <div class="flex justify-end">
                    <button type="button" @click="$refs.messageModal.style.display='none'" class="mr-3 px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        Send Message
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Upload Files Modal -->
    <div x-ref="uploadFilesModal" class="fixed inset-0 z-50 overflow-auto bg-gray-900 bg-opacity-50 hidden" style="display: none;">
        <div class="relative p-8 bg-white max-w-md m-auto flex-col flex rounded-lg shadow-lg mt-20">
            <div class="flex justify-between items-center mb-6">
                <h4 class="text-xl font-medium text-gray-900">Upload Files</h4>
                <button @click="$refs.uploadFilesModal.style.display='none'" class="text-gray-400 hover:text-gray-500">
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
                                PNG, JPG, PDF, DOC, DOCX, XLS, XLSX up to 10MB
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end">
                    <button type="button" @click="$refs.uploadFilesModal.style.display='none'" class="mr-3 px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
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
 
 @push('scripts')
 <script>
    // File upload preview
    document.addEventListener('DOMContentLoaded', function() {
        // Preview selected files in the upload modals
        const fileInputs = document.querySelectorAll('input[type="file"]');
        fileInputs.forEach(input => {
            input.addEventListener('change', function(e) {
                const fileCount = this.files.length;
                const fileLabel = this.parentElement.querySelector('span');
                fileLabel.textContent = fileCount > 0 ? `${fileCount} files selected` : 'Upload files';
            });
        });
    });
 </script>
 @endpush