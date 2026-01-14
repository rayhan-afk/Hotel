<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Approval extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'reference_id',
        'requested_by',
        'old_data',
        'new_data',
        'status',
        'approved_by',
        'notes',
        'approved_at',
    ];

    protected $casts = [
        'old_data' => 'array',
        'new_data' => 'array',
        'approved_at' => 'datetime',
    ];

    // === RELATIONSHIPS ===
    
    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // === HELPER METHODS ===
    
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isApproved()
    {
        return $this->status === 'approved';
    }

    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    // Get model yang di-approve (Type atau RuangRapatPaket)
    public function getApprovalModel()
    {
        if ($this->type === 'type') {
            return Type::find($this->reference_id);
        }
        
        if ($this->type === 'ruang_rapat_paket') {
            return RuangRapatPaket::find($this->reference_id);
        }
         // --- TAMBAHAN BARU ---
        if ($this->type === 'room') {
            return Room::find($this->reference_id);
        }
        
        return null;
    }

    // Get nama yang friendly untuk ditampilkan
    public function getTypeName()
    {
        return match($this->type) {
            'type' => 'Tipe Kamar',
            'type_price' => 'Harga Tipe Kamar',
            'ruang_rapat_paket' => 'Paket Ruang Rapat',
            'room' => 'Kamar',
            default => 'Unknown'
        };
    }
}