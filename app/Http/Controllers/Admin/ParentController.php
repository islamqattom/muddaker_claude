<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\{User, ParentProfile, Student};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class ParentController extends Controller {
    public function index() { $parents = User::where('role','parent')->with('parentProfile')->paginate(20); return view('admin.parents.index', compact('parents')); }
    public function create() { $students = Student::with('user')->get(); return view('admin.parents.create', compact('students')); }
    public function store(Request $request) {
        $request->validate(['name'=>'required','username'=>'nullable|string|min:3|max:30|regex:/^[a-zA-Z0-9_]+$/|unique:users,username','phone'=>'required|unique:users,phone','password'=>'required|min:8|confirmed']);
        DB::transaction(function() use ($request) {
            $user = User::create(['name'=>$request->name,'username'=>$request->username,'phone'=>$request->phone,'email'=>$request->email,'password'=>Hash::make($request->password),'role'=>'parent']);
            $parent = ParentProfile::create(['user_id'=>$user->id,'relationship'=>$request->relationship??'father']);
            if ($request->student_ids) { $parent->students()->attach($request->student_ids); }
        });
        return redirect()->route('admin.parents.index')->with('success','تم إضافة ولي الأمر');
    }
    public function show($id) { $parent = User::where('role','parent')->with('parentProfile.students.user')->findOrFail($id); return view('admin.parents.show',compact('parent')); }
    public function edit($id) { $parent = User::where('role','parent')->with('parentProfile')->findOrFail($id); $students = Student::with('user')->get(); return view('admin.parents.edit',compact('parent','students')); }
    public function update(Request $request, $id)
    {
        $parent = User::where('role', 'parent')->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'nullable|string|min:3|max:30|regex:/^[a-zA-Z0-9_]+$/|unique:users,username,' . $parent->id,
            'phone' => 'required|string|unique:users,phone,' . $parent->id,
            'email' => 'nullable|email|unique:users,email,' . $parent->id,
        ]);

        $parent->update([
            'name' => $request->name,
            'username' => $request->username,
            'phone' => $request->phone,
            'email' => $request->email,
        ]);

        if ($request->password) {
            $parent->update(['password' => Hash::make($request->password)]);
        }

        return redirect()->route('admin.parents.index')->with('success', 'تم التحديث');
    }
    public function destroy($id) { User::findOrFail($id)->delete(); return redirect()->route('admin.parents.index')->with('success','تم الحذف'); }
}
