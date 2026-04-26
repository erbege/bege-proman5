<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
        ['label' => 'Proyek', 'url' => route('projects.index')],
        ['label' => $project->name, 'url' => route('projects.show', $project)],
        ['label' => 'Files', 'url' => route('projects.files.index', $project)],
        ['label' => $file->name]
    ]" />
    </x-slot>

    <x-slot name="header">
        <div class="flex justify-between items-start">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ $file->name }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $file->original_name }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('projects.files.download', [$project, $file]) }}"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
                    <x-heroicon-o-arrow-down-tray class="w-4 h-4 mr-2" />
                    Download
                </a>
            </div>
        </div>
    </x-slot>

    @include('projects.navigation')

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Breadcrumb Navigation --}}
            <div class="mb-6">
                <nav class="flex" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <a href="{{ route('projects.files.index', $project) }}"
                                class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-gold-600 dark:text-gray-400 dark:hover:text-white">
                                <x-heroicon-o-home class="w-4 h-4 mr-2" />
                                Home
                            </a>
                        </li>
                        @if(isset($breadcrumbs))
                            @foreach($breadcrumbs as $crumb)
                                <li>
                                    <div class="flex items-center">
                                        <x-heroicon-o-chevron-right class="w-4 h-4 text-gray-400" />
                                        <a href="{{ $crumb['url'] }}"
                                            class="ml-1 text-sm font-medium text-gray-700 hover:text-gold-600 dark:text-gray-400 dark:hover:text-white md:ml-2">
                                            {{ $crumb['label'] }}
                                        </a>
                                    </div>
                                </li>
                            @endforeach
                        @endif
                        <li aria-current="page">
                            <div class="flex items-center">
                                <x-heroicon-o-chevron-right class="w-4 h-4 text-gray-400" />
                                <span
                                    class="ml-1 text-sm font-medium text-gray-500 dark:text-gray-400 md:ml-2">{{ $file->name }}</span>
                            </div>
                        </li>
                    </ol>
                </nav>
            </div>

            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                    {{ session('error') }}
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Left: File Info & Versions --}}
                <div class="lg:col-span-2 space-y-6">
                    {{-- File Info Card --}}
                    <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
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
                                    <dd class="mt-1 text-gray-900 dark:text-white">
                                        {{ $file->updated_at->format('d M Y H:i') }}
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
                                            <form action="{{ route('projects.files.status', [$project, $file]) }}" method="POST"
                                                class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="{{ $status }}">
                                                <button type="submit"
                                                    class="px-3 py-1 text-xs font-medium rounded border
                                                                    {{ $status === 'review' ? 'border-yellow-400 text-yellow-700 hover:bg-yellow-50' : '' }}
                                                                    {{ $status === 'approved' ? 'border-green-400 text-green-700 hover:bg-green-50' : '' }}
                                                                    {{ $status === 'final' ? 'border-blue-400 text-blue-700 hover:bg-blue-50' : '' }}
                                                                    {{ $status === 'draft' ? 'border-gray-400 text-gray-700 hover:bg-gray-50' : '' }}">
                                                    → {{ $label }}
                                                </button>
                                            </form>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Version History --}}
                    <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Riwayat Versi</h3>
                                <button type="button"
                                    onclick="document.getElementById('versionModal').classList.remove('hidden')"
                                    class="inline-flex items-center px-3 py-1.5 bg-green-600 text-white text-sm rounded-md hover:bg-green-700">
                                    <x-heroicon-o-arrow-up-tray class="w-4 h-4 mr-1" />
                                    Upload Versi Baru
                                </button>
                            </div>

                            <div class="space-y-4"
                                x-data="{ showRollbackModal: false, rollbackVersion: null, rollbackAction: '' }">
                                @foreach($file->versions as $version)
                                    <div
                                        class="flex items-start p-4 rounded-lg border {{ $version->version === $file->current_version ? 'border-green-300 bg-green-50 dark:border-green-700 dark:bg-green-900/20' : 'border-gray-200 dark:border-gray-700' }}">
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
                                                        class="p-1 text-blue-600 hover:bg-blue-100 rounded"
                                                        title="Download">
                                                        <x-heroicon-o-arrow-down-tray class="w-5 h-5" />
                                                    </a>
                                                    @if($version->version !== $file->current_version)
                                                        <button type="button"
                                                            @click="showRollbackModal = true; rollbackVersion = {{ $version->version }}; rollbackAction = '{{ route('projects.files.rollback', [$project, $file, $version]) }}'"
                                                            class="p-1 text-orange-600 hover:bg-orange-100 rounded"
                                                            title="Rollback">
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

                                <!-- Rollback Confirmation Modal -->
                                <template x-teleport="body">
                                    <div x-show="showRollbackModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto"
                                        aria-labelledby="modal-title" role="dialog" aria-modal="true">
                                        <div
                                            class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                                                @click="showRollbackModal = false"></div>
                                            <span
                                                class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                                            <div
                                                class="relative inline-block align-bottom bg-white dark:bg-dark-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                                                <div class="absolute top-0 right-0 pt-4 pr-4">
                                                    <button type="button" @click="showRollbackModal = false"
                                                        class="text-gray-400 hover:text-gray-500">
                                                        <x-heroicon-o-x-circle class="w-6 h-6" />
                                                    </button>
                                                </div>
                                                <div class="bg-white dark:bg-dark-800 px-4 pt-5 pb-4 sm:p-6">
                                                    <div class="sm:flex sm:items-start">
                                                        <div
                                                            class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-orange-100 sm:mx-0 sm:h-10 sm:w-10">
                                                            <x-heroicon-o-arrow-uturn-left
                                                                class="h-6 w-6 text-orange-600" />
                                                        </div>
                                                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                                            <h3
                                                                class="text-lg font-medium text-gray-900 dark:text-white">
                                                                Rollback Versi</h3>
                                                            <div class="mt-2">
                                                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                                                    Apakah Anda yakin ingin rollback ke versi <strong
                                                                        x-text="'v' + rollbackVersion"></strong>?
                                                                </p>
                                                                <p
                                                                    class="text-sm text-orange-600 dark:text-orange-400 mt-2">
                                                                    <x-heroicon-o-exclamation-triangle
                                                                        class="w-4 h-4 inline mr-1" />
                                                                    Versi ini akan menjadi versi aktif.
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div
                                                    class="bg-gray-50 dark:bg-dark-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                                    <form :action="rollbackAction" method="POST">
                                                        @csrf
                                                        <button type="submit"
                                                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-orange-600 text-base font-medium text-white hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 sm:ml-3 sm:w-auto sm:text-sm">
                                                            Rollback
                                                        </button>
                                                    </form>
                                                    <button type="button" @click="showRollbackModal = false"
                                                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:w-auto sm:text-sm dark:bg-dark-800 dark:text-gray-300 dark:border-dark-600 dark:hover:bg-gray-700">
                                                        Batal
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right: Comments --}}
                <div class="space-y-6">
                    <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                                Feedback & Komentar
                                @if($file->unresolved_comments_count > 0)
                                    <span
                                        class="ml-2 px-2 py-0.5 text-xs bg-red-100 text-red-700 rounded-full">{{ $file->unresolved_comments_count }}
                                        belum resolved</span>
                                @endif
                            </h3>

                            {{-- Add Comment Form --}}
                            <form action="{{ route('projects.files.comments.store', [$project, $file]) }}" method="POST"
                                class="mb-6">
                                @csrf
                                <textarea name="comment" rows="3" required placeholder="Tulis komentar atau feedback..."
                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-dark-700 dark:text-white text-sm"></textarea>
                                <div class="mt-2 flex justify-end">
                                    <button type="submit"
                                        class="px-4 py-2 bg-gold-500 text-white text-sm rounded-md hover:bg-gold-600">
                                        Kirim
                                    </button>
                                </div>
                            </form>

                            {{-- Comments List --}}
                            <div class="space-y-4">
                                @forelse($file->comments as $comment)
                                    <div
                                        class="p-3 rounded-lg {{ $comment->resolved ? 'bg-gray-50 dark:bg-dark-700 opacity-60' : 'bg-gray-100 dark:bg-dark-700' }}">
                                        <div class="flex items-start justify-between">
                                            <div class="flex items-center">
                                                <div
                                                    class="w-8 h-8 rounded-full bg-gold-500 text-white flex items-center justify-center text-xs font-medium">
                                                    {{ substr($comment->user->name, 0, 2) }}
                                                </div>
                                                <div class="ml-2">
                                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                        {{ $comment->user->name }}
                                                    </p>
                                                    <p class="text-xs text-gray-500">
                                                        {{ $comment->created_at->diffForHumans() }}
                                                    </p>
                                                </div>
                                            </div>
                                            <form
                                                action="{{ route('projects.files.comments.toggle', [$project, $file, $comment]) }}"
                                                method="POST">
                                                @csrf
                                                <button type="submit"
                                                    class="p-1 rounded {{ $comment->resolved ? 'text-green-600' : 'text-gray-400 hover:text-green-600' }}"
                                                    title="{{ $comment->resolved ? 'Unresolve' : 'Resolve' }}">
                                                    <x-heroicon-o-check-circle class="w-5 h-5" />
                                                </button>
                                            </form>
                                        </div>
                                        <p
                                            class="mt-2 text-sm text-gray-700 dark:text-gray-300 {{ $comment->resolved ? 'line-through' : '' }}">
                                            {{ $comment->comment }}
                                        </p>

                                        {{-- Replies --}}
                                        @if($comment->replies->isNotEmpty())
                                            <div class="mt-3 pl-4 border-l-2 border-gray-200 dark:border-gray-600 space-y-2">
                                                @foreach($comment->replies as $reply)
                                                    <div class="text-sm">
                                                        <span
                                                            class="font-medium text-gray-900 dark:text-white">{{ $reply->user->name }}</span>
                                                        <span
                                                            class="text-gray-500 text-xs">{{ $reply->created_at->diffForHumans() }}</span>
                                                        <p class="text-gray-700 dark:text-gray-300">{{ $reply->comment }}</p>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif

                                        {{-- Reply Form --}}
                                        <form action="{{ route('projects.files.comments.store', [$project, $file]) }}"
                                            method="POST" class="mt-2">
                                            @csrf
                                            <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                                            <div class="flex gap-2">
                                                <input type="text" name="comment" placeholder="Balas..." required
                                                    class="flex-1 text-xs rounded-md border-gray-300 dark:border-gray-600 dark:bg-dark-600 dark:text-white">
                                                <button type="submit"
                                                    class="px-2 py-1 bg-gray-200 dark:bg-dark-600 text-gray-700 dark:text-gray-300 text-xs rounded hover:bg-gray-300 dark:hover:bg-dark-500">
                                                    Balas
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                @empty
                                    <p class="text-sm text-gray-500 text-center py-4">Belum ada komentar</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    {{-- Danger Zone --}}
                    <div x-data="{ showDeleteFileModal: false }"
                        class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg border border-red-200 dark:border-red-900">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-red-600 dark:text-red-400 mb-4">Danger Zone</h3>
                            <button type="button" @click="showDeleteFileModal = true"
                                class="w-full px-4 py-2 bg-red-600 text-white text-sm rounded-md hover:bg-red-700">
                                Hapus File
                            </button>

                            <!-- Delete File Confirmation Modal -->
                            <template x-teleport="body">
                                <div x-show="showDeleteFileModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto"
                                    aria-labelledby="modal-title" role="dialog" aria-modal="true">
                                    <div
                                        class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                                            @click="showDeleteFileModal = false"></div>
                                        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                                        <div
                                            class="relative inline-block align-bottom bg-white dark:bg-dark-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                                            <div class="absolute top-0 right-0 pt-4 pr-4">
                                                <button type="button" @click="showDeleteFileModal = false"
                                                    class="text-gray-400 hover:text-gray-500">
                                                    <x-heroicon-o-x-circle class="w-6 h-6" />
                                                </button>
                                            </div>
                                            <div class="bg-white dark:bg-dark-800 px-4 pt-5 pb-4 sm:p-6">
                                                <div class="sm:flex sm:items-start">
                                                    <div
                                                        class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                                        <x-heroicon-o-exclamation-triangle
                                                            class="h-6 w-6 text-red-600" />
                                                    </div>
                                                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                                                            Hapus File</h3>
                                                        <div class="mt-2">
                                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                                Apakah Anda yakin ingin menghapus file
                                                                <strong>{{ $file->name }}</strong>?
                                                            </p>
                                                            <p class="text-sm text-red-600 dark:text-red-400 mt-2">
                                                                <x-heroicon-o-exclamation-triangle
                                                                    class="w-4 h-4 inline mr-1" />
                                                                Semua versi akan ikut terhapus. Tindakan ini tidak dapat
                                                                dibatalkan.
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div
                                                class="bg-gray-50 dark:bg-dark-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                                <form action="{{ route('projects.files.destroy', [$project, $file]) }}"
                                                    method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                                                        Hapus
                                                    </button>
                                                </form>
                                                <button type="button" @click="showDeleteFileModal = false"
                                                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:w-auto sm:text-sm dark:bg-dark-800 dark:text-gray-300 dark:border-dark-600 dark:hover:bg-gray-700">
                                                    Batal
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Upload Version Modal --}}
    <div id="versionModal" class="hidden fixed inset-0 z-50 overflow-y-auto scrollbar-overlay">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75"
                onclick="document.getElementById('versionModal').classList.add('hidden')"></div>
            <div class="relative bg-white dark:bg-dark-800 rounded-lg shadow-xl max-w-lg w-full p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Upload Versi Baru</h3>
                <form action="{{ route('projects.files.version', [$project, $file]) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">File</label>
                            <input type="file" name="file" required
                                class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-gold-50 file:text-gold-700 hover:file:bg-gold-100">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Catatan
                                Perubahan</label>
                            <textarea name="notes" rows="2"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-dark-700 dark:text-white"
                                placeholder="Jelaskan perubahan yang dilakukan..."></textarea>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end gap-2">
                        <button type="button" onclick="document.getElementById('versionModal').classList.add('hidden')"
                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-700 rounded-md">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700">
                            Upload
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>