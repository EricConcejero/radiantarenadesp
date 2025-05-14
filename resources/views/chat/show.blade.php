@extends('layouts.navbar')

@section('content')
<div class="container-fluid">
    <div id="app">
        <!-- Vue chat component with the current conversation ID -->
        <chat-component
            :initial-conversation-id="{{ $conversacion->id_conversacion }}"
        ></chat-component>
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
        console.log('Chat conversation loaded - ID: {{ $conversacion->id_conversacion }}');
    });
</script>
@endpush
