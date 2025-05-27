@extends('layouts.app')

@section('title', 'Ports')

@section('content')
    <div class="container">
        <h1>Ports</h1>
        <p><a href="{{ route('port.sync') }}">Refresh Port List</a></p>

        <x-andach-table>
            <x-andach-thead>
                <tr>
                    <x-andach-th>Name</x-andach-th>
                    <x-andach-th>URL</x-andach-th>
                    <x-andach-th>Slug</x-andach-th>
                    <x-andach-th>Number of Installs</x-andach-th>
                    <x-andach-th>Latest Install</x-andach-th>
                </tr>
            </x-andach-thead>
            <x-andach-tbody>
                @foreach ($ports as $port)
                    <tr>
                        <x-andach-td><a href="{{ route('port.show', $port->id) }}">{{ $port->name }}</a></x-andach-td>
                        <x-andach-td>{{ $port->github_url }}</x-andach-td>
                        <x-andach-td>{{ $port->slug }}</x-andach-td>
                        <x-andach-td>{{ $port->installs->count() }}</x-andach-td>
                        <x-andach-td>{{ $port->latest_install }}</x-andach-td>
                    </tr>
                @endforeach
            </x-andach-tbody>
        </x-andach-table>
    </div>
@endsection
