@extends('layouts.admin')
@section('title','Route Assignment')
@section('content')
@php
  $isEditing = ($route && $route->exists);
  $currentSchedule = null;
  $daysValue = [];
  $timeValue = '07:00';
  if ($isEditing) {
      $currentSchedule = $route->schedules->first();
  }
  $needsFirstSchedule = (! $isEditing || $route->schedules->count() === 0);
  if ($currentSchedule) {
      $daysValue = $currentSchedule->days;
      $timeValue = '';
      if ($currentSchedule->pickup_time) {
          $timeValue = date('H:i', strtotime($currentSchedule->pickup_time));
      }
  }
  if (old('days')) {
      $daysValue = old('days');
  }
  if (old('pickup_time')) {
      $timeValue = old('pickup_time');
  }
@endphp
<h1>Route Assignment</h1>
<p class="sub">Assign a truck to a route, then draw the exact streets it covers directly on the map. Saving also creates/updates the linked schedule.</p>
<div class="grid route-grid">
  <form class="card" method="POST" id="route-form" action="@if($isEditing){{ route('cgso.routes.update', $route) }}@else{{ route('cgso.routes.store') }}@endif">
    @csrf @if($isEditing) @method('PUT') @endif
    <h3>@if($isEditing) ✎ Edit {{ $route->name }} @else 🚚 Route Details @endif</h3>
    <div class="field">
      <label>Truck</label>
      <select name="truck_id" id="truck" required>
        @foreach($trucks as $truck)
          <option value="{{ $truck->id }}" data-color="{{ $truck->color }}" @selected(old('truck_id', $isEditing ? $route->truck_id : null) == $truck->id)>{{ $truck->select_label }}</option>
        @endforeach
      </select>
    </div>
    <div class="field"><label>Route Name</label><input type="text" name="name" value="{{ old('name', $isEditing ? $route->name : '') }}" placeholder="e.g. Route A" required></div>
    @if($needsFirstSchedule)
      <div class="field">
        <label>Collection Days <span class="days-quick"><a data-days="weekdays">Weekdays</a><a data-days="all">Everyday</a></span></label>
        <div class="days">@foreach(\App\Models\Schedule::DAYS as $dayKey => $dayName)<label><input type="checkbox" name="days[]" value="{{ $dayKey }}" @checked(in_array($dayKey, $daysValue))> {{ $dayKey }}</label>@endforeach</div>
      </div>
      <div class="field"><label>Pickup Time</label><input type="time" name="pickup_time" value="{{ $timeValue }}" required><small class="sub">Select the scheduled pickup time, such as 7:00 AM. This creates the first schedule of the route.</small></div>
    @else
      <div class="field">
        <label>Schedules of this route</label>
        <ul class="list-small">
          @foreach($route->schedules as $linkedSchedule)
            <li>{{ $linkedSchedule->days_label }}, {{ $linkedSchedule->pickup_time_label }} @if(! $linkedSchedule->is_active)<span class="badge badge-Inactive">Inactive</span>@endif</li>
          @endforeach
        </ul>
        <small class="sub">A route can have several schedules. Add, change or switch them off on the <a href="{{ route('cgso.schedules') }}">Schedules</a> page. If you change the truck or barangay here, all these schedules change with it.</small>
      </div>
    @endif
    <div class="field">
      <label>Barangay</label>
      <select name="barangay_id" required>
        @foreach($barangays as $barangay)
          <option value="{{ $barangay->id }}" @selected(old('barangay_id', $isEditing ? $route->barangay_id : null) == $barangay->id)>{{ $barangay->name }}</option>
        @endforeach
      </select>
    </div>
    <div class="field">
      <label>Route Points / Areas (in order)</label>
      <div class="button-row"><input type="text" id="street" placeholder="Street / area name"><button class="btn sm" type="button" id="addStreet">Add</button></div>
      <ul id="streets" class="street-list"></ul>
    </div>
    <input type="hidden" name="path" id="path"><input type="hidden" name="streets" id="streetsJson">
    <div class="stack-grid">
      <button class="btn primary center-button" type="button" id="drawBtn">✎ Draw Route</button>
      <div class="three-buttons"><button class="btn sm" type="button" id="undo">↶ Undo</button><button class="btn sm" type="button" id="clear">⌫ Clear</button> @if($isEditing)<button class="btn sm danger" type="button" id="deleteRouteBtn">🗑 Delete</button>@else<span></span>@endif</div>
      <small class="sub" id="drawHint">Click "Draw Route", then click points along the streets on the map.</small> <button class="btn primary center-button">💾 Save Route</button> @if($isEditing)<a class="btn center-button" href="{{ route('cgso.routes') }}">+ New Route</a>@endif
    </div>
  </form>
  <div class="grid">
    <div class="card">
      <div id="map" class="map tall"></div>
      <div class="legend">@foreach($routes as $oneRoute)<span class="pill"><i class="dot" data-color="{{ $oneRoute->truck->color }}"></i>{{ $oneRoute->truck->label }} — {{ $oneRoute->name }}</span>@endforeach</div>
    </div>
    <div class="card">
      <h3>☰ All Assigned Routes</h3>
      <div class="table-wrap table-flat">
        <table>
          <thead>
            <tr>
              <th>Truck</th>
              <th>Route</th>
              <th>Barangay</th>
              <th>Day / Time</th>
              <th>Status</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            @forelse($routes as $oneRoute)
              @php($oneSchedule = $oneRoute->schedules->first())
              <tr>
                <td><span class="pill"><i class="dot" data-color="{{ $oneRoute->truck->color }}"></i>{{ $oneRoute->truck->label }}</span></td>
                <td>{{ $oneRoute->name }}</td>
                <td>{{ $oneRoute->barangay->name }}</td>
                <td>@if($oneSchedule){{ $oneSchedule->days_label }}, {{ $oneSchedule->pickup_time_label }}@else — @endif</td>
                <td>@if($oneSchedule && $oneSchedule->is_active)<span class="badge badge-Active">Active</span>@else<span class="badge badge-Inactive">Inactive</span>@endif</td>
                <td><a class="btn sm" href="{{ route('cgso.routes.edit', $oneRoute) }}">Open</a></td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="empty">No routes yet.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@if($isEditing)
  <form id="del-form" method="POST" action="{{ route('cgso.routes.destroy', $route) }}" data-confirm="Delete this route?">@csrf @method('DELETE')</form>
@endif
<script type="application/json" id="page-data">@json($pageData)</script>
@endsection
@push('scripts')
<script src="{{ asset('js/cgso-routes.js') }}"></script>
@endpush
