<?php

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

if (! function_exists('log_user_activity')) {
function log_user_activity(string $action, array $context = [], array $fieldsToTrack = [])
    {
        $model = $context['model'] ?? null;

        // Ambil perubahan hanya jika ada model
        $changes = $model?->getChanges() ?? [];
        $original = $model?->getOriginal() ?? [];

        // Filter field yang mau dilacak
        if (!empty($fieldsToTrack)) {
            $changes = array_intersect_key($changes, array_flip($fieldsToTrack));
        }

        $before = array_intersect_key($original, $changes);
        $after = $changes;

        if (empty($after)) return; // tidak log jika tidak ada perubahan

        Log::channel('audit')->info($action, [
            'user_id'     => Auth::user()?->id,
            'action'      => $action,
            'module'      => $context['module'] ?? null,
            'entity_id'   => $context['entity_id'] ?? null,
            'url'         => request()->fullUrl(),
            'ip'          => request()->ip(),
            'method'      => request()->method(),
            'before'      => $before,
            'after'       => $after,
            'description' => $context['description'] ?? null,
        ]);
    }
}
