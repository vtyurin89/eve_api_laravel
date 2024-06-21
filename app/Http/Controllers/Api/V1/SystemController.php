<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\System;

class SystemController extends Controller
{
    public function index()
    {
        return System::all();
    }
}

?>