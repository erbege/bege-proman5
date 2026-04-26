<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
        ['label' => 'Proyek', 'url' => route('projects.index')],
        ['label' => $project->name, 'url' => route('projects.show', $project)],
        ['label' => 'Purchase Request', 'url' => route('projects.pr.index', $project)],
        ['label' => $pr->pr_number]
    ]" />
    </x-slot>

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Detail PR: {{ $pr->pr_number }}
            </h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('projects.pr.index', $project) }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-dark-700 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-300 dark:hover:bg-dark-600">
                    ← Kembali
                </a>
                @if($pr->status === 'draft' || $pr->status === 'pending')
                    <button type="button" onclick="document.getElementById('deletePRModal').classList.remove('hidden')"
                        class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700">
                        Hapus
                    </button>
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
                            <p class="text-sm text-gray-500 dark:text-gray-400">Status PR</p>
                            <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full
                                @if($pr->status === 'approved') bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300
                                @elseif($pr->status === 'rejected') bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300
                                @elseif($pr->status === 'pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300
                                @else bg-gray-100 text-gray-800 dark:bg-dark-700 dark:text-gray-300
                                @endif">
                                {{ strtoupper($pr->status) }}
                            </span>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Diminta Oleh</p>
                            <p class="font-medium">{{ $pr->requestedBy->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Tanggal Dibutuhkan</p>
                            <p
                                class="font-medium {{ $pr->priority === 'urgent' ? 'text-red-600 dark:text-red-400' : '' }}">
                                {{ $pr->required_date->format('d F Y') }}
                                <span
                                    class="text-xs text-gray-500 dark:text-gray-400">({{ $pr->priority_label }})</span>
                            </p>
                        </div>
                        <div class="md:col-span-2">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Catatan</p>
                            <p class="font-medium">{{ $pr->notes ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Item Pembelian</h3>
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
                                        class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                        Est. Harga</th>
                                    <th
                                        class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                        Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($pr->items as $item)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white font-medium">
                                            {{ $item->material->name }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white text-right">
                                            {{ number_format($item->quantity, 2) }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white text-center">
                                            {{ $item->material->unit }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white text-right">
                                            Rp {{ number_format($item->estimated_price, 0, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white text-right font-bold">
                                            Rp {{ number_format($item->total_price, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                                <tr class="bg-gray-50 dark:bg-dark-700">
                                    <td colspan="4"
                                        class="px-4 py-3 text-sm font-bold text-gray-900 dark:text-white text-right">
                                        Total Estimasi</td>
                                    <td class="px-4 py-3 text-sm font-bold text-gray-900 dark:text-white text-right">
                                        Rp {{ number_format($pr->total_estimated_price, 0, ',', '.') }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Approvals -->
            @if($pr->status === 'pending')
                <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-md font-medium text-gray-900 dark:text-white mb-4">Persetujuan PR</h3>
                    <div class="flex gap-4">
                        <button type="button" onclick="document.getElementById('approvePRModal').classList.remove('hidden')"
                            class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                            <x-heroicon-o-check class="w-4 h-4 mr-2" />
                            Approve
                        </button>
                        <button type="button" onclick="document.getElementById('rejectPRModal').classList.remove('hidden')"
                            class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700">
                            <x-heroicon-o-x-mark class="w-4 h-4 mr-2" />
                            Reject
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Delete PR Modal -->
    <x-confirm-modal id="deletePRModal" title="Hapus Purchase Request"
        message="Apakah Anda yakin ingin menghapus PR ini?" confirmColor="red" icon="trash">
        <form action="{{ route('projects.pr.destroy', [$project, $pr]) }}" method="POST">
            @csrf @method('DELETE')
            <button type="submit"
                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 sm:ml-3 sm:w-auto sm:text-sm">Ya,
                Hapus</button>
        </form>
    </x-confirm-modal>

    <!-- Approve PR Modal -->
    <x-confirm-modal id="approvePRModal" title="Approve Purchase Request"
        message="Apakah Anda yakin ingin menyetujui PR ini?" confirmColor="green" icon="check">
        <form action="{{ route('projects.pr.status', [$project, $pr]) }}" method="POST">
            @csrf
            <input type="hidden" name="status" value="approved">
            <button type="submit"
                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 sm:ml-3 sm:w-auto sm:text-sm">Ya,
                Approve</button>
        </form>
    </x-confirm-modal>

    <!-- Reject PR Modal -->
    <x-confirm-modal id="rejectPRModal" title="Reject Purchase Request"
        message="Apakah Anda yakin ingin menolak PR ini?" confirmColor="red" icon="x-mark">
        <form action="{{ route('projects.pr.status', [$project, $pr]) }}" method="POST">
            @csrf
            <input type="hidden" name="status" value="rejected">
            <button type="submit"
                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 sm:ml-3 sm:w-auto sm:text-sm">Ya,
                Reject</button>
        </form>
    </x-confirm-modal>
</x-app-layout>