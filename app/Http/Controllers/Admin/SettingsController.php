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

        // Update config for current session (in-memory)
        $settings = [
            'center_name',
            'center_email',
            'center_phone',
            'work_start_time',
            'work_end_time',
            'enable_notifications',
        ];

        foreach ($settings as $key) {
            if ($request->has($key)) {
                config(['app.' . $key => $request->$key]);
            }
        }

        if ($request->has('work_days') && is_array($request->work_days)) {
            config(['app.work_days' => implode(',', $request->work_days)]);
        }

        // Try to persist to .env file with error handling
        try {
            $envPath = base_path('.env');
            if (is_writable($envPath)) {
                $envContent = file_get_contents($envPath);
                $envContent = $this->updateEnvValue($envContent, 'CENTER_NAME', $request->center_name ?? 'مركز حفظ القرآن الكريم');
                $envContent = $this->updateEnvValue($envContent, 'CENTER_EMAIL', $request->center_email ?? '');
                $envContent = $this->updateEnvValue($envContent, 'CENTER_PHONE', $request->center_phone ?? '');
                $envContent = $this->updateEnvValue($envContent, 'WORK_START_TIME', $request->work_start_time ?? '08:00');
                $envContent = $this->updateEnvValue($envContent, 'WORK_END_TIME', $request->work_end_time ?? '20:00');
                $envContent = $this->updateEnvValue($envContent, 'WORK_DAYS', implode(',', $request->work_days ?? ['saturday', 'sunday', 'monday', 'tuesday', 'thursday']));
                $envContent = $this->updateEnvValue($envContent, 'ENABLE_NOTIFICATIONS', $request->enable_notifications ? 'true' : 'false');

                file_put_contents($envPath, $envContent);
            }
        } catch (\Exception $e) {
            // Log error but don't fail - settings work in memory
            \Log::error('Failed to write settings to .env: ' . $e->getMessage());
        }

        return redirect()->route('admin.settings.index')->with('success', 'تم حفظ الإعدادات بنجاح');
    }

    private function updateEnvValue($content, $key, $value) {
        if (preg_match('/^' . $key . '=.*$/m', $content)) {
            return preg_replace('/^' . $key . '=.*$/m', $key . '=' . $value, $content);
        } else {
            return $content . "\n" . $key . '=' . $value;
        }
    }
}
