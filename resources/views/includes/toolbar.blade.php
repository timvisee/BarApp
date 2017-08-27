{{-- Optional header style: border-top: none; border-bottom: 1px solid #CCCCCC; --}}

<div id="header" data-role="header">

    <div class="left">
        <a id="sidebar-toggle" href="#sidebar-panel" class="glyphicons glyphicons-menu-hamburger"></a>
    </div>

    <h1>
        <a href="{{ route('index') }}" data-ajax="false" title="Refresh app">
            {{-- TODO: Use a properly sized image here --}}
            <img src="{{ asset('img/logo/logo_header_big.png') }}" style="height: 21px; display: block;" />
        </a>
    </h1>

    <div class="right">
        <a href="{{ route('index') }}" class="glyphicons glyphicons-message-new toolbar-btn-message"></a>
        <a href="{{ route('index') }}" class="glyphicons glyphicons-lock toolbar-btn-security"></a>
    </div>

</div>
