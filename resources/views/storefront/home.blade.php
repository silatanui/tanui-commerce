@extends('layouts.storefront')
@section('title', $store->name)
@section('content')
    @include('storefront.themes.'.$store->slug.'.home')
@endsection
