<?php

namespace App\Http\Controllers;

use App\Models\Approval;
use App\Models\Type;
use App\Models\RuangRapatPaket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApprovalController extends Controller
{
    /**
     * Tampilkan Halaman HTML
     */
    public function index()
    {
        $pendingCount = Approval::where('status', 'pending')->count();
        return view('approval.index', compact('pendingCount'));
    }

    /**
     * Return JSON Data untuk DataTable (Route Khusus)
     */
    public function data(Request $request)
    {
        return $this->getApprovalsDatatable($request);
    }

    /**
     * Logic Pengambilan Data DataTable
     */
    private function getApprovalsDatatable($request)
    {
        $columns = [
            0 => 'id',
            1 => 'type',
            2 => 'item_name', // Virtual column
            3 => 'requester_name', // Virtual column
            4 => 'status',
            5 => 'created_at',
        ];

        // Parameter DataTable
        $limit = $request->input('length') != -1 ? $request->input('length') : 10;
        $start = $request->input('start') ?? 0;
        $orderIndex = $request->input('order.0.column') ?? 5;
        $order = $columns[$orderIndex] ?? 'created_at';
        $dir = $request->input('order.0.dir') ?? 'desc';
        $search = $request->input('search.value');

        // Query Utama
        $query = Approval::with(['requester', 'approver']);

        // Filter Status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Search
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'LIKE', "%{$search}%")
                  ->orWhere('type', 'LIKE', "%{$search}%")
                  ->orWhereHas('requester', function ($subQ) use ($search) {
                      $subQ->where('name', 'LIKE', "%{$search}%");
                  });
            });
        }

        $totalData = Approval::count();
        $totalFiltered = $query->count();

        // Ambil Data dari DB
        $models = $query->offset($start)
            ->limit($limit)
            ->orderBy($order, $dir)
            ->get();

        // Mapping Data (Safe Mode)
        $data = [];
        foreach ($models as $model) {
            try {
                // Cek item name secara aman
                $itemName = 'Unknown';
                if (method_exists($model, 'getApprovalModel')) {
                    $itemModel = $model->getApprovalModel();
                    $itemName = $itemModel ? ($itemModel->name ?? 'Tanpa Nama') : 'Item Terhapus';
                }

                $data[] = [
                    'id' => $model->id,
                    'type' => $model->type ?? '-',
                    'item_name' => $itemName,
                    'requester_name' => $model->requester->name ?? 'User Terhapus',
                    'status' => $model->status,
                    'created_at' => $model->created_at ? $model->created_at->format('d M Y H:i') : '-',
                ];
            } catch (\Exception $e) {
                Log::error("Error mapping approval ID {$model->id}: " . $e->getMessage());
                continue;
            }
        }

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalData,
            'recordsFiltered' => $totalFiltered,
            'data' => $data,
        ]);
    }

    /**
     * Show Detail (HTML Partial)
     */
    public function show(Approval $approval)
    {
        $approval->load(['requester', 'approver']);
        // Pastikan view 'approval.show' ada. Jika tidak, ganti return json biasa.
        $view = view('approval.show', compact('approval'))->render();
        return response()->json(['view' => $view]);
    }

    /**
     * Approve Logic
     */
    public function approve(Request $request, Approval $approval)
    {
        if (!$approval->isPending()) {
            return response()->json(['message' => 'Sudah diproses sebelumnya!'], 400);
        }

        try {
            DB::beginTransaction();
            $this->applyChanges($approval);
            $approval->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'notes' => $request->notes,
            ]);
            DB::commit();
            return response()->json(['message' => 'Berhasil di-approve!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Reject Logic
     */
    public function reject(Request $request, Approval $approval)
    {
        if (!$approval->isPending()) {
            return response()->json(['message' => 'Sudah diproses sebelumnya!'], 400);
        }

        $approval->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'notes' => $request->notes,
        ]);

        return response()->json(['message' => 'Berhasil ditolak!']);
    }

    /**
     * Apply Changes Helper
     */
    private function applyChanges(Approval $approval)
    {
        // 1. Logic untuk Tipe Kamar
        if ($approval->type === 'type') {
            $model = \App\Models\Type::find($approval->reference_id);
            if ($model) {
                $model->update($approval->new_data);
            }
        }

        // 2. Logic untuk Paket Ruang Rapat
        if ($approval->type === 'ruang_rapat_paket') {
            $model = \App\Models\RuangRapatPaket::find($approval->reference_id);
            if ($model) {
                $model->update($approval->new_data);
            }
        }

        // 3. [PERBAIKAN] Logic Baru untuk Kamar (Room)
        if ($approval->type === 'room') {
            $model = \App\Models\Room::find($approval->reference_id);
            if ($model) {
                $model->update($approval->new_data);
            }
        }
    }
}