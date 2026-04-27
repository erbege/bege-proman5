# Matriks Hak Akses (Permission Matrix) - ProMan5

Dokumen ini merinci hak akses untuk setiap peran (role) dalam sistem ProMan5, dikelompokkan berdasarkan modul fungsional, termasuk dampaknya pada API Endpoints.

## Peran Utama
1. **Superadmin / PM**: Kontrol penuh atas seluruh aspek proyek & finansial.
2. **Estimator / QS**: Fokus pada perencanaan budget (RAB) dan analisis material. Memiliki akses ke harga satuan.
3. **Site Manager / Engineer**: Fokus pada operasional lapangan, request material, dan jadwal. **Tidak memiliki akses ke harga satuan.**
4. **Logistik / Purchasing**: Fokus pada pengadaan barang, stok, dan administrasi PR/PO.

---

## 1. Modul Analisis Proyek (Project Analysis)
| Permission | Deskripsi | API Endpoints Affected | SA/PM | Est | SM | Log |
| :--- | :--- | :--- | :---: | :---: | :---: | :---: |
| `analysis.view` | Melihat tab Analisis | `GET /api/projects/{p}/analysis` | ✅ | ✅ | ✅ | ❌ |
| `analysis.manage` | Pemetaan manual | `POST /api/projects/{p}/analysis/map` | ✅ | ✅ | ❌ | ❌ |
| `analysis.run-ai` | Run AI Matching | `POST /api/projects/{p}/analysis/run-ai` | ✅ | ✅ | ❌ | ❌ |

## 2. Modul Finansial & Cost Control
| Permission | Deskripsi | API Endpoints Affected | SA/PM | Est | SM | Log |
| :--- | :--- | :--- | :---: | :---: | :---: | :---: |
| `financials.view` | Melihat Harga Satuan | (Dampak pada filter JSON di semua modul) | ✅ | ✅ | ❌ | ❌ |
| `financials.view-report` | Dashboard Cost Control | `GET /api/projects/{p}/financial-stats` | ✅ | ❌ | ❌ | ❌ |
| `financials.manage` | Approval PR/PO | `POST /api/purchase-requests/{id}/approve` | ✅ | ❌ | ❌ | ❌ |

## 3. Modul RAB (Budget)
| Permission | Deskripsi | API Endpoints Affected | SA/PM | Est | SM | Log |
| :--- | :--- | :--- | :---: | :---: | :---: | :---: |
| `rab.view` | Melihat rincian RAB | `GET /api/projects/{p}/rab` | ✅ | ✅ | ✅ | ✅ |
| `rab.manage` | Kelola AHSP & RAB | `POST /api/projects/{p}/rab/import` | ✅ | ✅ | ❌ | ❌ |

## 4. Modul Jadwal (Schedule)
| Permission | Deskripsi | API Endpoints Affected | SA/PM | Est | SM | Log |
| :--- | :--- | :--- | :---: | :---: | :---: | :---: |
| `schedule.view` | Melihat S-Curve | `GET /api/projects/{p}/schedule/scurve` | ✅ | ✅ | ✅ | ✅ |
| `schedule.manage` | Edit Progress | `POST /api/projects/{p}/progress` | ✅ | ❌ | ✅ | ❌ |

## 5. Modul Pengadaan (Procurement)
| Permission | Deskripsi | API Endpoints Affected | SA/PM | Est | SM | Log |
| :--- | :--- | :--- | :---: | :---: | :---: | :---: |
| `procurement.view` | List PR/PO | `GET /api/purchase-requests`, `/api/purchase-orders` | ✅ | ✅ | ✅ | ✅ |
| `procurement.manage` | Create PR/PO | `POST /api/purchase-requests`, `/api/purchase-orders` | ✅ | ✅ | ✅ | ✅ |

## 6. Modul Permintaan Material (MR)
| Permission | Deskripsi | API Endpoints Affected | SA/PM | Est | SM | Log |
| :--- | :--- | :--- | :---: | :---: | :---: | :---: |
| `mr.view` | List MR | `GET /api/material-requests` | ✅ | ✅ | ✅ | ✅ |
| `mr.manage` | Create MR | `POST /api/material-requests` | ✅ | ❌ | ✅ | ✅ |

## 7. Modul Stok & Inventaris (Inventory)
| Permission | Deskripsi | API Endpoints Affected | SA/PM | Est | SM | Log |
| :--- | :--- | :--- | :---: | :---: | :---: | :---: |
| `inventory.view` | List Stok | `GET /api/inventory` | ✅ | ✅ | ✅ | ✅ |
| `inventory.manage` | Stock Opname | `POST /api/inventory/adjust` | ✅ | ❌ | ❌ | ✅ |

## 8. Modul File Proyek (Project Files)
| Permission | Deskripsi | API Endpoints Affected | SA/PM | Est | SM | Log |
| :--- | :--- | :--- | :---: | :---: | :---: | :---: |
| `files.view` | Download File | `GET /api/projects/{p}/files/{id}/download` | ✅ | ✅ | ✅ | ✅ |
| `files.upload` | Upload File | `POST /api/projects/{p}/files/upload` | ✅ | ✅ | ✅ | ✅ |
| `files.manage` | Delete File | `DELETE /api/projects/{p}/files/{id}` | ✅ | ❌ | ❌ | ❌ |

---
**Catatan Teknis**: 
- Implementasi API menggunakan `middleware('can:permission_name')` atau `Gate::authorize()` di dalam Controller.
- JSON Response akan secara dinamis menyembunyikan field `unit_price` dan `total_price` jika user tidak memiliki permission `financials.view`.
