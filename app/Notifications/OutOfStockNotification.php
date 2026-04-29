<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use App\Models\Product;

class OutOfStockNotification extends Notification
{
    public function __construct(public Product $product) {}

    public function via($notifiable): array { return ['database']; }

    public function toArray($notifiable): array
    {
        return [
            'title'      => '¡Producto Agotado!',
            'message'    => "Tu producto '{$this->product->name}' se ha quedado sin stock.",
            'icon'       => 'warning',
            'product_id' => $this->product->id,
            'url'        => route('merchant.inventory'),
        ];
    }
}
