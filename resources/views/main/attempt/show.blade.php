@extends('layouts.app')

@section('title', 'Map '.$attempt->mapCompleted->internal_name.' of '.$attempt->wad->foldername)

@section('content')
    <p><a href="{{ route('wad.show', $attempt->wad->id) }}">Back to Wad</a></p>
    @if ($attempt->zipExists())
        <p>{{ $attempt->zipPathFull() }}</p>
    @else
        <p><a href="{{ route('attempt.zip', $attempt->id) }}">Create Zip</a></p>
    @endif


    <h2>Attempt</h2>
    <div class="grid grid-cols-2">
        <div>Category</div>
        <div>{{ $attempt->category }}</div>

        <div>Time</div>
        <div>{{ $attempt->time }}</div>

        <div>Skill Number</div>
        <div>{{ $attempt->skill_number }}</div>

        <div>Respawn?</div>
        <div>{{ $attempt->respawn }}</div>

        <div>Fast?</div>
        <div>{{ $attempt->fast }}</div>

        <div>No Monsters?</div>
        <div>{{ $attempt->nomonsters }}</div>
    </div>

    <h2>Description File</h2>
    <form method="post" action="{{ route('attempt.update') }}">
        @csrf
        <input type="hidden" name="attempt_id" value="{{ $attempt->id }}" />
        <textarea name="description_file_content" cols="75" rows="20">{{ $attempt->descriptionFileContent() }}</textarea>
        <br />
        <input type="submit" />
    </form>
@endsection
