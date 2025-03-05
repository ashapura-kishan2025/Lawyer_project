<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Quote extends Model
{
  use HasFactory, SoftDeletes, LogsActivity;

  protected $fillable = [
    'client_id',
    'reference',
    'expiry_at',
    'status',
    'assignment_id',
    'created_by',
    'approved_by',
    'approved_at',
    'created_at',
    'updated_at',
  ];

  public function getActivitylogOptions(): LogOptions
  {
    return LogOptions::defaults()
      ->useLogName('Quote_activity_log')
      ->logOnly(['*'])
      ->logOnlyDirty()
      ->setDescriptionForEvent(fn(string $event) => "Quote '{$this->client_id}' has been {$event}.");
  }


  /**
   * Define the relationship with the client.
   */
  public function client()
  {
    return $this->belongsTo(Client::class);
  }

  /**
   * Define the relationship with the user who created the quote.
   */
  public function creator()
  {
    return $this->belongsTo(User::class, 'created_by');
  }

  /**
   * Define the relationship with the user who approved the quote.
   */
  public function approver()
  {
    return $this->belongsTo(User::class, 'approved_by');
  }

  /**
   * Define the relationship with the quote tasks.
   */
  public function tasks()
  {
    return $this->hasMany(QuoteTask::class);
  }

  /**
   * Define the relationship with the quote users.
   */
  public function users()
  {
    return $this->hasMany(QuoteUser::class);
  }
}
