<?php

namespace App\Services;

use App\Models\ActivityLog;

class ActivityLogger
{
    public static function log(string $action, ?string $description = null, ?string $subjectType = null, ?int $subjectId = null, ?array $oldValues = null, ?array $newValues = null): ?ActivityLog
    {
        if (! auth()->check()) {
            return null;
        }

        return ActivityLog::log($action, $description, $subjectType, $subjectId, $oldValues, $newValues);
    }
}
