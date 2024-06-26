<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Builder;

class DangerRating extends Model
{
    use HasFactory;
    use SoftDeletes;
    
    public $timestamps = true;

    protected $table = 'danger_ratings';
    protected $guarded = [];

    use Prunable;
 
    public function prunable(): Builder
    {
        return static::where('created_at', '<=', now()->subMonth());
    }

    public function system()
    {
        return $this->belongsTo('App\Models\System');
    }
}
