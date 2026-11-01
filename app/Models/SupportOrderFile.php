<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;

/** A result file on the private disk; only reachable through SupportOrderController::download */
class SupportOrderFile extends Model
{
    use Auditable;

    protected $fillable = ['original_name', 'path', 'mime', 'size'];

    public function order()
    {
        return $this->belongsTo(SupportOrder::class, 'support_order_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime, 'image/');
    }

    public function sizeLabel(): string
    {
        return $this->size >= 1048576 ? round($this->size / 1048576, 1) . ' MB' : max(1, round($this->size / 1024)) . ' KB';
    }
}
