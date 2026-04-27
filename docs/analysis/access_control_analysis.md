# Analisis dan Rekomendasi Implementasi Pembatasan Akses (RBAC) - Proyek BEGE-PROMAN5

## 1. Pendahuluan
Dokumen ini menyajikan analisis mendalam terhadap sistem kontrol akses (Role-Based Access Control) pada proyek BEGE-PROMAN5. Fokus utama adalah pembatasan akses menu/modul dan perlindungan data sensitif seperti nilai proyek (Contract Value) dan nilai RAB bagi pengguna dengan peran tertentu.

## 2. Analisis Kondisi Saat Ini
Berdasarkan pemeriksaan struktur kode:
- **Paket yang Digunakan**: Proyek menggunakan `spatie/laravel-permission` yang merupakan standar industri untuk Laravel.
- **Definisi Role & Permission**: Sudah terdapat banyak role (Superadmin, Project Manager, Site Manager, Logistics, Purchasing, dll.) dan permission dasar (view, create, update, delete).
- **Implementasi UI**: 
    - Sebagian besar menu utama masih terlihat oleh semua user yang terautentikasi.
    - Data keuangan (Contract Value di Project dan Unit Price/Total Price di RAB) saat ini tampil secara eksplisit di view tanpa pengecekan tambahan selain permission `view`.
    - Belum ada pemisahan antara hak "melihat daftar pekerjaan" dan "melihat nilai uang".

## 3. Rekomendasi Strategis

### A. Penambahan Permission Khusus Data Sensitif
Diperlukan permission tambahan untuk membedakan antara akses operasional dan akses finansial.
- `financials.view`: Izin untuk melihat nilai kontrak, harga satuan, dan total harga.
- `financials.manage`: Izin untuk mengubah nilai harga (biasanya untuk Estimator/PM).

### B. Matriks Rekomendasi Akses Data Sensitif
| Role | Akses Modul | Akses Nilai Proyek/RAB | Rationale |
| :--- | :--- | :--- | :--- |
| **Superadmin** | Full | Full | Pemilik sistem. |
| **Project Manager**| Full | Full | Bertanggung jawab atas profitabilitas proyek. |
| **Estimator / QS** | RAB & AHSP | Full | Perlu data harga untuk menyusun penawaran. |
| **Site Manager** | Proyek & Progress | **Terbatas / Masked** | Fokus pada pelaksanaan teknis dan volume, bukan nilai uang. |
| **Logistics** | Inventory & GR | **Terbatas** | Hanya perlu melihat harga beli jika terkait PO, bukan nilai RAB. |
| **Purchasing** | PO & Supplier | Full (Harga Beli) | Perlu data harga untuk negosiasi vendor. |
| **Supervisor / Site**| Progress & Usage | **Hidden** | Tidak perlu akses data finansial. |

---

## 4. Rencana Implementasi Teknis

### 1. Update Database Seeder (`RolePermissionSeeder.php`)
Tambahkan permission `financials.view` dan distribusikan hanya ke role yang relevan.

```php
// Tambahkan di array $permissions
'financials.view',
'financials.manage',

// Assign ke role PM dan Estimator
$projectManager->givePermissionTo(['financials.view', 'financials.manage']);
$estimator->givePermissionTo(['financials.view', 'financials.manage']);
```

### 2. Pembatasan Visibilitas di Blade Templates
Gunakan direktif `@can` untuk menyembunyikan kolom sensitif.

**Contoh pada RAB Detail (`rab-detail.blade.php`):**
```html
{{-- Header --}}
@can('financials.view')
    <th>HARGA SATUAN (RP)</th>
    <th>JUMLAH HARGA (RP)</th>
@endcan

{{-- Baris Data --}}
@can('financials.view')
    <td class="text-right">{{ number_format($item->unit_price, 2, ',', '.') }}</td>
    <td class="text-right">{{ number_format($item->total_price, 2, ',', '.') }}</td>
@else
    <td colspan="2" class="text-center text-gray-400 italic">Tersembunyi</td>
@endcan
```

### 3. Masking Data di Level Model (Opsional)
Untuk keamanan lebih ketat, gunakan *Attribute Casting* atau *Accessors* di Model `Project` dan `RabItem`.

```php
// App\Models\Project.php
public function getContractValueAttribute($value) {
    if (!auth()->user()?->can('financials.view')) {
        return 0; // Atau null
    }
    return $value;
}
```

### 4. Pembatasan Menu Navigasi (`navigation.blade.php`)
Bungkus setiap elemen `x-nav-link` dengan permission yang sesuai.

```html
@can('inventory.view')
    <x-nav-link :href="route('inventory.index')">
        {{ __('Gudang') }}
    </x-nav-link>
@endcan

@can('users.manage')
    {{-- Menu Admin --}}
@endcan
```

### 5. Middleware pada Routes
Pastikan route sensitif (seperti pengelolaan AHSP atau Settings) diproteksi di `web.php`.

```php
Route::middleware(['can:settings.view'])->group(function () {
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
});
```

## 5. Keamanan Tambahan & Best Practice
1. **Principle of Least Privilege**: Berikan akses sekecil mungkin yang diperlukan user untuk bekerja.
2. **Audit Trait**: Manfaatkan `spatie/laravel-activitylog` (yang sudah terinstall) untuk mencatat siapa yang melihat atau mengubah data sensitif.
3. **Server-side Validation**: Jangan hanya menyembunyikan di UI (CSS `hidden`), tapi pastikan data tidak dikirim ke client jika user tidak berhak (Server-side rendering).
4. **Scoping Data**: Untuk Site Manager atau Supervisor, tambahkan scope pada Query sehingga mereka hanya melihat proyek yang ditugaskan kepada mereka (`ProjectTeam`).

## 6. Kesimpulan
Implementasi ini akan meningkatkan integritas data dan kerahasiaan nilai bisnis perusahaan. Langkah awal yang direkomendasikan adalah melakukan migrasi penambahan permission dan segera mengupdate file `navigation.blade.php` serta view RAB utama.
