<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssignmentTimekeep extends Model
{
    //
    use HasFactory, SoftDeletes;

    public $table = 'assignment_timekeep';
    protected $fillable = [
      'assignment_id',
      'user_id',
      'description',
      'memo_number',
      'quantity',
      'amount',
      'rate',
      'updated_by',
      'created_at',
      'updated_at',
    ];
    public function assignment()
    {
        return $this->belongsTo(Assignment::class, 'assignment_id', 'id');
    }
}
