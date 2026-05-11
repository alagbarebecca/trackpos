<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = [
        'user_type',
        'user_id',
        'action',
        'entity_type',
        'entity_id',
        'description',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function user()
    {
        return $this->morphTo();
    }

    public function entity()
    {
        return $this->morphTo('entity_type', 'entity_id');
    }

    /**
     * Log an activity
     */
    public static function log($action, $entity, $description = null, $oldValues = null, $newValues = null)
    {
        $user = auth()->user();
        
        return static::create([
            'user_type' => $user ? get_class($user) : null,
            'user_id' => $user?->id,
            'action' => $action,
            'entity_type' => is_string($entity) ? $entity : get_class($entity),
            'entity_id' => is_object($entity) ? $entity->id : $entity,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Log when a model is created
     */
    public static function logCreated($model, $description = null)
    {
        return static::log('created', $model, $description ?? "Created " . class_basename($model), null, $model->toArray());
    }

    /**
     * Log when a model is updated
     */
    public static function logUpdated($model, $oldValues, $description = null)
    {
        return static::log('updated', $model, $description ?? "Updated " . class_basename($model), $oldValues, $model->toArray());
    }

    /**
     * Log when a model is deleted
     */
    public static function logDeleted($model, $description = null)
    {
        return static::log('deleted', $model, $description ?? "Deleted " . class_basename($model), $model->toArray(), null);
    }

    /**
     * Log a custom action
     */
    public static function logAction($action, $entity, $description, $oldValues = null, $newValues = null)
    {
        return static::log($action, $entity, $description, $oldValues, $newValues);
    }
}