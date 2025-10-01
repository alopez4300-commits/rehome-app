<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            MyHome Activity Stream
        </x-slot>

        <x-slot name="description">
            Recent activity entries from this project's MyHome stream.
        </x-slot>

        <div class="space-y-4">
            @if($entries->count() > 0)
                @foreach($entries as $entry)
                    <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                        <div class="flex justify-between items-start mb-2">
                            <div class="flex items-center space-x-2">
                                <span class="text-sm font-medium text-gray-900">{{ $entry['user_name'] ?? 'Unknown' }}</span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($entry['kind'] === 'comment') bg-blue-100 text-blue-800
                                    @elseif($entry['kind'] === 'file_upload') bg-green-100 text-green-800
                                    @elseif($entry['kind'] === 'status_change') bg-yellow-100 text-yellow-800
                                    @elseif($entry['kind'] === 'system') bg-gray-100 text-gray-800
                                    @else bg-purple-100 text-purple-800
                                    @endif">
                                    {{ ucfirst(str_replace('_', ' ', $entry['kind'] ?? 'unknown')) }}
                                </span>
                            </div>
                            <span class="text-xs text-gray-500">
                                {{ \Carbon\Carbon::parse($entry['timestamp'])->diffForHumans() }}
                            </span>
                        </div>
                        
                        @if(isset($entry['content']))
                            <p class="text-sm text-gray-700 mb-2">{{ $entry['content'] }}</p>
                        @endif

                        @if(isset($entry['old_status']) && isset($entry['new_status']))
                            <p class="text-sm text-gray-700 mb-2">
                                Status changed from <span class="font-medium">{{ $entry['old_status'] }}</span> 
                                to <span class="font-medium">{{ $entry['new_status'] }}</span>
                            </p>
                        @endif

                        @if(isset($entry['filename']))
                            <p class="text-sm text-gray-700 mb-2">
                                📁 Uploaded: <span class="font-medium">{{ $entry['filename'] }}</span>
                            </p>
                        @endif

                        <div class="text-xs text-gray-400 mt-2">
                            ID: {{ $entry['id'] ?? 'N/A' }} | Project: {{ $project->name }}
                        </div>
                    </div>
                @endforeach
            @else
                <div class="text-center py-8 text-gray-500">
                    <p class="text-sm">No MyHome entries yet.</p>
                    <p class="text-xs mt-1">Activity will appear here as it happens.</p>
                </div>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>