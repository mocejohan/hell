<?php
require __DIR__ . /vendor/autoload.php;
 = require_once __DIR__ . /bootstrap/app.php;
 = ->make(Illuminate\Contracts\Console\Kernel::class);
->bootstrap();

use App\Models\Reporte;
use App\Models\Dictamen;
use Barryvdh\DomPDF\Facade\Pdf;

// Find a real report or mock one
 = Reporte::with([departamento, tecnico])->first();
if (!) {
     = new Reporte();
    ->id = 1452;
    ->solicitante = Lic.
