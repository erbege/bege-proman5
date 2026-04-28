<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
        ['label' => 'Proyek', 'url' => route('projects.index')],
        ['label' => $project->name, 'url' => route('projects.show', $project)],
        ['label' => 'Purchase Order', 'url' => route('projects.po.index', $project)],
        ['label' => $po->po_number]
    ]" />
    </x-slot>

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Purchase Order: {{ $po->po_number }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('projects.po.index', $project) }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-dark-700 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-300 dark:hover:bg-dark-600">
                    ← Kembali
                </a>
                @can('procurement.manage')
                @if($po->status === 'draft' || $po->status === 'sent')
                    <button type="button" onclick="document.getElementById('deletePOModal').classList.remove('hidden')"
                        class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700">
                        Hapus
                    </button>
                @endif
                @endcan
                @can('financials.view')
                <a href="{{ route('projects.po.print', [$project, $po]) }}" target="_blank"
                    class="inline-flex items-center px-4 py-2 bg-gold-500 border border-transparent rounded-md font-semibold text-xs text-gray-900 uppercase tracking-widest hover:bg-gold-600">
                    <x-heroicon-o-printer class="w-4 h-4 mr-1" />
                    Print / PDF
                </a>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">            <!-- Approval Progress -->
            @if($po->status !== 'draft' && $po->status !== 'rejected')
                <div class="bg-white dark:bg-dark-800 shadow-sm sm:rounded-lg mb-6 p-4">
                    <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-4">Progres Persetujuan</h3>
                    <div class="relative">
                        <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-gray-200 dark:bg-dark-700">
                            @php 
                                $percent = ($po->current_approval_level / ($po->max_approval_level + 1)) * 100;
                                if($po->is_fully_approved) $percent = 100;
                            @endphp
                            <div style="width:{{ $percent }}%" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-gold-500 transition-all duration-500"></div>
                        </div>
                        <div class="flex justify-between text-xs font-medium">
                            <div class="text-gray-500">Draft</div>
                            @for($i = 1; $i <= $po->max_approval_level; $i++)
                                <div class="{{ $po->current_approval_level >= $i ? 'text-gold-600 font-bold' : 'text-gray-400' }}">
                                    Level {{ $i }}
                                </div>
                            @endfor
                            <div class="{{ $po->is_fully_approved ? 'text-green-600 font-bold' : 'text-gray-400' }}">Selesai</div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-4 text-gray-900 dark:text-gray-100">
                    <!-- PO Header Info -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Supplier</p>
                            <p class="font-bold text-lg text-gold-600">{{ $po->supplier->name }}</p>
                            <p class="text-sm">{{ $po->supplier->address }}</p>
                            <p class="text-sm">{{ $po->supplier->phone }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Tanggal Order</p>
                            <p class="font-medium">{{ $po->order_date->format('d F Y') }}</p>

                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Diharapkan Tiba</p>
                            <p class="font-medium">{{ $po->expected_delivery->format('d F Y') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Status</p>
                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full 
                                @if($po->is_fully_approved) bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300
                                @elseif($po->status === 'rejected') bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300
                                @elseif($po->status === 'pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300
                                @else bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300
                                @endif">
                                {{ strtoupper($po->status) }}
                            </span>

                            @if($po->purchaseRequests->isNotEmpty())
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Ref PR</p>
                                <div class="flex flex-wrap gap-1 mt-1">
                                    @foreach($po->purchaseRequests as $refPr)
                                        <span class="px-2 py-0.5 bg-gray-100 dark:bg-dark-700 rounded text-xs font-medium">{{ $refPr->pr_number }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Items Table -->
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Daftar Item</h3>
                    <div class="overflow-x-auto border rounded-lg dark:border-dark-700 mb-6">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-dark-700">
                                <tr>
                                    <th class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Material</th>
                                    <th class="px-3 py-1.5 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Qty</th>
                                    @can('financials.view')
                                    <th class="px-3 py-1.5 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Harga Satuan</th>
                                    <th class="px-3 py-1.5 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Total</th>
                                    @endcan
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($po->items as $item)
                                    <tr>
                                        <td class="px-3 py-1.5">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $item->material->name }}</div>
                                            @if($item->notes)
                                                <div class="text-xs text-gray-500">{{ $item->notes }}</div>
                                            @endif
                                        </td>
                                        <td class="px-3 py-1.5 text-right text-sm text-gray-900 dark:text-white">{{ number_format($item->quantity, 2) }} {{ $item->material->unit }}</td>
                                        @can('financials.view')
                                        <td class="px-3 py-1.5 text-right text-sm text-gray-900 dark:text-white">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                        <td class="px-3 py-1.5 text-right text-sm font-bold text-gray-900 dark:text-white">Rp {{ number_format($item->total_price, 0, ',', '.') }}</td>
                                        @endcan
                                    </tr>
                                @endforeach
                            </tbody>
                            @can('financials.view')
                            <tfoot class="bg-gray-50 dark:bg-dark-700">
                                <tr>
                                    <td colspan="3" class="px-4 py-2 text-right text-sm font-medium text-gray-500">Subtotal</td>
                                    <td class="px-4 py-2 text-right text-sm font-bold text-gray-900 dark:text-white">Rp {{ number_format($po->subtotal, 0, ',', '.') }}</td>
                                </tr>
                                @if($po->tax_amount > 0)
                                    <tr>
                                        <td colspan="3" class="px-4 py-2 text-right text-sm font-medium text-gray-500">Pajak</td>
                                        <td class="px-4 py-2 text-right text-sm font-bold text-gray-900 dark:text-white">+ Rp {{ number_format($po->tax_amount, 0, ',', '.') }}</td>
                                    </tr>
                                @endif
                                @if($po->discount_amount > 0)
                                    <tr>
                                        <td colspan="3" class="px-4 py-2 text-right text-sm font-medium text-gray-500">Diskon</td>
                                        <td class="px-4 py-2 text-right text-sm font-bold text-green-600">- Rp {{ number_format($po->discount_amount, 0, ',', '.') }}</td>
                                    </tr>
                                @endif
                                <tr>
                                    <td colspan="3" class="px-3 py-1.5 text-right text-base font-bold text-gray-900 dark:text-white">Total Akhir</td>
                                    <td class="px-3 py-1.5 text-right text-base font-bold text-blue-600 dark:text-blue-400">{{ $po->formatted_total_amount }}</td>
                                </tr>
                            </tfoot>
                            @endcan
                        </table>
                    </div>

                    @if($po->notes)
                        <div class="mt-4 p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded border border-yellow-200">
                            <h4 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Catatan Order</h4>
                            <p class="text-sm text-yellow-700 dark:text-yellow-300 mt-1">{{ $po->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Approval Actions -->
            @can('po.approve')
            @if($po->status === 'pending' && !$po->is_fully_approved)
                @php
                    $canApprove = false;
                    $matrix = \App\Models\ApprovalMatrix::where('document_type', 'PO')
                        ->where('level', $po->current_approval_level)
                        ->where('is_active', true)
                        ->first();
                    
                    if ($matrix && app(\App\Services\ApprovalService::class)->canUserApprove(auth()->user(), $matrix)) {
                        $canApprove = true;
                    }
                @endphp

                @if($canApprove)
                    <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg p-4 mb-6 border-l-4 border-gold-500">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="text-md font-bold text-gray-900 dark:text-white">Menunggu Persetujuan Anda</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Anda bertanggung jawab untuk persetujuan Level {{ $po->current_approval_level }}</p>
                            </div>
                            <div class="flex gap-4">
                                <button type="button" onclick="document.getElementById('approvePOModal').classList.remove('hidden')"
                                    class="inline-flex items-center px-6 py-2 bg-green-600 border border-transparent rounded-md font-bold text-xs text-white uppercase tracking-widest hover:bg-green-700 shadow-lg transition">
                                    <x-heroicon-o-check class="w-4 h-4 mr-2" />
                                    Setujui PO
                                </button>
                                <button type="button" onclick="document.getElementById('rejectPOModal').classList.remove('hidden')"
                                    class="inline-flex items-center px-6 py-2 bg-red-600 border border-transparent rounded-md font-bold text-xs text-white uppercase tracking-widest hover:bg-red-700 shadow-lg transition">
                                    <x-heroicon-o-x-mark class="w-4 h-4 mr-2" />
                                    Tolak PO
                                </button>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg mb-6 flex items-center shadow-sm">
                        <x-heroicon-o-information-circle class="w-5 h-5 text-blue-500 mr-3" />
                        <p class="text-sm text-blue-700 dark:text-blue-300">
                            Menunggu persetujuan Level {{ $po->current_approval_level }} (Role: {{ str_replace('_', ' ', $matrix->role_name ?? 'N/A') }})
                        </p>
                    </div>
                @endif
            @endif
            @endcan

            <!-- Audit Trail / Approval History -->
            <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-6 flex items-center">
                        <x-heroicon-o-clock class="w-5 h-5 mr-2 text-gray-400" />
                        Riwayat Persetujuan & Aktivitas
                    </h3>
                    
                    <div class="flow-root">
                        <ul role="list" class="-mb-8">
                            @foreach($po->approvalLogs as $log)
                                <li>
                                    <div class="relative pb-8">
                                        @if(!$loop->last)
                                            <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200 dark:bg-dark-700" aria-hidden="true"></span>
                                        @endif
                                        <div class="relative flex space-x-3">
                                            <div>
                                                <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white dark:ring-dark-800 
                                                    {{ $log->status === 'approved' ? 'bg-green-500' : 'bg-red-500' }}">
                                                    @if($log->status === 'approved')
                                                        <x-heroicon-s-check class="w-5 h-5 text-white" />
                                                    @else
                                                        <x-heroicon-s-x-mark class="w-5 h-5 text-white" />
                                                    @endif
                                                </span>
                                            </div>
                                            <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                                <div>
                                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                                        <span class="font-bold text-gray-900 dark:text-white">{{ $log->user->name }}</span> 
                                                        {{ $log->status === 'approved' ? 'menyetujui' : 'menolak' }} 
                                                        <span class="font-medium text-gold-600">Level {{ $log->level }}</span>
                                                    </p>
                                                    @if($log->comment)
                                                        <p class="mt-1 text-sm italic text-gray-600 dark:text-gray-400">"{{ $log->comment }}"</p>
                                                    @endif
                                                </div>
                                                <div class="whitespace-nowrap text-right text-xs text-gray-500">
                                                    <time datetime="{{ $log->created_at }}">{{ $log->created_at->format('d M Y, H:i') }}</time>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                            
                            <li>
                                <div class="relative pb-8">
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white dark:ring-dark-800">
                                                <x-heroicon-s-paper-airplane class="w-5 h-5 text-white" />
                                            </span>
                                        </div>
                                        <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                            <div>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                                    <span class="font-bold text-gray-900 dark:text-white">{{ $po->createdBy->name }}</span> membuat Purchase Order
                                                </p>
                                            </div>
                                            <div class="whitespace-nowrap text-right text-xs text-gray-500">
                                                <time datetime="{{ $po->created_at }}">{{ $po->created_at->format('d M Y, H:i') }}</time>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete PO Modal -->
    <x-confirm-modal id="deletePOModal" title="Hapus Purchase Order" message="Apakah Anda yakin ingin menghapus PO ini?"
        confirmColor="red" icon="trash">
        <form action="{{ route('projects.po.destroy', [$project, $po]) }}" method="POST">
            @csrf @method('DELETE')
            <button type="submit"
                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 sm:ml-3 sm:w-auto sm:text-sm">Ya,
                Hapus</button>
        </form>
    </x-confirm-modal>

    <!-- Approve PO Modal -->
    <x-confirm-modal id="approvePOModal" title="Setujui Purchase Order"
        message="Apakah Anda yakin ingin menyetujui Purchase Order ini untuk Level {{ $po->current_approval_level }}?" 
        confirmColor="green" icon="check">
        <x-slot name="body">
            <form id="approvePOForm" action="{{ route('projects.po.status', [$project, $po]) }}" method="POST">
                @csrf
                <input type="hidden" name="status" value="approved">
                <div class="text-left">
                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Catatan Persetujuan (Opsional)</label>
                    <textarea name="comment" rows="3" 
                        class="w-full rounded-xl border-gray-200 dark:bg-dark-900 dark:border-dark-700 dark:text-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 text-sm transition-all" 
                        placeholder="Tambahkan catatan jika diperlukan..."></textarea>
                </div>
            </form>
        </x-slot>
        <x-slot name="footer">
            <button type="submit" form="approvePOForm"
                class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-5 py-2.5 bg-green-600 text-base font-bold text-white hover:bg-green-700 transition-all transform hover:scale-[1.02] active:scale-[0.98] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:w-auto sm:text-sm">
                Ya, Setujui
            </button>
            <button type="button" onclick="document.getElementById('approvePOModal').classList.add('hidden')"
                class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-200 dark:border-dark-600 shadow-sm px-5 py-2.5 bg-white dark:bg-dark-800 text-base font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all sm:mt-0 sm:w-auto sm:text-sm">
                Batal
            </button>
        </x-slot>
    </x-confirm-modal>

    <!-- Reject PO Modal -->
    <x-confirm-modal id="rejectPOModal" title="Tolak Purchase Order"
        message="Harap masukkan alasan penolakan untuk Purchase Order ini." 
        confirmColor="red" icon="x-mark">
        <x-slot name="body">
            <form id="rejectPOForm" action="{{ route('projects.po.status', [$project, $po]) }}" method="POST">
                @csrf
                <input type="hidden" name="status" value="rejected">
                <div class="text-left">
                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Alasan Penolakan</label>
                    <textarea name="comment" required rows="3" 
                        class="w-full rounded-xl border-gray-200 dark:bg-dark-900 dark:border-dark-700 dark:text-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 text-sm transition-all" 
                        placeholder="Wajib diisi..."></textarea>
                </div>
            </form>
        </x-slot>
        <x-slot name="footer">
            <button type="submit" form="rejectPOForm"
                class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-5 py-2.5 bg-red-600 text-base font-bold text-white hover:bg-red-700 transition-all transform hover:scale-[1.02] active:scale-[0.98] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:w-auto sm:text-sm">
                Ya, Tolak
            </button>
            <button type="button" onclick="document.getElementById('rejectPOModal').classList.add('hidden')"
                class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-200 dark:border-dark-600 shadow-sm px-5 py-2.5 bg-white dark:bg-dark-800 text-base font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all sm:mt-0 sm:w-auto sm:text-sm">
                Batal
            </button>
        </x-slot>
    </x-confirm-modal>
</x-app-layout>t>


