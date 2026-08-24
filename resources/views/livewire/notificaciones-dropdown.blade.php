<div class="relative" x-data="{ open: @entangle('open') }" @click.outside="open = false">
    {{-- Campana --}}
    <button @click="open = !open" class="relative p-2 text-gray-500 hover:text-gray-700 focus:outline-none transition">
        <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
        </svg>

        {{-- Badge con conteo --}}
        @if ($this->noLeidasCount > 0)
            <span class="absolute top-0 right-0 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full">
                {{ $this->noLeidasCount > 9 ? '9+' : $this->noLeidasCount }}
            </span>
        @endif
    </button>

    {{-- Dropdown --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 z-50 overflow-hidden"
         style="display: none;">

        {{-- Header --}}
        <div class="flex items-center justify-between px-4 py-3 bg-gray-50 border-b">
            <h3 class="text-sm font-semibold text-gray-700">Notificaciones</h3>
            @if ($this->noLeidasCount > 0)
                <button wire:click="marcarTodasLeidas"
                        class="text-xs text-blue-600 hover:text-blue-800 font-medium transition">
                    Marcar todas como leídas
                </button>
            @endif
        </div>

        {{-- Lista --}}
        <div class="max-h-80 overflow-y-auto divide-y divide-gray-100">
            @forelse ($notificaciones as $notif)
                <div wire:click="marcarLeida('{{ $notif->id }}')"
                     class="px-4 py-3 cursor-pointer hover:bg-gray-50 transition {{ is_null($notif->read_at) ? 'bg-blue-50 border-l-4 border-blue-500' : '' }}">
                    <div class="flex items-start gap-3">
                        {{-- Icono de estado --}}
                        <div class="flex-shrink-0 mt-0.5">
                            @if (($notif->data['nuevo_estado'] ?? '') === 'Atendido')
                                <span class="inline-flex items-center justify-center w-8 h-8 bg-green-100 rounded-full">
                                    <i class="fa-solid fa-check text-green-600 text-sm"></i>
                                </span>
                            @else
                                <span class="inline-flex items-center justify-center w-8 h-8 bg-red-100 rounded-full">
                                    <i class="fa-solid fa-circle-xmark text-red-600 text-sm"></i>
                                </span>
                            @endif
                        </div>

                        {{-- Contenido --}}
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-800 {{ is_null($notif->read_at) ? 'font-semibold' : '' }}">
                                {{ $notif->data['mensaje'] ?? 'Sin mensaje' }}
                            </p>
                            <p class="text-xs text-gray-400 mt-1">
                                {{ $notif->created_at->diffForHumans() }}
                            </p>
                        </div>

                        {{-- Punto de no leída --}}
                        @if (is_null($notif->read_at))
                            <span class="flex-shrink-0 w-2 h-2 bg-blue-500 rounded-full mt-2"></span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="px-4 py-8 text-center text-gray-400">
                    <svg class="w-10 h-10 mx-auto mb-2 text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                    </svg>
                    <p class="text-sm">No tienes notificaciones</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
