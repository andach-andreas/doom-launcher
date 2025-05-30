@extends('layouts.app')

@section('title', 'Andach Doom')

@section('content')
    @php
        $missingIwads = [];
        if (!Storage::disk('iwads')->exists('DOOM.WAD')) {
            $missingIwads[] = 'DOOM.WAD';
        }
        if (!Storage::disk('iwads')->exists('DOOM2.WAD')) {
            $missingIwads[] = 'DOOM2.WAD';
        }
    @endphp

    @if (!empty($missingIwads))
        <x-andach-alert color="red">
            Missing IWAD files: {{ implode(', ', $missingIwads) }}. Please upload them to the <b>{{ storage_path() }}/iwads</b> folder.
        </x-andach-alert>
    @endif

    <x-andach-card title="Instructions">
        <p>This is a program to manage Doom installations, demo playback and recording on Windows computers. This is currently in a pre-alpha stage and not ready for general use.</p>
    </x-andach-card>

    <x-andach-card title="Storage">
        <p>Storage Root Folder is <b>{{ storage_path() }}</b></p>
        <p>This program stores all data inside the above folder, in the <b>installs</b>, <b>wads</b>, and <b>zips</b> directories. While using the pre-alpha version, closing the software and deleting this directory entirely can often fix synchronisation issues.</p>
    </x-andach-card>

    <x-andach-card title="Functionality">
        <p>Currently, only DSDA-Doom v0.29 is supported. This can be installed in the <a href="{{ route('port.index') }}">ports list</a>.</p>
        <p>Once installed, <a href="{{ route('wad.index') }}">wads can be downloaded</a>, which will automatically extract level details as well.</p>
    </x-andach-card>
@endsection
