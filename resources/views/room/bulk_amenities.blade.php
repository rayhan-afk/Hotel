{{-- Note: Tidak perlu @extends karena ini Partial View untuk Modal --}}

<form method="POST" action="{{ route('room.bulk_amenities.update') }}" id="formBulkAmenities">
    @csrf
    
    <div class="alert alert-info border-0 shadow-sm mb-3">
        <small><i class="fas fa-info-circle me-1"></i> Perubahan angka di sini akan diterapkan ke <strong>SEMUA KAMAR</strong> berdasarkan tipenya.</small>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-sm table-hover text-center" style="font-size: 0.9rem;">
            <thead class="bg-light">
                <tr>
                    <th class="align-middle text-start ps-3">Tipe Kamar</th>
                    @foreach($amenities as $amenity)
                        <th class="align-middle">
                            {{ $amenity->name }} <br>
                            <span class="text-muted" style="font-size: 0.75rem;">({{ $amenity->satuan }})</span>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($types as $type)
                    <tr>
                        <td class="align-middle text-start fw-bold ps-3 bg-light">
                            {{ $type->name }}
                        </td>
                        
                        {{-- Ambil sampel data --}}
                        @php $sampleRoom = $type->rooms->first(); @endphp

                        @foreach($amenities as $amenity)
                            @php
                                $amount = 0;
                                if ($sampleRoom) {
                                    $existing = $sampleRoom->amenities->where('id', $amenity->id)->first();
                                    $amount = $existing ? $existing->pivot->amount : 0;
                                }
                            @endphp
                            <td class="p-1">
                                <input type="number" min="0" 
                                       name="items[{{ $type->id }}][{{ $amenity->id }}]" 
                                       value="{{ $amount }}" 
                                       class="form-control form-control-sm text-center border-0 fw-bold" 
                                       style="background-color: {{ $amount > 0 ? '#e8f5e9' : '#fff' }}; color: {{ $amount > 0 ? '#1b5e20' : '#ccc' }};">
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Tombol Simpan dipindah ke sini agar masuk dalam tag FORM --}}
    <div class="text-end mt-3">
        <button type="submit" class="btn btn-info text-white fw-bold">
            <i class="fas fa-save me-1"></i> Simpan Perubahan
        </button>
    </div>
</form>