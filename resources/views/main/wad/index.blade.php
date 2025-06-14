@extends('layouts.app')

@section('title', 'Wads')

@section('content')
    <x-andach-alert color="blue">
        This page shows all installed Wads, and allows you to click through to see and play individual levels in them. To install a wad, first search its name in the box below. The ZIP file will be automatically downloaded into the /zips/ folder, and unzipped into the /wads/ folder.
    </x-andach-alert>


    <x-andach-card title="New Wad">
        <form method="POST" action="{{ route('wad.download-and-extract', ['wadName' => '']) }}" onsubmit="event.preventDefault(); this.action = this.action.replace(/wad\/$/, 'wad/' + encodeURIComponent(this.wadName.value) + '/download-and-extract'); this.submit();">
            @csrf
            <input type="text" name="wadName" placeholder="Enter WAD Name" required>

            <button type="submit">Download and Extract WAD</button>
        </form>
    </x-andach-card>

    <x-andach-table>
        <x-andach-thead>
            <tr>
                <x-andach-th>Filename</x-andach-th>
                <x-andach-th>Name</x-andach-th>
                <x-andach-th>Comp Level</x-andach-th>
                <x-andach-th>IWAD</x-andach-th>
                <x-andach-th>Downloaded At</x-andach-th>
                <x-andach-th>Extracted At</x-andach-th>
            </tr>
        </x-andach-thead>
        <x-andach-tbody>
            @foreach ($wads as $wad)
                <tr>
                    <x-andach-td><a href="{{ route('wad.show', $wad->id) }}">{{ $wad->foldername }}</a></x-andach-td>
                    <x-andach-td>{{ $wad->name }}</x-andach-td>
                    <x-andach-td>{{ $wad->comp_level_id }}</x-andach-td>
                    <x-andach-td>{{ $wad->iwad }}</x-andach-td>
                    <x-andach-td>{{ $wad->downloaded_at }}</x-andach-td>
                    <x-andach-td>{{ $wad->extracted_at }}</x-andach-td>
                </tr>
            @endforeach
        </x-andach-tbody>
    </x-andach-table>
@endsection
