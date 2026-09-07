<?php

namespace App\Livewire;

use Livewire\Component;

class NotificacionesDropdown extends Component
{
    public bool $open = false;

    public function toggle()
    {
        $this->open = !$this->open;
    }

    public function marcarLeida(string $notificationId)
    {
        $notification = auth()->user()?->notifications()->find($notificationId);
        if ($notification) {
            $notification->markAsRead();
        }
    }

    public function marcarTodasLeidas()
    {
        auth()->user()?->unreadNotifications->markAsRead();
    }

    public function getNoLeidasCountProperty(): int
    {
        return auth()->user()?->unreadNotifications()->count() ?? 0;
    }

    public function render()
    {
        $notificaciones = auth()->user()
            ? auth()->user()->unreadNotifications()->latest()->take(20)->get()
            : collect();

        return view('livewire.notificaciones-dropdown', [
            'notificaciones' => $notificaciones,
        ]);
    }
}