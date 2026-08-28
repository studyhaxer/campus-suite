<?php

namespace App\Models\Concerns;

use App\Models\Campus;
use Illuminate\Database\Eloquent\Builder;

trait BelongsToCampus
{
    protected static function bootBelongsToCampus(): void
    {
        static::addGlobalScope('campus', function (Builder $builder) {
            if (app()->runningInConsole()) {
                return; // don't restrict artisan commands, seeders, tests
            }

            $user = auth()->user();

            if (! $user) {
                return;
            }

            $table = (new static)->getTable();

            // If a campus is currently selected (via the campus switcher),
            // always scope to it — this applies to every role, including Manager.
            if (session()->has('current_campus_id')) {
                $builder->where($table . '.campus_id', session('current_campus_id'));
                return;
            }

            // No campus selected yet: Managers see every campus in their organization.
            if ($user->hasRole('Manager')) {
                return;
            }

            // Everyone else falls back to every campus they're assigned to.
            $campusIds = $user->campuses->pluck('id');

            $builder->whereIn($table . '.campus_id', $campusIds);
        });

        // Auto-fill campus_id from the currently selected campus when creating records
        static::creating(function ($model) {
            if (! $model->campus_id && session()->has('current_campus_id')) {
                $model->campus_id = session('current_campus_id');
            }
        });
    }

    public function campus()
    {
        return $this->belongsTo(Campus::class);
    }
}