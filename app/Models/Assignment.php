<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
class Assignment extends Model
{
    //
    use HasFactory, SoftDeletes, LogsActivity;

    public $table = 'assignments';
    protected $fillable = [
      'assignment_type',
      'client_id',
      'description',
      'expiry_at',
      'status',
      'quote_id',
      'ledger',
      'created_by',
      'approved_by',
      'created_at',
      'updated_at',
    ];

    public function getActivitylogOptions(): LogOptions
    {
      return LogOptions::defaults()
        ->useLogName('Assignment_activity_log')
        ->logOnly(['*'])
        ->logOnlyDirty()
        ->setDescriptionForEvent(fn(string $event) => "Assignment '{$this->client_id}' has been {$event}.");
    }

    public function tasks()
    {
        return $this->hasMany(AssignmentTask::class, 'assignment_id', 'id');
    }
    public function timekeeps()
    {
        return $this->hasMany(AssignmentTimekeep::class, 'assignment_id', 'id');
    }
    public function client()
    {
      return $this->belongsTo(Client::class);
    }
}
