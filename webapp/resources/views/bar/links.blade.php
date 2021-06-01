@extends('layouts.app')

@section('title', __('pages.bar.links.title'))

@section('content')
    <h2 class="ui header">
        @yield('title')

        <div class="sub header">
            @lang('misc.for')
            <a href="{{ route('bar.manage', ['barId' => $bar->human_id]) }}">
                {{ $bar->name }}
            </a>
        </div>
    </h2>
    <p>@lang('pages.bar.links.description')</p>

    <table class="ui compact celled definition table">
        <tbody>
            <tr>
                <td>@lang('pages.bar.links.linkBar')</td>
                <td><code class="literal copy">{{ route('bar.show', ['barId' => $bar->human_id]) }}</code></td>
            </tr>
            @if($bar->self_enroll)
                <tr>
                    <td>@lang('pages.bar.links.linkJoinBar')</td>
                    <td><code class="literal copy">{{ route('bar.join', ['barId' => $bar->human_id]) }}</code></td>
                </tr>
            @endif
            @if($bar->self_enroll && $bar->password)
                <tr>
                    <td>@lang('pages.bar.links.linkJoinBarCode')</td>
                    <td><code class="literal copy">{{ route('bar.join', ['barId' => $bar->human_id, 'code' => $bar->password]) }}</code></td>
                </tr>
            @endif
            <tr>
                <td>@lang('pages.bar.links.linkQuickWallet')</td>
                <td><code class="literal copy">{{ route('community.wallet.quickShow', [
                    'communityId' => $community->human_id,
                    'economyId' => $bar->economy_id
                ]) }}</code></td>
            </tr>
            <tr>
                <td>@lang('pages.bar.links.linkQuickTopUp')</td>
                <td><code class="literal copy">{{ route('community.wallet.quickTopUp', [
                    'communityId' => $community->human_id,
                    'economyId' => $bar->economy_id
                ]) }}</code></td>
            </tr>
        </tbody>
    </table>

    <a href="{{ route('bar.manage', ['barId' => $bar->human_id]) }}"
            class="ui button basic">
        @lang('pages.bar.backToBar')
    </a>
@endsection
