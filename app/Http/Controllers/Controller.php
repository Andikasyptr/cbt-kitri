<?php

namespace App\Http\Controllers;

abstract class Controller
{
    protected function pastikanMilikGuru($guruId): void
    {
        abort_unless((int) $guruId === auth()->id(), 403, 'Ini bukan ujian Anda.');
    }
}