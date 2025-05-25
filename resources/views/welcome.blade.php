@extends('layouts.app')

@section('title', 'Andach Doom')

@section('content')
    <div class="container">
        <p>This is a program to manage Doom installations, demo playback and recording on Windows computers.</p>
        <p>Storage Root Folder is <b>{{ storage_path() }}</b></p>
    </div>
@endsection
