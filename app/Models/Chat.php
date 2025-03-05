<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Chat extends Model
{
  use HasFactory, SoftDeletes;

  protected $fillable = ['sender_id', 'receiver_id', 'message'];

  public function sender()
  {
    return $this->belongsTo(User::class, 'sender_id');
  }

  public function receiver()
  {
    return $this->belongsTo(User::class, 'receiver_id');
  }
}
