<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
            ['label' => 'Pengaturan', 'url' => '#'],
            ['label' => 'Matriks Approval']
        ]" />
    </x-slot>

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Konfigurasi Matriks Approval') }}
            </h2>
            <button type="button" onclick="document.getElementById('addMatrixModal').classList.remove('hidden')"
                class="inline-flex items-center px-4 py-2 bg-gold-500 border border-transparent rounded-md font-bold text-xs text-gray-900 uppercase tracking-widest hover:bg-gold-600 transition shadow-lg">
                <x-heroicon-o-plus class="w-4 h-4 mr-2" />
                Tambah Aturan
            </button>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <!-- Info Card -->
            <div class="bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-400 p-4 mb-6 rounded shadow-sm">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <x-heroicon-o-information-circle class="h-5 w-5 text-blue-400" />
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700 dark:text-blue-300">
                            Matriks approval menentukan siapa yang berwenang menyetujui dokumen (MR, PR, PO) pada setiap tingkatan level. 
                            Untuk <strong>PO</strong>, Anda dapat menetapkan <strong>Nominal Minimum</strong> agar level tersebut aktif (misal: Level 3 hanya aktif jika > 100jt).
                        </p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach(['MR' => 'Material Request', 'PR' => 'Purchase Request', 'PO' => 'Purchase Order'] as $type => $label)
                    <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg border border-gray-100 dark:border-dark-700">
                        <div class="p-4">
                            <div class="flex items-center justify-between mb-4 border-b pb-2 dark:border-dark-700">
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center">
                                    <span class="w-2 h-6 bg-gold-500 rounded mr-2"></span>
                                    {{ $label }}
                                </h3>
                                <span class="px-2 py-1 bg-gray-100 dark:bg-dark-700 rounded text-xs font-bold text-gray-500 uppercase">{{ $type }}</span>
                            </div>

                            <div class="space-y-4">
                                @php $typeMatrices = $matrices->where('document_type', $type); @endphp
                                @forelse($typeMatrices as $matrix)
                                    <div class="relative p-4 rounded-lg border {{ $matrix->is_active ? 'border-gray-200 dark:border-dark-600 bg-gray-50/50 dark:bg-dark-700/50' : 'border-red-100 bg-red-50/20 opacity-60' }}">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <div class="flex items-center gap-2">
                                                    <span class="flex items-center justify-center w-6 h-6 rounded-full bg-gold-100 text-gold-700 text-xs font-bold">{{ $matrix->level }}</span>
                                                    <p class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-tight">
                                                        {{ str_replace(['_', '-'], ' ', $matrix->role_name) }}
                                                    </p>
                                                </div>
                                                @if($matrix->min_amount > 0)
                                                    <p class="text-xs text-blue-600 dark:text-blue-400 mt-1 font-medium">
                                                        Berlaku jika > Rp {{ number_format($matrix->min_amount, 0, ',', '.') }}
                                                    </p>
                                                @else
                                                    <p class="text-xs text-gray-500 mt-1 italic">Selalu aktif</p>
                                                @endif
                                            </div>
                                            
                                            <div class="flex items-center gap-1">
                                                <button type="button" 
                                                    onclick="openEditModal({{ $matrix }})"
                                                    class="p-1.5 text-gray-400 hover:text-gold-600 transition">
                                                    <x-heroicon-o-pencil-square class="w-4 h-4" />
                                                </button>
                                                <button type="button" 
                                                    onclick="confirmDelete('{{ route('settings.approval-matrix.destroy', $matrix) }}')"
                                                    class="p-1.5 text-gray-400 hover:text-red-600 transition">
                                                    <x-heroicon-o-trash class="w-4 h-4" />
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-8">
                                        <x-heroicon-o-shield-exclamation class="w-10 h-10 text-gray-300 mx-auto mb-2" />
                                        <p class="text-sm text-gray-400 italic">Belum ada aturan</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Add Matrix Modal -->
    <x-confirm-modal id="addMatrixModal" title="Tambah Aturan Approval" message="Tentukan tingkatan level dan role yang bertanggung jawab." confirmColor="gold" icon="shield-check">
        <form action="{{ route('settings.approval-matrix.store') }}" method="POST" class="p-4 text-left">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2 md:col-span-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tipe Dokumen</label>
                    <select name="document_type" required class="w-full rounded-md border-gray-300 dark:bg-dark-900 dark:border-dark-700 dark:text-gray-300 focus:border-gold-500 focus:ring-gold-500 text-sm">
                        <option value="MR">Material Request (MR)</option>
                        <option value="PR">Purchase Request (PR)</option>
                        <option value="PO">Purchase Order (PO)</option>
                    </select>
                </div>
                <div class="col-span-2 md:col-span-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Level Approval</label>
                    <input type="number" name="level" required min="1" class="w-full rounded-md border-gray-300 dark:bg-dark-900 dark:border-dark-700 dark:text-gray-300 focus:border-gold-500 focus:ring-gold-500 text-sm" placeholder="Contoh: 1">
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Role Penanggung Jawab</label>
                    <select name="role_name" required class="w-full rounded-md border-gray-300 dark:bg-dark-900 dark:border-dark-700 dark:text-gray-300 focus:border-gold-500 focus:ring-gold-500 text-sm">
                        <option value="">Pilih Role...</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}">{{ ucwords(str_replace(['_', '-'], ' ', $role->name)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nominal Minimum (Khusus PO)</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 text-sm">Rp</span>
                        </div>
                        <input type="number" name="min_amount" value="0" class="w-full pl-10 rounded-md border-gray-300 dark:bg-dark-900 dark:border-dark-700 dark:text-gray-300 focus:border-gold-500 focus:ring-gold-500 text-sm">
                    </div>
                    <p class="text-[10px] text-gray-400 mt-1 italic">*Kosongkan atau isi 0 jika selalu aktif untuk semua nominal.</p>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('addMatrixModal').classList.add('hidden')"
                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md text-sm font-medium hover:bg-gray-200 transition">Batal</button>
                <button type="submit"
                    class="px-4 py-2 bg-gold-500 text-gray-900 rounded-md text-sm font-bold hover:bg-gold-600 transition shadow-md">Simpan Aturan</button>
            </div>
        </form>
    </x-confirm-modal>

    <!-- Edit Matrix Modal -->
    <x-confirm-modal id="editMatrixModal" title="Edit Aturan Approval" message="Perbarui peran atau ambang batas nominal." confirmColor="blue" icon="pencil-square">
        <form id="editMatrixForm" method="POST" class="p-4 text-left">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Role Penanggung Jawab</label>
                    <select name="role_name" id="edit_role_name" required class="w-full rounded-md border-gray-300 dark:bg-dark-900 dark:border-dark-700 dark:text-gray-300 focus:border-gold-500 focus:ring-gold-500 text-sm">
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}">{{ ucwords(str_replace(['_', '-'], ' ', $role->name)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nominal Minimum (Khusus PO)</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 text-sm">Rp</span>
                        </div>
                        <input type="number" name="min_amount" id="edit_min_amount" class="w-full pl-10 rounded-md border-gray-300 dark:bg-dark-900 dark:border-dark-700 dark:text-gray-300 focus:border-gold-500 focus:ring-gold-500 text-sm">
                    </div>
                </div>
                <div class="flex items-center">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" id="edit_is_active" value="1" class="rounded border-gray-300 text-gold-600 shadow-sm focus:ring-gold-500">
                    <label class="ml-2 block text-sm text-gray-700 dark:text-gray-300 italic">Aturan ini aktif</label>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('editMatrixModal').classList.add('hidden')"
                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md text-sm font-medium hover:bg-gray-200 transition">Batal</button>
                <button type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-bold hover:bg-blue-700 transition shadow-md">Update Aturan</button>
            </div>
        </form>
    </x-confirm-modal>

    <!-- Delete Confirm Form -->
    <form id="deleteForm" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>

    <script>
        function openEditModal(matrix) {
            const form = document.getElementById('editMatrixForm');
            form.action = `/settings/approval-matrix/${matrix.id}`;
            
            document.getElementById('edit_role_name').value = matrix.role_name;
            document.getElementById('edit_min_amount').value = matrix.min_amount;
            document.getElementById('edit_is_active').checked = matrix.is_active;
            
            document.getElementById('editMatrixModal').classList.remove('hidden');
        }

        function confirmDelete(url) {
            if (confirm('Apakah Anda yakin ingin menghapus aturan approval ini?')) {
                const form = document.getElementById('deleteForm');
                form.action = url;
                form.submit();
            }
        }
    </script>
</x-app-layout>


