<?php

namespace App\Providers;

use App\Repositories\Implementation\CustomerRepository;
use App\Repositories\Implementation\ImageRepository;
use App\Repositories\Implementation\PaymentRepository;
use App\Repositories\Implementation\ReservationRepository;
use App\Repositories\Implementation\RoomRepository;
use App\Repositories\Implementation\RoomStatusRepository;
use App\Repositories\Implementation\TransactionRepository;
use App\Repositories\Implementation\TypeRepository;
use App\Repositories\Implementation\UserRepository;
use App\Repositories\Implementation\RuangRapatPaketRepository;
use App\Repositories\Implementation\AmenityRepository;
use App\Repositories\Implementation\IngredientRepository;
use App\Repositories\Implementation\LaporanRepository;
use App\Repositories\Implementation\LaporanKamarRepository;
use App\Repositories\Implementation\CheckinRepository;
use App\Repositories\Implementation\CheckoutRepository;
use App\Repositories\Implementation\ReservasiKamarRepository;
use App\Repositories\Implementation\KamarTersediaRepository;
use App\Repositories\Implementation\KamarDibersihkanRepository;
use App\Repositories\Interface\CustomerRepositoryInterface;
use App\Repositories\Interface\ImageRepositoryInterface;
use App\Repositories\Interface\PaymentRepositoryInterface;
use App\Repositories\Interface\ReservationRepositoryInterface;
use App\Repositories\Interface\RoomRepositoryInterface;
use App\Repositories\Interface\RoomStatusRepositoryInterface;
use App\Repositories\Interface\TransactionRepositoryInterface;
use App\Repositories\Interface\TypeRepositoryInterface;
use App\Repositories\Interface\UserRepositoryInterface;
use App\Repositories\Interface\RuangRapatPaketRepositoryInterface;
use App\Repositories\Interface\AmenityRepositoryInterface;
use App\Repositories\Interface\IngredientRepositoryInterface;
use App\Repositories\Interface\LaporanRepositoryInterface;
use App\Repositories\Interface\LaporanKamarRepositoryInterface;
use App\Repositories\Interface\CheckinRepositoryInterface;
use App\Repositories\Interface\CheckoutRepositoryInterface;
use App\Repositories\Interface\ReservasiKamarRepositoryInterface;
use App\Repositories\Interface\KamarTersediaRepositoryInterface;
use App\Repositories\Interface\KamarDibersihkanRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(CustomerRepositoryInterface::class, CustomerRepository::class);
        $this->app->bind(ImageRepositoryInterface::class, ImageRepository::class);
        $this->app->bind(PaymentRepositoryInterface::class, PaymentRepository::class);
        $this->app->bind(ReservationRepositoryInterface::class, ReservationRepository::class);
        $this->app->bind(RoomRepositoryInterface::class, RoomRepository::class);
        $this->app->bind(TransactionRepositoryInterface::class, TransactionRepository::class);
        $this->app->bind(TypeRepositoryInterface::class, TypeRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(RuangRapatPaketRepositoryInterface::class, RuangRapatPaketRepository::class);
        $this->app->bind(AmenityRepositoryInterface::class, AmenityRepository::class);
        $this->app->bind(IngredientRepositoryInterface::class, IngredientRepository::class);
        $this->app->bind(LaporanRepositoryInterface::class, LaporanRepository::class);
        $this->app->bind(LaporanKamarRepositoryInterface::class, LaporanKamarRepository::class);
        $this->app->bind(CheckinRepositoryInterface::class, CheckinRepository::class);
        $this->app->bind(CheckoutRepositoryInterface::class, CheckoutRepository::class);
        $this->app->bind(ReservasiKamarRepositoryInterface::class, ReservasiKamarRepository::class);
        $this->app->bind(KamarTersediaRepositoryInterface::class, KamarTersediaRepository::class);
        $this->app->bind(KamarDibersihkanRepositoryInterface::class, KamarDibersihkanRepository::class);
        
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
