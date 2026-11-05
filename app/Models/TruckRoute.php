<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TruckRoute extends Model
{
    protected $fillable = ['truck_id', 'barangay_id', 'name', 'path'];

    protected $casts = ['path' => 'array'];

    public function truck() { return $this->belongsTo(Truck::class); }
    public function barangay() { return $this->belongsTo(Barangay::class); }
    public function areas() { return $this->hasMany(CollectionArea::class)->orderBy('sort'); }
    public function schedules() { return $this->hasMany(Schedule::class); }

    /** Data used by the Leaflet maps. */
    public function toMap()
    {
        $path = $this->path;
        if (! $path) {
            $path = [];
        }
        return [
            'id' => $this->id,
            'name' => $this->name,
            'truck' => $this->truck->label,
            'truck_id' => $this->truck_id,
            'color' => $this->truck->color,
            'barangay' => $this->barangay->name,
            'barangay_id' => $this->barangay_id,
            'path' => $path,
        ];
    }
}
