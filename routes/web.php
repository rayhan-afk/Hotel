<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\AmenityController;
use App\Http\Controllers\ApprovalController; 
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChartController;
use App\Http\Controllers\CheckinController;
use App\Http\Controllers\CheckoutController; 
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FacilityController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\IngredientController;
use App\Http\Controllers\KamarDibersihkanController;
use App\Http\Controllers\KamarTersediaController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\LaporanKamarController; 
use App\Http\Controllers\LaporanPosController;
use App\Http\Controllers\LaporanStockopnameAmenities;
use App\Http\Controllers\LaporanStockopnameIngredients;
use App\Http\Controllers\NotificationsController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\POSController;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\ReservasiKamarController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\RoomStatusController;
use App\Http\Controllers\RuangRapatController;
use App\Http\Controllers\RuangRapatReservationController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\TransactionRoomReservationController;
use App\Http\Controllers\TypeController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;

/*
|--------------------------------------------------------------------------
| CAPTCHA GENERATOR
|--------------------------------------------------------------------------
*/
Route::get('/captcha/generate', function (Request $request) {
    $captcha_code = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyz"), 0, 5);
    Session::put('captcha_code', strtoupper($captcha_code));

    $image = imagecreate(150, 40);
    $background = imagecolorallocate($image, 255, 255, 255);
    $text_color = imagecolorallocate($image, 0, 0, 0);

    for ($i = 0; $i < 5; $i++) {
        imageline($image, 0, rand() % 40, 150, rand() % 40, $text_color);
    }

    imagestring($image, 5, 40, 12, strtoupper($captcha_code), $text_color);
    ob_start();
    imagepng($image);
    $contents = ob_get_clean();
    imagedestroy($image);

    return response($contents)->header('Content-type', 'image/png');
})->name('captcha.generate');


/*
|--------------------------------------------------------------------------
| AUTHENTICATION ROUTES (Guest Only)
|--------------------------------------------------------------------------
*/
Route::view('/login', 'auth.login')->name('login.index');
Route::post('/login', [AuthController::class, 'login'])->name('login');

Route::group(['middleware' => 'guest'], function () {
    Route::get('/forgot-password', fn () => view('auth.passwords.email'))->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.email');
    Route::get('/reset-password/{token}', fn (string $token) => view('auth.reset-password', ['token' => $token]))->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});


/*
|--------------------------------------------------------------------------
| ROLE: HOUSEKEEPING (Amenities Only) 🆕
| Hanya Super dan Housekeeping yang bisa akses amenities
|--------------------------------------------------------------------------
*/
// Route::group(['middleware' => ['auth', 'checkRole:Super,Housekeeping,Manager,Admin']], function () {
//     // Resource Amenities
//    // 1. Taruh Custom Route PALING ATAS (Sebelum Resource)
// Route::post('/amenity/stock-opname', [App\Http\Controllers\AmenityController::class, 'stockOpname'])->name('amenity.stock-opname');
// Route::get('/laporan/amenities/pdf', [LaporanStockopnameAmenities::class, 'exportPdf'])
//         ->name('laporan.amenities.pdf');
// // 2. Baru kemudian Route Resource
// Route::resource('amenity', App\Http\Controllers\AmenityController::class);
//     // Route untuk melihat halaman riwayat
//     Route::get('/amenities/history', [AmenityController::class, 'history'])->name('amenities.history');
//     Route::post('/logout', [AuthController::class, 'logout'])->name('logout.housekeeping');
    
   
// });
/*
|--------------------------------------------------------------------------
| ROLE: AMENITIES (Format Meniru Bahan Baku/Ingredient)
| Akses: Super, Housekeeping, Manager, Admin
|--------------------------------------------------------------------------
*/

// 1. Route PDF (Ditaruh di luar/sendiri seperti Ingredient)
Route::get('/laporan/amenities/pdf', [LaporanStockopnameAmenities::class, 'exportPdf'])
    ->middleware(['auth', 'checkRole:Super,Housekeeping,Manager,Admin'])
    ->name('laporan.amenities.pdf');

// 2. Route Resource (Langsung ditempel middleware chain)
Route::resource('amenity', App\Http\Controllers\AmenityController::class)
    ->middleware(['auth', 'checkRole:Super,Housekeeping,Manager,Admin'])
    ->names('amenity');

// 3. Custom Route: Stock Opname
Route::post('/amenity/stock-opname', [App\Http\Controllers\AmenityController::class, 'stockOpname'])
    ->middleware(['auth', 'checkRole:Super,Housekeeping,Manager,Admin'])
    ->name('amenity.stock-opname');

// 4. Custom Route: History
Route::get('/amenities/history', [AmenityController::class, 'history'])
    ->middleware(['auth', 'checkRole:Super,Housekeeping,Manager,Admin'])
    ->name('amenities.history');

