@extends('layout.wrapper')

@section('title', '')

@section('body')
<div class="centeredContent">
    <h1>{{ get_bloginfo('name') }}
        <small>{{ __('Welcome') }}</small>
    </h1>


</div>
@endsection
