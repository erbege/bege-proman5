<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
        ['label' => 'Proyek', 'url' => route('projects.index')],
        ['label' => $project->name, 'url' => route('projects.show', $project)],
        ['label' => 'Material Request', 'url' => route('projects.mr.index', $project)],
        ['label' => $mr->code]
    ]" />
    </x-slot>

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Detail MR: {{ $mr->code }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('projects.mr.index', $project) }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300">
                    Kembali
                </a>
                @if($mr->status === 'pending')
                    <a href="{{ route('projects.mr.edit', [$project, $mr]) }}"
                        class="inline-flex items-center px-4 py-2 bg-yellow-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-600">
                        Edit
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Info Card -->
            <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Kode Proyek</p>
                            <p class="font-medium text-lg">{{ $project->code }} - {{ $project->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Status MR</p>
                            @php
                                $colors = [
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'approved' => 'bg-green-100 text-green-800',
                                    'rejected' => 'bg-red-100 text-red-800',
                                    'processed' => 'bg-blue-100 text-blue-800',
                                ];
                            @endphp
                            <span
                                class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full {{ $colors[$mr->status] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ ucfirst($mr->status) }}
                            </span>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Diminta Oleh</p>
                            <p class="font-medium">{{ $mr->requestedBy->name ?? 'Unknown' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Tanggal Permintaan</p>
                            <p class="font-medium">{{ $mr->request_date->format('d F Y') }}</p>
                        </div>
                        <div class="md:col-span-2">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Catatan</p>
                            <p class="font-medium">{{ $mr->notes ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Daftar Material</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-dark-700">
                                <tr>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                        Material</th>
                                    <th
                                        class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                        Qty</th>
                                    <th
                                        class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                        Satuan</th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                        Catatan Item</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($mr->items as $item)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white font-medium">
                                            {{ $item->material->name }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white text-right">
                                            {{ number_format($item->quantity, 2) }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white text-center">
                                            {{ $item->unit }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                            {{ $item->notes ?? '-' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Approvals -->
            @if($mr->status === 'pending')
                <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-md font-medium text-gray-900 dark:text-white mb-4">Aksi Persetujuan</h3>
                    <div class="flex gap-4">
                        <button type="button" onclick="document.getElementById('approveMRModal').classList.remove('hidden')"
                            class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                            <x-heroicon-o-check class="w-4 h-4 mr-2" />
                            Approve
                        </button>
                        <button type="button" onclick="document.getElementById('rejectMRModal').classList.remove('hidden')"
                            class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700">
                            <x-heroicon-o-x-mark class="w-4 h-4 mr-2" />
                            Reject
                        </button>
                    </div>
                </div>
            @elseif($mr->status === 'approved')
                <div
                    class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-green-500">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Permintaan Disetujui</h3>
                            <p class="text-sm text-gray-500">Permintaan ini telah disetujui. Silakan lanjut membuat Purchase
                                Request (PR).</p>
                        </div>
                        <a href="{{ route('projects.pr.create', ['project' => $project, 'from_mr' => $mr->id]) }}"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 shadow-lg transition transform hover:scale-105">
                            <x-heroicon-o-shopping-cart class="w-4 h-4 mr-2" />
                            Buat Purchase Request
                        </a>
                    </div>
                </div>
            @elseif($mr->status === 'processed')
                <div
                    class="bg-blue-50 dark:bg-blue-900/20 p-6 rounded-lg border border-blue-200 dark:border-blue-800 overflow-hidden shadow-sm">
                    <div class="flex items-center">
                        <x-heroicon-s-check-circle class="w-6 h-6 text-blue-500 mr-2" />
                        <span class="text-blue-700 dark:text-blue-300 font-medium">Material Request ini telah diproses
                            menjadi Purchase Request.</span>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Approve MR Modal -->
    <x-confirm-modal id="approveMRModal" title="Approve Material Request"
        message="Apakah Anda yakin ingin menyetujui permintaan material ini?" confirmColor="green" icon="check">
        <form action="{{ route('projects.mr.status', [$project, $mr]) }}" method="POST">
            @csrf
            <input type="hidden" name="status" value="approved">
            <button type="submit"
                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 sm:ml-3 sm:w-auto sm:text-sm">Ya,
                Approve</button>
        </form>
    </x-confirm-modal>

    <!-- Reject MR Modal -->
    <x-confirm-modal id="rejectMRModal" title="Reject Material Request"
        message="Apakah Anda yakin ingin menolak permintaan material ini?" confirmColor="red" icon="x-mark">
        <form action="{{ route('projects.mr.status', [$project, $mr]) }}" method="POST">
            @csrf
            <input type="hidden" name="status" value="rejected">
            <button type="submit"
                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 sm:ml-3 sm:w-auto sm:text-sm">Ya,
                Reject</button>
        </form>
    </x-confirm-modal>
</x-app-layout>