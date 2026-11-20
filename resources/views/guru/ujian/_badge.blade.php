@php
    $warna = [
        'Berlangsung'   => 'bg-green-100 text-green-700',
        'Belum dimulai' => 'bg-yellow-100 text-yellow-700',
        'Selesai'       => 'bg-slate-200 text-slate-600',
        'Nonaktif'      => 'bg-red-100 text-red-700',
    ][$u->status];
@endphp
<span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $warna }}">{{ $u->status }}</span>