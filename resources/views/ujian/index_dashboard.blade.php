@extends('layouts.app')

@section('content')
    @include('livewire.ujian.index-dashboard')
@endsection

@section('mobileDock')
@include('partials.mobile_dock_form', [
    'slots' => [
        [
            'label' => 'Ranking',
            'icon' => 'fa-trophy',
            'href' => route('ujian.dashboard'),
        ],
        [
            'label' => 'Mulai',
            'icon' => 'fa-play',
            'id' => 'dockQuickStartBtn',
            'style' => 'cta',
            'aria' => 'Mulai Ujian',
        ],
        [
            'label' => 'Generate',
            'icon' => 'fa-wand-magic-sparkles',
            'href' => route('admin.soal.index'),
        ],
    ],
])
@endsection
