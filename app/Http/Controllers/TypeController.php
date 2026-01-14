<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTypeRequest;
use App\Models\Type;
use App\Models\Approval;
use App\Models\TypePrice; 
use App\Models\Customer;
use App\Repositories\Interface\TypeRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            $data = $this->typeRepository->getTypesDatatable($request);
            return response()->json($data);
        }

        return view('type.index');
    }

    public function create()
    {
        $view = view('type.create')->render();
        return response()->json(['view' => $view]);
    }

    public function store(StoreTypeRequest $request)
    {
        $type = $this->typeRepository->store($request);
        return response()->json(['message' => 'Type '.$type->name.' created']);
    }

    public function edit(Type $type)
    {
        $view = view('type.edit', ['type' => $type])->render();
        return response()->json(['view' => $view]);
    }

    /**
     * Update method - Menggabungkan logika standar dan Approval System
     */
    public function update(Type $type, StoreTypeRequest $request)
    {
        $user = auth()->user();

        // === LOGIKA APPROVAL ===
        // Jika user BUKAN Manager atau Super, buat approval request
        if (!in_array($user->role, ['Manager', 'Super'])) {
            
            $oldData = [
                'name' => $type->name,
                'information' => $type->information,
            ];

            $newData = $request->only(['name', 'information']);

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
            return response()->json(['message' => 'Type '.$type->name.' deleted!']);
        } catch (\Exception $e) {
           // Handle Foreign Key Constraint (Data masih dipakai)
           if ($e->getCode() == "23000") {
                return response()->json([
                    'message' => 'Tipe kamar ini tidak bisa dihapus karena masih digunakan oleh Kamar atau memiliki riwayat Transaksi.'
                ], 409);
            }
            return response()->json(['message' => 'Database Error'], 500);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Server Error'], 500);
        }
    }

    // =========================================================================
    // API UNTUK MODAL HARGA (WeekDay vs WeekEnd)
    // =========================================================================

    public function getPrices($typeId)
    {
        // 1. Ambil Data Grup dari Database (Real Data)
        $dbGroups = Customer::select('customer_group')
                    ->whereNotNull('customer_group')
                    ->where('customer_group', '!=', '')
                    ->distinct()
                    ->pluck('customer_group')
                    ->toArray();
        
        // 2. Definisi Grup Standar (Yang WAJIB selalu muncul)
        $defaultGroups = ['WalkIn', 'OTA', 'Corporate', 'OwnerReferral'];

        // 3. Gabungkan keduanya dan hapus duplikat
        $groups = array_unique(array_merge($defaultGroups, $dbGroups));

        $data = [];
        foreach($groups as $group) {
            // Cek harga yang tersimpan di database untuk tipe kamar ini
            $price = TypePrice::where('type_id', $typeId)
                              ->where('customer_group', $group)
                              ->first();
                              
            $data[] = [
                'group'   => $group,
                'weekday' => $price ? $price->price_weekday : '', 
                'weekend' => $price ? $price->price_weekend : ''
            ];
        }
        
        return response()->json(array_values($data));
    }

    /**
     * Store Prices - Dengan Approval System untuk Admin
     */
    public function storePrices(Request $request)
    {
        $request->validate([
            'type_id' => 'required|exists:types,id',
            'prices'  => 'array'
        ]);

        $typeId = $request->type_id;
        $prices = $request->prices;
        $user = auth()->user();
        
        // === LOGIKA APPROVAL UNTUK HARGA ===
        // Jika user adalah Admin, buat approval request
        if ($user->role === 'Admin') {
            
            // Ambil data harga lama untuk perbandingan
            $oldPrices = [];
            foreach($prices as $group => $data) {
                $existingPrice = TypePrice::where('type_id', $typeId)
                                          ->where('customer_group', $group)
                                          ->first();
                
                $oldPrices[$group] = [
                    'weekday' => $existingPrice ? $existingPrice->price_weekday : null,
                    'weekend' => $existingPrice ? $existingPrice->price_weekend : null,
                ];
            }
            
            // Simpan ke approval table
            Approval::create([
                'type' => 'type_price', // Bedakan dengan 'type' untuk info kamar
                'reference_id' => $typeId,
                'requested_by' => $user->id,
                'old_data' => $oldPrices,
                'new_data' => $prices,
                'status' => 'pending',
            ]);
            
            $typeName = Type::find($typeId)->name;
            
            return response()->json([
                'success' => 'Perubahan harga tipe "'.$typeName.'" telah dikirim ke Manager untuk approval.',
                'requires_approval' => true
            ]);
        }

        // === LANGSUNG UPDATE (Manager/Super) ===
        DB::beginTransaction();
        try {
            foreach($prices as $group => $data) {
                $weekday = $data['weekday'] ?? null;
                $weekend = $data['weekend'] ?? null;

                // Jika input kosong semua, hapus data (Reset ke harga default kamar)
                if(empty($weekday) && empty($weekend)) {
                    TypePrice::where('type_id', $typeId)
                             ->where('customer_group', $group)
                             ->delete();
                    continue;
                }

                // Simpan atau Update
                TypePrice::updateOrCreate(
                    [
                        'type_id' => $typeId, 
                        'customer_group' => $group
                    ],
                    [
                        'price_weekday' => $weekday ?? 0,
                        'price_weekend' => $weekend ?? 0
                    ]
                );
            }
            
            DB::commit();
            return response()->json(['success' => 'Aturan harga berhasil diperbarui!']);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => 'Gagal menyimpan harga: ' . $e->getMessage()], 500);
        }
    }
}