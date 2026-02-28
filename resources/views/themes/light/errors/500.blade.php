
@extends($theme.'layouts.error')
@section('title', trans('500'))

@section('error_code','500')
@section('error_message', trans("The server encountered an internal error misconfiguration and was unable to complete your request. Please contact the server administrator."))
