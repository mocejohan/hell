<div class="bg-white shadow rounded-lg overflow-hidden border border-gray-300 mb-2">
    {{-- Header --}}
    <div class="px-4 py-1 {{ $reporte->color_header }} flex flex-col justify-between items-start">
        <h2 class="font-semibold text-gray-800 text-md">Reporte #{{ $reporte->id }}</h2>
        <h3 class="font-semibold text-gray-800 text-sm">{{ $reporte->categoria->name }}</h3>

        @if ($reporte->evento_nombre)
            <div class="flex justify-between w-full text-xs text-gray-500">
                <span>Evento: <strong>{{ $reporte->evento_nombre }}</strong></span>
            </div>
        @endif

        <div class="flex justify-between w-full text-xs text-gray-500">
            <span>Capturó: <strong>{{ $reporte->capturista->name }}</strong></span>
            <span>Fecha: <strong>{{ $reporte->created_at->format('d/m/Y') }} -
                    {{ $reporte->tiempo_transcurrido }}</strong></span>
            @if ($mostrarEstado)
                <span>Est: <strong>{{ $reporte->estado->name }}</strong></span>
            @endif
        </div>
        <div class="flex justify-between w-full text-xs text-gray-500">
            <span>Téc: <strong>{{ $reporte->tecnicos->pluck('name')->join(', ') ?: 'Sin técnico' }} </strong></span>
        </div>
    </div>

    {{-- Body --}}
    <div class="px-4 py-2 ">
        <p class="font-serif text-gray-800 text-1xl">{{ $reporte->descripcion }}</p>
    </div>
    <div class="px-4 py-1 bg-slate-100 flex flex-col justify-between items-start">
        <span class="text-xs text-gray-500">Solicitó: <strong>{{ $reporte->solicitante }}</strong></span>
        <span class="text-xs text-gray-500">Depto: <strong>{{ $reporte->departamento->name }}</strong></span>
    </div>
    @if ($reporte->comentarios->count() > 0)
        {{-- Comentarios --}}
        <div class="px-4 py-3 bg-white border-t">
            <h4 class="text-xs uppercase tracking-wider text-gray-500 mb-2">
                Comentarios
                <span class="text-gray-400">
                    ({{ $reporte->comentarios->count() }})
                </span>
            </h4>

            <ul class="space-y-3">
                @foreach ($reporte->comentarios as $c)
                    <li class="flex items-start gap-3">
                        <div
                            class="h-8 w-8 rounded-full bg-slate-200 flex items-center justify-center text-slate-600 text-sm font-semibold">
                            {{ mb_substr($c->user->name ?? 'U', 0, 1) }}
                        </div>
                        <div class="flex-1 bg-gray-100 p-2 rounded-2xl">
                            <div class="flex items-center gap-2">
                                <span
                                    class="font-semibold text-sm text-gray-800">{{ $c->user->name ?? 'Usuario' }}</span>
                            </div>
                            <p class="text-sm text-gray-700">
                                {{ $c->comentario }}
                            </p>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Footer Acciones --}}
    @if ($mostrarFooter)
        <div class="px-0 bg-gray-50 border-t flex divide-x text-sm text-gray-600">
            @can('atendido')
                <button wire:click="atender"
                    class="flex-1 flex items-center justify-center gap-2 py-2 hover:bg-gray-100 transition">
                    <i class="fa-solid fa-check text-green-600"></i>
                    <span>Atendido</span>
                </button>
            @endcan

            @if(auth()->user()->hasRole('Mesa-control') || auth()->user()->can('cerrarSolicitud'))
                @if ($reporte->estado->name == 'Atendido' || $reporte->dictamenes_count > 0 || $reporte->dictamen)
                    <button wire:click="cerrar"
                        class="flex-1 flex items-center justify-center gap-2 py-2 hover:bg-gray-100 transition text-vino-700 font-semibold">
                        <i class="fa-solid fa-folder-closed text-vino-600"></i>
                        <span>Cerrar Solicitud</span>
                    </button>
                @endif
            @endif

            @can('cancelar')
                <button wire:click="cancelar"
                    class="flex-1 flex items-center justify-center gap-2 py-2 hover:bg-gray-100 transition">
                    <i class="fa-solid fa-circle-xmark text-red-600"></i>
                    <span>Cancelar</span>
                </button>
            @endcan

            @if(auth()->user()->hasAnyRole(['Mesa-control', 'Tecnico']) || auth()->user()->can('dictaminar'))
                @if ($reporte->dictamenes_count == 0 && !in_array($reporte->estado_id, [3, 4]) && empty($reporte->closed_at))
                    <button wire:click="dictaminar"
                        class="flex-1 flex items-center justify-center gap-2 py-2 hover:bg-gray-100 transition text-vino-700 font-semibold">
                        <i class="fa-solid fa-file-contract text-vino-600"></i>
                        <span>Dictaminar</span>
                    </button>
                @endif
            @endcan

            @can('comentar')
                <button wire:click="comentar"
                    class="flex-1 flex items-center justify-center gap-2 py-2 hover:bg-gray-100 transition">
                    <i class="fa-regular fa-comment-dots text-gray-600"></i>
                    <span>Comentar</span>
                </button>
            @endcan
        </div>
    @endif
        
    {{-- Sección de Dictamen Técnico Registrado --}}
    @if ($reporte->dictamenes_count > 0 || $reporte->dictamen)
        <div class="bg-green-50 border-t border-green-200 px-4 py-2 flex flex-wrap items-center justify-between gap-2 text-xs text-green-900 font-medium"
             style="background-color: #f0fdf4; border-top: 1px solid #bbf7d0; color: #14532d;">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-circle-check text-green-600 text-sm" style="color: #16a34a;"></i>
                <span class="font-semibold" style="color: #15803d;">Dictamen Técnico Registrado</span>
                @if (in_array($reporte->estado_id, [3, 4]) || !empty($reporte->closed_at))
                    <span class="inline-flex items-center gap-1 bg-gray-200 text-gray-700 px-2 py-0.5 rounded text-[11px] font-normal" title="El reporte fue cerrado por la Mesa de Control, no permite más modificaciones.">
                        <i class="fa-solid fa-lock text-gray-500"></i> Cerrado por Mesa de Control
                    </span>
                @endif
            </div>

            <div class="flex items-center gap-2">
                {{-- Modificar Dictamen (Solo si el reporte está abierto / no cerrado) --}}
                @if(auth()->user()->hasAnyRole(['Mesa-control', 'Tecnico']) || auth()->user()->can('dictaminar'))
                    @if (!in_array($reporte->estado_id, [3, 4]) && empty($reporte->closed_at))
                        <button wire:click="dictaminar"
                            class="inline-flex items-center gap-1 text-white px-2.5 py-1 rounded text-xs font-semibold shadow-sm transition hover:opacity-90"
                            style="background-color: #d97706; color: #ffffff !important;"
                            title="Modificar dictamen técnico">
                            <i class="fa-solid fa-pen-to-square"></i>
                            <span>Modificar</span>
                        </button>
                    @endif
                @endif

                {{-- Historial de Versiones --}}
                <button wire:click="historialDictamen"
                    class="inline-flex items-center gap-1 text-white px-2.5 py-1 rounded text-xs font-semibold shadow-sm transition hover:opacity-90"
                    style="background-color: #475569; color: #ffffff !important;"
                    title="Ver historial de modificaciones y versiones">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    <span>Historial</span>
                </button>

                {{-- Imprimir PDF --}}
                @if(auth()->user()->hasAnyRole(['Mesa-control', 'Tecnico']) || auth()->user()->can('ImprimirDictamen'))
                    <a href="{{ route('reportes.dictamen.pdf', $reporte->id) }}" target="_blank" rel="noopener"
                        class="inline-flex items-center gap-1 text-white px-2.5 py-1 rounded text-xs font-semibold shadow-sm transition hover:opacity-90"
                        style="background-color: #047857; color: #ffffff !important;"
                        title="Descargar o imprimir PDF oficial">
                        <i class="fa-solid fa-file-pdf"></i>
                        <span>Imprimir PDF</span>
                    </a>
                @endif
            </div>
        </div>
    @endif

</div>
