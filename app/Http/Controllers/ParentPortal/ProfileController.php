<?php
namespace App\Http\Controllers\ParentPortal;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller {
    public function index() {
        $parent = Auth::user()->parentProfile()->with('students.user', 'students.level')->first();
        return view('parent.profile.index', compact('parent'));
    }

    public function update(Request $request) {
        $user = Auth::user();
        $parent = $user->parentProfile;

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'image' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ]);

        $newAvatarPath = null;

        if ($request->hasFile('image')) {
            // Delete old avatar if exists
            if ($user->avatar) {
                try {
                    $oldPath = storage_path('app/public/' . $user->avatar);
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
}
