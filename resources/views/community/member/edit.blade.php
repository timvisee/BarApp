@extends('layouts.app')

@php
    use \App\Perms\CommunityRoles;
@endphp

@section('content')
    <h2 class="ui header">{{ $member->name }}</h2>

    {!! Form::open(['action' => ['CommunityMemberController@doEdit', $community->human_id, $member->id], 'method' => 'PUT', 'class' => 'ui form']) !!}
        <div class="field {{ ErrorRenderer::hasError('role') ? 'error' : '' }}">
            {{ Form::label('role', __('misc.role')) }}

            <div class="ui fluid selection dropdown">
                <input type="hidden" name="role" value="{{ $member->pivot->role }}">
                <i class="dropdown icon"></i>

                <div class="default text">@lang('misc.pleaseSpecify')</div>
                <div class="menu">
                    @foreach(CommunityRoles::roles() as $id => $name)
                        <div class="item" data-value="{{ $id }}">{{ $name }}</div>
                    @endforeach
                </div>
            </div>

            {{ ErrorRenderer::inline('role') }}
        </div>

        <div class="ui warning message visible">
            <span class="halflings halflings-warning-sign"></span>
            @lang('pages.communityMembers.incorrectMemberRoleWarning')
        </div>

        <button class="ui button primary" type="submit">@lang('misc.saveChanges')</button>
        <a href="{{ route('community.member.show', ['communityId' => $community->human_id, 'memberId' => $member->id]) }}"
                class="ui button basic">
            @lang('general.cancel')
        </a>
    {!! Form::close() !!}
@endsection
