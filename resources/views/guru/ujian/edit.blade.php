@extends('layouts.app')
@section('title', 'Edit Ujian')
@section('content')
<form action="{{ route('guru.ujian.update', $ujian) }}" method="POST" class="card max-w-3xl space-y-4 p-6">
    @csrf @method('PUT')
    @include('guru.ujian._form')
</form>

<form action="{{ route('guru.ujian.destroy', $ujian) }}" method="POST" class="mt-4 max-w-3xl"
      onsubmit="return confirm('Hapus ujian beserta semua soal dan nilai siswa? Tindakan ini tidak bisa dibatalkan.')">
    @csrf @method('DELETE')
    <button class="text-sm text-red-600 hover:underline">Hapus ujian ini</button>
</form>
@endsection