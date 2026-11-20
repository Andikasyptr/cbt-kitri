@extends('layouts.app')
@section('title', 'Buat Ujian')
@section('content')
<form action="{{ route('guru.ujian.store') }}" method="POST" class="bg-white rounded-xl shadow p-6 max-w-3xl space-y-4">
    @csrf
    @include('guru.ujian._form')
</form>
@endsection