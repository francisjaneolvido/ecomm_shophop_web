{{-- ShopHop public marketplace — compact, rectangular, teal-first shopping layout. --}}
@extends('layouts.app')

@section('title', 'ShopHop — Hop In. Shop More.')

@push('styles')
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" href="{{ asset('css/shophop-marketplace-landing.css') }}?v=20261112-navy-pink-2">
@endpush

@section('content')
    <div class="sh-market" id="shop-top">
        @include('home.part-1')
        @include('home.part-2')
    </div>
@endsection

@push('scripts')
<script defer src="{{ asset('js/shophop-marketplace-landing.js') }}?v=20261112-navy-pink-2"></script>
@endpush
