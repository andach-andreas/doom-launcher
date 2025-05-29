@extends('layouts.app')

@section('title', 'Ports')

@section('content')
    <x-andach-alert color="blue">
        This page lists all ports available for installation. At the moment, only DSDA Doom v0.29 is supported. Initially, you will need to refresh the port list, as no installable versions will be found.
    </x-andach-alert>

    <x-andach-button link="{{ route('port.sync') }}" color="green">Refresh Port List</x-andach-button>

    <x-andach-table>
        <x-andach-thead>
            <tr>
                <x-andach-th>Name</x-andach-th>
                <x-andach-th>URL</x-andach-th>
                <x-andach-th>Slug</x-andach-th>
                <x-andach-th>Versions Available</x-andach-th>
                <x-andach-th>Versions Installed</x-andach-th>
                <x-andach-th>Latest Version</x-andach-th>
            </tr>
        </x-andach-thead>
        <x-andach-tbody>
            @foreach ($ports as $port)
                <tr>
                    <x-andach-td><a href="{{ route('port.show', $port->id) }}">{{ $port->name }}</a></x-andach-td>
                    <x-andach-td>{{ $port->github_url }}</x-andach-td>
                    <x-andach-td>{{ $port->slug }}</x-andach-td>
                    <x-andach-td>{{ $port->installs->count() }}</x-andach-td>
                    <x-andach-td>{{ $port->installs()->installed()->count() }}</x-andach-td>
                    <x-andach-td>{{ $port->latest_install->version ?? '' }}</x-andach-td>
                </tr>
            @endforeach
        </x-andach-tbody>
    </x-andach-table>
@endsection
