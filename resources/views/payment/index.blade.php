@extends('template.master')
@section('title', 'Payment')
@section('content')

    <div class="card shadow-sm border">
        <div class="card-body">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Ruangan</th>
                        <th scope="col">Lunas</th>
                        <th scope="col">Status</th>
                        <th scope="col">di</th>
                        <th scope="col">Dilayani Oleh</th>
                        <th scope="col">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($payments as $payment)
                        <tr>
                            <th scope="row">{{ ($payments->currentpage() - 1) * $payments->perpage() + $loop->index + 1 }}
                            </th>
                            <td>{{ $payment->transaction->room->number }}</td>
                            <td>{{ Helper::convertToRupiah($payment->price) }}</td>
                            <td>{{ $payment->status }}</td>
                            <td>{{ Helper::dateFormatTime($payment->created_at) }}</td>
                            <td>{{ $payment->user->name }}</td>
                            <td> <a href="{{ route('payment.invoice', $payment->id) }}">Invoice</a> </td>
                        </tr>
                    @empty
                        <tr class="text-center">
                            <td colspan="6">Data pembayaran tidak ditemukan</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection
