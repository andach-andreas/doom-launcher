@extends('layouts.app')

@section('title', 'Wad - '.$wad->name.' (Text Contents)')

@section('content')
    <p><a href="{{ route('wad.show', $wad->id) }}">Back to Wad</a></p>

    <pre>{{ $wad->text_file_contents }}</pre>
@endsection