// 5. Logout Khusus (Opsional)
Route::post('/logout-housekeeping', [AuthController::class, 'logout'])
    ->name('logout.housekeeping');

/*
|--------------------------------------------------------------------------
| ROLE: SUPER + MANAGER (User Management & Approval)
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => ['auth', 'checkRole:Super,Manager']], function () {
    Route::resource('user', UserController::class)->except(['show']);
    
    // Approval Management
    Route::group(['prefix' => 'approval', 'as' => 'approval.'], function () {
        Route::get('/', [ApprovalController::class, 'index'])->name('index');
        Route::get('/data', [ApprovalController::class, 'data'])->name('data');
        Route::get('/{approval}', [ApprovalController::class, 'show'])->name('show');
        Route::post('/{approval}/approve', [ApprovalController::class, 'approve'])->name('approve');
        Route::post('/{approval}/reject', [ApprovalController::class, 'reject'])->name('reject');
    });
});


/*
|--------------------------------------------------------------------------
| USER.SHOW TAMBAHAN (AGAR ROUTE user.show TIDAK ERROR)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/user/{user}', [UserController::class, 'show'])->name('user.show');
});


/*
|--------------------------------------------------------------------------
| ROLE: SUPER + DAPUR (Bahan Baku / Ingredients)
|--------------------------------------------------------------------------
*/
Route::get('/ingredient/laporan/pdf', [LaporanStockopnameIngredients::class, 'exportPdf'])
    ->name('laporan.ingredients.pdf');
    
Route::resource('ingredient', IngredientController::class)
    ->middleware(['auth', 'checkRole:Super,Dapur'])
    ->names('ingredient');
Route::post('/ingredients/opname', [IngredientController::class, 'storeOpname'])->name('ingredients.opname');
Route::get('/ingredients/opname-history', [App\Http\Controllers\IngredientController::class, 'history'])->name('ingredients.history');


