<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Order;

class NewOrderNotification extends Notification
{
    use Queueable;

    public function __construct(public Order $order)
    {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'id' => $this->order->id,
            'title' => '¡Nuevo Pedido Recibido!',
            'message' => "Has recibido un nuevo pedido #{$this->order->id}. Revisa los detalles ahora.",
            'icon' => 'shopping_cart',
            'url' => route('merchant.orders'),
            'type' => 'new_order',
            'amount' => $this->order->total,
        ];
    }
}
