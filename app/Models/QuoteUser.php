<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuoteUser extends Model
{
  use HasFactory, SoftDeletes;

  protected $fillable = [
    'quote_id',
    'user_id',
    'department_id',
    'access_level',
  ];

  /**
   * Define the relationship with the quote.
   */
  public function quote()
  {
    return $this->belongsTo(Quote::class);
  }

  /**
   * Define the relationship with the user.
   */
  public function department()
    {
        return $this->belongsTo(Department::class);
    }

    // Define the relationship to the user (if not already defined)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
