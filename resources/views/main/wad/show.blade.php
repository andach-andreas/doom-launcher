@extends('layouts.app')

@section('title', 'Wad - '.$wad->name)

@section('content')
    <p><a href="{{ route('wad.text', $wad->id) }}">View Text File</a></p>
    <p><a href="{{ route('install.play', [68, $wad->id]) }}">Play on DSDA</a></p>
    <p><a href="{{ route('dsda.sync', [$wad->id]) }}">Sync Records with DSDA</a></p>

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
                        <x-andach-td>
                            <form method="POST" action="{{ route('install.play') }}">
                                @csrf
                                <input type="hidden" name="install_id" value="68">
                                <input type="hidden" name="wad_id" value="{{ $map->wad->id }}">
                                <input type="hidden" name="map_id" value="{{ $map->id }}">
                                <input type="hidden" name="skill" value="4">
                                <input type="hidden" name="record" value="1">
                                <button type="submit">Play {{ $map->internal_name }}</button>
                            </form>


                            <form method="POST" action="{{ route('install.play') }}">
                                @csrf
                                <input type="hidden" name="install_id" value="68">
                                <input type="hidden" name="wad_id" value="{{ $map->wad->id }}">
                                <input type="hidden" name="map_id" value="{{ $map->id }}">
                                <input type="hidden" name="skill" value="4">
                                <button type="submit">Record Demo</button>
                            </form>
                        </x-andach-td>
                    </tr>
                @endforeach
            </x-andach-tbody>
        </x-andach-table>
    @endif

    <h2>Demos</h2>
    <x-andach-table>
        <x-andach-thead>
            <tr>
                <x-andach-th>Map</x-andach-th>
                <x-andach-th>Category</x-andach-th>
                <x-andach-th>Time</x-andach-th>
                <x-andach-th>Playback</x-andach-th>
            </tr>
        </x-andach-thead>
        <x-andach-tbody>
            @foreach ($wad->demosLink as $demo)
                <tr>
                    <x-andach-td>{{ $demo->map->internal_name ?? '' }}</x-andach-td>
                    <x-andach-td>{{ $demo->category }}</x-andach-td>
                    <x-andach-td>{{ $demo->time }}</x-andach-td>
                    <x-andach-td>
                        @php
                        dd($demo)
                        @endphp

                    </x-andach-td>
                </tr>
            @endforeach
        </x-andach-tbody>
    </x-andach-table>
@endsection
