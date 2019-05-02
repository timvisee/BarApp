@extends('layouts.app')

@section('title', $bar->name)

@php
    use \App\Http\Controllers\BarController;
    use \App\Http\Controllers\BarMemberController;
@endphp

@section('content')
    <h2 class="ui header">
        @if($joined)
            <a href="{{ route('bar.leave', ['barId' => $bar->human_id]) }}"
                    class="ui right pointing label green joined-label-popup"
                    data-title="@lang('pages.bar.joined')"
                    data-content="@lang('pages.bar.joinedClickToLeave')">
                <span class="halflings halflings-ok"></span>
            </a>
        @endif

        @yield('title')

        @if($joined)
            <a href="{{ route('community.wallet.list', [
                        'communityId' => $community->human_id,
                        'economyId' => $economy->id
                    ]) }}">
                {!! $economy->formatBalance(BALANCE_FORMAT_LABEL) !!}
            </a>
        @endif
    </h2>

    @unless($joined)
        <div class="ui info message visible">
            <div class="header">@lang('pages.bar.notJoined')</div>
            <p>@lang('pages.bar.hintJoin')</p>
            <a href="{{ route('bar.join', ['barId' => $bar->human_id]) }}"
                    class="ui button small positive basic">
                @lang('pages.bar.join')
            </a>
        </div>
    @endif

    <div class="ui vertical menu fluid">
        {!! Form::open(['action' => ['BarController@show', $bar->human_id], 'method' => 'GET', 'class' => 'ui form']) !!}
            <div class="item">
                <div class="ui transparent icon input">
                    {{ Form::text('q', Request::input('q'), [
                        'placeholder' => 'Search products...'
                    ]) }}
                    {{-- TODO: remove icon class? --}}
                    <i class="icon glyphicons glyphicons-search link"></i>
                </div>
            </div>
        {!! Form::close() !!}

        @forelse($products as $product)
            <a href="{{ route('bar.quickBuy', [
                        'barId' => $bar->human_id,
                        'productId' => $product->id,
                    ]) }}" class="item">
                {{ $product->displayName() }}

                <span class="sub-label">
                    bought {{ $product->count }}×
                </span>
            </a>
        @empty
            <i class="item">No products...</i>
        @endforelse

        <a href="#" class="ui bottom attached button">
            Show all
        </a>
    </div>

    <br />

    <a href="{{ route('community.show', ['communityId' => $community->human_id]) }}"
            class="ui button basic">
        @lang('pages.community.viewCommunity')
    </a>

    @if(perms(BarMemberController::permsView()))
        <a href="{{ route('bar.member.index', ['barId' => $bar->human_id]) }}"
                class="ui button basic">
            @lang('pages.barMembers.title')
        </a>
    @endif

    <a href="{{ route('community.wallet.list', ['communityId' => $community->human_id, 'economyId' => $bar->economy_id]) }}"
            class="ui button basic">
        @lang('pages.wallets.yourWallets')
    </a>

    @if(perms(BarController::permsManage()))
        <a href="{{ route('bar.edit', ['barId' => $bar->human_id]) }}"
                class="ui button basic">
            @lang('pages.bar.editBar')
        </a>
    @endif
@endsection
