@extends('layouts.master')

@section('content')

@endsection

@section('footer')
@parent
    {{ $webapp->getAppVersion() ?? '' }}
@endsection

@dummy <script> @enddummy
    // ///////////////////////////////////////////////
    @section('init')
        console.log("Init function for Home Page");
    @endsection
    // ///////////////////////////////////////////////
    @section('document_ready')
    @parent
        console.log("Document Ready - Home Page");
    @endsection
    // ///////////////////////////////////////////////
    @section('scripts')
        console.log("Custom Script for Home Page");
    @endsection
    // ///////////////////////////////////////////////
@dummy </script> @enddummy
