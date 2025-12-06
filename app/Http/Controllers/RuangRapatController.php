<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRuangRapatPaketRequest;
use App\Models\RuangRapatPaket;
use App\Models\Approval; // Ditambahkan dari File 2
use App\Repositories\Interface\RuangRapatPaketRepositoryInterface;
use Illuminate\Http\Request;
use App\Models\RapatTransaction;
use Carbon\Carbon;

class RuangRapatController extends Controller
{
    private $ruangRapatPaketRepository;

    public function __construct(RuangRapatPaketRepositoryInterface $ruangRapatPaketRepository)
    {
        $this->ruangRapatPaketRepository = $ruangRapatPaketRepository;
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->ruangRapatPaketRepository->getPaketsDatatable($request);
        }

        $now = Carbon::now();
        // Format Y-m-d H:i:s adalah kunci untuk perbandingan di MySQL
        $nowFormatted = $now->format('Y-m-d H:i:s'); 
        
        $baseQuery = RapatTransaction::with('rapatCustomer', 'ruangRapatPaket');

        if ($request->filled('search')) {
            $baseQuery->where(function($q) use ($request) {
                $q->where('id', $request->input('search'))
                  ->orWhereHas('rapatCustomer', function ($subQ) use ($request) {
                      $subQ->where('nama', 'like', '%' . $request->input('search') . '%');
                  });
            });
        }
        
        // =======================================================================
        // 1. RESERVASI BERLANGSUNG (SEDANG TERJADI SEKARANG)
        // Dimulai SEBELUM/SAAT INI ANDA LIHAT HALAMAN DAN Selesai SETELAH/SAAT INI
        // =======================================================================
        $rapatTransactionsBerlangsung = $baseQuery->clone()
            ->whereRaw("CONCAT(tanggal_pemakaian, ' ', waktu_mulai) <= ?", [$nowFormatted])
            ->whereRaw("CONCAT(tanggal_pemakaian, ' ', waktu_selesai) > ?", [$nowFormatted]) // > agar tidak tumpang tindih dengan Selesai
            ->orderBy('tanggal_pemakaian', 'ASC')
            ->orderBy('waktu_mulai', 'ASC')
            ->paginate(10, ['*'], 'berlangsung_page');

        // =======================================================================
        // 2. JADWAL RESERVASI (BELUM DIMULAI)
        // Dimulai STRICTLY SETELAH waktu saat ini
        // =======================================================================
        $rapatTransactionsJadwal = $baseQuery->clone()
            ->whereRaw("CONCAT(tanggal_pemakaian, ' ', waktu_mulai) > ?", [$nowFormatted])
            ->orderBy('tanggal_pemakaian', 'ASC')
            ->orderBy('waktu_mulai', 'ASC')
            ->paginate(10, ['*'], 'jadwal_page');

        // =======================================================================
        // 3. RESERVASI SELESAI (SUDAH BERAKHIR)
        // Selesai STRICTLY SEBELUM waktu saat ini
        // =======================================================================
        $rapatTransactionsExpired = $baseQuery->clone()
            ->whereRaw("CONCAT(tanggal_pemakaian, ' ', waktu_selesai) <= ?", [$nowFormatted])
            ->orderBy('tanggal_pemakaian', 'DESC')
            ->orderBy('waktu_selesai', 'DESC')
            ->paginate(10, ['*'], 'expired_page');

        return view('ruangrapat.index', compact(
            'rapatTransactionsJadwal', 
            'rapatTransactionsBerlangsung',
            'rapatTransactionsExpired'
        ));
    }

    // --- PERBAIKAN METHOD CREATE ---
    public function create()
    {
        // render() mengubah view menjadi string HTML
        $view = view('ruangrapat.create')->render();

        // Kirim sebagai JSON agar bisa ditangkap oleh JavaScript
        return response()->json([
            'view' => $view,
        ]);
    }

    public function store(StoreRuangRapatPaketRequest $request)
    {
        RuangRapatPaket::create($request->validated());

        return response()->json([
            'message' => 'Paket berhasil ditambahkan!',
        ]);
    }

    public function show(RuangRapatPaket $ruangrapat)
    {
        return view('ruangrapat.show', compact('ruangrapat'));
    }

    // --- PERBAIKAN METHOD EDIT ---
    public function edit(RuangRapatPaket $ruangrapat)
    {
        // render() mengubah view menjadi string HTML
        $view = view('ruangrapat.edit', [
            'ruangrapat' => $ruangrapat
        ])->render();

        return response()->json([
            'view' => $view,
        ]);
    }

    /**
     * Update method - Menggabungkan logika standar dan Approval System
     */
    public function update(StoreRuangRapatPaketRequest $request, RuangRapatPaket $ruangrapat)
    {
        $user = auth()->user();

        // === LOGIKA APPROVAL (Dari File 2) ===
        // Jika user BUKAN Manager atau Super, buat approval request
        // Pastikan field 'role' ada di tabel users Anda
        if (!in_array($user->role, ['Manager', 'Super'])) {
            
            // Simpan data lama
            $oldData = [
                'name' => $ruangrapat->name,
                'isi_paket' => $ruangrapat->isi_paket,
                'fasilitas' => $ruangrapat->fasilitas,
                'harga' => $ruangrapat->harga,
            ];

            // Data baru
            $newData = $request->only(['name', 'isi_paket', 'fasilitas', 'harga']);

            // Buat approval request
            Approval::create([
                'type' => 'ruang_rapat_paket',
                'reference_id' => $ruangrapat->id,
                'requested_by' => $user->id,
                'old_data' => $oldData,
                'new_data' => $newData,
                'status' => 'pending',
            ]);

            return response()->json([
                'message' => 'Perubahan Paket "'.$ruangrapat->name.'" telah dikirim ke Manager untuk approval.',
                'requires_approval' => true,
            ]);
        }

        // === LANGSUNG UPDATE (Manager/Super - Dari File 1 & 2) ===
        $ruangrapat->update($request->validated());

        return response()->json([
            'message' => 'Paket berhasil diperbarui!',
        ]);
    }

    public function destroy(RuangRapatPaket $ruangrapat)
    {
        try {
            $ruangrapat->delete();
            return response()->json(['message' => 'Paket berhasil dihapus!']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal menghapus paket!'], 500);
        }
    }

    public function cancelReservation(RapatTransaction $rapatTransaction)
    {
        try {
            // Hapus data reservasi
            $rapatTransaction->delete();
            
            // Redirect kembali ke halaman index ruang rapat dengan pesan sukses
            return redirect()->route('ruangrapat.index')
                             ->with('success', 'Reservasi ID ' . $rapatTransaction->id . ' berhasil dibatalkan.');
        } catch (\Exception $e) {
            // Jika gagal, redirect kembali dengan pesan error
            return redirect()->route('ruangrapat.index')
                             ->with('error', 'Gagal membatalkan reservasi.');
        }
    }
}