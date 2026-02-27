<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Warning;
use Illuminate\Http\Request;

class WarningController extends Controller {
    public function index() { $warnings = Warning::with(['student.user','issuedBy'])->latest()->paginate(20); return view('admin.warnings.index', compact('warnings')); }
    public function show(Warning $warning) { $warning->load(['student.user','issuedBy']); return view('admin.warnings.show', compact('warning')); }
    public function destroy(Warning $warning) { $warning->delete(); return redirect()->route('admin.warnings.index')->with('success','تم حذف الإنذار'); }
    public function create() { return redirect()->route('admin.warnings.index'); }
    public function store(Request $request) { return redirect()->route('admin.warnings.index'); }
    public function edit(Warning $warning) { return view('admin.warnings.show', compact('warning')); }
    public function update(Request $request, Warning $warning) { return redirect()->route('admin.warnings.show', $warning); }
}
