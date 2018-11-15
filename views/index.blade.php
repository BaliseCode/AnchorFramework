@extends('errorWrap')

@section('title', {{ bloginfo('name') }})

@section('body')
<div class="centeredContent">
    <h1>{{ bloginfo('name') }}
        <small>{{ __('Welcome') }}</small>
    </h1>


</div>
@endsection
