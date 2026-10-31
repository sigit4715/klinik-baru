<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Read-only audit log viewer. There is intentionally no edit or delete.
 */
class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'user_id' => 'nullable|integer',
            'event' => ['nullable', Rule::in(array_keys(AuditLog::EVENTS))],
            'model' => ['nullable', Rule::in(array_keys(AuditLog::MODELS))],
            'record_id' => 'nullable|integer',
            'from' => 'nullable|date',
            'to' => 'nullable|date|after_or_equal:from',
            'search' => 'nullable|string|max:100',
        ]);

        $logs = AuditLog::query()
            ->when($request->user_id, fn ($q, $id) => $q->where('user_id', $id))
            ->when($request->event, fn ($q, $event) => $q->where('event', $event))
            ->when($request->model, fn ($q, $model) => $q->where('auditable_type', $model))
            ->when($request->record_id, fn ($q, $id) => $q->where('auditable_id', $id))
            ->when($request->from, fn ($q, $from) => $q->where('created_at', '>=', Carbon::parse($from)->startOfDay()))
            ->when($request->to, fn ($q, $to) => $q->where('created_at', '<=', Carbon::parse($to)->endOfDay()))
            ->when($request->search, fn ($q, $s) => $q->where(fn ($w) => $w->where('description', 'like', "%{$s}%")->orWhere('user_name', 'like', "%{$s}%")))
            ->orderByDesc('id')
            ->paginate(50)
            ->withQueryString();

        $title = 'Audit Log';
        $description = 'Riwayat aktivitas pengguna';
        $users = User::orderBy('name')->get(['id', 'name']);
        $events = AuditLog::EVENTS;
        $models = AuditLog::MODELS;

        return view('pages.clinic.audit-logs.index', compact('title', 'description', 'logs', 'users', 'events', 'models'));
    }
}
