<?php

namespace App\Exports;

use App\Models\Bien;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BienesExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        protected ?string $search = null,
        protected ?string $equipoFiltro = null
    ) {}

    public function query()
    {
        return Bien::query()
            ->when($this->search, function ($query, $term) {
                $query->where(function ($q) use ($term) {
                    $q->where('numero_inventario', 'like', "%{$term}%")
                      ->orWhere('numero_inventario_anterior', 'like', "%{$term}%")
                      ->orWhere('equipo', 'like', "%{$term}%")
                      ->orWhere('marca', 'like', "%{$term}%")
                      ->orWhere('modelo', 'like', "%{$term}%")
                      ->orWhere('serie', 'like', "%{$term}%")
                      ->orWhere('ubicacion', 'like', "%{$term}%");
                });
            })
            ->when($this->equipoFiltro, fn($q, $v) => $q->where('equipo', $v))
            ->orderBy('numero_inventario');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Número Inventario',
            'Inventario Anterior',
            'Equipo',
            'Marca',
            'Modelo',
            'Serie',
            'Ubicación',
            'Fecha Registro',
        ];
    }

    public function map($bien): array
    {
        return [
            $bien->id,
            $bien->numero_inventario,
            $bien->numero_inventario_anterior,
            $bien->equipo,
            $bien->marca,
            $bien->modelo,
            $bien->serie,
            $bien->ubicacion,
            $bien->created_at?->format('Y-m-d H:i'),
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
                    'startColor' => ['argb' => 'FF1E3A8A'],
                ],
            ],
        ];
    }
}