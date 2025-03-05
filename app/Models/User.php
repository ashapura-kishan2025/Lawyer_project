<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class User extends Authenticatable
{
  use HasFactory, Notifiable, SoftDeletes, HasRoles, LogsActivity;

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'name',
    'email',
    'password',
    'role_id',
    'departments_id',
    'linkedin_url',
    'rate',
    'status',
    'last_login_ip',
    'last_login_at'
  ];

  /**
   * The attributes that should be hidden for serialization.
   *
   * @var array<int, string>
   */
  protected $hidden = [
    'password',
    'remember_token',
  ];


  public function getActivitylogOptions(): LogOptions
  {
    return LogOptions::defaults()
      ->useLogName('user_activity_log')
      ->logOnly(['*'])
      ->logOnlyDirty()
      ->setDescriptionForEvent(fn(string $event) => "User '{$this->name}' has been {$event}.");
  }

  /**
   * Get formatted activity log with old and new values
   */
  // public function getFormattedLogs()
  // {
  //   return $this->activityLogs()->get()->map(function ($log) {
  //     return [
  //       'event' => $log->event, // created, updated, deleted, etc.
  //       'old' => $log->properties['old'] ?? null, // Old values before change
  //       'new' => $log->properties['attributes'] ?? null, // New values after change
  //       'description' => $log->description,
  //       'created_at' => $log->created_at->format('Y-m-d H:i:s'),
  //     ];
  //   });
  // }

  /**
   * Get the attributes that should be cast.
   *
   * @return array<string, string>
   */
  protected function casts(): array
  {
    return [
      'email_verified_at' => 'datetime',
      'password' => 'hashed',
    ];
  }


  public function createdQuotes()
  {
    return $this->hasMany(Quote::class, 'created_by');
  }

  public function approvedQuotes()
  {
    return $this->hasMany(Quote::class, 'approved_by');
  }

  public function quoteUsers()
  {
    return $this->hasMany(QuoteUser::class);
  }
  // User model
  public function roles()
  {
    return $this->belongsToMany(Role::class, 'users_departments', 'user_id', 'role_id')
      ->withPivot('department_id');  // Fetch role along with department_id from pivot table
  }

  public function departments()
  {
    return $this->belongsToMany(Department::class, 'users_departments', 'user_id', 'department_id')
      ->withPivot('role_id');  // Fetch department along with role_id from pivot table
  }

  public function department()
  {
    return $this->belongsTo(Department::class);
  }

}
