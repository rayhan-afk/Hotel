@extends('template.master')
@section('title', 'Riwayat Transaksi POS')

@section('content')
<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4" style="color: #50200C">
        <h3><i class="fas fa-history me-2"></i>Riwayat Transaksi</h3>
        <a href="{{ route('pos.index') }}" class="btn btn-modal-close">
            <i class="fas fa-arrow-left me-1"></i> Kembali ke Kasir
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle table-soft-brown">
                    <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Tanggal</th>
                            <th>Total Belanja</th>
                            <th>Tunai</th>
                            <th>Kembalian</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $trx)
                        <tr>
                            <td>
                                <a href="javascript:void(0)" onclick="popupPrint('{{ $trx->invoice_number }}')" class="fw-bold" style="color: #50200C">
                                    {{ $trx->invoice_number }}
                                </a>
                            </td>
                            
                            <td>{{ $trx->created_at->format('d M Y H:i') }}</td>
                            <td class="fw-bold">Rp {{ number_format($trx->total_amount, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($trx->pay_amount, 0, ',', '.') }}</td>
                            <td class="" style="color: #F2C2B8">Rp {{ number_format($trx->change_amount, 0, ',', '.') }}</td>
                            
                            <td>
                                <button onclick="popupPrint('{{ $trx->invoice_number }}')" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-print me-1"></i> Cetak
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4" style="color: #50200C">Belum ada riwayat transaksi.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    function popupPrint(invoice) {
        // Arahkan ke route cetak yang sudah kita buat
        let url = "/pos/print/" + invoice;
        
        // Buka window baru dengan ukuran kecil (struk)
        window.open(url, "_blank", "width=400,height=600,top=100,left=100");
    }
</script>
@endsection