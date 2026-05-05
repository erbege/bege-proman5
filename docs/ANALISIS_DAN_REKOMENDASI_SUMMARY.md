# Analisis Komprehensif: Keterkaitan Jadwal Proyek, Laporan (Progress/Weekly/Monthly), dan Material Usage

Berdasarkan tinjauan arsitektur kode (*services*, *models*, dan relasi database) pada proyek **PROMAN5**, berikut adalah analisis mendalam mengenai keterkaitan antara Jadwal Proyek, Sistem Pelaporan, dan Penggunaan Material, beserta rekomendasi peningkatan arsitektur untuk menjaga integritas data dan performa sistem.

---

## 1. Analisis Keterkaitan Saat Ini (Current Architecture)

### A. Jadwal Proyek (Project Schedule) & Kurva-S
- **Aktor Utama**: `ProjectSchedule` dan `ScheduleCalculator`.
- **Mekanisme**: `ScheduleCalculator` memproyeksikan jadwal terencana (`planned_weight`) dengan memecah bobot RAB (`weight_percentage`) secara proporsional ke dalam satuan minggu berdasarkan `planned_start` dan `planned_end`.
- **Koneksi dengan Laporan**: Akumulasi bobot aktual (`actual_weight` dan `actual_cumulative`) secara dinamis dihitung dari selisih `progress_percentage` pada `ProgressReport`. Ketika Laporan Harian (Progress Report) dibuat atau diubah, *service* akan mentrigger `$this->scheduleCalculator->updateFromProgress($project)` untuk mengkalibrasi ulang nilai deviasi (S-Curve).

### B. Ekosistem Pelaporan (Progress, Weekly, Monthly)
- **Progress Report (Harian)**: Bertindak sebagai **titik entri utama (Source of Truth)** untuk pencapaian fisik (`actual_progress`). Data kemajuan fisik dikunci dan diakumulasi secara berurutan, memastikan `RabItem->actual_progress` selalu merepresentasikan akumulasi kemajuan terbaru.
- **Weekly & Monthly Report**: Bertindak sebagai **dokumen snapshot (Summary)**.
  - Menggunakan JSON `cumulative_data` untuk merekam snapshot data `planned`, `actual`, dan `deviation` pada minggu/bulan tersebut secara hirarkis.
  - Memiliki fitur *cascading update* (`cascadeToSubsequentWeeks` / `cascadeToSubsequentMonths`). Jika terdapat revisi realisasi kumulatif di masa lalu, sistem secara cerdas akan "merambat" (cascade) kalkulasi tersebut ke laporan-laporan di minggu/bulan setelahnya agar data tetap sinkron.

### C. Material Usage (Penggunaan Material)
- Saat ini pencatatan material terbagi menjadi dua ranah yang *loosely coupled* (kurang terikat erat):
  1. **Ranah Lapangan (Progress Report)**: Menyimpan rincian material yang dipakai hari itu pada kolom JSON `material_usage_summary`.
  2. **Ranah Gudang/Logistik (`MaterialUsage`)**: Pencatatan resmi keluarnya stok dari *Inventory* (`GoodsReceipt`, `InventoryLog`, `MaterialUsageItem`) yang mengikat ke `rab_item_id`.

---

## 2. Identifikasi Celah & Area Peningkatan (Bottlenecks & Gaps)

1. **Disconnected Material Flow (Silo Material Lapangan vs Gudang)**
   - Saat mandor/site engineer mengklaim menggunakan material via Laporan Harian (dalam JSON `material_usage_summary`), sistem *tidak* secara otomatis memotong stok gudang atau mengintegrasikannya ke entitas `MaterialUsage`. Hal ini berisiko memunculkan data ganda atau deviasi stok fisik (Logistik) vs klaim teknis (Engineering).
2. **Kinerja Cascading Laporan Berbasis JSON (Performance Overhead)**
   - Proses `cascadeToSubsequentWeeks` melakukan manipulasi mutasi array multi-dimensi (rekursif) dalam PHP memory dan menyimpan ulang array berskala besar (>100KB) ke dalam kolom JSON di database. Jika sebuah proyek memiliki ratusan item RAB dan revisi dilakukan pada "Minggu ke-2" dari total "Minggu ke-50", sistem harus membongkar-pasang JSON untuk 48 laporan secara berurutan. Ini rawan terhadap *timeout* dan mengonsumsi memori besar.
