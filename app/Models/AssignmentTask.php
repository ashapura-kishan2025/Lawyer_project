<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssignmentTask extends Model
{
    //
    use HasFactory, SoftDeletes;

    public $table = 'assignment_tasks';
    protected $fillable = [
      'assignment_id',
      'currency_id',
      'description',
      'received_amount',
      'amount',
      'status',
      'memo_number',
      'department_id',
      'created_by',
      'work_in_progress',
      'lkr_rate',
      'updated_by',
      'created_at',
      'updated_at',
    ];

    public function assignment()
    {
        return $this->belongsTo(Assignment::class, 'assignment_id', 'id');
    }
    
    // Define a relationship with the Department model if needed
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id', 'id');
    }
    public function currency()
  {
    return $this->belongsTo(Currency::class);
  }
}
