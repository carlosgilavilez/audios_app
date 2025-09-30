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
<style>
    html.dark body {
        background-image: linear-gradient(to bottom, rgba(156, 163, 175, 0.9) 0%, rgba(17, 24, 39, 0.95) 40%), url("{{ asset('images/logo rectangulo total.png') }}");
        background-size: 100% auto;
        background-position: top;
        background-repeat: no-repeat;
        background-attachment: fixed;
    }
    .bg-card {
        background-color: hsla(var(--card) / 0.7) !important;
    }
    .bg-muted\/60 {
        background-color: hsla(var(--muted) / 0.4) !important;
    }
    .border-border {
        border-color: hsla(var(--border) / 0.5) !important;
    }
    .bg-card\/80 {
        background-color: hsla(var(--card) / 0.6) !important;
    }
    .bg-card\/90 {
        background-color: hsla(var(--card) / 0.7) !important;
    }
    .bg-background {
        background-color: transparent !important;
    }
    .bg-muted\/40 {
        background-color: hsla(var(--muted) / 0.2) !important;
    }
    html.dark [data-results-count] {
        color: #fff !important;
    }
    html.dark .text-muted-foreground {
        color: #d1d5db !important;
    }
    html.dark .text-foreground {
        color: #fff !important;
    }
    html.dark .date-year-link {
        color: #fff !important;
    }
    html.dark .link-chip {
        color: #fff !important;
    }
</style>
@endpush

@section('content')
    @include('public.partials.audio-list')
@endsection
