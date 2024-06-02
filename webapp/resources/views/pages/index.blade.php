@extends('layouts.app')

@section('title', __('misc.welcome'))

@section('content')
    <div class="highlight-box">
        <h2 class="ui header">@lang('misc.welcomeTo')</h2>
        {{ logo()->element(true, false, ['class' => 'logo']) }}
    </div>

    @if(config('app.auth_session_link'))
        {!! Form::open(['action' => ['AuthController@doContinue'], 'method' => 'POST', 'class' => 'ui form']) !!}

        <p class="align-center">@lang('pages.index.emailAndContinue')</p>

        <div class="required field {{ ErrorRenderer::hasError('email') ? 'error' : '' }}">
            {{ Form::label('email', __('account.email') . ':') }}
            <div class="ui action input">
                {{ Form::text('email', '', ['type' => 'email', 'placeholder' => __('account.emailPlaceholder')]) }}
                <button class="ui button positive" type="submit">@lang('misc.continue')</button>
            </div>
            {{ ErrorRenderer::inline('email') }}
        </div>

        @if(is_recaptcha_enabled())
            {!! RecaptchaV3::initJs() !!}
            {!! RecaptchaV3::field('login') !!}
        @endif

        {!! Form::close() !!}
    @else
        <div class="ui stackable two column grid">
            <div class="column">
                <a href="{{ route('login') }}" class="ui button fluid large">@lang('auth.login')</a>
            </div>
            <div class="column">
                <a href="{{ route('register') }}" class="ui button fluid large">@lang('auth.register')</a>
            </div>
        </div>
    @endif

    <br>

    <div class="ui link list">
        <a href="{{ route('about') }}" class="item align-center">@lang('pages.about.aboutUs')</a>
    </div>
@endsection