// POS Routes
/*
|--------------------------------------------------------------------------
| ROLE: KASIR + SUPER + ADMIN (Operasional Kasir)
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => ['auth', 'checkRole:Super,Admin,Kasir']], function () {
    Route::get('/pos', [POSController::class, 'index'])->name('pos.index');
    Route::post('/pos/store', [POSController::class, 'store'])->name('pos.store');
    Route::get('/pos/history', [POSController::class, 'history'])->name('pos.history');
    Route::get('/pos/print/{invoice}', [POSController::class, 'printStruk'])->name('pos.print');
});
// Halaman Utama
// Recipe Routes
Route::get('/recipes', [RecipeController::class, 'index'])->name('recipes.index');
Route::get('/recipes/get/{menuId}', [RecipeController::class, 'getRecipe'])->name('recipes.get');
Route::post('/recipes/update', [RecipeController::class, 'updateApi'])->name('recipes.updateApi');
Route::post('/recipes/create-menu', [RecipeController::class, 'createMenu'])->name('recipes.createMenu');
// --- TAMBAHKAN INI ---
// Route untuk Edit Identitas Menu (Nama, Harga, Gambar)
Route::get('/recipes/edit-menu/{id}', [RecipeController::class, 'editMenu'])->name('recipes.editMenu');
Route::put('/recipes/update-menu/{id}', [RecipeController::class, 'updateMenu'])->name('recipes.updateMenu');
// Route untuk Hapus Menu
Route::delete('/recipes/delete-menu/{id}', [RecipeController::class, 'destroyMenu'])->name('recipes.destroyMenu');

/*
|--------------------------------------------------------------------------
| ROLE: KASIR + MANAGER + SUPER (Laporan Kasir)
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => ['auth', 'checkRole:Super,Manager,Kasir']], function () {
    Route::prefix('laporan')->name('laporan.')->group(function () {
        Route::get('/pos', [LaporanPosController::class, 'index'])->name('pos.index');
        Route::get('/pos/export', [LaporanPosController::class, 'exportExcel'])->name('pos.export');
    });
});
/*
|--------------------------------------------------------------------------
| ROLE: SUPER + ADMIN + MANAGER (Operasional Utama)
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => ['auth', 'checkRole:Super,Admin,Manager']], function () {

    // Upload & Delete Gambar
    Route::post('/room/{room}/image/upload', [ImageController::class, 'store'])->name('image.store');
    Route::delete('/image/{image}', [ImageController::class, 'destroy'])->name('image.destroy');

    // Transaksi Reservasi Kamar
    Route::name('transaction.reservation.')->group(function () {
        Route::get('/createIdentity', [TransactionRoomReservationController::class, 'createIdentity'])->name('createIdentity');
        Route::get('/pickFromCustomer', [TransactionRoomReservationController::class, 'pickFromCustomer'])->name('pickFromCustomer');
        Route::post('/storeCustomer', [TransactionRoomReservationController::class, 'storeCustomer'])->name('storeCustomer');
        Route::get('/{customer}/viewCountPerson', [TransactionRoomReservationController::class, 'viewCountPerson'])->name('viewCountPerson');
        Route::get('/{customer}/chooseRoom', [TransactionRoomReservationController::class, 'chooseRoom'])->name('chooseRoom');
        Route::get('/{customer}/{room}/{from}/{to}/confirmation', [TransactionRoomReservationController::class, 'confirmation'])->name('confirmation');
        Route::post('/{customer}/{room}/payDownPayment', [TransactionRoomReservationController::class, 'payDownPayment'])->name('payDownPayment');
        
        // Preview Invoice Kamar
        Route::get('/{customer}/{room}/{from}/{to}/preview-invoice', 
            [TransactionRoomReservationController::class, 'previewInvoice']
        )->name('previewInvoice');

    });

    // === TAMBAHKAN KODE INI ===
    Route::get('/transaction/invoice/{transaction}', [TransactionRoomReservationController::class, 'printInvoice'])
        ->name('transaction.invoice.print');

    // Resource Controllers
    Route::resource('customer', CustomerController::class);
    Route::resource('type', TypeController::class);
    // [BARU] Route untuk API Modal Harga (Weekday vs Weekend)
    // === [PASTE DISINI] ===
    // Route Khusus untuk Fitur Harga Dinamis (Sultan Mode)
    Route::get('/type/get-prices/{id}', [TypeController::class, 'getPrices'])->name('type.getPrices');
    Route::post('/type/store-prices', [TypeController::class, 'storePrices'])->name('type.storePrices');

    // === [PASTE DISINI: SETUP AMENITIES MASSAL] ===
    // Harus ditaruh SEBELUM Resource Room biar tidak error dianggap ID
    Route::get('room/setup-amenities', [RoomController::class, 'bulkAmenities'])->name('room.bulk_amenities');
    Route::post('room/setup-amenities', [RoomController::class, 'bulkAmenitiesUpdate'])->name('room.bulk_amenities.update');
    // ==============================================
    // ========================
    Route::resource('room', RoomController::class);
    Route::resource('roomstatus', RoomStatusController::class);
    Route::resource('facility', FacilityController::class);
    // CATATAN: Route amenity sudah dipindah ke group Housekeeping di atas ✅

    /*
    |--------------------------------------------------------------------------
    | RUANG RAPAT
    |--------------------------------------------------------------------------
    */
    Route::resource('ruangrapat', RuangRapatController::class);

    // [BARU] Route Hapus Data Reservasi (Modal Merah di Index)
    Route::delete('/transaction/rapat/delete/{id}', [RuangRapatReservationController::class, 'destroy'])
        ->name('rapat.transaction.destroy');

    // History Pembayaran Rapat
    Route::get('/rapat/payments', [RuangRapatController::class, 'paymentHistory'])->name('rapat.payment.index');

    // Group Wizard Reservasi Rapat
    Route::group(['prefix' => 'rapat/reservasi', 'as' => 'rapat.reservation.'], function () {
        Route::get('/step-1', [RuangRapatReservationController::class, 'showStep1_CustomerInfo'])->name('showStep1');
        Route::post('/step-1', [RuangRapatReservationController::class, 'storeStep1_CustomerInfo'])->name('storeStep1');
        Route::get('/step-2', [RuangRapatReservationController::class, 'showStep2_TimeInfo'])->name('showStep2');
        Route::post('/step-2', [RuangRapatReservationController::class, 'storeStep2_TimeInfo'])->name('storeStep2');
        Route::get('/step-3', [RuangRapatReservationController::class, 'showStep3_PaketInfo'])->name('showStep3');
        Route::post('/step-3', [RuangRapatReservationController::class, 'storeStep3_PaketInfo'])->name('storeStep3');
        Route::get('/step-4', [RuangRapatReservationController::class, 'showStep4_Confirmation'])->name('showStep4');
        Route::post('/bayar', [RuangRapatReservationController::class, 'processPayment'])->name('processPayment');
        
        // Cancel Wizard (Clear Session)
        Route::get('/cancel', [RuangRapatReservationController::class, 'cancelReservation'])->name('cancel');
    });

    // Preview Invoice (Sebelum Bayar)
    Route::get('/transaction/rapat/preview-invoice', [RuangRapatReservationController::class, 'previewInvoice'])
        ->name('rapat.reservation.previewInvoice');
    
    // Print Invoice (Dari Laporan)
    Route::get('/laporan/rapat/invoice/{id}', [RuangRapatReservationController::class, 'printInvoice'])
        ->name('rapat.invoice.print');

    /*
    |--------------------------------------------------------------------------
    | LAPORAN
    |--------------------------------------------------------------------------
    */
    Route::prefix('laporan')->name('laporan.')->group(function () {
        // Laporan Ruang Rapat
        Route::get('/rapat', [LaporanController::class, 'laporanRuangRapat'])->name('rapat.index');
        Route::get('/rapat/export', [LaporanController::class, 'exportExcel'])->name('rapat.export');

        // Laporan Kamar
        Route::get('/kamar', [LaporanKamarController::class, 'index'])->name('kamar.index'); 
        Route::get('/kamar/export', [LaporanKamarController::class, 'exportExcel'])->name('kamar.export');
        
    });

    /*
    |--------------------------------------------------------------------------
    | PAYMENT & CHART
    |--------------------------------------------------------------------------
    */
    Route::get('/payment', [PaymentController::class, 'index'])->name('payment.index');
    Route::get('/payment/{payment}/invoice', [PaymentController::class, 'invoice'])->name('payment.invoice');
    Route::get('/transaction/{transaction}/payment/create', [PaymentController::class, 'create'])->name('transaction.payment.create');
    Route::post('/transaction/{transaction}/payment/store', [PaymentController::class, 'store'])->name('transaction.payment.store');

    Route::get('/get-dialy-guest-chart-data', [ChartController::class, 'dailyGuestPerMonth']);
    Route::get('/get-dialy-guest/{year}/{month}/{day}', [ChartController::class, 'dailyGuest'])->name('chart.dailyGuest');
});


