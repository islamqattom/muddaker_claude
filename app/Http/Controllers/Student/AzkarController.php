<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AzkarCategory;
use App\Models\Adhkar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AzkarController extends Controller
{
    public function index()
    {
        $categories = AzkarCategory::withCount('adhkar')->orderBy('sort_order')->get();

        $stats = [
            'tasbeeh_count' => Session::get('tasbeeh_count', 0),
            'istighfar_count' => Session::get('istighfar_count', 0),
            'total_dhikr' => Session::get('total_dhikr', 0),
        ];

        return view('student.azkar.index', compact('categories', 'stats'));
    }

    public function category($id)
    {
        $category = AzkarCategory::with('adhkar')->findOrFail($id);

        return view('student.azkar.category', compact('category'));
    }

    public function track(Request $request)
    {
        $type = $request->input('type');
        $count = $request->input('count', 1);

        $stats = [
            'tasbeeh_count' => Session::get('tasbeeh_count', 0),
            'istighfar_count' => Session::get('istighfar_count', 0),
            'total_dhikr' => Session::get('total_dhikr', 0),
        ];

        switch ($type) {
            case 'tasbeeh':
                $stats['tasbeeh_count'] += $count;
                $stats['total_dhikr'] += $count;
                break;
            case 'istighfar':
                $stats['istighfar_count'] += $count;
                $stats['total_dhikr'] += $count;
                break;
            case 'dhikr':
                $stats['total_dhikr'] += $count;
                break;
        }

        Session::put('tasbeeh_count', $stats['tasbeeh_count']);
        Session::put('istighfar_count', $stats['istighfar_count']);
        Session::put('total_dhikr', $stats['total_dhikr']);

        return response()->json(['success' => true, 'stats' => $stats]);
    }

    public function resetStats()
    {
        Session::forget(['tasbeeh_count', 'istighfar_count', 'total_dhikr']);
        return response()->json(['success' => true]);
    }
}