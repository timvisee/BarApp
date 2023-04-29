{{-- TODO: implement pagination --}}

<div class="ui top vertical menu fluid">
    @foreach($groups as $group)
        {{-- Header --}}
        @if(isset($group['header']))
            <h5 class="ui item header">
                {{ $group['header'] }}
            </h5>
        @endif

        {{-- Mutations --}}
        {{-- TODO: show empty message if no transactions --}}
        @foreach($group['mutations'] as $mutation)
            <a class="item"
                    href="{{ route('transaction.mutation.show', [
                        'transactionId' => $mutation->transaction_id,
                        'mutationId' => $mutation->id,
                    ]) }}">
                {{ $mutation->describe() }}
                {!! $mutation->formatAmount(BALANCE_FORMAT_LABEL, ['neutral' => true]); !!}

                <span class="sub-label">
                    @include('includes.humanTimeDiff', [
                        'time' => $mutation->updated_at ?? $mutation->created_at,
                        'absolute' => true,
                        'short' => true,
                    ])
                </span>
            </a>
        @endforeach
    @endforeach

    {{-- Bottom button --}}
    @if(isset($button))
        <a href="{{ $button['link'] }}" class="ui bottom attached button">
            {{ $button['label'] }}
        </a>
    @endif
</div>
