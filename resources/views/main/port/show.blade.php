@extends('layouts.app')

@section('title', 'Ports')

@section('content')
    <div class="container">
        <h1>Ports - {{ $port->name }}</h1>

        <x-andach-table>
            <x-andach-thead>
                <tr>
                    <x-andach-th>Version</x-andach-th>
                    <x-andach-th>Download URL</x-andach-th>
                    <x-andach-th>Downloaded At</x-andach-th>
                    <x-andach-th>Extracted At</x-andach-th>
                </tr>
            </x-andach-thead>
            <x-andach-tbody>
                @foreach ($port->installs as $install)
                    <tr>
                        <x-andach-td><a href="{{ route('install.show', $install->id) }}">{{ $install->version }}</a></x-andach-td>
                        <x-andach-td>{{ $install->download_url }}</x-andach-td>
                        <x-andach-td>{{ $install->downloaded_at }}</x-andach-td>
                        <x-andach-td>{{ $install->extracted_at }}</x-andach-td>
                    </tr>
                @endforeach
            </x-andach-tbody>
        </x-andach-table>
    </div>
@endsection
