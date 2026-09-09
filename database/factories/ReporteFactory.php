<?php

namespace Database\Factories;

use App\Models\Reporte;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReporteFactory extends Factory
{
    protected $model = Reporte::class;

    public function definition(): array
    {
        $createdAt = $this->faker->dateTimeBetween('-250 days', 'now');
        $estadoId  = $this->faker->numberBetween(1, 4); // 1-4 según EstadosSeeder

        $closedAt = null;
        if ($estadoId == 3) { // Cerrado
            $closedAt = $this->faker->dateTimeBetween($createdAt, 'now');
        }

        // Escoge un técnico "principal" (ajusta el rango a tus usuarios técnicos reales)
        $tecnicoId = $this->faker->numberBetween(4, 23);

        return [
            'solicitante'               => $this->faker->name(),
            'descripcion'               => $this->faker->paragraph(1),
            'categoria_id'              => $this->faker->numberBetween(1, 53),   // CategoriasSeeder
            'estado_id'                 => $estadoId,
            'departamento_congreso_id'  => $this->faker->numberBetween(1, 118),  // DepartamentosCongresoSeeder
            'capturo_user_id'           => $this->faker->numberBetween(2, 3),    // UsuariosSeeder
            'area_informatica_id'       => $this->faker->numberBetween(1, 5),    // AreasInformaticaSeeder
            'tecnico_user_id'           => $tecnicoId,                            // Técnico principal
            'evento_id'                 => $this->faker->numberBetween(1, 6),     // Evento asignado (opcional)
            'closed_at'                 => $closedAt,
            'created_at'                => $createdAt,
            'updated_at'                => $this->faker->dateTimeBetween($createdAt, 'now'),
        ];
    }

    /**
     * Adjunta el técnico principal a la pivote reporte_user (1 técnico por reporte)
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Reporte $reporte) {
            // Si el factory puso tecnico_user_id, adjúntalo en la pivote
            if (!empty($reporte->tecnico_user_id)) {
                // Evita duplicados si tienes unique(reporte_id, user_id)
                $reporte->tecnicos()->syncWithoutDetaching([$reporte->tecnico_user_id]);
            } else {
                // Fallback por si no hubiera técnico principal
                // (ajusta el criterio de selección a tus "técnicos" reales)
                $tecId = User::inRandomOrder()->value('id');
                if ($tecId) {
                    $reporte->tecnicos()->syncWithoutDetaching([$tecId]);
                    $reporte->update(['tecnico_user_id' => $tecId]);
                }
            }
        });
    }

    // ---- Estados auxiliares que ya tenías ----

    public function pendiente()
    {
        return $this->state(fn(array $attributes) => [
            'estado_id' => 1,
            'closed_at' => null,
        ]);
    }

    public function atendido()
    {
        return $this->state(fn(array $attributes) => [
            'estado_id' => 2,
            'closed_at' => null,
        ]);
    }

    public function cerrado()
    {
        return $this->state(function (array $attributes) {
            $createdAt = $attributes['created_at'] ?? now()->subDay();
            return [
                'estado_id' => 3,
                'closed_at' => $this->faker->dateTimeBetween($createdAt, 'now'),
            ];
        });
    }

    public function cancelado()
    {
        return $this->state(function (array $attributes) {
            $createdAt = $attributes['created_at'] ?? now()->subDay();
            return [
                'estado_id' => 4,
                'closed_at' => $this->faker->dateTimeBetween($createdAt, 'now'),
            ];
        });
    }

    public function reciente()
    {
        return $this->state(function () {
            $createdAt = $this->faker->dateTimeBetween('-2 weeks', 'now');
            return [
                'created_at' => $createdAt,
                'updated_at' => $this->faker->dateTimeBetween($createdAt, 'now'),
            ];
        });
    }

    public function antiguo()
    {
        return $this->state(function () {
            $createdAt = $this->faker->dateTimeBetween('-1 year', '-3 months');
            return [
                'created_at' => $createdAt,
                'updated_at' => $this->faker->dateTimeBetween($createdAt, '-2 months'),
            ];
        });
    }

    public function conDiasAtras(int $dias): static
{
    return $this->state(function (array $attributes) use ($dias) {
        $createdAt = $this->faker->dateTimeBetween("-{$dias} days", 'now');

        // Si el estado ya fue seteado a "Cerrado" (3) o "Cancelado" (4),
        // respeta esa lógica de closed_at acorde al nuevo created_at
        $estadoId = $attributes['estado_id'] ?? null;
        $closedAt = null;

        if ($estadoId === 3 || $estadoId === 4) {
            $closedAt = $this->faker->dateTimeBetween($createdAt, 'now');
        }

        return [
            'created_at' => $createdAt,
            'updated_at' => $this->faker->dateTimeBetween($createdAt, 'now'),
            'closed_at'  => $closedAt, // null si no aplica
        ];
    });
}
}
