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

            <!-- Approval Progress -->
            @if($pr->status !== 'draft' && $pr->status !== 'rejected')
                <div class="bg-white dark:bg-dark-800 shadow-sm sm:rounded-lg mb-6 p-6">
                    <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-4">Progres Persetujuan</h3>
                    <div class="relative">
                        <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-gray-200 dark:bg-dark-700">
                            @php 
                                $percent = ($pr->current_approval_level / ($pr->max_approval_level + 1)) * 100;
                                if($pr->is_fully_approved) $percent = 100;
                            @endphp
                            <div style="width:{{ $percent }}%" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-gold-500 transition-all duration-500"></div>
                        </div>
                        <div class="flex justify-between text-xs font-medium">
                            <div class="text-gray-500">Draft</div>
                            @for($i = 1; $i <= $pr->max_approval_level; $i++)
                                <div class="{{ $pr->current_approval_level >= $i ? 'text-gold-600 font-bold' : 'text-gray-400' }}">
                                    Level {{ $i }}
                                </div>
                            @endfor
                            <div class="{{ $pr->is_fully_approved ? 'text-green-600 font-bold' : 'text-gray-400' }}">Selesai</div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Items Table -->
            <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <!-- ... (Items Table Content Remains Same) ... -->
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Item Pembelian</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-dark-700">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Material</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Qty</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Satuan</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Est. Harga</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($pr->items as $item)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white font-medium">{{ $item->material->name }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white text-right">{{ number_format($item->quantity, 2) }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white text-center">{{ $item->material->unit }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white text-right">Rp {{ number_format($item->estimated_price, 0, ',', '.') }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white text-right font-bold">Rp {{ number_format($item->total_price, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                                <tr class="bg-gray-50 dark:bg-dark-700">
                                    <td colspan="4" class="px-4 py-3 text-sm font-bold text-gray-900 dark:text-white text-right">Total Estimasi</td>
                                    <td class="px-4 py-3 text-sm font-bold text-gray-900 dark:text-white text-right">Rp {{ number_format($pr->total_estimated_price, 0, ',', '.') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Approval Actions -->
            @if($pr->status === 'pending' && !$pr->is_fully_approved)
                @php
                    $canApprove = false;
                    $matrix = \App\Models\ApprovalMatrix::where('document_type', 'PR')
                        ->where('level', $pr->current_approval_level)
                        ->where('is_active', true)
                        ->first();
                    if ($matrix && auth()->user()->hasRole($matrix->role_name)) {
                        $canApprove = true;
                    }
                @endphp

                @if($canApprove)
                    <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6 border-l-4 border-gold-500">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="text-md font-bold text-gray-900 dark:text-white">Menunggu Persetujuan Anda</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Anda bertanggung jawab untuk persetujuan Level {{ $pr->current_approval_level }}</p>
                            </div>
                            <div class="flex gap-4">
                                <button type="button" onclick="document.getElementById('approvePRModal').classList.remove('hidden')"
                                    class="inline-flex items-center px-6 py-2 bg-green-600 border border-transparent rounded-md font-bold text-xs text-white uppercase tracking-widest hover:bg-green-700 shadow-lg transition">
                                    <x-heroicon-o-check class="w-4 h-4 mr-2" />
                                    Setujui
                                </button>
                                <button type="button" onclick="document.getElementById('rejectPRModal').classList.remove('hidden')"
                                    class="inline-flex items-center px-6 py-2 bg-red-600 border border-transparent rounded-md font-bold text-xs text-white uppercase tracking-widest hover:bg-red-700 shadow-lg transition">
                                    <x-heroicon-o-x-mark class="w-4 h-4 mr-2" />
                                    Tolak
                                </button>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg mb-6 flex items-center">
                        <x-heroicon-o-information-circle class="w-5 h-5 text-blue-500 mr-3" />
                        <p class="text-sm text-blue-700 dark:text-blue-300">
                            Menunggu persetujuan Level {{ $pr->current_approval_level }} (Role: {{ str_replace('_', ' ', $matrix->role_name ?? 'N/A') }})
                        </p>
                    </div>
                @endif
            @endif

            <!-- Audit Trail / Approval History -->
            <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-6 flex items-center">
                        <x-heroicon-o-clock class="w-5 h-5 mr-2 text-gray-400" />
                        Riwayat Persetujuan & Aktivitas
                    </h3>
                    
                    <div class="flow-root">
                        <ul role="list" class="-mb-8">
                            @foreach($pr->approvalLogs as $log)
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
                            
                            <!-- Initial Request -->
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
                                                    <span class="font-bold text-gray-900 dark:text-white">{{ $pr->requestedBy->name }}</span> membuat dan mengajukan Purchase Request
                                                </p>
                                            </div>
                                            <div class="whitespace-nowrap text-right text-xs text-gray-500">
                                                <time datetime="{{ $pr->created_at }}">{{ $pr->created_at->format('d M Y, H:i') }}</time>
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
        message="Apakah Anda yakin ingin menyetujui PR ini untuk Level {{ $pr->current_approval_level }}?" confirmColor="green" icon="check">
        <form action="{{ route('projects.pr.status', [$project, $pr]) }}" method="POST" class="p-6">
            @csrf
            <input type="hidden" name="status" value="approved">
            
            <div class="mb-4 text-left">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Catatan Persetujuan (Opsional)</label>
                <textarea name="comment" rows="3" class="w-full rounded-md border-gray-300 dark:bg-dark-900 dark:border-dark-700 dark:text-gray-300 shadow-sm focus:border-gold-500 focus:ring-gold-500 text-sm" placeholder="Tambahkan catatan jika diperlukan..."></textarea>
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('approvePRModal').classList.add('hidden')"
                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md text-sm font-medium hover:bg-gray-200">Batal</button>
                <button type="submit"
                    class="px-4 py-2 bg-green-600 text-white rounded-md text-sm font-medium hover:bg-green-700">Ya, Approve</button>
            </div>
        </form>
    </x-confirm-modal>

    <!-- Reject PR Modal -->
    <x-confirm-modal id="rejectPRModal" title="Reject Purchase Request"
        message="Harap masukkan alasan penolakan PR ini." confirmColor="red" icon="x-mark">
        <form action="{{ route('projects.pr.status', [$project, $pr]) }}" method="POST" class="p-6">
            @csrf
            <input type="hidden" name="status" value="rejected">
            
            <div class="mb-4 text-left">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Alasan Penolakan</label>
                <textarea name="comment" required rows="3" class="w-full rounded-md border-gray-300 dark:bg-dark-900 dark:border-dark-700 dark:text-gray-300 shadow-sm focus:border-gold-500 focus:ring-gold-500 text-sm" placeholder="Wajib diisi..."></textarea>
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('rejectPRModal').classList.add('hidden')"
                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md text-sm font-medium hover:bg-gray-200">Batal</button>
                <button type="submit"
                    class="px-4 py-2 bg-red-600 text-white rounded-md text-sm font-medium hover:bg-red-700">Ya, Tolak</button>
            </div>
        </form>
    </x-confirm-modal>
</x-app-layout>