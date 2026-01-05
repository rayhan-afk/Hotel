<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\IngredientStockOpname;
use Barryvdh\DomPDF\Facade\Pdf; // <--- 1. TAMBAHKAN INI

class LaporanStockopnameIngredients extends Controller
{
   public function exportPdf(Request $request)
    {
        $startDate = $request->input('start_date', date('Y-m-01'));
        $endDate   = $request->input('end_date', date('Y-m-d'));

        // Hapus withTrashed() kalau bikin error
        $histories = IngredientStockOpname::with('dataBahan') 
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->orderBy('created_at', 'desc')
                ->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.pdf_stok_ingredients', [
            'histories' => $histories,
            'startDate' => $startDate,
            'endDate'   => $endDate
        ]);
        
        $pdf->setPaper('a4', 'landscape');
        return $pdf->download('Laporan.pdf');
    }
       
}