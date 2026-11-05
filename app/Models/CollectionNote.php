<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CollectionNote extends Model
{
    protected $fillable = ['truck_id', 'truck_route_id', 'user_id', 'note', 'photo_path'];

    public function user() { return $this->belongsTo(User::class); }
    public function truck() { return $this->belongsTo(Truck::class); }
    public function route() { return $this->belongsTo(TruckRoute::class, 'truck_route_id'); }
}
