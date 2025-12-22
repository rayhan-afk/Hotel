<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk - {{ $transaction->invoice_number }}</title>
    <style>
        /* Reset Default */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Courier New', Courier, monospace; }
        
        body {
            width: 58mm; /* Ukuran standar kertas thermal kecil */
            padding: 5px;
            font-size: 12px;
            color: #000;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .fw-bold { font-weight: bold; }
        .mb-1 { margin-bottom: 5px; }
        .border-bottom { border-bottom: 1px dashed #000; margin: 5px 0; }
        
        .items-table { width: 100%; margin-top: 10px; border-collapse: collapse; }
        .items-table td { padding: 2px 0; vertical-align: top; }
        
        .col-qty { width: 15%; }
        .col-item { width: 55%; }
        .col-price { width: 30%; text-align: right; }

        /* Sembunyikan tombol saat dicetak */
        @media print {
            .no-print { display: none; }
            @page { margin: 0; }
            body { margin: 0; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="text-center mb-1">
        <h3 class="fw-bold" style="font-size: 14px;">{{ $store['name'] }}</h3>
        <p>{{ $store['address'] }}</p>
        <p>Telp: {{ $store['phone'] }}</p>
    </div>

    <div class="border-bottom"></div>

    <div>
        <p>No: {{ $transaction->invoice_number }}</p>
        <p>Tgl: {{ date('d/m/Y H:i', strtotime($transaction->created_at)) }}</p>
        <p>Kasir: Admin</p> </div>

    <div class="border-bottom"></div>

    <table class="items-table">
        @foreach($transaction->details as $detail)
        <tr>
            <td class="col-qty">{{ $detail->qty }}x</td>
            <td class="col-item">
                {{ $detail->menu->name ?? 'Item Terhapus' }}
                @if($detail->qty > 1) 
                    <br><small class="text-muted">@ {{ number_format($detail->price, 0, ',', '.') }}</small>
                @endif
            </td>
            <td class="col-price">{{ number_format($detail->qty * $detail->price, 0, ',', '.') }}</td>
        </tr>
        @endforeach
    </table>

    <div class="border-bottom"></div>

    <table style="width: 100%">
        <tr>
            <td>Total</td>
            <td class="text-right fw-bold">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Bayar ({{ $transaction->payment_method }})</td>
            <td class="text-right">Rp {{ number_format($transaction->pay_amount, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Kembali</td>
            <td class="text-right">Rp {{ number_format($transaction->change_amount, 0, ',', '.') }}</td>
        </tr>
    </table>

    <div class="border-bottom"></div>

    <div class="text-center" style="margin-top: 10px;">
        <p>Terima Kasih</p>
        <p>Silakan Datang Kembali</p>
    </div>

    <button class="no-print" style="width: 100%; margin-top: 20px; padding: 10px;" onclick="window.close()">Tutup Window</button>

</body>
</html>