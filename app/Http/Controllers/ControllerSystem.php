<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\System;

class ControllerSystem extends Controller
{
    public function index () {
        $systems = System::all();
        return view('index', compact('systems'));
    }

    public function update () {
        $system = System::find(1);
        $system->update(['name' => 'dragon age']);
        dd($system);
    }

    public function delete () {
        $system_for_deletion = System::find(1);
        if ($system_for_deletion) {
            $system_for_deletion -> delete();
            dd($system_for_deletion);
        } else {
            dd('system already deleted!');
        }
    }
}
