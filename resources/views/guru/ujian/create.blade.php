@extends('layouts.app')
@section('title', 'Buat Ujian')
@section('content')
<form action="{{ route('guru.ujian.store') }}" method="POST" class="card max-w-3xl space-y-4 p-6">
    @csrf
    @include('guru.ujian._form')
</form>
@endsection