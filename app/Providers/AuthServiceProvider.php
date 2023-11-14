<?php

namespace App\Providers;

use App\Models\Parking_space;
use App\Models\Parking_zone;
use App\Models\Reservation;
use App\Policies\Parking_spacePolicy;
use App\Policies\Parking_zonePolicy;
use App\Policies\ReservationPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Reservation::class =>  ReservationPolicy::class,
        Parking_zone::class => Parking_zonePolicy::class,
        Parking_space::class => Parking_spacePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //
    }
}
