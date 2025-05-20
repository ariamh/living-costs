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
        $changes = $city->getChanges();
        $original = $city->getOriginal();

        log_user_activity("Updated city: {$city->name}", [
            'model' => $city,
            'module' => 'city',
            'entity_id' => $city->id,
            'description' => "City updated from {$original['name']} to {$changes['name']}",
        ], ['name', 'province', 'country']);
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
