@extends('layouts.app')

@section('content')

    <h2 class="ui header">@lang('pages.accountPage.addEmail.title')</h2>
    <p>@lang('pages.accountPage.addEmail.description')</p>

    {!! Form::open(['action' => ['EmailController@doCreate', 'userId' => $user->id], 'method' => 'POST', 'class' => 'ui form']) !!}

        <div class="field {{ ErrorRenderer::hasError('email') ? 'error' : '' }}">
            {{ Form::label('email', __('account.email') . ':') }}
            {{ Form::text('email', '', ['placeholder' => __('account.emailPlaceholder')]) }}
            {{ ErrorRenderer::inline('email') }}
        </div>

        <button class="ui button primary" type="submit">@lang('misc.add')</button>
        <a href="{{ route('account.emails', ['userId' => $user->id]) }}"
                class="ui button basic">
            @lang('general.cancel')
        </a>

    {!! Form::close() !!}

@endsection
