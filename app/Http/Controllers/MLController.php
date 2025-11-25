<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MLController extends Controller
{
    public function index() {
        return view('main/ml-manager');
    }
}
