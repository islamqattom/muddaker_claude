<?php
namespace App\Http\Controllers\Supervisor;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller {
    public function markAllRead() {
        try {
            Notification::where('user_id', Auth::id())->where('is_read', false)->update(['is_read' => true]);
            return back()->with('success', 'تم تحديد الإشعارات كمقروءة');
        } catch (\Exception $e) {
            \Log::error('Failed to mark notifications read: ' . $e->getMessage());
            return back()->with('error', 'حدث خطأ أثناء تحديث الإشعارات');
        }
    }
}
