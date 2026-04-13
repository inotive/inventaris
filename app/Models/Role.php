<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Role extends SpatieRole
{
    /**
     * Explicitly set default guard for roles to avoid guard mismatch
     * issues when counting/attaching permissions.
     */
    protected $guard_name = 'web';

    public function users(): MorphToMany
    {
        return $this->morphedByMany(User::class, 'model', config('permission.table_names.model_has_roles'));
    }
}
