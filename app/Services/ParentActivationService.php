<?php

namespace App\Services;

use App\Models\ParentProfile;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ParentActivationService
{
    /**
     * Re-evaluate a single parent's account status from the active state of
     * their linked children.
     *
     * Returns an event array when the parent was changed, or null when no
     * change was needed. Assumes the caller runs it inside a transaction.
     *
     * @return array{parent: User, action: string}|null
     */
    public function syncParentStatus(ParentProfile $parent, ?int $adminId = null): ?array
    {
        $parent->load('user');
        $parentUser = $parent->user;

        if (! $parentUser) {
            return null;
        }

        $students = $parent->students()->with('user')->get();

        // Rule 3: a parent with zero linked children is never touched.
        if ($students->isEmpty()) {
            return null;
        }

        $allInactive = $students->every(
            fn (Student $student) => ! optional($student->user)->is_active
        );

        if ($allInactive) {
            if (! $parentUser->is_active) {
                // Already inactive: keep whatever caused it (manual or system).
                return null;
            }

            $parentUser->update([
                'is_active' => false,
                'deactivated_by_system' => true,
            ]);

            Log::info('Parent account auto-deactivated', [
                'admin_id' => $adminId,
                'parent_user_id' => $parentUser->id,
                'parent_profile_id' => $parent->id,
                'children_count' => $students->count(),
            ]);

            return ['parent' => $parentUser, 'action' => 'deactivated'];
        }

        // At least one active child: only auto-reactivate accounts the system
        // deactivated. Manual admin deactivations are left alone.
        if (! $parentUser->is_active && $parentUser->deactivated_by_system) {
            $parentUser->update([
                'is_active' => true,
                'deactivated_by_system' => false,
            ]);

            Log::info('Parent account auto-reactivated', [
                'admin_id' => $adminId,
                'parent_user_id' => $parentUser->id,
                'parent_profile_id' => $parent->id,
            ]);

            return ['parent' => $parentUser, 'action' => 'reactivated'];
        }

        return null;
    }

    /**
     * Change a student's active state and re-evaluate every linked parent.
     *
     * @return array{is_active: bool|null, events: array<int, array>}
     */
    public function setStudentActive(Student $student, bool $active, ?int $adminId = null): array
    {
        return DB::transaction(function () use ($student, $active, $adminId) {
            $student->load('user');
            $studentUser = $student->user;

            if (! $studentUser) {
                return ['is_active' => null, 'events' => []];
            }

            $studentUser->update(['is_active' => $active]);

            $events = [];
            foreach ($student->parents()->get() as $parentProfile) {
                $event = $this->syncParentStatus($parentProfile, $adminId);
                if ($event) {
                    $events[] = $event;
                }
            }

            return ['is_active' => $active, 'events' => $events];
        });
    }

    /**
     * Build the Arabic flash messages for parent auto-change events.
     *
     * @param  array<int, array{parent: User, action: string}>  $events
     * @return array<int, string>
     */
    public function flashMessages(array $events): array
    {
        $messages = [];

        foreach ($events as $event) {
            $name = $event['parent']->name;
            $messages[] = $event['action'] === 'deactivated'
                ? "تم تعطيل حساب ولي الأمر {$name} تلقائيًا لأن جميع أبنائه معطّلون"
                : "تم تفعيل حساب ولي الأمر {$name} تلقائيًا لأن أحد أبنائه أصبح نشطًا";
        }

        return $messages;
    }
}
