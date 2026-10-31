<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\UniqueConstraintViolationException;

class MedicalLetter extends Model
{
    use Auditable;

    protected $fillable = [
        'type', 'patient_id', 'doctor_id', 'medical_record_id', 'issued_date',
        'purpose', 'rest_days', 'rest_from', 'rest_until', 'show_diagnosis',
        'referred_to', 'referred_department', 'referral_reason', 'findings', 'therapy',
        'diagnosis', 'notes', 'vitals',
    ];

    protected $casts = [
        'issued_date' => 'date',
        'rest_from' => 'date',
        'rest_until' => 'date',
        'show_diagnosis' => 'boolean',
        'vitals' => 'array',
    ];

    public const TYPES = [
        'healthy' => 'Surat Keterangan Sehat',
        'sick' => 'Surat Keterangan Sakit',
        'referral' => 'Surat Rujukan',
    ];

    /** Code in the letter number, e.g. 001/SKS/XI/2026 */
    public const CODES = [
        'healthy' => 'SKBS',
        'sick' => 'SKS',
        'referral' => 'RUJ',
    ];

    public const TYPE_BADGES = [
        'healthy' => 'success',
        'sick' => 'warning',
        'referral' => 'info',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class)->withTrashed();
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class)->withTrashed();
    }

    public function medicalRecord()
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function isVoid(): bool
    {
        return $this->status === 'void';
    }

    /** Save with the next number for its type and year, retrying on a concurrent collision */
    public function saveWithNumber(int $attempts = 5): void
    {
        for ($i = 1; ; $i++) {
            $this->letter_number = self::nextNumber($this->type, Carbon::parse($this->issued_date));
            try {
                $this->save();

                return;
            } catch (UniqueConstraintViolationException $e) {
                if ($i >= $attempts) {
                    throw $e;
                }
            }
        }
    }

    public static function nextNumber(string $type, Carbon $date): string
    {
        $code = self::CODES[$type];
        $suffix = "/{$code}/";
        $year = $date->format('Y');

        $max = self::where('type', $type)
            ->where('letter_number', 'like', "%{$suffix}%/{$year}")
            ->pluck('letter_number')
            ->map(fn ($n) => (int) strtok($n, '/'))
            ->max();

        $roman = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'][(int) $date->format('n')];

        return str_pad((string) (($max ?? 0) + 1), 3, '0', STR_PAD_LEFT) . $suffix . $roman . '/' . $year;
    }

    /** Indonesian number words, e.g. 3 -> "tiga" (used for rest days) */
    public static function terbilang(int $n): string
    {
        $words = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];
        if ($n < 12) {
            return $words[$n] ?: 'nol';
        }
        if ($n < 20) {
            return self::terbilang($n - 10) . ' belas';
        }
        if ($n < 100) {
            return trim(self::terbilang(intdiv($n, 10)) . ' puluh ' . ($n % 10 ? self::terbilang($n % 10) : ''));
        }
        if ($n < 200) {
            return trim('seratus ' . ($n % 100 ? self::terbilang($n % 100) : ''));
        }
        if ($n < 1000) {
            return trim(self::terbilang(intdiv($n, 100)) . ' ratus ' . ($n % 100 ? self::terbilang($n % 100) : ''));
        }

        return (string) $n;
    }
}
