@extends('layouts.app')

@section('title', __('pages.bunqAccounts.title'))

@section('content')
    <h2 class="ui header">
        @yield('title') ({{ count($accounts) }})

        <div class="sub header">
            @lang('misc.in')
            <a href="{{ route('app.manage') }}">
                {{ config('app.name') }}
            </a>
        </div>
    </h2>

    <p>@lang('pages.bunqAccounts.description')</p>

    <div class="ui vertical menu fluid">
        @forelse($accounts as $account)
            {{-- TODO: link to bunq account page --}}
            <a href="{{ route('app.bunqAccount.show', [
                'accountId' => $account->id
            ]) }}" class="item">
                {{ $account->name }}
            </a>
        @empty
            <div class="item">
                {{-- TODO: translate --}}
                <i>@lang('pages.bunqAccounts.noAccounts')</i>
            </div>
        @endforelse
    </div>

    <a href="{{ route('app.bunqAccount.create') }}"
            class="ui button basic positive">
        @lang('misc.add')
    </a>

    <a href="{{ route('app.manage') }}"
            class="ui button basic">
        @lang('general.goBack')
    </a>
@endsection
