@extends('layouts.app')

@section('title', 'Map '.$map->internal_name.' of '.$map->wad->foldername)

@section('content')
    <p><a href="{{ route('wad.show', $map->wad->id) }}">Back to Wad</a></p>

    <h2>Map</h2>
    <img src="{{ $map->renderTopDownMapImage() }}" />
    <p>{{ $map->renderTopDownMapImage() }}</p>

    <h2>Summary of Times</h2>
    <table class="table-auto w-full border">
        <thead>
        <tr>
            <th class="border px-4 py-2">Category</th>
            <th class="border px-4 py-2">Demo Time</th>
            <th class="border px-4 py-2">Attempt Time</th>
            <th class="border px-4 py-2">Faster?</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($combinedTimes as $category => $times)
            <tr>
                <td class="border px-4 py-2">{{ $category }}</td>
                <td class="border px-4 py-2">{{ $times['demo'] ?? '-' }}</td>
                <td class="border px-4 py-2">{{ $times['attempt'] ?? '-' }}</td>
                <td class="border px-4 py-2">
                    @if ($times['faster'])
                        ✅
                    @elseif ($times['demo'] && $times['attempt'])
                        ❌
                    @else
                        -
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

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
