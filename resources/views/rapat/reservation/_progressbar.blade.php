@php
    // Definisikan 4 langkah baru kita
    $steps = [
        'rapat.reservation.showStep1' => 'Data Pemesan',
        'rapat.reservation.showStep2' => 'Data Reservasi',
        'rapat.reservation.showStep3' => 'Paket dan Layanan',
        'rapat.reservation.showStep4' => 'Konfirmasi & Bayar',
    ];
    $currentRoute = Route::currentRouteName();

    // Hitung step yang sudah completed (UNTUK GARIS)
    $completedCount = 1;

    if ($currentRoute == 'rapat.reservation.showStep2') $completedCount = 2;
    if ($currentRoute == 'rapat.reservation.showStep3') $completedCount = 3;
    if ($currentRoute == 'rapat.reservation.showStep4') $completedCount = 4;
@endphp

<div class="row justify-content-center">
    <div class="col-lg-10"> <div class="card shadow-sm">
            <div class="card-body">
                <ul class="progress-indicator m-4">
                    
                    <li class="{{ 
                        ( $currentRoute == 'rapat.reservation.showStep1' ||
                          $currentRoute == 'rapat.reservation.showStep2' ||
                          $currentRoute == 'rapat.reservation.showStep3' ||
                          $currentRoute == 'rapat.reservation.showStep4' ) ? 'completed' : ''
                    }}">
                        <span class="bubble"></span> {{ $steps['rapat.reservation.showStep1'] }}
                    </li>
                    
                    <li class="{{ 
                        ( $currentRoute == 'rapat.reservation.showStep2' ||
                          $currentRoute == 'rapat.reservation.showStep3' ||
                          $currentRoute == 'rapat.reservation.showStep4' ) ? 'completed' : ''
                    }}">
                        <span class="bubble"></span> {{ $steps['rapat.reservation.showStep2'] }}
                    </li>
                    
                    <li class="{{ 
                        ( $currentRoute == 'rapat.reservation.showStep3' ||
                          $currentRoute == 'rapat.reservation.showStep4' ) ? 'completed' : ''
                    }}">
                        <span class="bubble"></span> {{ $steps['rapat.reservation.showStep3'] }}
                    </li>
                    
                    <li class="{{ 
                        ( $currentRoute == 'rapat.reservation.showStep4' ) ? 'completed' : ''
                    }}">
                        <span class="bubble"></span> {{ $steps['rapat.reservation.showStep4'] }}
                    </li>
                    
                </ul>
            </div>
        </div>
    </div>
</div>