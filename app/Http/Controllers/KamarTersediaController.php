<?php

namespace App\Http\Controllers;

use App\Repositories\Interface\KamarTersediaRepositoryInterface;
use Illuminate\Http\Request;

class KamarTersediaController extends Controller
{
    private $kamarTersediaRepository;

    public function __construct(KamarTersediaRepositoryInterface $kamarTersediaRepository)
    {
        $this->kamarTersediaRepository = $kamarTersediaRepository;
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            return response()->json(
                $this->kamarTersediaRepository->getDatatable($request)
            );
        }

        return view('room-info.available');
    }
}