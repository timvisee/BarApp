@extends('layouts.app')

@section('title', $economy->name)
@php
    $breadcrumbs = Breadcrumbs::generate('community.economy.show', $economy);
    $menusection = 'community_manage';

    use App\Http\Controllers\CurrencyController;
    use App\Http\Controllers\EconomyController;
    use App\Http\Controllers\EconomyWalletController;
@endphp

@section('content')
    <h2 class="ui header">@yield('title')</h2>

    @if(perms(EconomyController::permsManage()))
        <div class="ui vertical menu fluid">
            <h5 class="ui item header">@lang('pages.community.economy')</h5>
            <a href="{{ route('community.economy.edit', ['communityId' => $community->human_id, 'economyId' => $economy->id]) }}"
                    class="item">
                @lang('pages.economies.editEconomy')
            </a>
            <a href="{{ route('community.economy.delete', ['communityId' => $community->human_id, 'economyId' => $economy->id]) }}"
                    class="item">
                @lang('pages.economies.deleteEconomy')
            </a>
        </div>
    @endif

    <div class="ui vertical menu fluid">
        <h5 class="ui item header">@lang('misc.assets')</h5>
        <a href="{{ route('community.economy.product.index', [
                    'communityId' => $community->human_id,
                    'economyId' => $economy->id,
                ]) }}"
                class="item">
            @lang('pages.products.title')
        </a>
        <a href="{{ route('community.economy.inventory.index', [
                    'communityId' => $community->human_id,
                    'economyId' => $economy->id,
                ]) }}"
                class="item">
            @lang('pages.inventories.title')
        </a>
        <a href="{{ route('community.economy.payment.index', [
                    'communityId' => $community->human_id,
                    'economyId' => $economy->id,
                ]) }}"
                class="item">
            @lang('pages.economyPayments.title')
        </a>
        <a href="{{ route('community.economy.payservice.index', [
                    'communityId' => $community->human_id,
                    'economyId' => $economy->id,
                ]) }}"
                class="item">
            @lang('pages.paymentService.title')
        </a>
        <a href="{{ route('community.economy.balanceimport.index', [
                    'communityId' => $community->human_id,
                    'economyId' => $economy->id,
                ]) }}"
                class="item">
            @lang('pages.balanceImport.title')
        </a>
        <a href="{{ route('community.economy.finance.overview', [
                    'communityId' => $community->human_id,
                    'economyId' => $economy->id,
                ]) }}"
                class="item">
            @lang('pages.finance.title')
        </a>
        @if(perms(EconomyWalletController::permsManage()))
            <a href="{{ route('community.economy.wallets.overview', [
                        'communityId' => $community->human_id,
                        'economyId' => $economy->id,
                    ]) }}"
                    class="item">
                @lang('pages.economies.walletOperations')
            </a>
        @else
            <div class="item disabled">@lang('pages.economies.walletOperations')</div>
        @endif
    </div>

    @if(perms(CurrencyController::permsView()))
        @include('community.economy.include.currencyList', [
            'header' => __('misc.currencies') . ' (' .  $currencies->count() . ')',
            'currencies' => $currencies,
            'button' => [
                'label' => __('pages.currencies.manage'),
                'link' => route('community.economy.currency.index', [
                    'communityId' => $community->human_id,
                    'economyId' => $economy->id,
                ]),
            ],
        ])
    @endif

    <p>
        <a href="{{ route('community.economy.index', ['communityId' => $community->human_id]) }}"
                class="ui button basic">
            @lang('pages.economies.backToEconomies')
        </a>
    </p>

    <div class="ui fluid accordion">
        <div class="title">
            <i class="dropdown icon"></i>
            @lang('misc.details')
        </div>
        <div class="content">
            <table class="ui compact celled definition table">
                <tbody>
                    <tr>
                        <td>@lang('misc.name')</td>
                        <td>{{ $economy->name }}</td>
                    </tr>
                    <tr>
                        <td>@lang('misc.createdAt')</td>
                        <td>@include('includes.humanTimeDiff', ['time' => $economy->created_at])</td>
                    </tr>
                    @if($economy->created_at != $economy->updated_at)
                        <tr>
                            <td>@lang('misc.lastChanged')</td>
                            <td>@include('includes.humanTimeDiff', ['time' => $economy->updated_at])</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
@endsection
