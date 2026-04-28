# Matriks Hak Akses (Permission Matrix) - ProMan5

Dokumen ini merinci hak akses untuk setiap peran (role) dalam sistem ProMan5, dikelompokkan berdasarkan modul fungsional, termasuk dampaknya pada API Endpoints.

## Peran Utama
1. **Superadmin / PM**: Kontrol penuh atas seluruh aspek proyek & finansial, termasuk approval semua dokumen.
2. **Estimator / QS**: Fokus pada perencanaan budget (RAB) dan analisis material. Memiliki akses ke harga satuan. **Tidak memiliki hak approval.**
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
| `financials.manage` | Pengaturan finansial strategis | - | ✅ | ❌ | ❌ | ❌ |

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

## 5. Modul Permintaan Material (MR)
| Permission | Deskripsi | API Endpoints Affected | SA/PM | Est | SM | Log |
| :--- | :--- | :--- | :---: | :---: | :---: | :---: |
| `mr.view` | Melihat daftar MR | `GET /api/material-requests` | ✅ | ✅ | ✅ | ✅ |
| `mr.manage` | Membuat/edit MR | `POST /api/material-requests` | ✅ | ❌ | ✅ | ✅ |
| `mr.approve` | Approve/reject MR | `POST /api/material-requests/{id}/approve` | ✅ | ❌ | ❌ | ❌ |

## 6. Modul Pengadaan (Procurement - PR/PO)
| Permission | Deskripsi | API Endpoints Affected | SA/PM | Est | SM | Log/Purch |
| :--- | :--- | :--- | :---: | :---: | :---: | :---: |
| `procurement.view` | Melihat daftar PR/PO | `GET /api/purchase-requests`, `GET /api/purchase-orders` | ✅ | ✅ | ✅ | ✅ |
| `procurement.manage` | Membuat PR/PO | `POST /api/purchase-requests`, `POST /api/purchase-orders` | ✅ | ❌ | ❌ | ✅ |
| `pr.approve` | Approve/reject PR | `POST /api/purchase-requests/{id}/approve` | ✅ | ❌ | ❌ | ❌ |
| `po.approve` | Approve/reject PO | `POST /api/purchase-orders/{id}/approve` | ✅ | ❌ | ❌ | ❌ |

## 7. Modul Penerimaan Barang (GR)
| Permission | Deskripsi | API Endpoints Affected | SA/PM | Est | SM | Log |
| :--- | :--- | :--- | :---: | :---: | :---: | :---: |
| `gr.view` | Melihat daftar GR | `GET /api/goods-receipts` | ✅ | ✅ | ✅ | ✅ |
| `gr.create` | Membuat GR | `POST /api/goods-receipts` | ✅ | ❌ | ❌ | ✅ |
| `gr.approve` | Approve/reject GR | `POST /api/goods-receipts/{id}/approve` | ✅ | ❌ | ❌ | ❌ |

## 8. Modul Stok & Inventaris (Inventory)
| Permission | Deskripsi | API Endpoints Affected | SA/PM | Est | SM | Log |
| :--- | :--- | :--- | :---: | :---: | :---: | :---: |
| `inventory.view` | List Stok | `GET /api/inventory` | ✅ | ✅ | ✅ | ✅ |
| `inventory.manage` | Stock Opname | `POST /api/inventory/adjust` | ✅ | ❌ | ❌ | ✅ |

## 9. Modul File Proyek (Project Files)
| Permission | Deskripsi | API Endpoints Affected | SA/PM | Est | SM | Log |
| :--- | :--- | :--- | :---: | :---: | :---: | :---: |
| `files.view` | Download File | `GET /api/projects/{p}/files/{id}/download` | ✅ | ✅ | ✅ | ✅ |
| `files.upload` | Upload File | `POST /api/projects/{p}/files/upload` | ✅ | ✅ | ✅ | ✅ |
| `files.manage` | Delete File | `DELETE /api/projects/{p}/files/{id}` | ✅ | ❌ | ❌ | ❌ |

---

## Kebijakan Approval (Segregation of Duties)

1. **Self-approval dilarang**: Pembuat dokumen tidak dapat menyetujui dokumen yang dibuatnya sendiri.
2. **Permission approval terpisah**: Setiap modul memiliki permission `.approve` yang terpisah dari `.manage`.
3. **Multi-level approval**: Didukung melalui `ApprovalMatrix` yang dinamis per document type.
4. **Amount-based routing** (PO): Level approval dapat disesuaikan berdasarkan `min_amount`.

---
**Catatan Teknis**: 
- Implementasi API menggunakan `middleware('can:permission_name')` atau `Gate::authorize()` di dalam Controller.
- JSON Response akan secara dinamis menyembunyikan field `unit_price` dan `total_price` jika user tidak memiliki permission `financials.view`.
- Approval workflow dikelola oleh `ApprovalService` yang terintegrasi dengan `ApprovalMatrix`.
