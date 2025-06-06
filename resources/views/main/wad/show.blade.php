@extends('layouts.app')

@section('title', 'Wad - '.$wad->name)

@section('content')
    <p><a href="{{ route('wad.text', $wad->id) }}">View Text File</a></p>
    <p><a href="{{ route('wad.viddump-all', $wad->id) }}">Dump all Videos to mp4</a></p>
    <p><a href="{{ route('install.play', [68, $wad->id]) }}">Play on DSDA</a></p>
    <p><a href="{{ route('dsda.sync', [$wad->id]) }}">Sync Records with DSDA</a></p>
    <p><a href="{{ route('attempt.sync', [$wad->id]) }}">Read Attempts on Disk</a></p>

    <x-andach-card title="Base WAD Information">
        <div class="grid grid-cols-2">
            <div>Filename</div>
            <div>{{ $wad->filename }}</div>

            <div>IDGames Path</div>
            <div>{{ $wad->idgames_path }}</div>

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
                @foreach ($maps as $map)
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
                                <button type="submit">Play {{ $map->internal_name }}</button>
                            </form>


                            <form method="POST" action="{{ route('install.play') }}">
                                @csrf
                                <input type="hidden" name="install_id" value="68">
                                <input type="hidden" name="wad_id" value="{{ $map->wad->id }}">
                                <input type="hidden" name="map_id" value="{{ $map->id }}">
                                <input type="hidden" name="record" value="1">
                                <select name="skill">
                                    @foreach ($skills as $value => $label)
                                        <option value="{{ $value }}" {{ $value === 4 ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
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
                <x-andach-th>Video</x-andach-th>
            </tr>
        </x-andach-thead>
        <x-andach-tbody>
            @foreach ($demos as $demo)
                <tr>
                    <x-andach-td>{{ $demo->map->internal_name ?? '' }}</x-andach-td>
                    <x-andach-td>{{ $demo->category }}</x-andach-td>
                    <x-andach-td>{{ $demo->time }}</x-andach-td>
                    <x-andach-td>
                        <form method="POST" action="{{ route('install.play') }}">
                            @csrf
                            <input type="hidden" name="install_id" value="68">
                            <input type="hidden" name="wad_id" value="{{ $map->wad->id }}">
                            <input type="hidden" name="map_id" value="{{ $map->id }}">
                            <input type="hidden" name="demo_id" value="{{ $demo->id }}">
                            <button type="submit">Playback Demo</button>
                        </form>
                    </x-andach-td>
                    <x-andach-td>
                        @if ($demo->viddump_path)
                            <a href="{{ $demo->viddump_full_path }}">{{ $demo->viddump_full_path }}</a>
                        @else
                            <form method="POST" action="{{ route('install.viddump') }}">
                                @csrf
                                <input type="hidden" name="install_id" value="68">
                                <input type="hidden" name="demo_id" value="{{ $demo->id }}">
                                <button type="submit">Dump Video</button>
                            </form>
                        @endif
                    </x-andach-td>
                </tr>
            @endforeach
        </x-andach-tbody>
    </x-andach-table>

    <h2>Attempts</h2>
    <x-andach-table>
        <x-andach-thead>
            <tr>
                <x-andach-th>Map Attempted</x-andach-th>
                <x-andach-th>Map Completed</x-andach-th>
                <x-andach-th>Category</x-andach-th>
                <x-andach-th>Time</x-andach-th>
                <x-andach-th>Playback</x-andach-th>
            </tr>
        </x-andach-thead>
        <x-andach-tbody>
            @foreach ($attempts as $attempt)
                <tr>
                    <x-andach-td>{{ $attempt->map->internal_name ?? '' }}</x-andach-td>
                    <x-andach-td>{{ $attempt->mapCompleted->internal_name ?? '' }}</x-andach-td>
                    <x-andach-td>{{ $attempt->category }}</x-andach-td>
                    <x-andach-td>{{ $attempt->time }}</x-andach-td>
                    <x-andach-td>
                        <form method="POST" action="{{ route('install.play') }}">
                            @csrf
                            <input type="hidden" name="install_id" value="68">
                            <input type="hidden" name="wad_id" value="{{ $map->wad->id }}">
                            <input type="hidden" name="map_id" value="{{ $map->id }}">
                            <input type="hidden" name="attempt_id" value="{{ $attempt->id }}">
                            <button type="submit">Playback Attempt (NOT YET WORKING)</button>
                        </form>
                    </x-andach-td>
                </tr>
            @endforeach
        </x-andach-tbody>
    </x-andach-table>
@endsection
