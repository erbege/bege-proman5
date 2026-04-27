<div>
    {{-- Success/Error Messages --}}
    @if(session()->has('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
            {{ session('success') }}
        </div>
    @endif
    @if(session()->has('error'))
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        {{-- Left: File Info & Versions --}}
        <div class="lg:col-span-2 space-y-4">
            {{-- File Info Card --}}
            <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Informasi File</h3>
                        </div>
                        {{-- Status Badge --}}
                        <span
                            class="px-3 py-1 text-sm font-medium rounded-full 
                            {{ $file->status === 'draft' ? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' : '' }}
                            {{ $file->status === 'review' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300' : '' }}
                            {{ $file->status === 'approved' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' : '' }}
                            {{ $file->status === 'final' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300' : '' }}">
                            {{ $file->status_label }}
                        </span>
                    </div>

                    <dl class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Kategori</dt>
                            <dd class="mt-1 text-gray-900 dark:text-white">{{ $file->category_label }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Versi Aktif</dt>
                            <dd class="mt-1 text-gray-900 dark:text-white">v{{ $file->current_version }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Diupload oleh</dt>
                            <dd class="mt-1 text-gray-900 dark:text-white">{{ $file->uploader->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Terakhir diupdate</dt>
                            <dd class="mt-1 text-gray-900 dark:text-white">{{ $file->updated_at->format('d M Y H:i') }}
                            </dd>
                        </div>
                    </dl>

                    @if($file->description)
                        <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <dt class="text-sm text-gray-500 dark:text-gray-400">Deskripsi</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $file->description }}</dd>
                        </div>
                    @endif

                    {{-- Status Workflow --}}
                    <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Ubah Status</h4>
                        <div class="flex flex-wrap gap-2">
                            @foreach(['draft' => 'Draft', 'review' => 'Review', 'approved' => 'Approved', 'final' => 'Final'] as $status => $label)
                                @if($file->canTransitionTo($status))
                                    <button wire:click="updateStatus('{{ $status }}')"
                                        class="px-3 py-1 text-xs font-medium rounded border transition
                                                {{ $status === 'review' ? 'border-yellow-400 text-yellow-700 hover:bg-yellow-50' : '' }}
                                                {{ $status === 'approved' ? 'border-green-400 text-green-700 hover:bg-green-50' : '' }}
                                                {{ $status === 'final' ? 'border-blue-400 text-blue-700 hover:bg-blue-50' : '' }}
                                                {{ $status === 'draft' ? 'border-gray-400 text-gray-700 hover:bg-gray-50' : '' }}">
                                        → {{ $label }}
                                    </button>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- Version History --}}
            <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Riwayat Versi</h3>
                        <button wire:click="openVersionModal"
                            class="inline-flex items-center px-3 py-1.5 bg-green-600 text-white text-sm rounded-md hover:bg-green-700">
                            <x-heroicon-o-arrow-up-tray class="w-4 h-4 mr-1" />
                            Upload Versi Baru
                        </button>
                    </div>

                    <div class="space-y-4">
                        @foreach($file->versions->sortByDesc('version') as $version)
                            <div wire:key="version-{{ $version->id }}"
                                class="flex items-start p-2 rounded-lg border {{ $version->version === $file->current_version ? 'border-green-300 bg-green-50 dark:border-green-700 dark:bg-green-900/20' : 'border-gray-200 dark:border-gray-700' }}">
                                <div class="flex-shrink-0 mr-4">
                                    <div
                                        class="w-10 h-10 rounded-full flex items-center justify-center {{ $version->version === $file->current_version ? 'bg-green-500 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-400' }}">
                                        v{{ $version->version }}
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ $version->notes ?? 'Version ' . $version->version }}
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $version->uploader->name }} •
                                                {{ $version->created_at->format('d M Y H:i') }} •
                                                {{ $version->file_size_formatted }}
                                            </p>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('projects.files.download', [$project, $file, $version]) }}"
                                                class="p-1 text-blue-600 hover:bg-blue-100 rounded" title="Download">
                                                <x-heroicon-o-arrow-down-tray class="w-5 h-5" />
                                            </a>
                                            @if($version->version !== $file->current_version)
                                                <button wire:click="rollback({{ $version->id }})"
                                                    wire:confirm="Rollback ke versi {{ $version->version }}?"
                                                    class="p-1 text-orange-600 hover:bg-orange-100 rounded" title="Rollback">
                                                    <x-heroicon-o-arrow-uturn-left class="w-5 h-5" />
                                                </button>
                                            @else
                                                <span
                                                    class="px-2 py-0.5 text-xs bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300 rounded">Aktif</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Right: Comments --}}
        <div class="space-y-4">
            <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                        Feedback & Komentar
                        @php
                            $unresolvedCount = $file->comments->where('resolved', false)->whereNull('parent_id')->count();
                        @endphp
                        @if($unresolvedCount > 0)
                            <span
                                class="ml-2 px-2 py-0.5 text-xs bg-red-100 text-red-700 rounded-full">{{ $unresolvedCount }}
                                belum resolved</span>
                        @endif
                    </h3>

                    {{-- Add Comment Form --}}
                    <form wire:submit="addComment" class="mb-6">
                        <textarea wire:model="newComment" rows="3" required
                            placeholder="Tulis komentar atau feedback..."
                            class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-dark-700 dark:text-white text-sm"></textarea>
                        @error('newComment') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        <div class="mt-2 flex justify-end">
                            <button type="submit"
                                class="px-4 py-2 bg-gold-500 text-white text-sm rounded-md hover:bg-gold-600">
                                Kirim
                            </button>
                        </div>
                    </form>

                    {{-- Comments List --}}
                    <div class="space-y-4">
                        @forelse($file->comments->whereNull('parent_id') as $comment)
                            <div wire:key="comment-{{ $comment->id }}"
                                class="p-3 rounded-lg {{ $comment->resolved ? 'bg-gray-50 dark:bg-dark-700 opacity-60' : 'bg-gray-100 dark:bg-dark-700' }}">
                                <div class="flex items-start justify-between">
                                    <div class="flex items-center">
                                        <div
                                            class="w-8 h-8 rounded-full bg-gold-500 text-white flex items-center justify-center text-xs font-medium">
                                            {{ substr($comment->user->name, 0, 2) }}
                                        </div>
                                        <div class="ml-2">
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ $comment->user->name }}</p>
                                            <p class="text-xs text-gray-500">{{ $comment->created_at->diffForHumans() }}</p>
                                        </div>
                                    </div>
                                    <button wire:click="toggleResolve({{ $comment->id }})"
                                        class="p-1 rounded {{ $comment->resolved ? 'text-green-600' : 'text-gray-400 hover:text-green-600' }}"
                                        title="{{ $comment->resolved ? 'Unresolve' : 'Resolve' }}">
                                        <x-heroicon-o-check-circle class="w-5 h-5" />
                                    </button>
                                </div>
                                <p
                                    class="mt-2 text-sm text-gray-700 dark:text-gray-300 {{ $comment->resolved ? 'line-through' : '' }}">
                                    {{ $comment->comment }}
                                </p>

                                {{-- Replies --}}
                                @if($comment->replies->isNotEmpty())
                                    <div class="mt-3 pl-4 border-l-2 border-gray-200 dark:border-gray-600 space-y-2">
                                        @foreach($comment->replies as $reply)
                                            <div class="text-sm" wire:key="reply-{{ $reply->id }}">
                                                <span
                                                    class="font-medium text-gray-900 dark:text-white">{{ $reply->user->name }}</span>
                                                <span class="text-gray-500 text-xs">{{ $reply->created_at->diffForHumans() }}</span>
                                                <p class="text-gray-700 dark:text-gray-300">{{ $reply->comment }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                {{-- Reply Form --}}
                                @if($replyingTo === $comment->id)
                                    <div class="mt-2 flex gap-2">
                                        <input type="text" wire:model="replyComment" placeholder="Balas..."
                                            class="flex-1 text-xs rounded-md border-gray-300 dark:border-gray-600 dark:bg-dark-600 dark:text-white">
                                        <button wire:click="submitReply"
                                            class="px-2 py-1 bg-gold-500 text-white text-xs rounded hover:bg-gold-600">
                                            Kirim
                                        </button>
                                        <button wire:click="cancelReply"
                                            class="px-2 py-1 bg-gray-200 dark:bg-dark-600 text-gray-700 dark:text-gray-300 text-xs rounded">
                                            Batal
                                        </button>
                                    </div>
                                @else
                                    <button wire:click="startReply({{ $comment->id }})"
                                        class="mt-2 text-xs text-gray-500 hover:text-gray-700">
                                        Balas
                                    </button>
                                @endif
                            </div>
                        @empty
                            <p class="text-sm text-gray-500 text-center py-4">Belum ada komentar</p>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Danger Zone --}}
            <div
                class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg border border-red-200 dark:border-red-900">
                <div class="p-4">
                    <h3 class="text-lg font-medium text-red-600 dark:text-red-400 mb-4">Danger Zone</h3>
                    <button wire:click="deleteFile"
                        wire:confirm="Yakin ingin menghapus file ini? Semua versi akan ikut terhapus."
                        class="w-full px-4 py-2 bg-red-600 text-white text-sm rounded-md hover:bg-red-700">
                        Hapus File
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Upload Version Modal --}}
    @if($showVersionModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" @keydown.escape.window="$wire.closeVersionModal()">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75" wire:click="closeVersionModal"></div>
                <div class="relative bg-white dark:bg-dark-800 rounded-lg shadow-xl max-w-lg w-full p-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Upload Versi Baru</h3>
                    <form wire:submit="uploadVersion">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">File</label>
                                <input type="file" wire:model="newVersionFile"
                                    class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-gold-50 file:text-gold-700 hover:file:bg-gold-100">
                                @error('newVersionFile') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror

                                <div wire:loading wire:target="newVersionFile" class="mt-2">
                                    <div class="flex items-center gap-2">
                                        <svg class="animate-spin h-4 w-4 text-gold-500" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                            </path>
                                        </svg>
                                        <span class="text-sm text-gray-500">Mengupload...</span>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Catatan
                                    Perubahan</label>
                                <textarea wire:model="versionNotes" rows="2"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-dark-700 dark:text-white"
                                    placeholder="Jelaskan perubahan yang dilakukan..."></textarea>
                            </div>
                        </div>
                        <div class="mt-6 flex justify-end gap-2">
                            <button type="button" wire:click="closeVersionModal"
                                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-700 rounded-md">
                                Batal
                            </button>
                            <button type="submit"
                                class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 disabled:opacity-50"
                                wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="uploadVersion">Upload</span>
                                <span wire:loading wire:target="uploadVersion">Uploading...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>


