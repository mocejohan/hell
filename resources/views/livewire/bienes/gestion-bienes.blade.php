<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

    {{-- Alertas de Sesión --}}
    @if (session()->has('success'))
        <div class="flex items-center p-4 mb-4 text-green-800 bg-green-50 border border-green-200 rounded-xl shadow-sm transition" role="alert">
            <svg class="flex-shrink-0 w-5 h-5 text-green-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <div class="text-sm font-medium">{{ session('success') }}</div>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="flex items-center p-4 mb-4 text-red-800 bg-red-50 border border-red-200 rounded-xl shadow-sm transition" role="alert">
            <svg class="flex-shrink-0 w-5 h-5 text-red-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            <div class="text-sm font-medium">{{ session('error') }}</div>
        </div>
    @endif

    {{-- Encabezado Principal --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-blue-50 text-blue-600 rounded-xl">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Inventario de Bienes Informáticos</h1>
                    <p class="text-sm text-gray-500">Administra, carga y consulta el catálogo general de bienes del Congreso.</p>
                </div>
            </div>
        </div>

        {{-- Botonera de Acciones --}}
        <div class="flex flex-wrap items-center gap-2.5 w-full md:w-auto">
            {{-- Botón Agregar Bien Individual --}}
            <button wire:click="abrirModalCrear"
                    type="button"
                    style="background-color: #2563eb !important; color: #ffffff !important;"
                    class="inline-flex items-center px-4 py-2 text-sm font-semibold rounded-xl transition shadow hover:shadow-md hover:bg-blue-700 cursor-pointer">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                + Agregar Bien Individual
            </button>

            {{-- Botón Carga Masiva (Excel / CSV) --}}
            <button wire:click="abrirModalImportar"
                    type="button"
                    style="background-color: #4f46e5 !important; color: #ffffff !important;"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-xl transition shadow-sm hover:shadow hover:bg-indigo-700 cursor-pointer">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
                Carga Masiva
            </button>

            {{-- Botón Exportar --}}
            <button wire:click="exportar"
                    type="button"
                    class="inline-flex items-center px-3.5 py-2 text-sm font-medium text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded-xl transition shadow-sm hover:shadow cursor-pointer">
                <svg class="w-4 h-4 mr-2 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Exportar
            </button>

            {{-- Botón Plantilla Excel --}}
            <button wire:click="descargarPlantilla"
                    type="button"
                    class="inline-flex items-center px-3.5 py-2 text-sm font-medium text-gray-700 bg-gray-50 hover:bg-gray-100 border border-gray-300 rounded-xl transition shadow-sm hover:shadow cursor-pointer">
                <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Plantilla Excel
            </button>
        </div>
    </div>

    {{-- Filtros y Barra de Búsqueda --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-center">
            {{-- Buscador General --}}
            <div class="md:col-span-6 relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input wire:model.live.debounce.300ms="search"
                       type="text"
                       placeholder="Buscar por inventario, anterior, equipo, marca, modelo, serie, ubicación..."
                       class="w-full pl-10 pr-4 py-2 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"/>
            </div>

            {{-- Filtro Tipo de Equipo --}}
            <div class="md:col-span-4">
                <select wire:model.live="equipoFiltro"
                        class="w-full py-2 px-3 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition text-gray-700">
                    <option value="">Todos los tipos de equipo</option>
                    @foreach ($tiposEquipo as $tipo)
                        <option value="{{ $tipo }}">{{ $tipo }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Elementos por página --}}
            <div class="md:col-span-2 flex items-center justify-end gap-2 text-sm text-gray-500">
                <span>Mostrar:</span>
                <select wire:model.live="perPage"
                        class="py-1.5 px-2.5 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                    <option value="10">10</option>
                    <option value="15">15</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Tabla de Bienes --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50/75 text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    <tr>
                        <th wire:click="sortBy('numero_inventario')" class="px-6 py-3.5 text-left cursor-pointer hover:bg-gray-100 transition select-none">
                            <div class="flex items-center gap-1.5">
                                <span>No. Inventario</span>
                                @if ($sortField === 'numero_inventario')
                                    <span>{!! $sortDirection === 'asc' ? '↑' : '↓' !!}</span>
                                @endif
                            </div>
                        </th>
                        <th class="px-6 py-3.5 text-left">Inv. Anterior</th>
                        <th wire:click="sortBy('equipo')" class="px-6 py-3.5 text-left cursor-pointer hover:bg-gray-100 transition select-none">
                            <div class="flex items-center gap-1.5">
                                <span>Equipo</span>
                                @if ($sortField === 'equipo')
                                    <span>{!! $sortDirection === 'asc' ? '↑' : '↓' !!}</span>
                                @endif
                            </div>
                        </th>
                        <th wire:click="sortBy('marca')" class="px-6 py-3.5 text-left cursor-pointer hover:bg-gray-100 transition select-none">
                            <div class="flex items-center gap-1.5">
                                <span>Marca / Modelo</span>
                                @if ($sortField === 'marca')
                                    <span>{!! $sortDirection === 'asc' ? '↑' : '↓' !!}</span>
                                @endif
                            </div>
                        </th>
                        <th class="px-6 py-3.5 text-left">Serie</th>
                        <th wire:click="sortBy('ubicacion')" class="px-6 py-3.5 text-left cursor-pointer hover:bg-gray-100 transition select-none">
                            <div class="flex items-center gap-1.5">
                                <span>Ubicación</span>
                                @if ($sortField === 'ubicacion')
                                    <span>{!! $sortDirection === 'asc' ? '↑' : '↓' !!}</span>
                                @endif
                            </div>
                        </th>
                        <th class="px-6 py-3.5 text-center">Dictámenes</th>
                        <th class="px-6 py-3.5 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    @forelse ($bienes as $bien)
                        <tr class="hover:bg-blue-50/30 transition">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-bold text-blue-700 bg-blue-50 px-2.5 py-1 rounded-lg border border-blue-100 font-mono">
                                    {{ $bien->numero_inventario }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-500 font-mono text-xs">
                                {{ $bien->numero_inventario_anterior ?: '—' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">
                                {{ $bien->equipo }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                                <div>{{ $bien->marca ?: '—' }}</div>
                                <div class="text-xs text-gray-400">{{ $bien->modelo }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-500 font-mono text-xs">
                                {{ $bien->serie ?: '—' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-600 text-xs">
                                <span class="truncate max-w-[200px] inline-block" title="{{ $bien->ubicacion }}">
                                    {{ $bien->ubicacion ?: 'Sin ubicación' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if ($bien->dictamenes_count > 0)
                                    <button wire:click="abrirModalHistorial({{ $bien->id }})"
                                            title="Ver historial de dictámenes"
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 hover:bg-amber-200 transition">
                                        📋 {{ $bien->dictamenes_count }} {{ $bien->dictamenes_count === 1 ? 'dictamen' : 'dictámenes' }}
                                    </button>
                                @else
                                    <span class="text-xs text-gray-400">0</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="inline-flex items-center gap-2">
                                    {{-- Botón Historial --}}
                                    <button wire:click="abrirModalHistorial({{ $bien->id }})"
                                            title="Historial de dictámenes"
                                            class="p-1.5 text-gray-400 hover:text-blue-600 rounded-lg hover:bg-blue-50 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </button>

                                    {{-- Botón Editar --}}
                                    <button wire:click="abrirModalEditar({{ $bien->id }})"
                                            title="Editar datos del bien"
                                            class="p-1.5 text-gray-400 hover:text-amber-600 rounded-lg hover:bg-amber-50 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>

                                    {{-- Botón Eliminar --}}
                                    <button wire:click="confirmarEliminar({{ $bien->id }})"
                                            title="Eliminar bien"
                                            class="p-1.5 text-gray-400 hover:text-red-600 rounded-lg hover:bg-red-50 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-400">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                    </svg>
                                    <p class="text-base font-semibold text-gray-700">No se encontraron bienes</p>
                                    <p class="text-sm text-gray-400 mt-1">Prueba cambiando los filtros o carga registros al inventario.</p>
                                    <div class="mt-4 flex gap-2">
                                        <button wire:click="abrirModalCrear"
                                                style="background-color: #2563eb !important; color: #ffffff !important;"
                                                class="text-xs font-semibold px-3.5 py-2 rounded-lg hover:bg-blue-700 transition">
                                            + Agregar Bien Individual
                                        </button>
                                        <button wire:click="abrirModalImportar"
                                                style="background-color: #4f46e5 !important; color: #ffffff !important;"
                                                class="text-xs font-semibold px-3.5 py-2 rounded-lg hover:bg-indigo-700 transition">
                                            Carga Masiva Excel
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginación --}}
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
            {{ $bienes->links() }}
        </div>
    </div>


    {{-- MODAL 1: Crear / Editar Bien Individual --}}
    @if ($showModalForm)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity backdrop-blur-sm" wire:click="$set('showModalForm', false)"></div>

                <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-gray-100" style="background-color: #ffffff;">
                    <form wire:submit.prevent="guardar">
                        <div class="px-6 py-4 text-white flex items-center justify-between" style="background: linear-gradient(135deg, #1e40af 0%, #3730a3 100%);">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                <h3 class="text-lg font-bold">
                                    {{ $bienId ? 'Editar Bien Informático' : 'Agregar Bien Individual' }}
                                </h3>
                            </div>
                            <button type="button" wire:click="$set('showModalForm', false)" class="text-white hover:text-gray-200 text-lg font-bold p-1 rounded-lg">
                                ✕
                            </button>
                        </div>

                        <div class="p-6 space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                        No. Inventario <span class="text-red-500">*</span>
                                    </label>
                                    <input wire:model="numero_inventario"
                                           type="text"
                                           placeholder="Ej. INV-2025-001"
                                           class="w-full text-sm rounded-xl border-gray-300 focus:ring-blue-500 focus:border-blue-500 @error('numero_inventario') border-red-500 @enderror"/>
                                    @error('numero_inventario') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                        No. Inventario Anterior
                                    </label>
                                    <input wire:model="numero_inventario_anterior"
                                           type="text"
                                           placeholder="Opcional"
                                           class="w-full text-sm rounded-xl border-gray-300 focus:ring-blue-500 focus:border-blue-500"/>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                    Equipo / Descripción <span class="text-red-500">*</span>
                                </label>
                                <input wire:model="equipo"
                                       type="text"
                                       placeholder="Ej. COMPUTADORA DE ESCRITORIO, LAPTOP, IMPRESORA..."
                                       class="w-full text-sm rounded-xl border-gray-300 focus:ring-blue-500 focus:border-blue-500 @error('equipo') border-red-500 @enderror"/>
                                @error('equipo') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                        Marca
                                    </label>
                                    <input wire:model="marca"
                                           type="text"
                                           placeholder="Ej. HP, Dell, Lenovo"
                                           class="w-full text-sm rounded-xl border-gray-300 focus:ring-blue-500 focus:border-blue-500"/>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                        Modelo
                                    </label>
                                    <input wire:model="modelo"
                                           type="text"
                                           placeholder="Ej. ProDesk 400 G6"
                                           class="w-full text-sm rounded-xl border-gray-300 focus:ring-blue-500 focus:border-blue-500"/>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                        Serie
                                    </label>
                                    <input wire:model="serie"
                                           type="text"
                                           placeholder="Ej. MXL123456"
                                           class="w-full text-sm rounded-xl border-gray-300 focus:ring-blue-500 focus:border-blue-500"/>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                    Ubicación / Área
                                </label>
                                <input wire:model="ubicacion"
                                       type="text"
                                       placeholder="Ej. DIRECCIÓN DE FINANZAS, EDIFICIO A, PISO 2"
                                       class="w-full text-sm rounded-xl border-gray-300 focus:ring-blue-500 focus:border-blue-500"/>
                            </div>
                        </div>

                        <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3 rounded-b-2xl border-t border-gray-100">
                            <button type="button"
                                    wire:click="$set('showModalForm', false)"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition cursor-pointer">
                                Cancelar
                            </button>
                            <button type="submit"
                                    style="background-color: #2563eb !important; color: #ffffff !important;"
                                    class="px-5 py-2 text-sm font-semibold rounded-xl transition shadow-sm hover:bg-blue-700 cursor-pointer">
                                {{ $bienId ? 'Actualizar Bien' : 'Guardar Bien' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif


    {{-- MODAL 2: Importación Masiva (Excel / CSV) --}}
    @if ($showModalImport)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity backdrop-blur-sm" wire:click="$set('showModalImport', false)"></div>

                <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-gray-100" style="background-color: #ffffff;">
                    <form wire:submit.prevent="procesarImportacion">
                        <div class="px-6 py-4 text-white flex items-center justify-between" style="background: linear-gradient(135deg, #4338ca 0%, #6b21a8 100%);">
                            <div class="flex items-center gap-2">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                                <h3 class="text-lg font-bold">Carga Masiva de Bienes (Excel / CSV)</h3>
                            </div>
                            <button type="button" wire:click="$set('showModalImport', false)" class="text-white hover:text-gray-200 text-lg font-bold p-1 rounded-lg">
                                ✕
                            </button>
                        </div>

                        <div class="p-6 space-y-5">
                            {{-- Resumen previo o resultados --}}
                            @if ($importStats)
                                <div class="p-4 bg-gray-50 rounded-xl border border-gray-200 space-y-2">
                                    <h4 class="text-sm font-bold text-gray-800">Resultado del Proceso:</h4>
                                    <div class="grid grid-cols-3 gap-2 text-center text-xs">
                                        <div class="p-2 bg-green-50 text-green-700 rounded-lg font-semibold border border-green-200">
                                            ✓ {{ $importStats['creados'] }} Nuevos
                                        </div>
                                        <div class="p-2 bg-blue-50 text-blue-700 rounded-lg font-semibold border border-blue-200">
                                            ↻ {{ $importStats['actualizados'] }} Actualizados
                                        </div>
                                        <div class="p-2 bg-amber-50 text-amber-700 rounded-lg font-semibold border border-amber-200">
                                            ⚠ {{ $importStats['omitidos'] }} Omitidos
                                        </div>
                                    </div>

                                    @if (!empty($importStats['errores']))
                                        <div class="mt-2 max-h-32 overflow-y-auto p-2 bg-red-50 text-red-700 text-xs rounded border border-red-200 font-mono">
                                            @foreach ($importStats['errores'] as $err)
                                                <div>• {{ $err }}</div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endif

                            {{-- Selector de Archivo --}}
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                                    Selecciona el archivo (.xlsx, .xls, .csv)
                                </label>
                                <div class="border-2 border-dashed border-gray-300 hover:border-indigo-500 rounded-2xl p-6 text-center bg-gray-50/50 transition cursor-pointer relative">
                                    <input wire:model="archivoExcel"
                                           type="file"
                                           accept=".xlsx,.xls,.csv"
                                           class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"/>
                                    <div class="flex flex-col items-center">
                                        <svg class="w-10 h-10 text-indigo-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        <p class="text-sm font-medium text-gray-700">
                                            @if ($archivoExcel)
                                                <span class="text-indigo-600 font-bold">{{ $archivoExcel->getClientOriginalName() }}</span>
                                            @else
                                                Haz clic o arrastra tu archivo aquí
                                            @endif
                                        </p>
                                        <p class="text-xs text-gray-400 mt-1">Formatos soportados: Excel (.xlsx, .xls) o CSV hasta 20MB</p>
                                    </div>
                                </div>
                                @error('archivoExcel') <p class="text-xs text-red-600 mt-1.5">{{ $message }}</p> @enderror

                                {{-- Indicador de carga de archivo --}}
                                <div wire:loading wire:target="archivoExcel" class="mt-2 text-xs text-indigo-600 font-medium flex items-center gap-1.5">
                                    <svg class="animate-spin h-4 w-4 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                    </svg>
                                    Subiendo archivo al servidor...
                                </div>
                            </div>

                            {{-- Modo de Carga --}}
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                                    Modo de Importación
                                </label>
                                <div class="space-y-2 text-sm">
                                    <label class="flex items-start gap-3 p-3 bg-gray-50 hover:bg-gray-100 rounded-xl border border-gray-200 cursor-pointer transition">
                                        <input type="radio" wire:model="modoImportacion" value="upsert" class="mt-0.5 text-indigo-600 focus:ring-indigo-500">
                                        <div>
                                            <p class="font-semibold text-gray-800">Actualizar existentes y agregar nuevos (Recomendado)</p>
                                            <p class="text-xs text-gray-500">Si el número de inventario ya existe, se actualizarán su equipo, marca, modelo, serie y ubicación. Los nuevos se agregarán.</p>
                                        </div>
                                    </label>

                                    <label class="flex items-start gap-3 p-3 bg-gray-50 hover:bg-gray-100 rounded-xl border border-gray-200 cursor-pointer transition">
                                        <input type="radio" wire:model="modoImportacion" value="solo_nuevos" class="mt-0.5 text-indigo-600 focus:ring-indigo-500">
                                        <div>
                                            <p class="font-semibold text-gray-800">Solo agregar registros nuevos</p>
                                            <p class="text-xs text-gray-500">Si un número de inventario ya está registrado en el sistema, se omitirá y no se sobrescribirá.</p>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            {{-- Ayuda y Descarga de Plantilla --}}
                            <div class="flex items-center justify-between p-3 bg-indigo-50/70 border border-indigo-100 rounded-xl text-xs text-indigo-900">
                                <span>¿No estás seguro del formato requerido?</span>
                                <button type="button" wire:click="descargarPlantilla" class="font-bold underline hover:text-indigo-700 cursor-pointer">
                                    Descargar Plantilla Oficial
                                </button>
                            </div>
                        </div>

                        <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3 rounded-b-2xl border-t border-gray-100">
                            <button type="button"
                                    wire:click="$set('showModalImport', false)"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition cursor-pointer">
                                Cerrar
                            </button>
                            <button type="submit"
                                    wire:loading.attr="disabled"
                                    style="background-color: #4f46e5 !important; color: #ffffff !important;"
                                    class="inline-flex items-center px-5 py-2 text-sm font-semibold rounded-xl transition shadow-sm hover:bg-indigo-700 disabled:opacity-50 cursor-pointer">
                                <span wire:loading.remove wire:target="procesarImportacion">Iniciar Carga</span>
                                <span wire:loading wire:target="procesarImportacion">Procesando registros...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif


    {{-- MODAL 3: Historial de Dictámenes del Bien --}}
    @if ($showModalHistorial && $bienSeleccionado)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity backdrop-blur-sm" wire:click="$set('showModalHistorial', false)"></div>

                <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full border border-gray-100" style="background-color: #ffffff;">
                    <div class="px-6 py-4 text-white flex items-center justify-between" style="background: linear-gradient(135deg, #1f2937 0%, #111827 100%);">
                        <div>
                            <h3 class="text-lg font-bold">Historial de Dictámenes</h3>
                            <p class="text-xs text-gray-300">Inventario: <span class="font-mono text-amber-300 font-bold">{{ $bienSeleccionado->numero_inventario }}</span> — {{ $bienSeleccionado->equipo }}</p>
                        </div>
                        <button type="button" wire:click="$set('showModalHistorial', false)" class="text-white hover:text-gray-200 text-lg font-bold p-1 rounded-lg">
                            ✕
                        </button>
                    </div>

                    <div class="p-6 space-y-4">
                        {{-- Tarjeta Informativa del Bien --}}
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-gray-50 p-4 rounded-xl text-xs border border-gray-200">
                            <div>
                                <span class="text-gray-400 block font-semibold">Marca:</span>
                                <span class="text-gray-800 font-medium">{{ $bienSeleccionado->marca ?: '—' }}</span>
                            </div>
                            <div>
                                <span class="text-gray-400 block font-semibold">Modelo:</span>
                                <span class="text-gray-800 font-medium">{{ $bienSeleccionado->modelo ?: '—' }}</span>
                            </div>
                            <div>
                                <span class="text-gray-400 block font-semibold">Serie:</span>
                                <span class="text-gray-800 font-mono font-medium">{{ $bienSeleccionado->serie ?: '—' }}</span>
                            </div>
                            <div>
                                <span class="text-gray-400 block font-semibold">Ubicación:</span>
                                <span class="text-gray-800 font-medium">{{ $bienSeleccionado->ubicacion ?: '—' }}</span>
                            </div>
                        </div>

                        {{-- Listado de Dictámenes --}}
                        <div class="space-y-3">
                            <h4 class="text-sm font-bold text-gray-800">Dictámenes Registrados:</h4>

                            @if (count($historialDictamenes) > 0)
                                <div class="divide-y divide-gray-100 border border-gray-200 rounded-xl overflow-hidden max-h-80 overflow-y-auto">
                                    @foreach ($historialDictamenes as $dictamen)
                                        <div class="p-4 hover:bg-gray-50 transition">
                                            <div class="flex items-start justify-between">
                                                <div>
                                                    <div class="flex items-center gap-2">
                                                        <span class="font-bold text-sm text-gray-900 font-mono">Dictamen #{{ $dictamen->id }}</span>
                                                        @if ($dictamen->reporte)
                                                            <span class="text-xs bg-blue-100 text-blue-800 px-2 py-0.5 rounded font-semibold">
                                                                Reporte #{{ $dictamen->reporte->id }}
                                                            </span>
                                                            <span class="text-xs bg-gray-100 text-gray-700 px-2 py-0.5 rounded">
                                                                Estado: {{ $dictamen->reporte->estado?->name ?? '—' }}
                                                            </span>
                                                        @endif
                                                    </div>

                                                    <p class="text-xs text-gray-600 mt-1">
                                                        <span class="font-semibold">Diagnóstico:</span> {{ $dictamen->diagnostico }}
                                                    </p>

                                                    <div class="flex items-center gap-4 text-xs text-gray-400 mt-2">
                                                        <span>📅 {{ $dictamen->created_at->format('d/m/Y H:i') }}</span>
                                                        @if ($dictamen->reporte?->tecnico)
                                                            <span>👤 Técnico: {{ $dictamen->reporte->tecnico->name }}</span>
                                                        @endif
                                                        @if ($dictamen->reporte?->departamento)
                                                            <span>🏢 Depto: {{ $dictamen->reporte->departamento->name }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="p-8 text-center bg-gray-50 rounded-xl text-gray-400 text-sm">
                                    Este bien no cuenta con dictámenes técnicos registrados hasta el momento.
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="bg-gray-50 px-6 py-4 flex justify-end rounded-b-2xl border-t border-gray-100">
                        <button type="button"
                                wire:click="$set('showModalHistorial', false)"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition cursor-pointer">
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif


    {{-- MODAL 4: Confirmación de Eliminación --}}
    @if ($showModalDelete && $bienParaEliminar)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity backdrop-blur-sm" wire:click="$set('showModalDelete', false)"></div>

                <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100" style="background-color: #ffffff;">
                    <div class="bg-red-50 p-6 flex items-start gap-4">
                        <div class="p-3 bg-red-100 text-red-600 rounded-full flex-shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">¿Eliminar Bien Informático?</h3>
                            <p class="text-sm text-gray-600 mt-1">
                                Estás a punto de eliminar el bien con número de inventario <strong class="font-mono text-gray-900">{{ $bienParaEliminar->numero_inventario }}</strong> ({{ $bienParaEliminar->equipo }}).
                            </p>

                            @if ($bienParaEliminar->dictamenes_count > 0)
                                <div class="mt-3 p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-800 font-medium">
                                    ⚠️ Este bien tiene {{ $bienParaEliminar->dictamenes_count }} dictamen(es) vinculado(s). Por seguridad e integridad histórica, no podrá ser eliminado.
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3 rounded-b-2xl border-t border-gray-100">
                        <button type="button"
                                wire:click="$set('showModalDelete', false)"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition cursor-pointer">
                            Cancelar
                        </button>
                        <button type="button"
                                wire:click="eliminar"
                                @if ($bienParaEliminar->dictamenes_count > 0) disabled @endif
                                style="background-color: #dc2626 !important; color: #ffffff !important;"
                                class="px-5 py-2 text-sm font-semibold rounded-xl transition shadow-sm hover:bg-red-700 disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer">
                            Confirmar Eliminación
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>
