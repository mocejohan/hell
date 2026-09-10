<?php

namespace App\Services;

use App\Models\Bien;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class BienLookupService
{
    /**
     * Busca un bien por número de inventario.
     * 1° Busca en la tabla local 'bienes'. Si existe pero no tiene resguardatario, intenta completarlo desde Aries.
     * 2° Si no existe, consulta Aries (Solo Lectura) via dbo.Inventarios + dbo.Resguardos.
     * 3° Si Aries lo encuentra, lo guarda localmente y lo retorna.
     *
     * @param  string  $termino  Número de inventario a buscar
     * @return Bien|null
     */
    public static function buscarPorInventario(string $termino): ?Bien
    {
        $termino = trim($termino);

        if (empty($termino)) {
            return null;
        }

        // ── Paso 1: Búsqueda local ──
        $bien = Bien::where('numero_inventario', $termino)
            ->orWhere('numero_inventario_anterior', $termino)
            ->first();

        if ($bien) {
            // Si el bien local ya tiene resguardatario, retornarlo de inmediato
            if (!empty($bien->resguardatario)) {
                return $bien;
            }

            // Si no tiene resguardatario registrado localmente, intentamos enriquecerlo desde Aries
            $datosAries = static::consultarAries($termino);
            if ($datosAries && !empty($datosAries['resguardatario'])) {
                $bien->update([
                    'resguardatario' => $datosAries['resguardatario'],
                ]);
            }

            return $bien;
        }

        // ── Paso 2: Fallback a Aries (Solo Lectura) ──
        $datosAries = static::consultarAries($termino);

        if (! $datosAries) {
            return null; // No existe en ningún lado
        }

        // ── Paso 3: Guardar localmente y retornar ──
        return Bien::updateOrCreate(
            ['numero_inventario' => $datosAries['numero_inventario']],
            [
                'numero_inventario_anterior' => $datosAries['numero_inventario_anterior'] ?? null,
                'equipo'         => $datosAries['equipo'] ?? null,
                'marca'          => $datosAries['marca'] ?? null,
                'modelo'         => $datosAries['modelo'] ?? null,
                'serie'          => $datosAries['serie'] ?? null,
                'ubicacion'      => $datosAries['ubicacion'] ?? null,
                'resguardatario' => $datosAries['resguardatario'] ?? null,
            ]
        );
    }

    /**
     * Consulta Aries con el patrón de 2 niveles (PDO sqlsrv → Python TDS).
     * Solo ejecuta SELECT, nunca modifica datos en Aries.
     *
     * @param  string  $termino  Número de inventario
     * @return array|null  Datos mapeados al formato local, o null si no se encontró
     */
    private static function consultarAries(string $termino): ?array
    {
        // ── Nivel 1: PDO nativo (si el driver está disponible) ──
        if (extension_loaded('pdo_sqlsrv') || extension_loaded('pdo_dblib')) {
            try {
                $record = DB::connection('aries')
                    ->table('dbo.Inventarios as i')
                    ->leftJoin('dbo.Resguardos as r', 'i.Número de Inventario', '=', 'r.Número de Inventario')
                    ->select([
                        'i.Número de Inventario as numero_inventario',
                        'i.Número Inventario Anterior as numero_inventario_anterior',
                        'i.Descripción del Bien as equipo',
                        'i.Marca as marca',
                        'i.Modelo as modelo',
                        'i.Número de Serie as serie',
                        'i.Ubicación as ubicacion',
                        'r.Nombre de Usuario as resguardatario',
                    ])
                    ->where('i.Número de Inventario', $termino)
                    ->first();

                if ($record) {
                    return static::mapearDesdeAries((array) $record);
                }

                return null;
            } catch (Throwable $e) {
                Log::warning('BienLookupService PDO error: ' . $e->getMessage());
                // Continuar al nivel 2
            }
        }

        // ── Nivel 2: Fallback Python TDS (impacket) ──
        try {
            $pyScript = database_path('seeders/lookup_bien.py');

            if (! file_exists($pyScript)) {
                Log::error('BienLookupService: Script Python no encontrado en ' . $pyScript);
                return null;
            }

            $command = sprintf(
                'python3 %s %s %s %s %s %s',
                escapeshellarg($pyScript),
                escapeshellarg(env('DB_SQLSRV_HOST', '172.16.2.10')),
                escapeshellarg(env('DB_SQLSRV_INVENTARIOS_DATABASE', 'InventariosSQL2018')),
                escapeshellarg(env('DB_SQLSRV_USERNAME', 'sa')),
                escapeshellarg(env('DB_SQLSRV_PASSWORD', 'Pa$$w0rd')),
                escapeshellarg($termino)
            );

            $output = shell_exec($command);

            if ($output) {
                $json = json_decode(trim($output), true);

                if (is_array($json) && isset($json['error'])) {
                    Log::warning('BienLookupService Aries error: ' . $json['error']);
                    return null;
                }

                if (is_array($json) && isset($json['not_found'])) {
                    return null; // Bien no existe en Aries
                }

                if (is_array($json) && isset($json['numero_inventario'])) {
                    return $json; // Ya viene mapeado desde el script Python
                }
            }
        } catch (Throwable $e) {
            Log::warning('BienLookupService Python TDS error: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Mapea nombres de columnas de Aries (PDO) al formato de nuestra tabla local 'bienes'.
     *
     * @param  array  $record  Registro tal como viene de dbo.Inventarios via PDO
     * @return array  Datos en formato compatible con Bien::create/updateOrCreate
     */
    private static function mapearDesdeAries(array $record): array
    {
        $limpiar = function ($valor) {
            if ($valor === null) {
                return null;
            }
            $v = trim((string) $valor);
            if ($v === '' || strtoupper($v) === 'NULL') {
                return null;
            }
            return $v;
        };

        return [
            'numero_inventario'          => trim($record['Número de Inventario'] ?? $record['numero_inventario'] ?? ''),
            'numero_inventario_anterior' => $limpiar($record['Número Inventario Anterior'] ?? $record['numero_inventario_anterior'] ?? null),
            'equipo'                     => $limpiar($record['Descripción del Bien'] ?? $record['equipo'] ?? null),
            'marca'                      => $limpiar($record['Marca'] ?? $record['marca'] ?? null),
            'modelo'                     => $limpiar($record['Modelo'] ?? $record['modelo'] ?? null),
            'serie'                      => $limpiar($record['Número de Serie'] ?? $record['serie'] ?? null),
            'ubicacion'                  => $limpiar($record['Ubicación'] ?? $record['ubicacion'] ?? null),
            'resguardatario'             => $limpiar($record['Nombre de Usuario'] ?? $record['resguardatario'] ?? null),
        ];
    }
}
