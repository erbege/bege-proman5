# PROMAN5 - Construction Project Management System

PROMAN5 adalah sistem Enterprise Resource Planning (ERP) dan Manajemen Proyek mutakhir yang dirancang khusus untuk industri konstruksi dan kontraktor di Indonesia. Dibangun menggunakan **Laravel 12**, sistem ini mengintegrasikan seluruh siklus hidup proyek konstruksi, mulai dari perencanaan anggaran (RAB), pengadaan material (Procurement), manajemen stok, hingga pelaporan progres lapangan secara komprehensif.

## 🚀 Fitur Utama

### 1. Manajemen Proyek & Dokumen
* **Project Tracking & Scheduling**: Pemantauan jadwal proyek dan penugasan tim (Project Team) secara terstruktur.
* **Document Versioning**: Sistem manajemen file proyek dengan dukungan versi dokumen (Project File Version) dan fitur komentar.
* **Progress Reporting**: Pembuatan laporan progres mingguan dan harian (Weekly & Progress Report) untuk memonitor perkembangan fisik lapangan.

### 2. Estimasi Biaya (RAB) & AHSP
* **Integrasi AHSP (Analisa Harga Satuan Pekerjaan)**: Manajemen sistematis untuk kategori, komponen, dan tipe pekerjaan berstandar AHSP.
* **RAB (Rencana Anggaran Biaya)**: Pembuatan RAB proyek yang mendetail (RabSection, RabItem) guna menjamin akurasi budget.
* **Price History & Snapshot**: Pelacakan riwayat harga dasar AHSP untuk analisis fluktuasi biaya historis.

### 3. Pengadaan (Procurement) & Supply Chain
* **Supplier Management**: Pengelolaan basis data vendor dan pemasok proyek.
* **Purchase Request (PR) & Purchase Order (PO)**: Alur pengajuan dan persetujuan pengadaan barang yang tersistematisasi.
* **Goods Receipt**: Pencatatan penerimaan barang di gudang atau lokasi proyek yang secara otomatis memperbarui status inventaris.

### 4. Manajemen Material & Inventaris
* **Material Request & Usage**: Alur permintaan kebutuhan material serta pencatatan penggunaannya di lokasi proyek.
* **Material Forecasting**: Fitur peramalan kebutuhan material proyek berbasis data riwayat (Material Forecast).
* **Real-time Inventory**: Pelacakan stok material secara real-time yang dilengkapi dengan riwayat mutasi barang (Inventory Log).

### 5. Keamanan & Notifikasi Terpadu
* **Role & Permission Management**: Sistem manajemen hak akses (RBAC) granular menggunakan `spatie/laravel-permission`.
* **Activity Audit Trails**: Pencatatan otomatis untuk setiap aksi pengguna di dalam sistem melalui `spatie/laravel-activitylog`.
* **Push Notifications**: Terintegrasi penuh dengan **Firebase Cloud Messaging (FCM)** untuk pengiriman notifikasi real-time via web dan perangkat mobile.

### 6. Integrasi AI & Ekosistem Modern
* **Kecerdasan Buatan (AI)**: Mendukung integrasi **OpenAI** dan **Google Gemini** (`openai-php/laravel`, `google-gemini-php/laravel`) untuk kemudahan analitik dan otomasi fitur.
* **Export/Import Excel**: Pengelolaan data bulk secara mudah melalui impor dan ekspor file Excel (`maatwebsite/excel`).
* **API Documentation**: Dokumentasi API otomatis dan rapi menggunakan `knuckleswtf/scribe` dan `dedoc/scramble`.

## 🛠️ Tech Stack & Requirements

- **Backend**: PHP `^8.2`, Laravel Framework `^12.0`
- **Frontend/UI**: Livewire `^3.6`, TailwindCSS, Vite
- **Database Engine**: MySQL / PostgreSQL / SQLite
- **Cache, Session, & Queue**: Redis (`predis/predis`)
- **Cloud Storage Integration**: AWS S3 (`league/flysystem-aws-s3-v3`)

## ⚙️ Instalasi & Setup

1. **Clone repositori**
   ```bash
   git clone <repository_url> bege-proman5
   cd bege-proman5
   ```

2. **Install dependensi (PHP & Node.js)**
   ```bash
   composer install
   npm install
   ```

3. **Setup environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   *Pastikan untuk menyesuaikan konfigurasi database, Redis, Firebase credentials, dan AWS di dalam file `.env`.*

4. **Migrasi dan Seeding Database**
   ```bash
   php artisan migrate
   ```

5. **Jalankan Aplikasi**
   Untuk memulai server aplikasi secara terintegrasi (menjalankan server web, antrian, log, dan Vite secara bersamaan):
   ```bash
   npm run dev
   ```

## 📝 Lisensi

Aplikasi ini bersifat **Proprietary** dan dikembangkan khusus untuk keperluan operasional. Dilarang menyalin, mendistribusikan ulang, atau menggunakan bagian manapun dari kode sumber ini tanpa izin tertulis yang sah.
