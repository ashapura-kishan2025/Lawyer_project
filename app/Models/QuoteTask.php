<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuoteTask extends Model
{
  use HasFactory, SoftDeletes;

  protected $fillable = [
    'quote_id',
    'amount',
    'currency_id',
    'description',
    'department_id',
  ];


  /**
   * Define the relationship with the quote.
   */
  public function quote()
  {
    return $this->belongsTo(Quote::class);
  }

  /**
   * Define the relationship with the currency.
   */
  public function currency()
  {
    return $this->belongsTo(Currency::class);
  }

  /**
   * Define the relationship with the department.
   */
  public function department()
  {
    return $this->belongsTo(Department::class);
  }
}
