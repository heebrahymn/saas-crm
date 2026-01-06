<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    protected $tenantColumn;

    public function __construct($tenantColumn = 'company_id')
    {
        $this->tenantColumn = $tenantColumn;
    }

    public function apply(Builder $builder, Model $model)
    {
        $tenant = app()->bound('current_tenant') ? app('current_tenant') : null;

        if ($tenant) {
            $builder->where($this->tenantColumn, $tenant->id);
        }
    }
}
