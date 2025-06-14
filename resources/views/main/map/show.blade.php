@extends('layouts.app')

@section('title', 'Map '.$map->internal_name.' of '.$map->wad->foldername)

@section('content')
    <p><a href="{{ route('wad.show', $map->wad->id) }}">Back to Wad</a></p>

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
        @foreach ($combined as $category => $times)
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


@endsection
