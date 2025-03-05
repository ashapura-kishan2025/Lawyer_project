<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Country extends Model
{
  use HasFactory, SoftDeletes, LogsActivity;

  protected $fillable = ['country', 'code', 'status'];

  public function getActivitylogOptions(): LogOptions
  {
    return LogOptions::defaults()
      ->useLogName('Country_activity_log')
      ->logOnly(['*'])
      ->logOnlyDirty()
      ->setDescriptionForEvent(fn(string $event) => "Country '{$this->country}' has been {$event}.");
  }
}
