<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Models\Permission;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
class Department extends Model
{
  use HasFactory, SoftDeletes, LogsActivity;

  protected $fillable = ['name', 'description', 'status'];

  public function getActivitylogOptions(): LogOptions
    {
      return LogOptions::defaults()
        ->useLogName('Department_activity_log')
        ->logOnly(['*'])
        ->logOnlyDirty()
        ->setDescriptionForEvent(fn(string $event) => "Department '{$this->name}' has been {$event}.");
    }
  public function quoteTasks()
  {
    return $this->hasMany(QuoteTask::class);
  }
  public function users()
    {
        return $this->belongsToMany(User::class, 'users_departments');
    }
  public function getusers()
  {
      return $this->hasMany(User::class);
  }

  public function permissions()
  {
      return $this->hasMany(Permission::class);
  }

  // Define relationship with roles
  public function roles()
  {
      return $this->belongsToMany(Role::class, 'users_departments');
  }
}
