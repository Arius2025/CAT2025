@extends('layouts.app')

@section('content')
    @include('livewire.guide.index-page')
@endsection

@section('mobileDock')
@include('partials.mobile_dock_form', [
    'slots' => [
        [
            'label' => 'Mulai',
            'icon' => 'fa-play',
            'id' => 'dockQuickStartBtn',
            'style' => 'item',
            'aria' => 'Mulai Ujian',
        ],
        [
            'label' => 'Panduan',
            'icon' => 'fa-circle-info',
            'href' => route('guide.index'),
            'style' => 'cta',
            'aria' => 'Panduan',
        ],
        [
            'label' => 'Generate',
            'icon' => 'fa-wand-magic-sparkles',
            'href' => route('admin.soal.index'),
        ],
    ],
])
@endsection
