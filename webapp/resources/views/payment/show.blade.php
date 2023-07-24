@extends('layouts.app')

@section('title', __('pages.payments.details'))
@php
    $breadcrumbs = Breadcrumbs::generate('payment.show', $payment);

    use BarPay\Models\Payment;
@endphp

@section('content')
    <h2 class="ui header">@yield('title')</h2>

    <div class="ui divider hidden"></div>

    <p class="align-center" title="@lang('misc.description')">{{ $payment->displayName() }}</p>

    {{-- Amount & state icon --}}
    <div class="ui one small statistics">
        <div class="statistic">
            <div class="value">
                {!! $payment->formatCost(BALANCE_FORMAT_COLOR) !!}
            </div>
            <div class="label">@lang('misc.amount')</div>
        </div>
    </div>
    <br>
    <div class="ui one small statistics">
        @switch($payment->state)
            @case(Payment::STATE_INIT)
            @case(Payment::STATE_PENDING_COMMUNITY)
            @case(Payment::STATE_PENDING_AUTO)
                <div class="statistic yellow">
                    <div class="value">
                        <span class="halflings halflings-hourglass" title="{{ $payment->stateName() }}"></span>
                    </div>
                    <div class="label">@lang('misc.state')</div>
                </div>
                @break

            @case(Payment::STATE_PENDING_USER)
                <div class="statistic orange">
                    <div class="value">
                        <span class="halflings halflings-user" title="{{ $payment->stateName() }}"></span>
                    </div>
                    <div class="label">@lang('misc.state')</div>
                </div>
                @break

            @case(Payment::STATE_PROCESSING)
                <div class="statistic yellow">
                    <div class="value">
                        <span class="halflings halflings-refresh" title="{{ $payment->stateName() }}"></span>
                    </div>
                    <div class="label">@lang('misc.state')</div>
                </div>
                @break

            @case(Payment::STATE_COMPLETED)
                <div class="statistic green">
                    <div class="value">
                        <span class="halflings halflings-ok" title="{{ $payment->stateName() }}"></span>
                    </div>
                    <div class="label">@lang('misc.state')</div>
                </div>
                @break

            @case(Payment::STATE_REVOKED)
            @case(Payment::STATE_REJECTED)
                <div class="statistic red">
                    <div class="value">
                        <span class="halflings halflings-remove" title="{{ $payment->stateName() }}"></span>
                    </div>
                    <div class="label">@lang('misc.state')</div>
                </div>
                @break

            @case(Payment::STATE_FAILED)
                <div class="statistic red">
                    <div class="value">
                        <span class="halflings halflings-alert" title="{{ $payment->stateName() }}"></span>
                    </div>
                    <div class="label">@lang('misc.state')</div>
                </div>
                @break

            @default
                <div class="statistic">
                    <div class="value">
                        {{ $payment->stateName() }}
                    </div>
                    <div class="label">@lang('misc.state')</div>
                </div>
        @endswitch
    </div>

    <div class="ui divider hidden"></div>

    @if($payment->isInProgress())
        <div class="ui info message visible">
            <div class="header">@lang('pages.payments.inProgress')</div>
            <p>@lang('pages.payments.inProgressDescription')</p>
            <div class="ui buttons">
                @if($payment->isInProgress())
                    <a href="{{ route('payment.pay', ['paymentId' => $payment->id]) }}"
                            class="ui button positive">
                        @lang('misc.continue')
                    </a>
                @endif
                @if($payment->canCancel())
                    <a href="{{ route('payment.cancel', ['paymentId' => $payment->id]) }}"
                            class="ui button negative">
                        @lang('general.cancel')
                    </a>
                @endif
            </div>
        </div>
    @endif

    <div class="ui divider hidden"></div>

    <p>
        <a class="ui button primary"
                href="{{ route('dashboard') }}"
                title="@lang('pages.dashboard.title')">
            @lang('pages.dashboard.title')
        </a>
        <a href="{{ route('payment.index') }}"
                class="ui button basic">
            @lang('pages.payments.backToPayments')
        </a>
    </p>

    <div class="ui fluid accordion">
        <div class="title">
            <i class="dropdown icon"></i>
            @lang('misc.details')
        </div>
        <div class="content">
            {{-- Show link to transaction --}}
            @if(!empty($transaction))
                <div class="ui top vertical menu fluid">
                    <h5 class="ui item header">
                        @lang('pages.transactions.linkedTransaction')
                    </h5>

                    <a class="item"
                            href="{{ route('transaction.show', [
                                'transactionId' => $transaction->id,
                            ])}}">
                        {{ $transaction->describe() }}
                        <span class="subtle">
                            ({{ $transaction->stateName() }})
                        </span>

                        {!! $transaction->formatCost(BALANCE_FORMAT_LABEL) !!}

                        <span class="sub-label">
                            @include('includes.humanTimeDiff', ['time' => $transaction->updated_at ?? $transaction->created_at])
                        </span>
                    </a>
                </div>
            @endif

            {{-- Details --}}
            <table class="ui compact celled definition table">
                <tbody>
                    <tr>
                        <td>@lang('misc.state')</td>
                        <td>{{ $payment->stateName() }}</td>
                    </tr>
                    <tr>
                        <td>@lang('misc.reference')</td>
                        <td><code class="literal copy">{{ $payment->getReference() }}</code></td>
                    </tr>
                    @if($payment->service_id)
                        <tr>
                            <td>@lang('pages.paymentService.serviceType')</td>
                            <td>{{ $payment->displayName() }}</td>
                        </tr>
                    @endif
                    @if($payment->user_id == barauth()->getUser()->id)
                        <tr>
                            <td>@lang('misc.user')</td>
                            <td>{{ $payment->user->name }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td>@lang('misc.initiatedAt')</td>
                        <td>@include('includes.humanTimeDiff', ['time' => $payment->created_at])</td>
                    </tr>
                    @if($payment->created_at != $payment->updated_at)
                        <tr>
                            <td>@lang('misc.lastChanged')</td>
                            <td>@include('includes.humanTimeDiff', ['time' => $payment->updated_at])</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
@endsection
