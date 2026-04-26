<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Order - {{ $po->po_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            background: #fff;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #eab308;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .company-info h1 {
            font-size: 24px;
            color: #1a1a1a;
            margin-bottom: 5px;
        }

        .company-info p {
            color: #666;
            font-size: 11px;
        }

        .po-title {
            text-align: right;
        }

        .po-title h2 {
            font-size: 20px;
            color: #eab308;
            margin-bottom: 5px;
        }

        .po-title .po-number {
            font-size: 16px;
            font-weight: bold;
            color: #1a1a1a;
        }

        .info-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .info-box {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 12px;
        }

        .info-box h3 {
            font-size: 10px;
            text-transform: uppercase;
            color: #888;
            margin-bottom: 8px;
            letter-spacing: 0.5px;
        }

        .info-box p {
            margin-bottom: 4px;
        }

        .info-box .value {
            font-weight: 600;
            color: #1a1a1a;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table thead {
            background: #1a1a1a;
            color: #fff;
        }

        table thead th {
            padding: 10px 12px;
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        table thead th.right {
            text-align: right;
        }

        table tbody td {
            padding: 10px 12px;
            border-bottom: 1px solid #e5e7eb;
        }

        table tbody td.right {
            text-align: right;
        }

        table tbody tr:nth-child(even) {
            background: #f9fafb;
        }

        .material-name {
            font-weight: 600;
        }

        .material-notes {
            font-size: 10px;
            color: #888;
            margin-top: 2px;
        }

        .totals {
            display: flex;
            justify-content: flex-end;
        }

        .totals-box {
            width: 280px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            overflow: hidden;
        }

        .totals-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 12px;
            border-bottom: 1px solid #e5e7eb;
        }

        .totals-row:last-child {
            border-bottom: none;
        }

        .totals-row.grand-total {
            background: #eab308;
            color: #1a1a1a;
            font-weight: bold;
            font-size: 14px;
        }

        .totals-row .label {
            color: #666;
        }

        .totals-row .value {
            font-weight: 600;
        }

        .notes-section {
            margin-top: 20px;
            padding: 12px;
            background: #fffbeb;
            border: 1px solid #fef3c7;
            border-radius: 6px;
        }

        .notes-section h4 {
            font-size: 11px;
            text-transform: uppercase;
            color: #92400e;
            margin-bottom: 8px;
        }

        .notes-section p {
            color: #78350f;
        }

        .footer {
            margin-top: 40px;
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
            text-align: center;
        }

        .signature-box {
            padding: 10px;
        }

        .signature-box .title {
            font-size: 11px;
            color: #888;
            margin-bottom: 50px;
        }

        .signature-box .line {
            border-top: 1px solid #333;
            padding-top: 5px;
        }

        .signature-box .name {
            font-weight: 600;
        }

        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #eab308;
            color: #1a1a1a;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
        }

        .print-btn:hover {
            background: #ca8a04;
        }

        @media print {
            .print-btn {
                display: none;
            }

            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
        }
    </style>
</head>

<body>
    <button class="print-btn" onclick="window.print()">🖨️ Print / PDF</button>

    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="company-info">
                <h1>{{ config('app.name', 'BEGE ProMan') }}</h1>
                <p>Sistem Manajemen Proyek Konstruksi</p>
            </div>
            <div class="po-title">
                <h2>PURCHASE ORDER</h2>
                <div class="po-number">{{ $po->po_number }}</div>
            </div>
        </div>

        <!-- Info Section -->
        <div class="info-section">
            <div class="info-box">
                <h3>Supplier</h3>
                <p class="value">{{ $po->supplier->name }}</p>
                <p>{{ $po->supplier->address }}</p>
                @if($po->supplier->phone)
                    <p>Telp: {{ $po->supplier->phone }}</p>
                @endif
                @if($po->supplier->email)
                    <p>Email: {{ $po->supplier->email }}</p>
                @endif
            </div>
            <div class="info-box">
                <h3>Detail Order</h3>
                <p><strong>Proyek:</strong> {{ $project->name }}</p>
                <p><strong>Tanggal Order:</strong> {{ $po->order_date->format('d F Y') }}</p>
                <p><strong>Pengiriman:</strong> {{ $po->expected_delivery->format('d F Y') }}</p>
                @if($po->payment_terms)
                    <p><strong>Pembayaran:</strong> {{ $po->payment_terms }}</p>
                @endif
            </div>
        </div>

        <!-- Items Table -->
        <table>
            <thead>
                <tr>
                    <th style="width: 40px;">No</th>
                    <th>Material</th>
                    <th class="right" style="width: 80px;">Qty</th>
                    <th style="width: 60px;">Satuan</th>
                    <th class="right" style="width: 120px;">Harga Satuan</th>
                    <th class="right" style="width: 130px;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($po->items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <div class="material-name">{{ $item->material->name }}</div>
                            @if($item->notes)
                                <div class="material-notes">{{ $item->notes }}</div>
                            @endif
                        </td>
                        <td class="right">{{ number_format($item->quantity, 2) }}</td>
                        <td>{{ $item->material->unit }}</td>
                        <td class="right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                        <td class="right">Rp {{ number_format($item->total_price, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div class="totals">
            <div class="totals-box">
                <div class="totals-row">
                    <span class="label">Subtotal</span>
                    <span class="value">Rp {{ number_format($po->subtotal, 0, ',', '.') }}</span>
                </div>
                @if($po->tax_amount > 0)
                    <div class="totals-row">
                        <span class="label">Pajak / PPN</span>
                        <span class="value">+ Rp {{ number_format($po->tax_amount, 0, ',', '.') }}</span>
                    </div>
                @endif
                @if($po->discount_amount > 0)
                    <div class="totals-row">
                        <span class="label">Diskon</span>
                        <span class="value" style="color: #16a34a;">- Rp
                            {{ number_format($po->discount_amount, 0, ',', '.') }}</span>
                    </div>
                @endif
                <div class="totals-row grand-total">
                    <span>TOTAL</span>
                    <span>Rp {{ number_format($po->total_amount, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Notes -->
        @if($po->notes)
            <div class="notes-section">
                <h4>Catatan</h4>
                <p>{{ $po->notes }}</p>
            </div>
        @endif

        <!-- Signature Section -->
        <div class="footer">
            <div class="signature-box">
                <div class="title">Dibuat Oleh</div>
                <div class="line">
                    <div class="name">{{ $po->createdBy->name }}</div>
                </div>
            </div>
            <div class="signature-box">
                <div class="title">Disetujui Oleh</div>
                <div class="line">
                    <div class="name">____________________</div>
                </div>
            </div>
            <div class="signature-box">
                <div class="title">Diterima Oleh (Supplier)</div>
                <div class="line">
                    <div class="name">____________________</div>
                </div>
            </div>
        </div>

        <p style="text-align: center; margin-top: 30px; color: #888; font-size: 10px;">
            Dicetak pada {{ now()->format('d F Y H:i') }} | {{ config('app.name') }}
        </p>
    </div>
</body>

</html>