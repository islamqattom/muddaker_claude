<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class TafseerController extends Controller
{
    public function index()
    {
        $apiToken = Auth::check() ? Auth::user()->createToken('api-token')->plainTextToken : '';
        return view('student.tafseer.index', compact('apiToken'));
    }
}