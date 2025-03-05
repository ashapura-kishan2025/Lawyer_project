<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Source extends Model
{

  use HasFactory, SoftDeletes;

  protected $table = 'sources';

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
    'title',
  ];

  /**
   * Relationships
   */

  // Relationship with Clients
  public function clients()
  {
    return $this->hasMany(Client::class);
  }
}
