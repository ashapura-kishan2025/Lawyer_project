<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission;
class Role extends Model
{
    public $table = 'roles';
    protected $fillable = [
      'name'
    ];  
    public function users()
    {
        return $this->belongsToMany(User::class, 'users_departments', 'role_id', 'user_id');
    }

    public function departments()
    {
        return $this->belongsToMany(Department::class, 'users_departments'); // Assuming a pivot table role_department
    }

    // Define relationship with permissions via departments
    public function permissions()
    {
        return $this->hasManyThrough(Permission::class, Department::class);
    }
}
