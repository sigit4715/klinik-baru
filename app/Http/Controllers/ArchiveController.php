<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\Service;
use Illuminate\Http\Request;

/**
 * Arsip Data: soft-deleted patients, doctors, medicines, services and appointments,
 * with a restore button. Records are never permanently deleted from here.
 */
class ArchiveController extends Controller
{
    private const TYPES = [
        'patients' => ['model' => Patient::class, 'label' => 'Pasien'],
        'doctors' => ['model' => Doctor::class, 'label' => 'Dokter'],
        'medicines' => ['model' => Medicine::class, 'label' => 'Obat'],
        'services' => ['model' => Service::class, 'label' => 'Layanan'],
        'appointments' => ['model' => Appointment::class, 'label' => 'Antrian'],
    ];

    public function index(Request $request)
    {
        $type = array_key_exists($request->query('type'), self::TYPES) ? $request->query('type') : 'patients';
        $model = self::TYPES[$type]['model'];

        $query = $model::onlyTrashed()->orderByDesc('deleted_at');
        if ($type === 'appointments') {
            $query->with(['patient', 'doctor']);
        }

        $search = trim((string) $request->query('search'));
        if ($search !== '') {
            $query->where(function ($q) use ($type, $search) {
                match ($type) {
                    'patients' => $q->where('name', 'like', "%{$search}%")->orWhere('no_rm', 'like', "%{$search}%")->orWhere('nik', 'like', "%{$search}%"),
                    'doctors' => $q->where('name', 'like', "%{$search}%")->orWhere('sip_number', 'like', "%{$search}%"),
                    'medicines', 'services' => $q->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"),
                    'appointments' => $q->where('queue_number', 'like', "%{$search}%"),
                };
            });
        }

        $items = $query->paginate(20)->withQueryString();
        $counts = collect(self::TYPES)->map(fn ($t) => $t['model']::onlyTrashed()->count());
        $types = collect(self::TYPES)->map(fn ($t) => $t['label']);

        $title = 'Arsip Data';
        $description = 'Data yang dihapus dan bisa dipulihkan';

        return view('pages.clinic.archive.index', compact('title', 'description', 'type', 'items', 'counts', 'types', 'search'));
    }

    public function restore($language, string $type, int $id)
    {
        abort_unless(array_key_exists($type, self::TYPES), 404);

        $record = self::TYPES[$type]['model']::onlyTrashed()->findOrFail($id);
        $record->restore();

        $name = $record->name ?? $record->queue_number ?? ('#' . $record->id);

        return redirect()->route('clinic.archive.index', [app()->getLocale(), 'type' => $type])
            ->with('success', self::TYPES[$type]['label'] . " {$name} berhasil dipulihkan.");
    }
}
