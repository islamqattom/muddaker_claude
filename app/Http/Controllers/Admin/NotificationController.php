<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller {
    public function markAllRead() { Auth::user()->notifications()->update(['is_read'=>true]); return back()->with('success','تم تحديد كل الإشعارات كمقروءة'); }
}
