<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use SoftDeletes, Auditable;

    protected $fillable = ['name', 'contact_person', 'phone', 'address', 'notes', 'status'];

    protected $attributes = ['status' => 'active'];

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function returns()
    {
        return $this->hasMany(PurchaseReturn::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
