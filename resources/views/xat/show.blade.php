@extends('layouts.navbar')

@section('content')
<div class="container-fluid">
    <div id="app">
        <!-- Vue xat component with the current conversation ID -->
        <xat-component
            :initial-conversation-id="{{ $conversacion->id_conversacion }}"
        ></xat-component>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Styles will be handled by the Vue component */
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('xat conversation loaded - ID: {{ $conversacion->id_conversacion }}');
    });
</script>
@endpush
