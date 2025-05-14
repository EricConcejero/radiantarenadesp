@extends('layouts.navbar')

@section('content')
<div class="container-fluid">
    <div id="app">
        <!-- Vue will mount the xat component here -->
        <xat-component
            :initial-conversation-id="{{ $id ? $id : 'null' }}"
        ></xat-component>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* We can remove any duplicate styles since they'll be handled in the Vue component */
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('xat page loaded - Vue component should initialize');
        // No need for the old JavaScript here as Vue will handle everything
    });
</script>
@endpush