/*
|--------------------------------------------------------------------------
| ROLE: SEMUA YANG LOGIN (Akses Umum & Monitoring)
| CATATAN: Housekeeping khusus untuk room-info.cleaning saja
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => ['auth', 'checkRole:Super,Admin,Customer,Manager,Dapur,Housekeeping,Kasir']], function () {

    // Dashboard & Auth
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Notifications
    Route::view('/notification', 'notification.index')->name('notification.index');
    Route::get('/mark-all-as-read', [NotificationsController::class, 'markAllAsRead'])->name('notification.markAllAsRead');
    Route::get('/notification-to/{id}', [NotificationsController::class, 'routeTo'])->name('notification.routeTo');
    
    // Activity Log
    Route::get('/activity-log', [ActivityController::class, 'index'])->name('activity-log.index');
    Route::get('/activity-log/all', [ActivityController::class, 'all'])->name('activity-log.all');

    // === INFO KAMAR (Monitoring Kamar) ===
    Route::prefix('room-info')->as('room-info.')->group(function () {
        Route::get('/available', [KamarTersediaController::class, 'index'])->name('available');
        Route::get('/reservation', [ReservasiKamarController::class, 'index'])->name('reservation');
        Route::post('/reservation/{id}/check-in', [ReservasiKamarController::class, 'checkIn'])->name('reservation.checkIn');
        Route::post('/reservation/{id}/cancel', [ReservasiKamarController::class, 'cancel'])->name('reservation.cancel');
        Route::get('/cleaning', [KamarDibersihkanController::class, 'index'])->name('cleaning');
        Route::post('/cleaning/{id}/finish', [KamarDibersihkanController::class, 'finishCleaning'])->name('cleaning.finish');

        // Route untuk hitung harga via AJAX
    Route::get('/transaction/payment/count', [TransactionController::class, 'getCountPayment'])->name('transaction.countPayment');
    });

    // === OPERASIONAL CHECK IN - CHECK OUT ===
    Route::prefix('transaction')->as('transaction.')->group(function () {
        Route::get('/check-in', [CheckinController::class, 'index'])->name('checkin.index');
        Route::get('/check-in/{transaction}/edit', [CheckinController::class, 'edit'])->name('checkin.edit');
        Route::put('/check-in/{transaction}', [CheckinController::class, 'update'])->name('checkin.update');
        Route::delete('/check-in/{transaction}', [CheckinController::class, 'destroy'])->name('checkin.destroy');
        Route::post('/check-in/{transaction}/checkout', [CheckinController::class, 'checkout'])->name('checkin.checkout');

        // [BARU] Route Trigger Check-In & Potong Stok Amenities
        Route::post('/check-in/{id}/process', [CheckinController::class, 'processCheckIn'])->name('checkin.process');

        Route::get('/check-out', [CheckoutController::class, 'index'])->name('checkout.index');
        Route::post('/check-out/{transaction}', [CheckoutController::class, 'process'])->name('checkout.process');
        Route::post('/checkout/{id}', [CheckoutController::class, 'processCheckout'])->name('checkout.process2');
    });

    Route::resource('transaction', TransactionController::class);
});


/*
|--------------------------------------------------------------------------
| ROOT REDIRECT
|--------------------------------------------------------------------------
*/
Route::redirect('/', '/dashboard');