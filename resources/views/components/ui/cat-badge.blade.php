@props(['name' => ''])
@php
  $map = [
    'Predicaciones'    => 'bg-blue-100 text-blue-800',
    'Temas Esenciales' => 'bg-orange-100 text-orange-800',
    'Conferencias'     => 'bg-purple-100 text-purple-800',
  ];
  $classes = $map[$name] ?? 'bg-gray-100 text-gray-800';
@endphp
<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $classes }}">
  {{ $name }}
</span>
