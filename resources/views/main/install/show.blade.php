@extends('layouts.app')

@section('title', 'Ports')

@section('content')
    <div class="container">
        <h1>Install - {{ $install->port->name }} {{ $install->version }}</h1>

        <div class="grid grid-cols-2">
            <div>Port</div>
            <div><a href="{{ route('port.show', $install->port->id) }}">{{ $install->port->name }}</a></div>

            <div>Version</div>
            <div>{{ $install->version }}</div>

            <div>Download Date</div>
            <div>{{ $install->downloaded_at }}</div>

            <div>Extract Date</div>
            <div>
                @if ($install->extracted_at)
                    {{ $install->extracted_at }}
                @else
                    <a href="{{ route('install.extract', $install->id) }}">EXTRACT NOW</a>
                @endif
            </div>

            <div>Install Folder</div>
            <div>{{ $install->folder_path }}</div>

            <div>Path to Zip</div>
            <div>{{ $install->zip_path }}</div>
        </div>
    </div>
@endsection
