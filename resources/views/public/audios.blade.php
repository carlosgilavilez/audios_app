@extends('layouts.preview')

@section('title', 'Biblioteca de Audios')

@push('body-attrs', 'data-embed-mode="'.($isEmbed ? '1' : '0').'"')
@if($showPreviewBar)
    @push('body-attrs', 'data-wp-preview="1"')
    @push('body-attrs', 'data-wp-width="'.$previewWidth.'"')
@endif

@php
    $queryFor = function (array $overrides = [], array $removals = []) {
        $params = array_merge(request()->query(), $overrides);
        foreach (array_merge(['page'], $removals) as $key) {
            unset($params[$key]);
        }
        return array_filter($params, fn ($value) => $value !== null && $value !== '');
    };
@endphp

@push('styles')
    @vite('resources/css/public-library.css')
@endpush

@section('content')
    @include('public.partials.audio-list')
@endsection
