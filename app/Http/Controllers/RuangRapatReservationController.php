<?php

namespace App\Http\Controllers;

// Import Model
use App\Models\RapatCustomer;
use App\Models\RapatTransaction;
use App\Models\RuangRapatPaket;
use App\Models\Transaction; // Digunakan untuk generate dummy invoice number

// Import class lain
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use App\Helpers\Helper; // Pastikan helper ada

class RuangRapatReservationController extends Controller
{
    /**
     * Session Key untuk menyimpan data sementara antar step
     */
    private $sessionKey = 'rapat_reservation';
    
    /**
     * Konfigurasi Harga Sewa Ruang per Jam
     */
    private $hargaSewaPerJam = 100000; 

    // =========================================================================
    // STEP 1: DATA CUSTOMER
    // =========================================================================
    public function showStep1_CustomerInfo()
    {
        $reservationData = Session::get($this->sessionKey, []);
        $customer = $reservationData['customer'] ?? null;
        
        return view('rapat.reservation.step1_customer', compact('customer'));
    }

    public function storeStep1_CustomerInfo(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'no_hp' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'instansi' => 'nullable|string|max:255',
        ]);

        Session::put($this->sessionKey . '.customer', $validated);

        return redirect()->route('rapat.reservation.showStep2');
    }

    // =========================================================================
    // STEP 2: DATA WAKTU & DURASI
    // =========================================================================
    public function showStep2_TimeInfo()
    {
        if (!Session::has($this->sessionKey . '.customer')) {
            return redirect()->route('rapat.reservation.showStep1')->with('error', 'Harap isi data diri terlebih dahulu.');
        }
        
        $reservationData = Session::get($this->sessionKey, []);
        $timeInfo = $reservationData['time'] ?? null;
        $customer = $reservationData['customer']; 

        return view('rapat.reservation.step2_time', compact('timeInfo', 'customer'));
    }

    public function storeStep2_TimeInfo(Request $request)
    {
        // 1. Validasi Format Input
        $validated = $request->validate([
            'tanggal_pemakaian' => 'required|date|after_or_equal:today',
            'waktu_mulai' => 'required|date_format:H:i',
            'waktu_selesai' => 'required|date_format:H:i|after:waktu_mulai',
            'durasi_jam' => 'required|integer|min:1|max:24', 
        ]);

        // 2. CEK BENTROK (OVERLAP CHECK)
        // Kita cari apakah ada reservasi lain di tanggal & jam yang sama
        $tanggal = $request->tanggal_pemakaian;
        $mulaiBaru = $request->waktu_mulai;
        $selesaiBaru = $request->waktu_selesai;

        $isBooked = RapatTransaction::where('tanggal_pemakaian', $tanggal)
            // Abaikan yang statusnya sudah dibatalkan
            ->where('status_reservasi', '!=', 'Canceled') 
            // Logika Overlap:
            // (Mulai Lama < Selesai Baru) DAN (Selesai Lama > Mulai Baru)
            ->where(function ($query) use ($mulaiBaru, $selesaiBaru) {
                $query->where('waktu_mulai', '<', $selesaiBaru)
                      ->where('waktu_selesai', '>', $mulaiBaru);
            })
            ->exists(); // Cek apakah ada datanya?

        // 3. Jika Bentrok, Kembalikan dengan Error
        if ($isBooked) {
            return redirect()->back()
                ->withInput() // Agar isian tidak hilang
                ->with('error', 'Maaf, Ruang Rapat sudah dipesan pada jam tersebut. Silakan pilih waktu lain.');
        }

        // 4. Jika Aman, Simpan ke Session
        Session::put($this->sessionKey . '.time', $validated);

        return redirect()->route('rapat.reservation.showStep3');
    }

    // =========================================================================
    // STEP 3: PILIH PAKET
    // =========================================================================
    public function showStep3_PaketInfo(Request $request)
    {
        if (!Session::has($this->sessionKey . '.time')) {
            return redirect()->route('rapat.reservation.showStep2')->with('error', 'Harap isi data waktu terlebih dahulu.');
        }

        $reservationData = Session::get($this->sessionKey);
        $timeInfo = $reservationData['time'];
        $customer = $reservationData['customer'];
        $selectedPaket = $reservationData['paket'] ?? null;
        
        $sort_name = $request->input('sort_name', 'harga');
        $sort_type = $request->input('sort_type', 'ASC');

        if (!in_array($sort_name, ['harga', 'name'])) $sort_name = 'harga';
        if (!in_array($sort_type, ['ASC', 'DESC'])) $sort_type = 'ASC';

        $pakets = RuangRapatPaket::orderBy($sort_name, $sort_type)->paginate(5);
        $paketsCount = $pakets->total();

        return view('rapat.reservation.step3_paket', compact(
            'pakets', 'paketsCount', 'timeInfo', 'customer', 'selectedPaket', 'sort_name', 'sort_type'
        ));
    }

    public function storeStep3_PaketInfo(Request $request)
    {
        $validated = $request->validate([
            'ruang_rapat_paket_id' => 'required|exists:ruang_rapat_pakets,id',
            'jumlah_peserta' => 'required|integer|min:20',
        ], [
            'jumlah_peserta.min' => 'Mohon maaf, minimal peserta rapat adalah 20 orang.'
        ]);

        Session::put($this->sessionKey . '.paket', $validated);

        return redirect()->route('rapat.reservation.showStep4');
    }

    // =========================================================================
    // STEP 4: KONFIRMASI & HITUNG BIAYA
    // =========================================================================
    public function showStep4_Confirmation()
    {
        if (!Session::has($this->sessionKey . '.paket')) {
            return redirect()->route('rapat.reservation.showStep3')->with('error', 'Harap pilih paket terlebih dahulu.');
        }

        $reservationData = Session::get($this->sessionKey);
        $customer = $reservationData['customer'];
        $timeInfo = $reservationData['time'];
        $paketInfo = $reservationData['paket'];

        $paket = RuangRapatPaket::findOrFail($paketInfo['ruang_rapat_paket_id']);

        $durasiJam = $timeInfo['durasi_jam']; 
        $jumlahOrang = $paketInfo['jumlah_peserta'];

        $biayaPaketTotal = $paket->harga * $jumlahOrang;
        $biayaSewaRuangTotal = $this->hargaSewaPerJam * $durasiJam;

        $subTotal = $biayaPaketTotal + $biayaSewaRuangTotal;
        $pajak = $subTotal * 0.10;
        $totalHarga = $subTotal + $pajak;

        Session::put($this->sessionKey . '.harga', $totalHarga);

        return view('rapat.reservation.step4_confirmation', compact(
            'customer', 
            'timeInfo', 
            'paket', 
            'paketInfo', 
            'totalHarga', 
            'durasiJam', 
            'biayaPaketTotal',
            'biayaSewaRuangTotal',
            'jumlahOrang',
            'pajak',
            'subTotal'
        ));
    }

    // =========================================================================
    // PREVIEW INVOICE DARI STEP 4 (SESSION)
    // =========================================================================
    public function previewInvoice(Request $request)
    {
        if (!Session::has($this->sessionKey . '.paket')) {
            return redirect()->route('rapat.reservation.showStep1')
                ->with('error', 'Sesi habis. Silakan ulangi reservasi.');
        }

        $data = Session::get($this->sessionKey);
        
        $paket = RuangRapatPaket::findOrFail($data['paket']['ruang_rapat_paket_id']);
        
        // Dummy Object Customer
        $customer = new \stdClass();
        $customer->nama = $data['customer']['nama'];
        $customer->instansi = $data['customer']['instansi'];
        $customer->no_hp = $data['customer']['no_hp'];
        $customer->email = $data['customer']['email'];

        $pax = $data['paket']['jumlah_peserta'];
        $duration = $data['time']['durasi_jam'];
        
        $biayaPaketTotal = $paket->harga * $pax;
        $biayaSewaRuangTotal = $this->hargaSewaPerJam * $duration;
        
        $subTotal = $biayaPaketTotal + $biayaSewaRuangTotal;
        $pajak = $subTotal * 0.10;
        $grandTotal = $subTotal + $pajak;

        $dateCode = Carbon::now()->format('dmY');
        $nextId = RapatTransaction::count() + 1; 
        $transactionCode = 'INV-RPT-' . $dateCode . '-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);

        return view('rapat.reservation.invoice_preview', [
            'transactionCode' => $transactionCode,
            'customer' => $customer,
            'paket' => $paket,
            'pax' => $pax,
            'date' => $data['time']['tanggal_pemakaian'],
            'duration' => $duration,
            'timeStart' => $data['time']['waktu_mulai'],
            'timeEnd' => $data['time']['waktu_selesai'],
            'biayaPaketTotal' => $biayaPaketTotal,
            'biayaSewaRuangTotal' => $biayaSewaRuangTotal,
            'subTotal' => $subTotal,
            'pajak' => $pajak,
            'grandTotal' => $grandTotal,
            'today' => Carbon::now()->isoFormat('D MMMM Y'),
            'user_name' => auth()->user()->name
        ]);
    }

    // =========================================================================
    // FINAL: PROSES PEMBAYARAN
    // =========================================================================
    public function processPayment(Request $request)
    {
        if (!Session::has($this->sessionKey . '.harga')) {
            return redirect()->route('rapat.reservation.showStep1')->with('error', 'Sesi reservasi habis/tidak lengkap.');
        }
        
        $data = Session::get($this->sessionKey);
        $totalTagihan = $data['harga'];

        $customer = RapatCustomer::create([
            'nama' => $data['customer']['nama'],
            'no_hp' => $data['customer']['no_hp'],
            'email' => $data['customer']['email'],
            'instansi' => $data['customer']['instansi'],
        ]);

        $transaction = RapatTransaction::create([
            'rapat_customer_id' => $customer->id,
            'ruang_rapat_paket_id' => $data['paket']['ruang_rapat_paket_id'],
            'tanggal_pemakaian' => $data['time']['tanggal_pemakaian'],
            'waktu_mulai' => $data['time']['waktu_mulai'],
            'waktu_selesai' => $data['time']['waktu_selesai'],
            'jumlah_peserta' => $data['paket']['jumlah_peserta'],
            'harga' => $totalTagihan,
            'total_pembayaran' => $totalTagihan,
            'status_pembayaran' => 'Paid',
            'status_reservasi' => 'Reservation', 
        ]);

        Session::forget($this->sessionKey);

        return redirect()->route('ruangrapat.index')
                         ->with('success', 'Reservasi Berhasil! Silakan Check In tamu saat acara dimulai.');
    }

    // =========================================================================
    // BATALKAN WIZARD RESERVASI (SESSION)
    // =========================================================================
    public function cancelReservation()
    {
        Session::forget($this->sessionKey);
        return redirect()->route('dashboard.index')->with('success', 'Reservasi dibatalkan.');
    }

    // =========================================================================
    // CHECK IN TAMU (DARI INDEX)
    // =========================================================================
    public function checkIn($id)
    {
        $transaction = RapatTransaction::findOrFail($id);

        if($transaction->status_reservasi == 'Reservation') {
            $transaction->update([
                'status_reservasi' => 'Check In' 
            ]);
            
            return response()->json(['message' => 'Berhasil Check In! Ruang rapat kini aktif digunakan.']);
        }

        return response()->json(['message' => 'Gagal, status bukan reservasi.'], 400);
    }

    // =========================================================================
    // HAPUS DATA RESERVASI PERMANEN (DARI INDEX - TOMBOL MERAH)
    // =========================================================================
    public function destroy($id)
    {
        $transaction = RapatTransaction::findOrFail($id);
        
        // Hapus Data
        $transaction->delete();

        // Optional: Jika ingin menghapus customer-nya juga (jika tidak dipakai di transaksi lain)
        // $transaction->rapatCustomer()->delete();

        return back()->with('success', 'Data reservasi berhasil dihapus permanen.');
    }

    // =========================================================================
    // CETAK INVOICE DARI DATABASE (UNTUK LAPORAN)
    // =========================================================================
    public function printInvoice($id)
    {
        // 1. Panggil relasi 'rapatCustomer' & 'ruangRapatPaket'
        $transaction = RapatTransaction::with(['rapatCustomer', 'ruangRapatPaket'])->findOrFail($id);
        
        // 2. Akses datanya
        $paket = $transaction->ruangRapatPaket;
        $customer = $transaction->rapatCustomer;

        // 3. Hitung Durasi
        $start = Carbon::parse($transaction->waktu_mulai);
        $end = Carbon::parse($transaction->waktu_selesai);
        $duration = $start->diffInHours($end);
        if ($duration < 1) $duration = 1;

        $pax = $transaction->jumlah_peserta;
        $biayaPaketTotal = $paket->harga * $pax;
        $biayaSewaRuangTotal = $this->hargaSewaPerJam * $duration;
        $subTotal = $biayaPaketTotal + $biayaSewaRuangTotal;
        $pajak = $subTotal * 0.10;
        $grandTotal = $subTotal + $pajak;

        $dateCode = Carbon::parse($transaction->created_at)->format('dmY');
        $transactionCode = 'INV-RPT-' . $dateCode . '-' . str_pad($transaction->id, 3, '0', STR_PAD_LEFT);

        return view('rapat.reservation.invoice_preview', [
            'transactionCode' => $transactionCode,
            'customer' => $customer,
            'paket' => $paket,
            'pax' => $pax,
            'date' => $transaction->tanggal_pemakaian,
            'duration' => $duration,
            'timeStart' => $transaction->waktu_mulai,
            'timeEnd' => $transaction->waktu_selesai,
            'biayaPaketTotal' => $biayaPaketTotal,
            'biayaSewaRuangTotal' => $biayaSewaRuangTotal,
            'subTotal' => $subTotal,
            'pajak' => $pajak,
            'grandTotal' => $grandTotal,
            'today' => Carbon::now()->isoFormat('D MMMM Y'),
            'user_name' => auth()->user()->name
        ]);
    }
}