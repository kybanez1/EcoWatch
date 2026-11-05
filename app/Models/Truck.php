<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Truck extends Model
{
    public const STATUSES = ['Available', 'On Route', 'Maintenance'];
    public const COLORS = ['#2F9E44', '#1971C2', '#E8590C', '#9C36B5', '#E03131', '#1E9E8A', '#E0A030'];

    protected $fillable = ['truck_code', 'plate_number', 'capacity_tons', 'status', 'color', 'collector_id'];

    public function collector() { return $this->belongsTo(User::class, 'collector_id'); }
    public function routes() { return $this->hasMany(TruckRoute::class); }
    public function schedules() { return $this->hasMany(Schedule::class); }

    /** TRUCK-001 becomes "Truck #01" (the label used in the design). */
    public function getLabelAttribute()
    {
        $number = (int) preg_replace('/\D/', '', $this->truck_code);
        return 'Truck #' . str_pad((string) $number, 2, '0', STR_PAD_LEFT);
    }

    /** CSS class of the status badge, for example badge-OnRoute. */
    public function getBadgeClassAttribute()
    {
        return 'badge-' . str_replace(' ', '', $this->status);
    }

    public function getSelectLabelAttribute()
    {
        if ($this->collector) {
            return $this->label . ' — ' . $this->collector->name;
        }
        return $this->label;
    }
}
