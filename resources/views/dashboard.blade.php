@extends('layouts.app')

@section('content')
    @livewire(\App\Modules\Dashboard\Livewire\DashboardOverview::class)
@endsection
