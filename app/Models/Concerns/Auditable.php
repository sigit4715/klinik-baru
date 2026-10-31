<?php

namespace App\Models\Concerns;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

/**
 * Writes an audit log row whenever the model is created, updated, deleted (archived) or restored.
 * A model can set `protected array $auditExclude = [...]` to skip noisy columns.
 */
trait Auditable
{
    /** Never stored in the audit log */
    private static array $auditAlwaysExcluded = ['created_at', 'updated_at', 'deleted_at', 'password', 'remember_token'];

    public static function bootAuditable(): void
    {
        static::created(function (Model $model) {
            $model->writeAudit('created', [], $model->auditFilter($model->getAttributes()));
        });

        static::updated(function (Model $model) {
            $changes = $model->auditFilter($model->getChanges());

            if (array_key_exists('password', $model->getChanges())) {
                $changes['password'] = '(diubah)';
            }

            if ($changes === []) {
                return;
            }

            $old = array_intersect_key($model->getOriginal(), $changes);
            $model->writeAudit('updated', $model->auditFilter($old), $changes);
        });

        static::deleted(function (Model $model) {
            $model->writeAudit('deleted', $model->auditFilter($model->getAttributes()), []);
        });

        if (method_exists(static::class, 'restored')) {
            static::restored(function (Model $model) {
                $model->writeAudit('restored', [], []);
            });
        }
    }

    protected function auditFilter(array $values): array
    {
        $excluded = array_merge(self::$auditAlwaysExcluded, $this->auditExclude ?? []);

        return array_diff_key($values, array_flip($excluded));
    }

    protected function writeAudit(string $event, array $old, array $new): void
    {
        // Auditing must never break the actual save
        try {
            AuditLog::record($event, $this, $old, $new, $this->auditDescription());
        } catch (\Throwable $e) {
            Log::warning('Audit log failed: ' . $e->getMessage());
        }
    }

    /** Short human-readable label, e.g. the patient name or invoice number */
    protected function auditDescription(): ?string
    {
        foreach (['name', 'invoice_number', 'queue_number', 'label', 'key', 'code'] as $field) {
            if (! empty($this->attributes[$field])) {
                return (string) $this->attributes[$field];
            }
        }

        return null;
    }
}
