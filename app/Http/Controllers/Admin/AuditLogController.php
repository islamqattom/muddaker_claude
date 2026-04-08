<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = AuditLog::with('user')
            ->filterByRequest($request)
            ->latest()
            ->paginate(50);

        $users = User::query()
            ->whereIn('id', AuditLog::query()->select('user_id')->distinct()->whereNotNull('user_id'))
            ->pluck('name', 'id');

        return view('admin.audit-logs.index', [
            'logs' => $logs,
            'users' => $users,
            'modelTypes' => AuditLog::AUDITABLE_MODELS,
            'actions' => AuditLog::AUDIT_ACTIONS,
        ]);
    }

    public function show(AuditLog $auditLog)
    {
        $auditLog->load('user');
        return view('admin.audit-logs.show', compact('auditLog'));
    }

    public function export(Request $request)
    {
        $logs = AuditLog::with('user')
            ->filterByRequest($request)
            ->latest()
            ->get();

        if ($request->format === 'pdf') {
            $pdf = Pdf::loadView('admin.audit-logs.export-pdf', compact('logs'));
            return $pdf->download('audit-logs.pdf');
        }

        $csvContent = $this->generateCsv($logs);
        return response()->streamDownload(
            fn() => print($csvContent),
            'audit-logs.csv',
            ['Content-Type' => 'text/csv']
        );
    }

    protected function generateCsv($logs): string
    {
        $headers = ['ID', 'Date', 'User', 'Role', 'Action', 'Model Type', 'Model ID', 'IP Address', 'Old Values', 'New Values'];
        $output = fopen('php://temp', 'r+');

        fputcsv($output, $headers);

        foreach ($logs as $log) {
            fputcsv($output, [
                $log->id,
                $log->created_at?->format('Y-m-d H:i:s'),
                $this->escapeCsvField($log->user?->name ?? 'N/A'),
                $log->role,
                $log->action,
                $log->model_type,
                $log->model_id,
                $log->ip_address,
                $this->escapeCsvField(json_encode($log->old_values)),
                $this->escapeCsvField(json_encode($log->new_values)),
            ]);
        }

        rewind($output);
        $content = stream_get_contents($output);
        fclose($output);

        return $content;
    }

    protected function escapeCsvField(string $value): string
    {
        if (in_array(substr($value, 0, 1), ['=', '+', '-', '@'])) {
            return "'" . $value;
        }
        return $value;
    }
}
