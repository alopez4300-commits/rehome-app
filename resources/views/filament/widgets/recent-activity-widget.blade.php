<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Recent Activity Across All Projects
        </x-slot>

        <x-slot name="description">
            Monitor system-wide activity from MyHome streams (read-only view for site health).
        </x-slot>

        <div class="space-y-3">
            @forelse($this->getRecentActivity() as $entry)
                <div class="border-l-4 border-blue-500 pl-4 py-2">
                    <div class="flex justify-between items-start">
                        <div>
                            <span class="font-medium text-sm">{{ $entry['user_name'] ?? 'Unknown User' }}</span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                @if($entry['kind'] === 'comment') bg-blue-100 text-blue-800
                                @elseif($entry['kind'] === 'file_upload') bg-green-100 text-green-800
                                @elseif($entry['kind'] === 'status_change') bg-yellow-100 text-yellow-800
                                @elseif($entry['kind'] === 'system') bg-gray-100 text-gray-800
                                @else bg-purple-100 text-purple-800
                                @endif ml-2">
                                {{ ucfirst(str_replace('_', ' ', $entry['kind'] ?? 'unknown')) }}
                            </span>
                            <span class="text-xs text-gray-400 ml-2">
                                in {{ $entry['workspace_name'] ?? 'Unknown' }} / {{ $entry['project_name'] ?? 'Unknown' }}
                            </span>
                        </div>
                        <span class="text-xs text-gray-400">
                            {{ \Carbon\Carbon::parse($entry['timestamp'])->diffForHumans() }}
                        </span>
                    </div>
                    <div class="mt-1 text-sm text-gray-700">
                        @if(isset($entry['content']))
                            {{ \Illuminate\Support\Str::limit($entry['content'], 100) }}
                        @elseif(isset($entry['old_status']) && isset($entry['new_status']))
                            Status changed from {{ $entry['old_status'] }} to {{ $entry['new_status'] }}
                        @elseif(isset($entry['filename']))
                            📁 Uploaded: {{ $entry['filename'] }}
                        @else
                            {{ $entry['kind'] ?? 'Unknown' }} activity
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-8 text-gray-500">
                    <p class="text-sm">No activity yet across the system.</p>
                    <p class="text-xs mt-1">Entries will appear here when users add them via the SPA.</p>
                </div>
            @endforelse
        </div>

        @if($this->getRecentActivity()->count() > 0)
            <div class="mt-4 pt-4 border-t border-gray-200">
                <p class="text-xs text-gray-500">
                    🔍 Admin View: Monitoring {{ $this->getRecentActivity()->count() }} recent entries across all projects.
                </p>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>