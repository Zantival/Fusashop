<?php
namespace App\Notifications;

use Illuminate\Notifications\Notification;
use App\Models\Order;

class OrderStatusChanged extends Notification
{
    public function __construct(public Order $order) {}

    public function via($notifiable): array { return ['database']; }

    public function toArray($notifiable): array
    {
        $labels = [
            'pending'    => 'Pendiente',
            'processing' => 'En proceso',
            'shipped'    => 'Enviado',
            'delivered'  => 'Entregado',
            'cancelled'  => 'Cancelado',
        ];
        $icons = [
            'pending'    => 'schedule',
            'processing' => 'sync',
            'shipped'    => 'local_shipping',
            'delivered'  => 'check_circle',
            'cancelled'  => 'cancel',
        ];
        $status = $this->order->status;
        return [
            'title'    => 'Pedido #' . $this->order->id . ' actualizado',
            'message'  => 'El estado de tu pedido cambió a: ' . ($labels[$status] ?? $status),
            'icon'     => $icons[$status] ?? 'info',
            'order_id' => $this->order->id,
        ];
    }
}
