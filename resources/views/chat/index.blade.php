@extends('layouts.navbar')

@section('content')
<div class="container-fluid" data-vue-component="chat-component">
    <!-- Chat Vue component will be mounted here -->
    <chat-component></chat-component>
</div>
@endsection

@push('styles')
<!-- Vue component has its own styling -->
@endpush

@push('scripts')
<!-- No JavaScript needed here as Vue component handles everything -->
@endpush
