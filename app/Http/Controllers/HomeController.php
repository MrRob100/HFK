<?php

namespace App\Http\Controllers;

use App\Models\Result;

class HomeController extends Controller
{
    public function index()
    {
        return view('home');
    }
}
