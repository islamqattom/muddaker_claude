<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;

class VoiceSearchController extends Controller
{
    public function index()
    {
        return view('student.voice-search.index');
    }
}