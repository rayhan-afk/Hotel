<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTypeRequest;
use App\Models\Type;
use App\Models\Approval; // Ditambahkan dari File 2
use App\Repositories\Interface\TypeRepositoryInterface;
use Illuminate\Http\Request;

class TypeController extends Controller
{
    public function __construct(
        private TypeRepositoryInterface $typeRepository
    ) {
        $this->typeRepository = $typeRepository;
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->typeRepository->getTypesDatatable($request);
        }

        return view('type.index');
    }

    public function create()
    {
        $view = view('type.create')->render();

        return response()->json([
            'view' => $view,
        ]);
    }

    public function store(StoreTypeRequest $request)
    {
        $type = $this->typeRepository->store($request);

        return response()->json([
            'message' => 'Type '.$type->name.' created',
        ]);
    }

    public function edit(Type $type)
    {
        $view = view('type.edit', [
            'type' => $type,
        ])->render();

        return response()->json([
            'view' => $view,
        ]);
    }

    /**
     * Update method - Menggabungkan logika standar dan Approval System
     */
    public function update(Type $type, StoreTypeRequest $request)
    {
        $user = auth()->user();

        // === LOGIKA APPROVAL (Dari File 2) ===
        // Jika user BUKAN Manager atau Super, buat approval request
        if (!in_array($user->role, ['Manager', 'Super'])) {
            
            // Simpan data lama (sebelum edit)
            $oldData = [
                'name' => $type->name,
                'information' => $type->information,
            ];

            // Data baru (hasil edit)
            $newData = $request->only(['name', 'information']);

            // Buat approval request
            Approval::create([
                'type' => 'type',
                'reference_id' => $type->id,
                'requested_by' => $user->id,
                'old_data' => $oldData,
                'new_data' => $newData,
                'status' => 'pending',
            ]);

            return response()->json([
                'message' => 'Perubahan Tipe Kamar "'.$type->name.'" telah dikirim ke Manager untuk approval.',
                'requires_approval' => true,
            ]);
        }

        // === LANGSUNG UPDATE (Manager/Super) ===
        $type->update($request->all());

        return response()->json([
            'message' => 'Type '.$type->name.' updated!',
        ]);
    }

    public function destroy(Type $type)
    {
        try {
            $type->delete();

            return response()->json([
                'message' => 'Type '.$type->name.' deleted!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Type '.$type->name.' cannot be deleted! Error Code:'.$e->errorInfo[1],
            ], 500);
        }
    }
}