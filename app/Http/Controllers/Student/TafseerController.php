<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;

class TafseerController extends Controller
{
    public function index()
    {
        return view('student.tafseer.index');
    }
}