3. **Synchronous Schedule Calculation**
   - Laporan harian secara sinkron (`synchronous`) mentrigger perancangan ulang jadwal (`generateSchedule()`) yang menghapus (`delete`) dan menyisipkan ulang (`bulk insert`) baris jadwal proyek setiap ada laporan baru. Saat *traffic* persetujuan tinggi (misalnya di akhir hari kerja), hal ini dapat membebani *database lock*.

---

## 3. Rekomendasi & Rencana Tindak Lanjut (Actionable Recommendations)

### Rekomendasi 1: Integrasi Ekosistem Material (Bridging Progress & Logistik)
**Masalah**: Klaim material harian tidak sinkron otomatis dengan stok gudang.
**Solusi**:
- **Pivot Relasional**: Ubah `material_usage_summary` (JSON) menjadi relasi tabel khusus, misalnya `ProgressReportMaterial`.
- **Approval Trigger**: Ketika Laporan Progress statusnya berubah menjadi `STATUS_REVIEWED` atau disetujui, buat sistem secara otomatis menghasilkan dokumen `MaterialUsage` berstatus **Draft** di *dashboard* Logistik.
- **Workflow**: Logistik gudang akan melihat *Draft Material Usage* hasil dari laporan harian, meninjau fisik yang benar-benar keluar, lalu meng-*approve*-nya. Ini memastikan kalkulasi **ACWP** (Actual Cost of Work Performed) akurat berdasarkan harga material yang keluar dari gudang (FIFO/Average).

### Rekomendasi 2: Migrasi Snapshot JSON ke Tabel Relasional Khusus
**Masalah**: Beban komputasi saat mengkalkulasi dan melakukan *cascade update* pada JSON.
**Solusi**:
- Daripada menyimpan snapshot *Planned/Actual* 1 proyek penuh ke dalam satu kolom JSON `cumulative_data`, buat tabel `ReportProgressSnapshots` yang merekam: `report_type` (Weekly/Monthly), `report_id`, `rab_item_id`, `planned_weight`, `actual_weight`.
- **Keuntungan**: Melakukan perbaikan (cascading) ke minggu-minggu berikutnya hanya membutuhkan 1 sintaks query `UPDATE report_snapshots SET actual_weight = actual_weight + X WHERE week_number > Y`, menghilangkan kebutuhan manipulasi JSON rekursif di memori aplikasi. Ini akan secara drastis mengoptimalkan skala performa saat RAB mencapai >1.000 item.

### Rekomendasi 3: Asynchronous Schedule Recalculation
**Masalah**: UI lambat (blocking) ketika *Submit*/*Approve* Progress Report karena peritungan Kurva-S berjalan bersamaan.
**Solusi**:
- Pindahkan logika `$this->scheduleCalculator->updateFromProgress($project)` ke dalam *Job/Queue* Laravel (misal: `RecalculateProjectScheduleJob::dispatch($project)`).
- Gunakan *Event-Driven Architecture*. Set `ProgressReportApproved` event, dan pastikan re-kalkulasi kalender (`delete` & `bulk insert` ke `project_schedules`) berjalan di latar belakang (background worker) agar *User Experience* tidak terhambat.

### Rekomendasi 4: Integrasi Earned Value Management (EVM)
- Dengan data Laporan (Kemajuan Fisik / BCWP), Jadwal (Kemajuan Terencana / BCWS), dan Material Usage (Biaya Riil / ACWP) yang sudah cukup kaya, PROMAN5 sudah sangat siap diintegrasikan dengan metrik **Earned Value Management (EVM)**.
- **Rekomendasi UI**: Tambahkan modul/dashboard **Cost Control (Cost Performance Index - CPI & Schedule Performance Index - SPI)** yang menyoroti perbandingan *Real-time* antara klaim progres fisik lapangan vs total biaya *Purchase Order / Material Usage* secara langsung.

---
**Kesimpulan:**
Fondasi arsitektur **PROMAN5** untuk integrasi Jadwal, Kemajuan, dan Biaya sudah sangat solid dengan kalkulasi dinamis dan fitur kalibrasi deviasi. Fokus peningkatan selanjutnya direkomendasikan pada transisi dari *penyimpanan array JSON* ke *struktur basis data RDBMS yang ternormalisasi* untuk rekapitulasi, integrasi antar modul (Logistik ↔️ Lapangan) secara proaktif (event-driven), serta pendelegasian proses komputasi berat ke fitur antrian (Queue Worker) Laravel.