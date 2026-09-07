<?php

namespace App\Imports;

use App\Models\Bien;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class BienesImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    public int $creados = 0;
    public int $actualizados = 0;
    public int $omitidos = 0;
    public array $errores = [];

    /**
     * @param string $modo 'upsert' (crea o actualiza) o 'solo_nuevos' (ignora existentes)
     */
    public function __construct(public string $modo = 'upsert') {}

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $filaNum = $index + 2; // +2 por encabezado y base 0

            // Normalizar las claves de las columnas
            $rowArray = $row->toArray();
            $datos = $this->normalizarFila($rowArray);

            $inventario = trim((string)($datos['numero_inventario'] ?? ''));

            if (empty($inventario)) {
                $this->errores[] = "Fila #{$filaNum}: El número de inventario está vacío. Se omitió la fila.";
                $this->omitidos++;
                continue;
            }

            try {
                $bienExistente = Bien::where('numero_inventario', $inventario)->first();

                $datosGuardar = [
                    'numero_inventario'          => $inventario,
                    'numero_inventario_anterior' => $datos['numero_inventario_anterior'] ? trim((string)$datos['numero_inventario_anterior']) : null,
                    'equipo'                     => $datos['equipo'] ? trim((string)$datos['equipo']) : 'EQUIPO DE CÓMPUTO',
                    'marca'                      => $datos['marca'] ? trim((string)$datos['marca']) : null,
                    'modelo'                     => $datos['modelo'] ? trim((string)$datos['modelo']) : null,
                    'serie'                      => $datos['serie'] ? trim((string)$datos['serie']) : null,
                    'ubicacion'                  => $datos['ubicacion'] ? trim((string)$datos['ubicacion']) : null,
                ];

                if ($bienExistente) {
                    if ($this->modo === 'upsert') {
                        $bienExistente->update($datosGuardar);
                        $this->actualizados++;
                    } else {
                        $this->omitidos++;
                    }
                } else {
                    Bien::create($datosGuardar);
                    $this->creados++;
                }
            } catch (\Exception $e) {
                $this->errores[] = "Fila #{$filaNum} (Inventario: {$inventario}): Error al procesar - " . $e->getMessage();
                $this->omitidos++;
            }
        }
    }

    /**
     * Normaliza los encabezados comunes en hojas de cálculo.
     */
    private function normalizarFila(array $row): array
    {
        $claves = [
            'numero_inventario' => ['numero_inventario', 'no_inventario', 'inventario', 'no_inv', 'num_inventario', 'inventario_actual', 'no_de_inventario', 'numero_de_inventario'],
            'numero_inventario_anterior' => ['numero_inventario_anterior', 'no_inventario_anterior', 'inventario_anterior', 'no_anterior', 'inv_anterior', 'num_inventario_anterior'],
            'equipo' => ['equipo', 'tipo_de_equipo', 'tipo_equipo', 'descripcion_equipo', 'bien', 'nombre_equipo', 'descripcion'],
            'marca' => ['marca', 'fabricante'],
            'modelo' => ['modelo', 'model'],
            'serie' => ['serie', 'numero_de_serie', 'no_serie', 'num_serie', 'serial', 'sn'],
            'ubicacion' => ['ubicacion', 'area', 'departamento', 'lugar', 'adscripcion', 'oficina'],
        ];

        $resultado = [
            'numero_inventario'          => null,
            'numero_inventario_anterior' => null,
            'equipo'                     => null,
            'marca'                      => null,
            'modelo'                     => null,
            'serie'                      => null,
            'ubicacion'                  => null,
        ];

        // Mapear comparando claves normalizadas (minúsculas y sin acentos ni espacios raros)
        foreach ($row as $columnaOriginal => $valor) {
            $colLimpia = strtolower(trim((string)$columnaOriginal));
            $colLimpia = str_replace([' ', '-', '.'], '_', $colLimpia);
            $colLimpia = preg_replace('/[^a-z0-9_]/', '', $colLimpia);

            foreach ($claves as $campoDestino => $variaciones) {
                if (in_array($colLimpia, $variaciones)) {
                    $resultado[$campoDestino] = $valor;
                    break;
                }
            }
        }

        return $resultado;
    }
}