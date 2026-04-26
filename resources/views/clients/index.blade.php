<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
        ['label' => 'Master Klien']
    ]" />
    </x-slot>

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Master Data Klien') }}
            </h2>
            <button type="button" onclick="openClientModal()"
                class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <x-heroicon-o-plus class="w-4 h-4 mr-2" />
                Tambah Klien
            </button>
        </div>
    </x-slot>

    <!-- Client Modal (Add/Edit) -->
    <div id="clientModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog"
        aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeClientModal()"
                aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div
                class="inline-block align-bottom bg-white dark:bg-dark-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                <form id="clientForm" method="POST">
                    @csrf
                    <input type="hidden" name="_method" id="clientFormMethod" value="POST">

                    <div class="bg-white dark:bg-dark-800 px-4 pt-5 pb-4 sm:p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white"
                                id="clientModalTitle">
                                Tambah Klien
                            </h3>
                            <button type="button" onclick="closeClientModal()"
                                class="text-gray-400 hover:text-gray-500">
                                <x-heroicon-o-x-mark class="w-6 h-6" />
                            </button>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="modal_code" value="Kode Klien" />
                                <x-text-input id="modal_code" name="code" type="text" class="mt-1 block w-full"
                                    placeholder="Kosongkan untuk generate otomatis" />
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400" id="codeHint">Kosongkan untuk
                                    kode otomatis (KLN-XXXX)</p>
                            </div>

                            <div>
                                <x-input-label for="modal_name" value="Nama Klien" />
                                <x-text-input id="modal_name" name="name" type="text" class="mt-1 block w-full"
                                    required />
                            </div>

                            <div>
                                <x-input-label for="modal_contact_person" value="Nama Kontak" />
                                <x-text-input id="modal_contact_person" name="contact_person" type="text"
                                    class="mt-1 block w-full" />
                            </div>

                            <div>
                                <x-input-label for="modal_phone" value="Telepon" />
                                <x-text-input id="modal_phone" name="phone" type="text" class="mt-1 block w-full" />
                            </div>

                            <div>
                                <x-input-label for="modal_email" value="Email" />
                                <x-text-input id="modal_email" name="email" type="email" class="mt-1 block w-full" />
                            </div>

                            <div>
                                <x-input-label for="modal_city" value="Kota" />
                                <x-text-input id="modal_city" name="city" type="text" class="mt-1 block w-full" />
                            </div>

                            <div class="md:col-span-2">
                                <x-input-label for="modal_address" value="Alamat" />
                                <textarea id="modal_address" name="address" rows="2"
                                    class="mt-1 block w-full border-gray-300 dark:border-dark-700 dark:bg-dark-900 dark:text-gray-300 focus:border-gold-500 dark:focus:border-gold-600 focus:ring-gold-500 dark:focus:ring-gold-600 rounded-md shadow-sm"></textarea>
                            </div>

                            <div class="md:col-span-2">
                                <x-input-label for="modal_notes" value="Catatan" />
                                <textarea id="modal_notes" name="notes" rows="2"
                                    class="mt-1 block w-full border-gray-300 dark:border-dark-700 dark:bg-dark-900 dark:text-gray-300 focus:border-gold-500 dark:focus:border-gold-600 focus:ring-gold-500 dark:focus:ring-gold-600 rounded-md shadow-sm"></textarea>
                            </div>

                            <div id="clientStatusField" class="hidden md:col-span-2">
                                <label class="flex items-center">
                                    <input type="hidden" name="is_active" value="0">
                                    <input type="checkbox" id="modal_is_active" name="is_active" value="1"
                                        class="rounded dark:bg-dark-900 border-gray-300 dark:border-dark-700 text-gold-600 shadow-sm focus:ring-gold-500 dark:focus:ring-gold-600 dark:focus:ring-offset-gray-800">
                                    <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Klien Aktif</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 dark:bg-dark-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <x-primary-button type="submit" class="sm:ml-3">
                            Simpan
                        </x-primary-button>
                        <button type="button" onclick="closeClientModal()"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gold-500 sm:mt-0 sm:w-auto sm:text-sm dark:bg-dark-800 dark:text-gray-300 dark:border-dark-600 dark:hover:bg-gray-700">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-dark-700">
                            <tr>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    Kode</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    Nama</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    Kontak</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    Telepon</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    Kota</th>
                                <th
                                    class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    Status</th>
                                <th
                                    class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($clients as $client)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $client->code }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ $client->name }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                        {{ $client->contact_person ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                        {{ $client->phone ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                        {{ $client->city ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if($client->is_active)
                                            <span
                                                class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">Aktif</span>
                                        @else
                                            <span
                                                class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right text-sm">
                                        <button type="button" onclick="editClient({{ json_encode($client) }})"
                                            class="text-gold-600 hover:text-gold-900 dark:text-gold-400">Edit</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                        Belum ada klien. <button type="button" onclick="openClientModal()"
                                            class="text-blue-600 hover:underline">Tambah klien</button>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-4">
                        {{ $clients->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function openClientModal(client = null) {
                const modal = document.getElementById('clientModal');
                const form = document.getElementById('clientForm');
                const title = document.getElementById('clientModalTitle');
                const methodInput = document.getElementById('clientFormMethod');
                const statusField = document.getElementById('clientStatusField');
                const codeInput = document.getElementById('modal_code');
                const codeHint = document.getElementById('codeHint');

                // Reset form
                form.reset();

                if (client) {
                    // Edit mode
                    title.textContent = 'Edit Klien';
                    form.action = `/clients/${client.id}`;
                    methodInput.value = 'PUT';
                    statusField.classList.remove('hidden');

                    // Make code readonly on edit
                    codeInput.readOnly = true;
                    codeInput.classList.add('bg-gray-100', 'dark:bg-dark-700', 'cursor-not-allowed');
                    codeHint.classList.add('hidden');

                    // Fill form fields
                    document.getElementById('modal_code').value = client.code || '';
                    document.getElementById('modal_name').value = client.name || '';
                    document.getElementById('modal_contact_person').value = client.contact_person || '';
                    document.getElementById('modal_phone').value = client.phone || '';
                    document.getElementById('modal_email').value = client.email || '';
                    document.getElementById('modal_city').value = client.city || '';
                    document.getElementById('modal_address').value = client.address || '';
                    document.getElementById('modal_notes').value = client.notes || '';
                    document.getElementById('modal_is_active').checked = client.is_active;
                } else {
                    // Create mode
                    title.textContent = 'Tambah Klien';
                    form.action = '{{ route("clients.store") }}';
                    methodInput.value = 'POST';
                    statusField.classList.add('hidden');

                    // Make code editable on create
                    codeInput.readOnly = false;
                    codeInput.classList.remove('bg-gray-100', 'dark:bg-dark-700', 'cursor-not-allowed');
                    codeHint.classList.remove('hidden');
                }

                modal.classList.remove('hidden');
            }

            function editClient(client) {
                openClientModal(client);
            }

            function closeClientModal() {
                document.getElementById('clientModal').classList.add('hidden');
            }

            // Close modal on escape key
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    closeClientModal();
                }
            });
        </script>
    @endpush
</x-app-layout>