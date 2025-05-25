@extends('layouts.app')

@section('title', 'Map '.$map->name.' of '.$map->wad->name)

@section('content')
    <p><a href="{{ route('wad.show', $map->wad->id) }}">Back to Wad</a></p>

    <p><img src="{{ $map->map_image_url }}" /></p>
@endsection
