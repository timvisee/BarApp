@extends('layouts.app')

@section('content')
    <h2 class="ui header">@lang('pages.bars')</h2>
    @include('bar.include.list')

    <h2 class="ui header">@lang('pages.communities')</h2>
    @include('community.include.list')
@endsection
