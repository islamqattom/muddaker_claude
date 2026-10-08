<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\{User, ParentProfile, Student};
use App\Services\ParentActivationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ParentController extends Controller {
    public function index() { $parents = User::where('role','parent')->with('parentProfile.students')->latest()->paginate(20); return view('admin.parents.index', compact('parents')); }
    public function create() { $students = Student::with('user')->get(); return view('admin.parents.create', compact('students')); }
    public function store(Request $request, ParentActivationService $parentActivation) {
        $request->validate([
            'name'=>'required',
            'username'=>'required|string|min:3|max:30|regex:/^[a-zA-Z0-9_]+$/|unique:users,username',
            'phone'=>'nullable|unique:users,phone',
            'password'=>'required|min:8|confirmed',
            'image' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ]);

        $avatarPath = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            try {
                $filename = uniqid('avatar_') . '.' . $file->extension();
                $destinationPath = storage_path('app/public/avatars');
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }
                $file->move($destinationPath, $filename);
                $avatarPath = 'avatars/' . $filename;
            } catch (\Exception $e) {
                Log::error('Image upload failed: ' . $e->getMessage());
                return back()->withInput()->with('error', 'فشل في رفع الصورة. يرجى المحاولة مرة أخرى.');
            }
        }

        $events = DB::transaction(function() use ($request, $avatarPath, $parentActivation) {
            $user = User::create([
                'name'=>$request->name,
                'username'=>$request->username,
                'phone'=>$request->phone,
                'email'=>$request->email,
                'password'=>Hash::make($request->password),
                'role'=>'parent',
                'avatar' => $avatarPath,
            ]);
            $parent = ParentProfile::create(['user_id'=>$user->id,'relationship'=>$request->relationship??'father']);
            if ($request->student_ids) { $parent->students()->attach($request->student_ids); }
            $event = $parentActivation->syncParentStatus($parent, auth()->id());
            return $event ? [$event] : [];
        });

        $message = 'تم إضافة ولي الأمر';
        foreach ($parentActivation->flashMessages($events) as $parentMessage) {
            $message .= ' ' . $parentMessage;
        }

        return redirect()->route('admin.parents.index')->with('success', $message);
    }
    public function show($id) { $parent = User::where('role','parent')->with('parentProfile.students.user')->findOrFail($id); return view('admin.parents.show',compact('parent')); }
    public function edit($id) { $parent = User::where('role','parent')->with('parentProfile')->findOrFail($id); $students = Student::with('user')->get(); return view('admin.parents.edit',compact('parent','students')); }
    public function update(Request $request, $id)
    {
        $parent = User::where('role', 'parent')->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|min:3|max:30|regex:/^[a-zA-Z0-9_]+$/|unique:users,username,' . $parent->id,
            'phone' => 'nullable|string|unique:users,phone,' . $parent->id,
            'email' => 'nullable|email|unique:users,email,' . $parent->id,
            'image' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ]);

        $newAvatarPath = null;

        if ($request->hasFile('image')) {
            // Delete old avatar if exists
            if ($parent->avatar) {
                try {
                    $oldPath = storage_path('app/public/' . $parent->avatar);
                    if (file_exists($oldPath)) {
                        unlink($oldPath);
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to delete old avatar: ' . $e->getMessage());
                }
            }
            try {
                $file = $request->file('image');
                $filename = uniqid('avatar_') . '.' . $file->extension();
                $destinationPath = storage_path('app/public/avatars');
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }
                $file->move($destinationPath, $filename);
                $newAvatarPath = 'avatars/' . $filename;
            } catch (\Exception $e) {
                Log::error('Image upload failed: ' . $e->getMessage());
                return back()->withInput()->with('error', 'فشل في رفع الصورة. يرجى المحاولة مرة أخرى.');
            }
        }

        $userData = [
            'name' => $request->name,
            'username' => $request->username,
            'phone' => $request->phone,
            'email' => $request->email,
        ];

        if ($newAvatarPath) {
            $userData['avatar'] = $newAvatarPath;
        }

        $parent->update($userData);

        if ($request->password) {
            $parent->update(['password' => Hash::make($request->password)]);
        }

        if ($parent->parentProfile && $request->relationship) {
            $parent->parentProfile->update(['relationship' => $request->relationship]);
        }

        return redirect()->route('admin.parents.index')->with('success', 'تم التحديث');
    }
    public function destroy($id) { User::findOrFail($id)->delete(); return redirect()->route('admin.parents.index')->with('success','تم الحذف'); }

    public function toggleActive($id)
    {
        $parent = User::where('role', 'parent')->findOrFail($id);
        $parent->update([
            'is_active' => ! $parent->is_active,
            'deactivated_by_system' => false,
        ]);

        Log::info('Parent account toggled', [
            'admin_id' => auth()->id(),
            'user_id' => $parent->id,
            'is_active' => $parent->is_active,
        ]);

        app(\App\Services\LeaderboardService::class)->clearCache();

        return back()->with(
            'success',
            $parent->is_active ? 'تم تفعيل حساب ولي الأمر.' : 'تم تعطيل حساب ولي الأمر.'
        );
    }
}
