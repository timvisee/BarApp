{{-- TODO: implement pagination --}}

<div class="ui top vertical menu fluid{{ !empty($class) ? ' ' . implode(' ', $class) : '' }}">
    @foreach($groups as $group)
        {{-- Header --}}
        @if(isset($group['header']))
            <h5 class="ui item header">
                {{ $group['header'] }}
            </h5>
        @endif

        {{-- Payments --}}
        @forelse($group['payments'] as $payment)
            <a class="item"
                    href="{{ route('payment.show', [
                        'paymentId' => $payment->id,
                    ]) }}">
                {{ $payment->displayName() }}
                {!! $payment->formatCost(BALANCE_FORMAT_LABEL); !!}

                @if(isset($group['showUser']) && $group['showUser'] && $payment->user)
                    <span class="subtle">
                        @lang('misc.by') {{ $payment->user->first_name }}
                    </span>
                @endif

                {{-- TODO: show some other stat here --}}
                <span class="sub-label">
                    @include('includes.humanTimeDiff', ['time' => $payment->updated_at ?? $payment->created_at, 'short' => true])
                </span>
            </a>
        @empty
            {{-- TODO: improve this translation --}}
            <i class="item">@lang('pages.payments.noPayments')</i>
        @endforelse
    @endforeach

    {{-- Bottom button --}}
    @if(isset($button))
        <a href="{{ $button['link'] }}" class="ui bottom attached button">
            {{ $button['label'] }}
        </a>
    @endif
</div>
