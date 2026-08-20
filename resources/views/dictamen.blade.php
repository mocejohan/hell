<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    @role('Mesa-control')
                        Dictámenes Técnicos (Listado General)
                    @else
                        Mis Dictámenes Técnicos Recientes
                    @endrole
                </h2>
                <p class="text-xs text-gray-500 mt-1">
                    @role('Mesa-control')
                        Consulta de todos los dictámenes emitidos por los técnicos del área de informática.
                    @else
                        Listado de los dictámenes técnicos que has generado.
                    @endrole
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('ok'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative shadow-sm" role="alert">
                    <span class="block sm:inline"><i class="fa-solid fa-circle-check mr-2"></i>{{ session('ok') }}</span>
                </div>
            @endif

            {{-- Filtros y Buscador --}}
            <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 mb-6">
                <form method="GET" action="{{ route('dictamen') }}" class="flex flex-col sm:flex-row gap-3 items-center justify-between">
                    <div class="relative flex-1 w-full">
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Buscar por folio, inventario, equipo, serie o solicitante..."
                            class="w-full pl-10 pr-4 py-2 text-sm rounded-md border-gray-300 focus:border-vino-500 focus:ring-vino-500 shadow-sm" />
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </div>
                    </div>

                    <div class="flex gap-2 w-full sm:w-auto">
                        <button type="submit" class="bg-vino-700 hover:bg-vino-800 text-white px-4 py-2 rounded-md text-sm font-semibold transition flex items-center justify-center gap-2">
                            <i class="fa-solid fa-filter"></i>
                            <span>Filtrar</span>
                        </button>

                        @if (request()->filled('search'))
                            <a href="{{ route('dictamen') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-3 py-2 rounded-md text-sm transition flex items-center justify-center">
                                Limpiar
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            {{-- Tabla de Dictámenes --}}
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-600">
                        <thead class="bg-gray-50 border-b border-gray-200 text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            <tr>
                                <th class="px-4 py-3">Folio</th>
                                <th class="px-4 py-3">Reporte / Solicitante</th>
                                <th class="px-4 py-3">Equipo & Inventario</th>
                                <th class="px-4 py-3">Serie / Marca</th>
                                <th class="px-4 py-3">Técnico</th>
                                <th class="px-4 py-3">Fecha</th>
                                <th class="px-4 py-3 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($dictamenes as $d)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-4 py-3 font-bold text-vino-800 whitespace-nowrap">
                                        C{{ $d->id }}/{{ $d->created_at->format('Y') }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-semibold text-gray-900">#{{ $d->reporte->id ?? $d->reporte_id }}</div>
                                        <div class="text-xs text-gray-500">{{ $d->reporte->solicitante ?? 'N/A' }}</div>
                                        <div class="text-xs text-gray-400 italic">{{ $d->reporte->departamento->name ?? '' }}</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-gray-900">{{ $d->equipo }}</div>
                                        <span class="inline-block bg-gray-100 text-gray-700 text-xs px-2 py-0.5 rounded font-mono border">
                                            Inv: {{ $d->inventario }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="text-gray-800">{{ $d->marca }} {{ $d->modelo }}</div>
                                        <div class="text-xs text-gray-500">Serie: <strong>{{ $d->serie }}</strong></div>
                                    </td>
                                    <td class="px-4 py-3 text-xs text-gray-700">
                                        {{ $d->reporte->tecnico->name ?? $d->reporte->tecnicos->pluck('name')->join(', ') ?? 'N/A' }}
                                    </td>
                                    <td class="px-4 py-3 text-xs text-gray-500 whitespace-nowrap">
                                        {{ $d->created_at->format('d/m/Y') }}
                                        <div class="text-gray-400">{{ $d->created_at->format('H:i') }} hrs</div>
                                    </td>
                                    <td class="px-4 py-3 text-center whitespace-nowrap">
                                        @can('ImprimirDictamen')
                                            <a href="{{ route('reportes.dictamen.pdf', $d->reporte_id) }}" target="_blank" rel="noopener"
                                                class="inline-flex items-center gap-1.5 bg-red-700 hover:bg-red-800 text-white px-3 py-1.5 rounded-md text-xs font-semibold shadow-sm transition">
                                                <i class="fa-solid fa-file-pdf"></i>
                                                <span>Imprimir PDF</span>
                                            </a>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                        <div class="flex flex-col items-center justify-center">
                                            <i class="fa-solid fa-file-contract text-4xl text-gray-300 mb-2"></i>
                                            <p class="font-medium text-gray-600">No se encontraron dictámenes registrados.</p>
                                            @if (request()->filled('search'))
                                                <p class="text-xs text-gray-400 mt-1">Prueba con otros términos de búsqueda.</p>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($dictamenes->hasPages())
                    <div class="px-4 py-3 border-t border-gray-200 bg-gray-50">
                        {{ $dictamenes->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
