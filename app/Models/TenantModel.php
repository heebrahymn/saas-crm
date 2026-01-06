<?php

namespace App\Models;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;

abstract class TenantModel extends Model
{
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new TenantScope());
    }
}