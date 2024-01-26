<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait BootedModelUserActions
{
    protected static function booted(): void
    {
        static::addGlobalScope('by_user', function (Builder $builder) {
            if ($userId = self::getAuthUserId()) {
                $builder->where('user_id', $userId);
            }
        });

        static::creating(function ($model) {
            if ($userId = self::getAuthUserId()) {
                $model->user_id = $userId;
            }
        });

        static::updating(function ($model) {
            if ($userId = self::getAuthUserId()) {
                $model->user_id = $userId;
            }
        });

        static::deleting(function ($model) {
            if ($userId = self::getAuthUserId()) {
                $model->user_id = $userId;
            }
        });
    }

    private static function getAuthUserId(): int|null
    {
        return auth()->check() ? auth()->id() : null;
    }
}
