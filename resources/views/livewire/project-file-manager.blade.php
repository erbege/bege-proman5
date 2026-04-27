<div>
    {{-- Success/Error Messages --}}
    @if(session()->has('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
            {{ session('success') }}
        </div>
    @endif


    {{-- Breadcrumbs --}}
    <div class="mb-4">
        <nav class="flex" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('projects.files.index', $project) }}" wire:navigate
                        class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-gold-600 dark:text-gray-400 dark:hover:text-white">
                        <x-heroicon-o-home class="w-4 h-4 mr-2" />
                        Home
                    </a>
                </li>
                @foreach($this->breadcrumbs as $folder)
                    <li>
                        <div class="flex items-center">
                            <x-heroicon-o-chevron-right class="w-4 h-4 text-gray-400" />
                            <a href="{{ route('projects.files.index', [$project, 'folder' => $folder->id]) }}"
                                wire:navigate
                                class="ml-1 text-sm font-medium text-gray-700 hover:text-gold-600 dark:text-gray-400 dark:hover:text-white md:ml-2">
                                {{ $folder->name }}
                            </a>
                        </div>
                    </li>
                @endforeach
            </ol>
        </nav>
    </div>

    {{-- Files Grid --}}
    <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-4">
            {{-- Toolbar --}}
            <div class="mb-4 flex flex-wrap justify-between items-center gap-4">
                {{-- Actions --}}
                <div class="flex items-center gap-2">
                    @can('files.create')
                    <button wire:click="openUploadModal"
                        class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition lg:text-sm text-xs">
                        <x-heroicon-o-arrow-up-tray class="w-4 h-4 mr-2" />
                        Upload
                    </button>
                    <button wire:click="openFolderModal"
                        class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition lg:text-sm text-xs">
                        <x-heroicon-o-folder-plus class="w-4 h-4 mr-2" />
                        Folder
                    </button>
                    @endcan
                </div>

                {{-- Filters --}}
                <div class="flex items-center gap-2">
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari file..."
                        class="text-sm rounded-md border-gray-300 dark:border-gray-600 dark:bg-dark-700 dark:text-white w-48">
                    <select wire:model.live="category"
                        class="text-sm rounded-md border-gray-300 dark:border-gray-600 dark:bg-dark-700 dark:text-white">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    <select wire:model.live="status"
                        class="text-sm rounded-md border-gray-300 dark:border-gray-600 dark:bg-dark-700 dark:text-white">
                        <option value="">Semua Status</option>
                        @foreach($statuses as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            @if($files->isEmpty() && $folders->isEmpty() && !$search && !$category && !$status)
                <div class="text-center py-12">
                    <x-heroicon-o-folder-open class="mx-auto h-12 w-12 text-gray-400" />
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Belum ada file</h3>
                    <p class="mt-1 text-sm text-gray-500">Upload file pertama untuk memulai.</p>
                </div>
            @else
                {{-- Folders --}}
                @if($folders->isNotEmpty())
                    <div class="mb-4">
                        <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Folder</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                            @foreach($folders as $folder)
                                <div class="relative group">
                                    <a href="{{ route('projects.files.index', [$project, 'folder' => $folder->id]) }}"
                                        class="flex flex-col items-center p-4 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-dark-700 transition">
                                        <x-heroicon-o-folder class="w-12 h-12 text-gold-500" />
                                        <span class="mt-2 text-sm font-medium text-gray-900 dark:text-white truncate w-full text-center">{{ $folder->name }}</span>
                                    </a>
                                    {{-- Folder Actions --}}
                                    <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity flex gap-1 bg-white/80 dark:bg-dark-800/80 rounded p-1">
                                        @can('files.update')
                                        <button wire:click="editFolder({{ $folder->id }})" class="text-blue-600 hover:text-blue-800 p-1" title="Rename">
                                            <x-heroicon-o-pencil class="w-4 h-4" />
                                        </button>
                                        @endcan
                                        @can('files.delete')
                                        <button wire:click="deleteFolder({{ $folder->id }})" wire:confirm="Hapus folder ini?" class="text-red-600 hover:text-red-800 p-1" title="Hapus">
                                            <x-heroicon-o-trash class="w-4 h-4" />
                                        </button>
                                        @endcan
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Files Table --}}
                <div class="overflow-x-auto" wire:loading.class="opacity-50">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-dark-700">
                            <tr>
                                <th class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">File</th>
                                <th class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Kategori</th>
                                <th class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                                <th class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Versi</th>
                                <th class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Ukuran</th>
                                <th class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Updated</th>
                                <th class="px-3 py-1.5"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($files as $file)
                                <tr class="hover:bg-gray-50 dark:hover:bg-dark-700" wire:key="file-{{ $file->id }}">
                                <td class="px-3 py-1.5">
                                    <a href="{{ route('projects.files.show', [$project, $file]) }}" class="flex items-center" wire:navigate>
                                        <x-heroicon-o-document class="w-8 h-8 text-gray-400 mr-3" />
                                        <div>
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $file->name }}</div>
                                            <div class="text-xs text-gray-500">{{ $file->original_name }}</div>
                                        </div>
                                    </a>
                                </td>
                                <td class="px-3 py-1.5">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ $file->category_label }}</span>
                                </td>
                                <td class="px-3 py-1.5">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full 
                                        {{ $file->status === 'draft' ? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' : '' }}
                                        {{ $file->status === 'review' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300' : '' }}
                                        {{ $file->status === 'approved' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' : '' }}
                                        {{ $file->status === 'final' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300' : '' }}">
                                        {{ $file->status_label }}
                                    </span>
                                </td>
                                <td class="px-3 py-1.5">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">v{{ $file->current_version }}</span>
                                </td>
                                <td class="px-3 py-1.5">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ $file->latestVersion?->file_size_formatted ?? '-' }}</span>
                                </td>
                                <td class="px-3 py-1.5">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ $file->updated_at->diffForHumans() }}</span>
                                </td>
                                <td class="px-3 py-1.5 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('projects.files.download', [$project, $file]) }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400" title="Download">
                                                <x-heroicon-o-arrow-down-tray class="w-5 h-5" />
                                            </a>
                                            @can('files.update')
                                            <button wire:click="openMoveModal({{ $file->id }})" class="text-gray-600 hover:text-gray-800 dark:text-gray-400" title="Pindahkan">
                                                <x-heroicon-o-arrow-right class="w-5 h-5" />
                                            </button>
                                            @endcan
                                            @can('files.delete')
                                            <button wire:click="confirmDelete({{ $file->id }})" class="text-red-600 hover:text-red-800 dark:text-red-400" title="Hapus">
                                                <x-heroicon-o-trash class="w-5 h-5" />
                                            </button>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="mt-4">
                    {{ $files->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Upload Modal --}}
    @if($showUploadModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" @keydown.escape.window="$wire.closeUploadModal()">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75" wire:click="closeUploadModal"></div>
                <div class="relative bg-white dark:bg-dark-800 rounded-lg shadow-xl max-w-lg w-full p-4">
                    <button type="button" wire:click="closeUploadModal" class="absolute top-4 right-4 text-gray-400 hover:text-gray-500">
                        <x-heroicon-o-x-circle class="w-6 h-6" />
                    </button>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Upload File</h3>
                    <form wire:submit="uploadFile">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">File</label>
                                <input type="file" wire:model="file" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-gold-50 file:text-gold-700 hover:file:bg-gold-100">
                                @error('file') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror

                                {{-- Upload Progress --}}
                                <div wire:loading wire:target="file" class="mt-2">
                                    <div class="flex items-center gap-2">
                                        <svg class="animate-spin h-4 w-4 text-gold-500" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span class="text-sm text-gray-500">Mengupload file...</span>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama (opsional)</label>
                                <input type="text" wire:model="fileName" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-dark-700 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Kategori</label>
                                <select wire:model="fileCategory" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-dark-700 dark:text-white">
                                    @foreach($categories as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Deskripsi</label>
                                <textarea wire:model="fileDescription" rows="2" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-dark-700 dark:text-white"></textarea>
                            </div>
                        </div>
                        <div class="mt-6 flex justify-end gap-2">
                            <button type="button" wire:click="closeUploadModal" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-700 rounded-md">Batal</button>
                            <button type="submit" class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 disabled:opacity-50" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="uploadFile">Upload</span>
                                <span wire:loading wire:target="uploadFile">Uploading...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Folder Modal --}}
    @if($showFolderModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" @keydown.escape.window="$wire.closeFolderModal()">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75" wire:click="closeFolderModal"></div>
                <div class="relative bg-white dark:bg-dark-800 rounded-lg shadow-xl max-w-md w-full p-4">
                    <button type="button" wire:click="closeFolderModal" class="absolute top-4 right-4 text-gray-400 hover:text-gray-500">
                        <x-heroicon-o-x-circle class="w-6 h-6" />
                    </button>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">{{ $editingFolderId ? 'Edit Folder' : 'Buat Folder' }}</h3>
                    <form wire:submit="saveFolder">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Folder</label>
                            <input type="text" wire:model="folderName" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-dark-700 dark:text-white">
                            @error('folderName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="mt-6 flex justify-end gap-2">
                            <button type="button" wire:click="closeFolderModal" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-700 rounded-md">Batal</button>
                            <button type="submit" class="px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-md hover:bg-gray-700">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Move Modal --}}
    @if($showMoveModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" @keydown.escape.window="$wire.closeMoveModal()">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75" wire:click="closeMoveModal"></div>
                <div class="relative bg-white dark:bg-dark-800 rounded-lg shadow-xl max-w-md w-full p-4">
                    <button type="button" wire:click="closeMoveModal" class="absolute top-4 right-4 text-gray-400 hover:text-gray-500">
                        <x-heroicon-o-x-circle class="w-6 h-6" />
                    </button>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Pindahkan File</h3>
                    <form wire:submit="moveFile">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Pilih Folder Tujuan</label>
                            <select wire:model="targetFolderId" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-dark-700 dark:text-white">
                                <option value="">Root / Halaman Utama</option>
                                @foreach($this->allFolders as $folder)
                                    <option value="{{ $folder->id }}">{{ $folder->name }}</option>
                                @endforeach
                            </select>
                            @error('targetFolderId') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="mt-6 flex justify-end gap-2">
                            <button type="button" wire:click="closeMoveModal" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-700 rounded-md">Batal</button>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700">Pindahkan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
 
    {{-- Delete Confirmation Modal --}}
    @if($showDeleteModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" @keydown.escape.window="$wire.cancelDelete()">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75" wire:click="cancelDelete"></div>
                <div class="relative bg-white dark:bg-dark-800 rounded-lg shadow-xl max-w-sm w-full p-4">
                    <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full dark:bg-red-900">
                        <x-heroicon-o-exclamation-triangle class="w-6 h-6 text-red-600 dark:text-red-400" />
                    </div>
                    <div class="mt-3 text-center sm:mt-5">
                        <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white">
                            Hapus File?
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Apakah Anda yakin ingin menghapus file ini? Tindakan ini tidak dapat dibatalkan.
                            </p>
                        </div>
                    </div>
                    <div class="mt-5 sm:mt-6 flex gap-2">
                        <button type="button" wire:click="cancelDelete"
                            class="w-full inline-flex justify-center px-4 py-2 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gold-500 sm:text-sm dark:bg-dark-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-dark-600">
                            Batal
                        </button>
                        <button type="button" wire:click="deleteFile"
                            class="w-full inline-flex justify-center px-4 py-2 text-base font-medium text-white bg-red-600 border border-transparent rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:text-sm">
                            Hapus
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>


