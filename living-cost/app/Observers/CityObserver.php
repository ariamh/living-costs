<?php

namespace App\Observers;

use App\Models\City;

class CityObserver
{
    /**
     * Handle the City "created" event.
     */
    public function created(City $city): void
    {
        log_user_activity("Created city: {$city->name}");
    }

    /**
     * Handle the City "updated" event.
     */
    public function updated(City $city): void
    {
        log_user_activity("Updated city: {$city->name}");
    }

    /**
     * Handle the City "deleted" event.
     */
    public function deleted(City $city): void
    {
        log_user_activity("Deleted city: {$city->name}");
    }

    /**
     * Handle the City "restored" event.
     */
    public function restored(City $city): void
    {
        //
    }

    /**
     * Handle the City "force deleted" event.
     */
    public function forceDeleted(City $city): void
    {
        //
    }
}
