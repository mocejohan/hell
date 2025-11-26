<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Nuevo dictamen</h2>
    </x-slot>

    <div class="max-w-4xl mx-auto py-6 px-4">
        @if (session('ok'))
            <div class="mb-4 rounded border border-green-200 bg-green-50 p-3 text-green-700 text-sm">
                {{ session('ok') }}
            </div>
        @endif

        <div class="bg-white border rounded-lg shadow p-6">
            <form method="POST" action="{{ route('dictamenes.store') }}" class="space-y-6" x-data="buscarReporte()">
                @csrf

                {{-- Mantén el valor real que se enviará al store() --}}
                <input type="hidden" name="reporte_id" :value="reporte?.id || ''">

                <div class="grid grid-cols-1 md:grid-cols-1 gap-4">
                    {{-- Núm Reporte + botón Buscar --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Núm Reporte</label>
                        <div class="mt-1 flex gap-2">
                            <input type="text" x-model="numero" class="block w-50 rounded-md border-gray-300"
                                placeholder="Ej. 123" />
                            <button type="button" @click="buscar()"
                                class="inline-flex items-center rounded-md border px-3 py-2 text-sm hover:bg-gray-50 disabled:opacity-60"
                                :disabled="loading || !numero" aria-label="Buscar reporte">
                                <template x-if="!loading">
                                    <span class="inline-flex items-center gap-2">
                                        <i class="fa-solid fa-magnifying-glass text-gray-600"></i>
                                        <span>Buscar</span>
                                    </span>
                                </template>

                                <template x-if="loading">
                                    <span class="inline-flex items-center gap-2">
                                        <!-- Spinner con Tailwind -->
                                        <svg class="h-4 w-4 animate-spin text-gray-600" viewBox="0 0 24 24"
                                            fill="none">
                                            <circle class="opacity-25" cx="12" cy="12" r="10"
                                                stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                        </svg>
                                        <span>Buscando…</span>
                                    </span>
                                </template>
                            </button>
                        </div>
                        {{-- error del backend por si falla la validación de store() --}}
                        @error('reporte_id')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror

                        {{-- Mensajes del buscador --}}
                        <template x-if="error">
                            <p class="text-xs text-red-600 mt-1" x-text="error"></p>
                        </template>

                        {{-- Panel con datos del reporte encontrado --}}
                        <template x-if="reporte">
                            <div class="mt-3 rounded-md border border-gray-200 p-3 text-sm bg-gray-50">
                                <div class="flex items-center justify-between">
                                    <div class="font-semibold">Reporte #<span x-text="reporte.id"></span></div>
                                    <span
                                        class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold"
                                        :class="reporte.estado_nombre === 'Cerrado' ?
                                            'bg-amber-100 text-amber-800 border-amber-200':
                                            'bg-green-100 text-green-800 border-green-200'"> 
                                        <span x-text="reporte.estado_nombre || '—'"></span>
                                    </span>
                                </div>

                                <dl class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-1">
                                    <div>
                                        <dt class="text-gray-500">Departamento</dt>
                                        <dd class="font-medium" x-text="reporte.departamento_nombre || '—'"></dd>
                                    </div>
                                    <div>
                                        <dt class="text-gray-500">Solicitante</dt>
                                        <dd class="font-medium" x-text="reporte.solicitante || '—'"></dd>
                                    </div>
                                    <div>
                                        <dt class="text-gray-500">Técnico</dt>
                                        <dd class="font-medium" x-text="reporte.tecnico_nombre || '—'"></dd>
                                    </div>
                                    <div>
                                        <dt class="text-gray-500">Inventario</dt>
                                        <dd class="font-medium" x-text="reporte.numero_inventario || '—'"></dd>
                                    </div>
                                    <div>
                                        <dt class="text-gray-500">Creado</dt>
                                        <dd class="font-medium" x-text="reporte.created_at || '—'"></dd>
                                    </div>
                                    {{-- <div>
                                        <dt class="text-gray-500">Solicitó</dt>
                                        <dd class="font-medium" x-text="reporte.solicitante || '—'"></dd>
                                    </div> --}}
                                </dl>
                            </div>
                        </template>
                    </div>


                </div>

                {{-- Equipo / Marca / Modelo / Serie --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        {{-- Inventario (puede auto-rellenarse si el reporte lo trae) --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Inventario</label>
                        <input type="text" name="inventario" x-model="inventario"
                            class="mt-1 block w-full rounded-md border-gray-300" />
                        @error('inventario')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Equipo</label>
                        <input type="text" name="equipo" value="{{ old('equipo') }}"
                            class="mt-1 block w-full rounded-md border-gray-300" />
                        @error('equipo')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Marca</label>
                        <input type="text" name="marca" value="{{ old('marca') }}"
                            class="mt-1 block w-full rounded-md border-gray-300" />
                        @error('marca')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Modelo</label>
                        <input type="text" name="modelo" value="{{ old('modelo') }}"
                            class="mt-1 block w-full rounded-md border-gray-300" />
                        @error('modelo')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Serie</label>
                        <input type="text" name="serie" value="{{ old('serie') }}"
                            class="mt-1 block w-full rounded-md border-gray-300" />
                        @error('serie')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Diagnóstico --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700">Diagnóstico</label>
                    <textarea name="diagnostico" rows="4" class="mt-1 block w-full rounded-md border-gray-300">{{ old('diagnostico') }}</textarea>
                    @error('diagnostico')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Sugerencia --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700">Sugerencia</label>
                    <textarea name="sugerencia" rows="3" class="mt-1 block w-full rounded-md border-gray-300">{{ old('sugerencia') }}</textarea>
                    @error('sugerencia')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Observaciones (opcional) --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700">Observaciones (opcional)</label>
                    <textarea name="observaciones" rows="3" class="mt-1 block w-full rounded-md border-gray-300">{{ old('observaciones') }}</textarea>
                    @error('observaciones')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end gap-2">
                    <a href="{{ url()->previous() }}"
                        class="inline-flex items-center rounded-md border px-4 py-2 text-sm">Cancelar</a>

                    <button type="submit"
                        class="inline-flex items-center rounded-md bg-vino-700 px-4 py-2 text-sm font-semibold text-white hover:bg-vino-800">
                        Guardar dictamen
                    </button>
                </div>
            </form>

            <script>
                function buscarReporte() {
                    return {
                        numero: '',
                        loading: false,
                        error: null,
                        reporte: null,
                        inventario: @json(old('inventario', '')),

                        async buscar() {
                            this.error = null;
                            this.reporte = null;
                            if (!this.numero) return;

                            this.loading = true;
                            try {
                                const url = "{{ route('reportes.lookup', ':id') }}".replace(':id', encodeURIComponent(this
                                    .numero));
                                const res = await fetch(url, {
                                    headers: {
                                        'Accept': 'application/json'
                                    }
                                });

                                if (!res.ok) {
                                    const j = await res.json().catch(() => ({}));
                                    this.error = j?.message || 'No se pudo obtener el reporte.';
                                    return;
                                }

                                const j = await res.json();
                                if (j.ok) {
                                    this.reporte = j.data;

                                    // Si el reporte trae número de inventario, sugerirlo al usuario
                                    if (!this.inventario && this.reporte.numero_inventario) {
                                        this.inventario = this.reporte.numero_inventario;
                                    }
                                } else {
                                    this.error = 'Reporte no encontrado.';
                                }
                            } catch (e) {
                                this.error = 'Error de red.';
                            } finally {
                                this.loading = false;
                            }
                        }
                    }
                }
            </script>

        </div>
    </div>
</x-app-layout>
