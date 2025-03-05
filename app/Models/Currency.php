<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Currency extends Model
{
  use HasFactory, SoftDeletes, LogsActivity;

  protected $fillable = ['currency', 'code', 'status'];

  public function getActivitylogOptions(): LogOptions
    {
      return LogOptions::defaults()
        ->useLogName('Currency_activity_log')
        ->logOnly(['*'])
        ->logOnlyDirty()
        ->setDescriptionForEvent(fn(string $event) => "Currency '{$this->currency}' has been {$event}.");
    }

  public function quoteTasks()
  {
    return $this->hasMany(QuoteTask::class);
  }
}
