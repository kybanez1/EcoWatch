@extends('layouts.admin')
@section('title','Collection Monitoring')
@section('content')
<h1>Collection Monitoring</h1>
<p class="sub">Today's collection progress of every route, and the notes written by the collectors. Collection progress is separate from resident report statuses.</p>
<div class="grid grid-two align-start">
  <div class="card">
    <h3>☰ Today's Collection Progress</h3>
    @forelse($routes as $route)
      <div class="item-box spaced">
        <span class="pill"><i class="dot" data-color="{{ $route->truck->color }}"></i>{{ $route->truck->label }} — {{ $route->name }}</span> <small class="sub">Barangay {{ $route->barangay->name }}@if($route->truck->collector) · {{ $route->truck->collector->name }}@endif</small>
        @forelse($route->areas as $area)
          <div class="area-row"><span>{{ $area->name }}</span><span class="badge badge-{{ $area->today_status }}">{{ $area->today_status }}</span></div>
        @empty
          <p class="sub">No streets/areas on this route yet.</p>
        @endforelse
      </div>
    @empty
      <p class="sub">No routes yet.</p>
    @endforelse
  </div>
  <div class="card">
    <h3>📝 Collection Notes</h3>
    @forelse($notes as $note)
      <div class="note-item"><b>{{ $note->user->name ?? 'Removed user' }}</b> <span class="sub">· {{ $note->created_at->format('Y-m-d g:i A') }}</span><br> {{ $note->note }}<br> <small class="sub">@if($note->truck){{ $note->truck->label }}@endif @if($note->route) · {{ $note->route->name }}@endif</small> @if($note->photo_path) <a href="{{ asset($note->photo_path) }}" target="_blank">View photo</a>@endif</div>
    @empty
      <p class="sub">No collection notes yet.</p>
    @endforelse
    {{ $notes->links() }}
  </div>
</div>
@endsection
