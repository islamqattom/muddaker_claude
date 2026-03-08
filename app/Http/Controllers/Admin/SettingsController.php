<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingsController extends Controller {
    public function index() {
        return view('admin.settings.index');
    }

    public function update(Request $request) {
        $request->validate([
            'center_name' => 'nullable|string|max:255',
            'center_email' => 'nullable|email',
            'center_phone' => 'nullable|string|max:20',
            'work_start_time' => 'nullable',
            'work_end_time' => 'nullable',
        ]);

        // Define settings to save
        $settings = [
            'center_name' => $request->center_name ?? 'مركز التسميع',
            'center_email' => $request->center_email ?? '',
            'center_phone' => $request->center_phone ?? '',
            'work_start_time' => $request->work_start_time ?? '08:00',
            'work_end_time' => $request->work_end_time ?? '20:00',
            'work_days' => implode(',', $request->work_days ?? ['saturday', 'sunday', 'monday', 'tuesday', 'thursday']),
            'enable_notifications' => $request->has('enable_notifications') ? 'true' : 'false',
        ];

        // Save each setting to database
        $now = now();
        foreach ($settings as $key => $value) {
            $existing = \DB::table('settings')->where('key', $key)->first();

            \DB::table('settings')->updateOrInsert(
                ['key' => $key],
                [
                    'value' => $value,
                    'group' => 'general',
                    'created_at' => $existing ? $existing->created_at : $now,
                    'updated_at' => $now,
                ]
            );

            // Update config for current session
            $configValue = $value;
            if ($value === 'true') {
                $configValue = true;
            } elseif ($value === 'false') {
                $configValue = false;
            }
            config(['app.' . $key => $configValue]);
        }

        return redirect()->route('admin.settings.index')->with('success', 'تم حفظ الإعدادات بنجاح');
    }
}
