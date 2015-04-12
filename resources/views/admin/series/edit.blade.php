@extends('layouts.dashboard.master')

@section('content')
    <h1>Edit Series: {{ $series->title }}</h1>

    {!! Form::model($series, ['method' => 'PATCH', 'action' => ['SeriesController@update', $series->slug]]) !!}
        @include('dashboard.series.form', ['submitButtonText' => 'Update Series'])
    {!! Form::close() !!}

    @include('errors.list')

@endsection