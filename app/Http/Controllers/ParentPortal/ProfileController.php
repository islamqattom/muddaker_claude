<?php
namespace App\Http\Controllers\ParentPortal;
use App\Http\Controllers\Controller;
use App\Traits\AvatarUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller {
    use AvatarUpload;
    public function index() {
        $parent = Auth::user()->parentProfile()->with('students.user', 'students.level')->first();
        return view('parent.profile.index', compact('parent'));
    }

    public function update(Request $request) {
        $user = Auth::user();
        $parent = $user->parentProfile;

        $request->validate([
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'image' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ]);

        $newAvatarPath = null;

        if ($request->hasFile('image')) {
            if ($user->avatar) {
                $this->deleteAvatar($user->avatar);
            }
            try {
                $newAvatarPath = $this->uploadAvatar($request);
            } catch (\Exception $e) {
                Log::error('Image upload failed: ' . $e->getMessage());
                return back()->withInput()->with('error', 'فشل في رفع الصورة. يرجى المحاولة مرة أخرى.');
            }
        }

        $userData = [
            'email' => $request->email,
        ];

        if ($newAvatarPath) {
            $userData['avatar'] = $newAvatarPath;
        }

        $user->update($userData);

        $parent->update([
            'phone' => $request->phone,
        ]);

        return redirect()->route('parent.profile.index')->with('success', 'تم تحديث البيانات الشخصية بنجاح');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();

        // Verify current password
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withInput()->with('error', 'كلمة المرور الحالية غير صحيحة');
        }

        // Update password
        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return back()->with('success', 'تم تغيير كلمة المرور بنجاح');
    }
}
