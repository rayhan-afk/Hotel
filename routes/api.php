<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Inventory\IngredientController;
use App\Http\Controllers\Inventory\IngredientTransactionController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::prefix('ingredients')->group(function () {
    Route::get('/', [IngredientController::class, 'index']); 
    Route::post('/', [IngredientController::class, 'store']); 
    Route::get('/{id}', [IngredientController::class, 'show']); 
    Route::put('/{id}', [IngredientController::class, 'update']); 
    Route::delete('/{id}', [IngredientController::class, 'destroy']); 
    Route::post('/{id}/transaction', [IngredientTransactionController::class, 'store']);
});

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
