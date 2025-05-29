@extends('layouts.app')

@section('title', 'Wad - '.$wad->name)

@section('content')
    <p><a href="{{ route('wad.text', $wad->id) }}">View Text File</a></p>
    <p><a href="{{ route('install.play', [68, $wad->id]) }}">Play on DSDA</a></p>

    <x-andach-card title="Base WAD Information">
        <div class="grid grid-cols-2">
            <div>Filename</div>
            <div>{{ $wad->filename }}</div>

            <div>Download Date</div>
            <div>{{ $wad->downloaded_at }}</div>

            <div>Extract Date</div>
            <div>
                @if ($wad->extracted_at)
                    {{ $wad->extracted_at }}
                @else
                    <a href="{{ route('wad.download-and-extract', $wad->id) }}">DOWNLOAD AND EXTRACT NOW</a>
                @endif
            </div>
        </div>
    </x-andach-card>

    @if ($wad->maps->count())
        <h2>Maps</h2>

        <x-andach-table>
            <x-andach-thead>
                <tr>
                    <x-andach-th>Name</x-andach-th>
                    <x-andach-th>Things</x-andach-th>
                    <x-andach-th>Sectors</x-andach-th>
                    <x-andach-th>Play</x-andach-th>
                </tr>
            </x-andach-thead>
            <x-andach-tbody>
                @foreach ($wad->maps as $map)
                    <tr>
                        <x-andach-td><a href="{{ route('map.show', $map->id) }}">{{ $map->internal_name }}</a></x-andach-td>
                        <x-andach-td>{{ $map->count_things }}</x-andach-td>
                        <x-andach-td>{{ $map->count_sectors }}</x-andach-td>
                        <x-andach-td><a href="{{ route('install.play', [68, $map->wad->id, $map->id]) }}">Play on DSDA</a></x-andach-td>
                    </tr>
                @endforeach
            </x-andach-tbody>
        </x-andach-table>
    @endif
@endsection
