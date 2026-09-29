<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Providers\SettingsServiceProvider;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        return view('admin.settings.index');
    }

    public function update(Request $request)
    {
        $validDays = implode(',', array_keys(SettingsServiceProvider::DAY_NAMES_AR));

        $request->validate([
            'center_name' => 'nullable|string|max:255',
            'center_email' => 'nullable|email',
            'center_phone' => 'nullable|string|max:20',
            'work_start_time' => 'nullable',
            'work_end_time' => 'nullable',
            'work_days' => 'nullable|array',
            'work_days.*' => 'nullable|string|in:'.$validDays,
            'recitation_days' => 'nullable|array',
            'recitation_days.*' => 'nullable|string|in:'.$validDays,
        ]);

        $defaultWorkDays = array_keys(SettingsServiceProvider::DAY_NAMES_AR);
        $defaultRecitationDays = explode(',', SettingsServiceProvider::DEFAULT_RECITATION_DAYS);

        // A checkbox group sends nothing when every day is unchecked. The
        // hidden "<group>_submitted" markers let us distinguish "all
        // unchecked" (save an empty list) from "field not sent" (keep the
        // default), so a deliberate empty selection is preserved.
        $selectedWorkDays = $this->selectedDays($request, 'work_days', $defaultWorkDays);
        $selectedRecitationDays = $this->selectedDays($request, 'recitation_days', $defaultRecitationDays);

        // Define settings to save
        $settings = [
            'center_name' => $request->center_name ?? 'مركز التسميع',
            'center_email' => $request->center_email ?? '',
            'center_phone' => $request->center_phone ?? '',
            'work_start_time' => $request->work_start_time ?? '08:00',
            'work_end_time' => $request->work_end_time ?? '20:00',
            'work_days' => implode(',', $selectedWorkDays),
            'recitation_days' => implode(',', $selectedRecitationDays),
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
            config(['app.'.$key => $configValue]);
        }

        return redirect()->route('admin.settings.index')->with('success', 'تم حفظ الإعدادات بنجاح');
    }

    /**
     * Resolve the selected days for a checkbox group. When the form's hidden
     * marker is present, an absent field means "all unchecked" (empty list);
     * otherwise (programmatic calls) fall back to the provided defaults.
     */
    private function selectedDays(Request $request, string $field, array $defaults): array
    {
        if ($request->has($field.'_submitted')) {
            return $request->input($field) ?? [];
        }

        return $request->input($field) ?? $defaults;
    }
}
