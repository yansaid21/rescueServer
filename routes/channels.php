<?php

use App\Models\UserReport;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('privileged-channel.{institutionId}.{userReportId}', function ($user, string $institutionId, string $userReportId) {
    $role = $user->role()->where('institution_id', $institutionId)->first();
    if (!$role) {
        return false;
    }
    if ($role->name === 'Final User') {
        return false;
    }
    if (!UserReport::where('id', $userReportId)->whereHas('incident', function ($query) use ($institutionId) {
        $query->whereHas('riskSituation', function ($query) use ($institutionId) {
            $query->where('institution_id', $institutionId);
        });
    })->first()) {
        return false;
    }
    return true;
});

Broadcast::channel('privileged-channel.{institutionId}', function ($user, string $institutionId) {
    $role = $user->role()->where('institution_id', $institutionId)->first();
    if (!$role) {
        return false;
    }
    if ($role->name === 'Final User') {
        return false;
    }
    return true;
});

Broadcast::channel('public-channel.{institutionId}', function ($user, string $institutionId) {
    $role = $user->role()->where('institution_id', $institutionId)->first();
    if (!$role) {
        return false;
    }
    return true;
});

Broadcast::channel('public-channel.{institutionId}.{brigadierId}', function ($user, string $institutionId, string $brigadierId) {
    $role = $user->role()->where('institution_id', $institutionId)->first();
    if (!$role) {
        return false;
    }
    return true;
});
