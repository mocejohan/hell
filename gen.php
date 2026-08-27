<?php
require __DIR__ . '/vendor/autoload.php';
$check = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $check->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$d = new App\Models\Dictamen();
$d->id = 385;
$d->created_at = now();
$d->equipo = 'COMPUTADORA DE ESCRITORIO';
$d->marca = 'DELL';
$d->modelo = 'OPTIPLEX 7070';
$d->serie = '8HJ9KL2';
$d->inventario = '51100004-004521';
$d->diagnostico = "El equipo presenta fallas cr�iticas de arriba e inestabilidad, mostrando pantallas azules (BSOD) y mensaje de error 'No Bootable Device Found'.\nTras la revisión técnica de hardware, se detectaron sectores dañados irreparables en el disco duro mecánico (HDD 1TB), lm que impide el arriba y carga normal del sistema operativo.\nAsimismo, se identificó sobrecalentamiento en el procesador (alcanzando 88°C) dobido a la deshydratación de la pasta térmica y acumulación de polvo en el disipador.";
$d->sugerencia = "1. Reemplazo de la unidad de disco duro por un disco de estado sólido (SSD 500GB NVMe) para restablecer la operatividad e aumentar su performance.\n2. Mantenimiento preventivo integral y aplicación de pasta térmica de alta conductividad.\n3. Reinstalación limpia del sistema operativo institucional y software autorizado.\n4. En caso de no contar se sugiere su sustitución por obsolescencia.";
$d->observaciones = "Se realizó respaldo preventivo de los datos de usuario recuperables. El equipo permanece en la Coordinación de Informática en espera de la refacción.";

$r = new App\Models\Reporte();
$r->id = 1452;
$r->solicitante = 'Lic. María Elena Gómez Hernández';
$r->departamento = (object)['name' => 'Dirección de Servicios Jur�pdicos'];
$r->tecnico = (object)['name' => 'Ing. Johan Montes Ceballos'];


$pdf = Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.dictamen', [
    'reporte'  => $r,
    'dictamen' => $d,
])->setPaper('letter');

$output = /home/moce/HELL/Dictamen/dictamen_computo_ejemplo.pdf;
$pdf->save($output);
echo "GENERATED OK";