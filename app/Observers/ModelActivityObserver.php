<?php

namespace App\Observers;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

/**
 * Observe les modèles métier et logge automatiquement les opérations CRUD.
 */
class ModelActivityObserver
{
    public function created(Model $model): void
    {
        $this->log('created', $model, null, $model->getAttributes());
    }

    public function updated(Model $model): void
    {
        $this->log('updated', $model, $model->getOriginal(), $model->getAttributes());
    }

    public function deleted(Model $model): void
    {
        $this->log('deleted', $model, $model->getOriginal(), null);
    }

    protected function log(string $action, Model $model, ?array $old, ?array $new): void
    {
        if (! auth()->check()) {
            return;
        }

        // Évite de logger les propres ActivityLog.
        if ($model instanceof ActivityLog) {
            return;
        }

        ActivityLog::log(
            $action,
            null,
            get_class($model),
            $model->getKey(),
            $old,
            $new
        );
    }
}

