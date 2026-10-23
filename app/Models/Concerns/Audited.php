<?php

namespace App\Models\Concerns;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

trait Audited
{
    public static function bootAudited(): void
    {
        static::creating(function ($model) {
            $model->created_by ??= Auth::id();
        });

        static::created(fn ($model) => $model->writeAudit('insert', null, $model->auditable($model->getAttributes())));

        static::updated(function ($model) {
            $changes = $model->auditable($model->getChanges());
            unset($changes['updated_at']);
            if ($changes) {
                $model->writeAudit('update', array_intersect_key($model->auditable($model->getOriginal()), $changes), $changes);
            }
        });

        static::deleted(fn ($model) => $model->writeAudit('delete', $model->auditable($model->getOriginal()), null));
    }

    protected function auditable(array $values): array
    {
        return array_diff_key($values, array_flip(['password', 'remember_token', 'created_at']));
    }

    protected function writeAudit(string $action, ?array $old, ?array $new): void
    {
        AuditLog::create([
            'table_name' => $this->getTable(),
            'record_id' => $this->getKey() ?? 0,
            'action' => $action,
            'changed_by' => Auth::id(),
            'changed_at' => now(),
            'old_values' => $old,
            'new_values' => $new,
        ]);
    }
}
