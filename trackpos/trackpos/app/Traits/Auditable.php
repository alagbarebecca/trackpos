<?php

namespace App\Traits;

use App\Models\AuditLog;

trait Auditable
{
    /**
     * Boot the trait
     */
    public static function bootAuditable()
    {
        static::created(function ($model) {
            AuditLog::logCreated($model);
        });

        static::updated(function ($model) {
            $original = $model->getOriginal();
            $changes = $model->getChanges();
            
            // Only log if there are actual changes
            if (!empty($changes)) {
                AuditLog::logUpdated($model, $original);
            }
        });

        static::deleted(function ($model) {
            AuditLog::logDeleted($model);
        });
    }

    /**
     * Log a custom action on this model
     */
    public function logAction($action, $description, $oldValues = null, $newValues = null)
    {
        AuditLog::logAction($action, $this, $description, $oldValues, $newValues);
    }
}