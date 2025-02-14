@extends('layouts.app')

@section('title', __('pages.bar.purchaseSummary'))
@php
    $breadcrumbs = Breadcrumbs::generate('bar.summary', $bar);
    $menusection = 'bar_manage';
@endphp

@section('content')
    <h2 class="ui header bar-header">
        @yield('title')
    </h2>

    <div class="ui two item menu">
        <a href="{{ route('bar.summary', ['barId' => $bar->human_id]) }}" class="item {{ !$specificPeriod ? 'active' : '' }}">@lang('misc.recent')</a>
        <a href="{{ route('bar.summary', [
            'barId' => $bar->human_id,
            'time_from' => $timeFrom->toDateTimeLocalString('minute'),
            'time_to' => $timeTo->ceilMinute()->toDateTimeLocalString('minute'),
        ]) }}" class="item {{ $specificPeriod ? 'active' : '' }}">@lang('misc.specificPeriod')</a>
    </div>

    {{-- Period input --}}
    @if($specificPeriod)
        {!! Form::open([
            'method' => 'GET',
            'class' => 'ui form'
        ]) !!}
            <div class="two fields">
                <div class="required field {{ ErrorRenderer::hasError('time_from') ? 'error' : '' }}">
                    {{ Form::label('time_from', __('misc.fromTime') . ':') }}
                    {{ Form::datetimeLocal('time_from', $timeFrom?->toDateTimeLocalString('minute'), [
                        'min' => $bar->created_at->floorDay()->toDateTimeLocalString('minute'),
                        'max' => now()->ceilMinute()->toDateTimeLocalString('minute'),
                    ]) }}
                    {{ ErrorRenderer::inline('time_from') }}
                </div>

                <div class="required field {{ ErrorRenderer::hasError('time_to') ? 'error' : '' }}">
                    {{ Form::label('time_to', __('misc.toTime') . ':') }}
                    {{ Form::datetimeLocal('time_to', ($timeTo ?? now()->ceilMinute())->toDateTimeLocalString('minute'), [
                        'min' => $bar->created_at->floorDay()->toDateTimeLocalString('minute'),
                        'max' => now()->ceilMinute()->toDateTimeLocalString('minute'),
                    ]) }}
                    {{ ErrorRenderer::inline('time_to') }}
                </div>
            </div>

            <button class="ui button blue" type="submit">@lang('misc.apply')</button>

            <div class="ui buttons">
                @if(!$bar->created_at->addDay()->isFuture())
                    <a href="{{ route('bar.summary', [
                                'barId' => $bar->id,
                                'time_from' => now()->subDay()->max($bar->created_at)->toDateTimeLocalString('minute'),
                                'time_to' => now()->ceilMinute()->toDateTimeLocalString('minute'),
                            ]) }}"
                            class="ui button">
                        @lang('pages.inventories.period.day')
                    </a>
                @endif
                @if(!$bar->created_at->addWeek()->isFuture())
                    <a href="{{ route('bar.summary', [
                                'barId' => $bar->id,
                                'time_from' => now()->subWeek()->max($bar->created_at)->toDateTimeLocalString('minute'),
                                'time_to' => now()->ceilMinute()->toDateTimeLocalString('minute'),
                            ]) }}"
                            class="ui button">
                        @lang('pages.inventories.period.week')
                    </a>
                @endif
                @if(!$bar->created_at->addMonth()->isFuture())
                    <a href="{{ route('bar.summary', [
                                'barId' => $bar->id,
                                'time_from' => now()->subMonth()->max($bar->created_at)->toDateTimeLocalString('minute'),
                                'time_to' => now()->ceilMinute()->toDateTimeLocalString('minute'),
                            ]) }}"
                            class="ui button">
                        @lang('pages.inventories.period.month')
                    </a>
                @endif
            </div>

            <div class="ui hidden divider"></div>
        {!! Form::close() !!}
    @endif

    <p>@lang('pages.bar.purchaseSummaryDescription')</p>

    @if($summary->isNotEmpty())
        <p>
            @lang('pages.bar.purchaseSummaryDescriptionSum', [
                'quantity' => $quantity,
                'amount' => !$amount->isZero() ? $amount->formatAmount(BALANCE_FORMAT_COLOR) : 0,
                'from' => $timeFrom->longAbsoluteDiffForHumans(null, null),
                'to' => $timeTo->longRelativeDiffForHumans(null, null),
            ]):
        </p>

        @if($showingLimited)
            <div class="ui warning message">
                <span class="halflings halflings-warning-sign icon"></span>
                @lang('pages.bar.purchaseSummaryLimited')
            </div>
        @endif
    @endif

    @forelse($summary as $userSummary)
        <div class="ui top vertical menu fluid">

        {{-- Start user header, link to user summary if known --}}
        @if($userSummary['member'] != null)
            <a id="member-{{ $userSummary['member']->id }}"
                class="header item"
                href="{{ route('bar.member.show', [
                    'barId' => $bar->human_id,
                    'memberId' => $userSummary['member']->id,
                ]) }}">
        @else
            <div class="header item">
        @endif

            {{ $userSummary['owner']?->name ?? __('misc.unknownUser') }}

            {{-- Relative delay --}}
            <span class="subtle">
                &nbsp;&middot;&nbsp;
                @include('includes.humanTimeDiff', [
                    'time' => $userSummary['oldestUpdated'],
                    'absolute' => true,
                    'short' => true,
                ])
                @if($userSummary['oldestUpdated'] != $userSummary['newestUpdated'])
                    @lang('misc.to')
                    @include('includes.humanTimeDiff', [
                        'time' => $userSummary['newestUpdated'],
                        'absolute' => true,
                        'short' => true,
                    ])
                @endif
            </span>

            {!! $userSummary['amount']->formatAmount(BALANCE_FORMAT_LABEL, [
                'color' => true,
            ]) !!}

        {{-- End user header --}}
        @if($userSummary['member'] != null)
            </a>
        @else
            </div>
        @endif

        @foreach($userSummary['products'] as $userProducts)
            {{-- Start product item, link to product if known --}}
            @if($userProducts['product'] != null)
                <a class="item"
                    href="{{ route('community.economy.product.show', [
                        'communityId' => $bar->community_id,
                        'economyId' => $userProducts['product']->economy_id,
                        'productId' => $userProducts['product']->id,
                    ]) }}">
            @else
                <div class="item">
            @endif

            @if($userProducts['quantity'] != 1)
                <span class="subtle">{{ $userProducts['quantity'] }}×</span>
            @endif

            {{ $userProducts['name'] ?? __('misc.unknownProduct') }}
            {!! $userProducts['amount']->formatAmount(BALANCE_FORMAT_LABEL, [
                'color' => false,
            ]) !!}

            <span class="sub-label">
                {{-- Icon for delayed purchases --}}
                @if($userProducts['anyDelayed'])
                    <span class="halflings halflings-hourglass"></span>
                @endif

                {{-- Icon for kiosk purchases --}}
                @if($userProducts['anyInitiatedByKiosk'])
                    <span class="halflings halflings-shopping-cart"></span>
                @endif
            </span>

            {{-- End product item --}}
            @if($userProducts['product'] != null)
                </a>
            @else
                </div>
            @endif
        @endforeach

        </div>

    @empty
        <div class="ui top vertical menu fluid">
            <i class="item">@lang('pages.bar.noPurchases')...</i>
        </div>
    @endforelse

    <p>
        <div class="ui floating right labeled icon dropdown button">
            <i class="dropdown icon"></i>
            @lang('misc.moreInfo')
            <div class="menu">
                <a href="{{ route('bar.history', ['barId' => $bar->human_id]) }}"
                        class="item">
                    @lang('pages.bar.purchases')
                </a>
                <a href="{{ route('bar.tally', ['barId' => $bar->human_id]) }}"
                        class="item">
                    @lang('pages.bar.tallySummary')
                </a>
            </div>
        </div>

        <a href="{{ route('bar.manage', ['barId' => $bar->human_id]) }}"
                class="ui button basic">
            @lang('pages.bar.backToBar')
        </a>
    </p>
@endsection
