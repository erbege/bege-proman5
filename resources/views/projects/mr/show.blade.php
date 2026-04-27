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

            <!-- Approval Progress -->
            @if($mr->status !== 'draft' && $mr->status !== 'rejected')
                <div class="bg-white dark:bg-dark-800 shadow-sm sm:rounded-lg mb-6 p-6">
                    <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-4">Progres Persetujuan</h3>
                    <div class="relative">
                        <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-gray-200 dark:bg-dark-700">
                            @php 
                                $percent = ($mr->current_approval_level / ($mr->max_approval_level + 1)) * 100;
                                if($mr->is_fully_approved) $percent = 100;
                            @endphp
                            <div style="width:{{ $percent }}%" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-gold-500 transition-all duration-500"></div>
                        </div>
                        <div class="flex justify-between text-xs font-medium">
                            <div class="text-gray-500">Draft</div>
                            @for($i = 1; $i <= $mr->max_approval_level; $i++)
                                <div class="{{ $mr->current_approval_level >= $i ? 'text-gold-600 font-bold' : 'text-gray-400' }}">
                                    Level {{ $i }}
                                </div>
                            @endfor
                            <div class="{{ $mr->is_fully_approved ? 'text-green-600 font-bold' : 'text-gray-400' }}">Selesai</div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Items Table -->
            <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Daftar Material</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-dark-700">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Material</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Qty</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Satuan</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Catatan Item</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($mr->items as $item)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white font-medium">{{ $item->material->name }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white text-right">{{ number_format($item->quantity, 2) }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white text-center">{{ $item->unit }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $item->notes ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Approval Actions -->
            @if($mr->status === 'pending' && !$mr->is_fully_approved)
                @php
                    $canApprove = false;
                    $matrix = \App\Models\ApprovalMatrix::where('document_type', 'MR')
                        ->where('level', $mr->current_approval_level)
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
                                <p class="text-sm text-gray-500 dark:text-gray-400">Anda bertanggung jawab untuk persetujuan Level {{ $mr->current_approval_level }}</p>
                            </div>
                            <div class="flex gap-4">
                                <button type="button" onclick="document.getElementById('approveMRModal').classList.remove('hidden')"
                                    class="inline-flex items-center px-6 py-2 bg-green-600 border border-transparent rounded-md font-bold text-xs text-white uppercase tracking-widest hover:bg-green-700 shadow-lg transition">
                                    <x-heroicon-o-check class="w-4 h-4 mr-2" />
                                    Setujui
                                </button>
                                <button type="button" onclick="document.getElementById('rejectMRModal').classList.remove('hidden')"
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
                            Menunggu persetujuan Level {{ $mr->current_approval_level }} (Role: {{ str_replace('_', ' ', $matrix->role_name ?? 'N/A') }})
                        </p>
                    </div>
                @endif
            @elseif($mr->is_fully_approved && $mr->status === 'approved')
                <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6 border-l-4 border-green-500">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Permintaan Disetujui</h3>
                            <p class="text-sm text-gray-500">Permintaan ini telah disetujui sepenuhnya. Silakan lanjut membuat Purchase Request (PR).</p>
                        </div>
                        <a href="{{ route('projects.pr.create', ['project' => $project, 'from_mr' => $mr->id]) }}"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 shadow-lg transition transform hover:scale-105">
                            <x-heroicon-o-shopping-cart class="w-4 h-4 mr-2" />
                            Buat Purchase Request
                        </a>
                    </div>
                </div>
            @elseif($mr->status === 'processed')
                <div class="bg-blue-50 dark:bg-blue-900/20 p-6 rounded-lg mb-6 flex items-center shadow-sm">
                    <x-heroicon-s-check-circle class="w-6 h-6 text-blue-500 mr-2" />
                    <span class="text-blue-700 dark:text-blue-300 font-medium">Material Request ini telah diproses menjadi Purchase Request.</span>
                </div>
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
                            @foreach($mr->approvalLogs as $log)
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
                                                    <span class="font-bold text-gray-900 dark:text-white">{{ $mr->requestedBy->name }}</span> membuat dan mengajukan Material Request
                                                </p>
                                            </div>
                                            <div class="whitespace-nowrap text-right text-xs text-gray-500">
                                                <time datetime="{{ $mr->created_at }}">{{ $mr->created_at->format('d M Y, H:i') }}</time>
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

    <!-- Approve MR Modal -->
    <x-confirm-modal id="approveMRModal" title="Approve Material Request"
        message="Apakah Anda yakin ingin menyetujui permintaan material ini untuk Level {{ $mr->current_approval_level }}?" confirmColor="green" icon="check">
        <form action="{{ route('projects.mr.status', [$project, $mr]) }}" method="POST" class="p-6">
            @csrf
            <input type="hidden" name="status" value="approved">
            
            <div class="mb-4 text-left">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Catatan Persetujuan (Opsional)</label>
                <textarea name="comment" rows="3" class="w-full rounded-md border-gray-300 dark:bg-dark-900 dark:border-dark-700 dark:text-gray-300 shadow-sm focus:border-gold-500 focus:ring-gold-500 text-sm" placeholder="Tambahkan catatan jika diperlukan..."></textarea>
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('approveMRModal').classList.add('hidden')"
                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md text-sm font-medium hover:bg-gray-200">Batal</button>
                <button type="submit"
                    class="px-4 py-2 bg-green-600 text-white rounded-md text-sm font-medium hover:bg-green-700">Ya, Approve</button>
            </div>
        </form>
    </x-confirm-modal>

    <!-- Reject MR Modal -->
    <x-confirm-modal id="rejectMRModal" title="Reject Material Request"
        message="Harap masukkan alasan penolakan permintaan ini." confirmColor="red" icon="x-mark">
        <form action="{{ route('projects.mr.status', [$project, $mr]) }}" method="POST" class="p-6">
            @csrf
            <input type="hidden" name="status" value="rejected">
            
            <div class="mb-4 text-left">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Alasan Penolakan</label>
                <textarea name="comment" required rows="3" class="w-full rounded-md border-gray-300 dark:bg-dark-900 dark:border-dark-700 dark:text-gray-300 shadow-sm focus:border-gold-500 focus:ring-gold-500 text-sm" placeholder="Wajib diisi..."></textarea>
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('rejectMRModal').classList.add('hidden')"
                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md text-sm font-medium hover:bg-gray-200">Batal</button>
                <button type="submit"
                    class="px-4 py-2 bg-red-600 text-white rounded-md text-sm font-medium hover:bg-red-700">Ya, Tolak</button>
            </div>
        </form>
    </x-confirm-modal>
</x-app-layout>