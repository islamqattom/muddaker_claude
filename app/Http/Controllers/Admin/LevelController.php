<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Level;
use Illuminate\Http\Request;

class LevelController extends Controller {
    public function index() { $levels = Level::withCount('students')->orderBy('level_number')->get(); return view('admin.levels.index', compact('levels')); }
    public function create() { return view('admin.levels.create'); }
    public function store(Request $request) {
        $request->validate(['name'=>'required','level_number'=>'required|integer','new_memorization_pages'=>'required|integer|min:1','review_pages'=>'required|integer|min:1']);
        Level::create($request->all());
        return redirect()->route('admin.levels.index')->with('success','تم إضافة المستوى');
    }
    public function show(Level $level) { return view('admin.levels.show', compact('level')); }
    public function edit(Level $level) { return view('admin.levels.edit', compact('level')); }
    public function update(Request $request, Level $level) { $level->update($request->all()); return redirect()->route('admin.levels.index')->with('success','تم التحديث'); }
    public function destroy(Level $level) { $level->delete(); return redirect()->route('admin.levels.index')->with('success','تم الحذف'); }
}
