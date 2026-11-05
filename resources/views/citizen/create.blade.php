@extends('layouts.app')
@section('title','Report a Problem')
@section('content')
<div class="container narrow">
  <h1>Report a Garbage Problem</h1>
  <p class="sub">Fill in the details below to report a waste problem. Pin the exact location so a collector can find it quickly.</p>
  @include('partials.flash')
  <form class="card" method="POST" action="{{ route($role.'.reports.store') }}" enctype="multipart/form-data">
    @csrf <input type="hidden" name="submission_token" value="{{ old('submission_token', $submissionToken) }}">
    <div class="two-columns">
      <div class="field">
        <label>Category</label>
        <select name="category" required>
          <option value="">Select category</option>
          @foreach(\App\Models\Report::CATEGORIES as $category)
            <option @selected(old('category') === $category)>{{ $category }}</option>
          @endforeach
        </select>
      </div>
      <div class="field">
        <label>Barangay</label>
        <select name="barangay_id" required>
          <option value="">Select barangay</option>
          @foreach($barangays as $barangay)
            <option value="{{ $barangay->id }}" @selected(old('barangay_id', $selected) == $barangay->id)>{{ $barangay->name }}</option>
          @endforeach
        </select>
      </div>
    </div>
    <div class="field"><label>Specific Location (street / landmark)</label><input type="text" name="location_text" value="{{ old('location_text') }}" placeholder="e.g. Corner of Rizal St. and Luna St." required></div>
    <div class="field"><label>Description</label><textarea name="description" rows="3" placeholder="Describe the problem...">{{ old('description') }}</textarea></div>
    <div class="field"><label>Photo of the Waste</label><input type="file" name="photo" accept="image/jpeg,image/png,image/webp"><small class="sub">Upload a clear photo of the waste problem (JPG, PNG or WEBP, up to 5 MB).</small></div>
    <div class="field">
      <div class="label-row"><label>Map Location <span class="required-mark">*</span></label><button class="btn sm" type="button" id="loc">◇ Use My Current Location</button></div>
      <small class="sub">Click or tap the map to mark the waste location. You can drag the marker to adjust it.</small>
      <div id="map" class="map map-spaced"></div>
      <input type="hidden" name="lat" id="lat" value="{{ old('lat') }}"><input type="hidden" name="lng" id="lng" value="{{ old('lng') }}">
    </div>
    <button class="btn primary" id="submit" type="submit" disabled>Submit Report</button> <span class="sub" id="hint">Select a map location to enable submit.</span>
  </form>
</div>
<script type="application/json" id="page-data">@json(['lat' => old('lat'), 'lng' => old('lng')])</script>
@endsection
@push('scripts')
<script src="{{ asset('js/report-form.js') }}"></script>
@endpush
