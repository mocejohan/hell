<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PlantillaBienesExport implements FromArray, WithHeadings, WithStyles
{
    public function headings(): array
    {
        return [
            'numero_inventario',
            'numero_inventario_anterior',
            'equipo',
            'marca',
            'modelo',
            'serie',
            'ubicacion',
        ];
    }

    public function array(): array
    {
        return [
            [
                'INV-2025-001',
                'ANT-9901',
                'COMPUTADORA DE ESCRITORIO',
                'HP',
                'ProDesk 400 G6',
                'MXL1234567',
                'DIRECCIÓN DE FINANZAS',
            ],
            [
                'INV-2025-002',
                '',
                'LAPTOP',
                'DELL',
                'Latitude 3420',
                '5CD8901234',
                'DIRECCIÓN JURÍDICA',
            ],
            [
                'INV-2025-003',
                '',
                'IMPRESORA LÁSER',
                'EPSON',
                'EcoTank L3250',
                'EPS7890123',
                'SALA DE JUNTAS PISO 2',
            ],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['argb' => 'FFFFFFFF'],
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF1E40AF'], // Azul Tailwind (blue-800)
                ],
            ],
        ];
    }
}