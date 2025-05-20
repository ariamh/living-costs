<?php

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

if (! function_exists('log_user_activity')) {
    function log_user_activity($message)
    {
        Log::channel('audit')->info($message, [
            'user_id' => Auth::user()?->id,
            'ip' => request()->ip(),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
        ]);
    }
}
