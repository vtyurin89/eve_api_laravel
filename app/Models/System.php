<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class System extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'systems';
    protected $guarded = [];

    public function constellation()
    {
        return $this->belongsTo('App\Models\Constellation');
    }

    public function getSystemsByArray($filterArray) {
        return $this->all();
    }
}
