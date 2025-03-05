<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Client extends Model
{
  use HasFactory, SoftDeletes, LogsActivity;

  protected $fillable = [
    'client',
    'email',
    'billing_address',
    'type',
    'contact_person',
    'mobile',
    'company_name',
    'currency_id',
    'source_id',
    'source_other',
    'country_id',
    'created_by',
    'linkedin_url',
    'website_url',
  ];

  public function getActivitylogOptions(): LogOptions
  {
    return LogOptions::defaults()
      ->useLogName('Client_activity_log')
      ->logOnly(['*'])
      ->logOnlyDirty()
      ->setDescriptionForEvent(fn(string $event) => "Client '{$this->client}' has been {$event}.");
  }

  /**
   * Relationships
   */

  // Relationship with Currency
  public function currency()
  {
    return $this->belongsTo(Currency::class);
  }

  // Relationship with Source
  public function source()
  {
    return $this->belongsTo(Source::class);
  }

  // Relationship with Country
  public function country()
  {
    return $this->belongsTo(Country::class);
  }

  // Relationship with User (Created By)
  public function createdBy()
  {
    return $this->belongsTo(User::class, 'created_by');
  }

  public function quotes()
  {
    return $this->hasMany(Quote::class);
  }
}
