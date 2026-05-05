# Analisis & Rekomendasi: Manajemen Perubahan Kontrak (Addendum/CCO) - PROMAN5

## 1. Pendahuluan
Dalam industri konstruksi, perubahan kontrak atau *Contract Change Order* (CCO) adalah hal yang hampir pasti terjadi. Perubahan ini bisa berupa tambah/kurang volume, penambahan item pekerjaan baru, hingga perpanjangan waktu pelaksanaan. PROMAN5 memerlukan mekanisme yang kuat untuk menangani hal ini tanpa merusak integritas data laporan progres yang sudah berjalan.

## 2. Analisis Struktur Saat Ini
Berdasarkan arsitektur yang ada:
- **Project Model**: Memiliki `contract_value`.
- **RabItem Model**: Memiliki `total_price`, `weight_percentage`, dan `actual_progress`.
- **ProgressReport Model**: Terikat langsung pada `RabItem`.

**Kelemahan saat ini:** Jika `total_price` pada `RabItem` diubah langsung, maka bobot (`weight_percentage`) semua item lain akan berubah. Hal ini akan menyebabkan data historis pada laporan mingguan/bulanan sebelumnya menjadi tidak akurat (angka progres kumulatif akan bergeser).

## 3. Rekomendasi Strategi (Best Practice)

### A. Konsep "Versioning & Addendum"
Jangan mengubah data asli. Gunakan tabel baru untuk mencatat setiap addendum.
- **Model Baru: `ProjectAddendum`**: Mencatat informasi administratif addendum (Nomor, Tanggal, Perubahan Nilai, Perpanjangan Waktu).
- **Model Baru: `AddendumRabItem`**: Mencatat perubahan pada level item (Item mana yang ditambah, dikurangi, atau baru).

### B. Penanganan Item Pekerjaan
1. **Pekerjaan Tambah (Add)**: Menambahkan item baru atau menambah volume pada item yang sudah ada.
2. **Pekerjaan Kurang (Deduct)**: Mengurangi volume atau menghapus item (secara logis, bukan fisik).
3. **Pekerjaan Baru**: Item yang benar-benar tidak ada di RAB awal.

### C. Mekanisme Perhitungan Progres
Sistem harus mampu menghitung dua jenis progres:
- **Progres terhadap Kontrak Awal**: Digunakan untuk evaluasi kinerja internal.
- **Progres terhadap Kontrak Addendum Terbaru**: Digunakan untuk dasar penagihan (*Invoicing* / Termijn).

## 4. Rekomendasi Perubahan Skema Database

### Model `ProjectAddendum`
| Field | Type | Description |
|-------|------|-------------|
| project_id | FK | Relasi ke proyek |
| addendum_number | string | Nomor dokumen addendum |
| addendum_date | date | Tanggal efektif |
| type | enum | CCO (Teknis) atau Addendum (Admin/Biaya) |
| net_value_change | decimal | Selisih nilai (+/-) |
| time_extension | integer | Tambahan hari (jika ada) |
| status | enum | Draft, Approved, Applied |

### Model `AddendumItem`
| Field | Type | Description |
|-------|------|-------------|
| addendum_id | FK | Relasi ke ProjectAddendum |
| rab_item_id | FK | Relasi ke RabItem asli |
| original_qty | decimal | Qty sebelum addendum |
| new_qty | decimal | Qty sesudah addendum |
| original_unit_price | decimal | Harga sebelum addendum |
| new_unit_price | decimal | Harga sesudah addendum |

## 5. Implementasi Workflow di PROMAN5

1. **Pengajuan**: Estimator membuat draft Addendum di modul baru.
2. **Review & Approval**: Melalui alur persetujuan (PM -> Owner).
3. **Execution (The "Snapshot" Moment)**: 
   - Saat Addendum disetujui, sistem melakukan *re-calculation* bobot secara otomatis.
   - Bobot baru disimpan sebagai "Current Weight", namun "Original Weight" tetap ada di history.
4. **Impact to Progress**: Laporan progres berikutnya akan menggunakan basis bobot terbaru.

## 6. Kesimpulan
Dengan menerapkan sistem *Versioning*, PROMAN5 akan memiliki *Audit Trail* yang lengkap. Pengguna bisa melihat bagaimana proyek berkembang dari kontrak asli hingga addendum ke-n tanpa kehilangan data historis progres pekerjaan.
