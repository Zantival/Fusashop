<?php

namespace App\Listeners;

use App\Events\ProductOutOfStock;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class NotifySellerOutOfStock implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(ProductOutOfStock $event): void
    {
        $product = $event->product;
        Log::info("The product '{$product->name}' (ID: {$product->id}) is out of stock. Seller ID: {$product->merchant_id} notified.");
        // We log it. In a real scenario we'd send an Email/Notification to $product->merchant.
    }
}
