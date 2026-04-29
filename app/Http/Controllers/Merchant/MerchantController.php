<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Models\{Product, Order, OrderItem, CompanyProfile, Review};
use App\Notifications\OrderStatusChanged;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MerchantController extends Controller
{

    public function dashboard()
    {
        $merchant = Auth::user();
        $productIds = $merchant->products()->pluck('id');

        $totalProducts  = $merchant->products()->count();
        $outOfStock     = $merchant->products()->where('stock', 0)->count();
        $totalSales     = OrderItem::whereIn('product_id', $productIds)->selectRaw('SUM(quantity * price) as total')->value('total');
        $pendingOrders  = Order::whereHas('items', fn($q) => $q->whereIn('product_id', $productIds))
                               ->where('status','pending')->count();
        $recentOrders   = Order::whereHas('items', fn($q) => $q->whereIn('product_id', $productIds))
                               ->with('user','items.product')->latest()->take(5)->get();
        $recentReviews  = Review::whereIn('product_id', $productIds)->with('user','product')->latest()->take(5)->get();
        $avgRating      = Review::whereIn('product_id', $productIds)->avg('rating') ?? 0;
        $totalReviews   = Review::whereIn('product_id', $productIds)->count();

        return view('merchant.dashboard', compact(
            'totalProducts','outOfStock','totalSales','pendingOrders','recentOrders',
            'recentReviews','avgRating','totalReviews'
        ));
    }

    public function onboardingProfile()
    {
        $profile = Auth::user()->companyProfile;
        return view('merchant.profile', compact('profile'));
    }

    public function storeProfile(Request $request)
    {
        $request->validate([
            'owner_name'           => 'required|string|max:255',
            'company_name'         => 'required|string|max:255',
            'business_type'        => 'required|string|max:255',
            'phone'                => 'required|string|max:50',
            'employee_count'       => 'nullable|integer|min:1',
            'description'          => 'nullable|string|max:2000',
            'rut_file'             => 'required_without:rut_exists|mimes:pdf|max:2048',
            'camara_comercio_file' => 'required_without:camara_comercio_exists|mimes:pdf,jpg,jpeg,png|max:3072',
            'logo'            => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'banners.*'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:3072',
            'address'         => 'nullable|string|max:255',
            'google_maps_url' => 'nullable|url|max:500',
            'latitude'        => 'nullable|numeric',
            'longitude'       => 'nullable|numeric',
        ]);

        $user = Auth::user();
        $user->update(['name' => strip_tags($request->owner_name)]);

        $profile = $user->companyProfile ?? new CompanyProfile(['merchant_id' => $user->id]);
        
        $profile->company_name    = strip_tags($request->company_name);
        $profile->business_type   = strip_tags($request->business_type);
        $profile->phone           = strip_tags($request->phone);
        $profile->employee_count  = $request->employee_count;
        $profile->description     = strip_tags($request->description ?? '');
        $profile->address         = strip_tags($request->address ?? '');
        $profile->google_maps_url = strip_tags($request->google_maps_url ?? '');
        $profile->latitude        = $request->latitude;
        $profile->longitude       = $request->longitude;
        
        if ($request->has('whatsapp')) {
            $profile->whatsapp = preg_replace('/\D/', '', $request->whatsapp ?? '');
        }
        
        if ($request->hasFile('rut_file')) {
            if ($profile->rut_path) {
                Storage::delete($profile->rut_path);
            }
            $profile->rut_path = $request->file('rut_file')->storeAs('kyc', Str::uuid() . '_rut.pdf', 'local');
            $profile->kyc_status = 'pending';
        }

        if ($request->hasFile('camara_comercio_file')) {
            if ($profile->camara_comercio_path) {
                Storage::delete($profile->camara_comercio_path);
            }
            $ext = $request->file('camara_comercio_file')->getClientOriginalExtension();
            $profile->camara_comercio_path = $request->file('camara_comercio_file')->storeAs('kyc', Str::uuid() . '_camara.' . $ext, 'local');
            $profile->kyc_status = 'pending';
        }

        if ($request->hasFile('logo')) {
            if ($profile->logo_path) {
                Storage::disk('public')->delete($profile->logo_path);
            }
            $profile->logo_path = $request->file('logo')->store('merchants/logos', 'public');
        }

        // Handle banner deletion checkbox FIRST to avoid deleting newly uploaded ones
        if ($request->boolean('delete_banners') && !$request->hasFile('banners')) {
            if ($profile->banners_path && is_array($profile->banners_path)) {
                foreach ($profile->banners_path as $ob) { 
                    $path = is_array($ob) ? ($ob['path'] ?? null) : $ob;
                    if ($path) Storage::disk('public')->delete($path); 
                }
            }
            $profile->banners_path = null;
        }

        if ($request->hasFile('banners')) {
            // Delete old banners as they will be replaced
            if ($profile->banners_path && is_array($profile->banners_path)) {
                foreach ($profile->banners_path as $ob) { 
                    $path = is_array($ob) ? ($ob['path'] ?? null) : $ob;
                    if ($path) Storage::disk('public')->delete($path); 
                }
            }
            
            $bannerData = [];
            $titles = $request->input('banner_titles', []);
            $subtitles = $request->input('banner_subtitles', []);
            
            foreach ($request->file('banners') as $index => $banner) {
                $path = $banner->store('merchants/banners', 'public');
                $bannerData[] = [
                    'path'     => $path,
                    'title'    => strip_tags($titles[$index] ?? ''),
                    'subtitle' => strip_tags($subtitles[$index] ?? ''),
                ];
            }
            $profile->banners_path = $bannerData;
        } elseif ($profile->banners_path && is_array($profile->banners_path) && !$request->boolean('delete_banners')) {
            // Solo actualizar textos de banners existentes si no se subieron archivos nuevos
            $titles = $request->input('banner_titles', []);
            $subtitles = $request->input('banner_subtitles', []);
            $updatedBanners = $profile->banners_path;

            foreach($updatedBanners as $index => $b) {
                if (is_array($b)) {
                    $updatedBanners[$index]['title'] = strip_tags($titles[$index] ?? '');
                    $updatedBanners[$index]['subtitle'] = strip_tags($subtitles[$index] ?? '');
                } else {
                    $updatedBanners[$index] = [
                        'path' => $b,
                        'title' => strip_tags($titles[$index] ?? ''),
                        'subtitle' => strip_tags($subtitles[$index] ?? ''),
                    ];
                }
            }
            $profile->banners_path = $updatedBanners;
        }

        $profile->save();

        return back()->with('success', 'Cambios guardados exitosamente.');
    }

    public function editStore()
    {
        $profile = Auth::user()->companyProfile;
        return view('merchant.store-edit', compact('profile'));
    }

    public function products()
    {
        $products = Auth::user()->products()->latest()->paginate(12);
        return view('merchant.products', compact('products'));
    }

    public function productCreate()
    {
        $profile = Auth::user()->companyProfile;
        if (!$profile || $profile->kyc_status !== 'approved') {
            return redirect()->route('merchant.profile')->with('error', 'Debes completar tu perfil y ser validado (KYC) antes de publicar productos.');
        }
        return view('merchant.product-form', ['product' => null]);
    }

    public function productStore(Request $request)
    {
        $profile = Auth::user()->companyProfile;
        if (!$profile || $profile->kyc_status !== 'approved') {
            abort(403, 'KYC No Aprobado');
        }

        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'price'       => 'required|numeric|min:0',
            'stock'       => 'required|integer|min:0',
            'category'    => 'required|string|max:100',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'is_active'   => 'boolean',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        Auth::user()->products()->create([
            'name'        => strip_tags($data['name']),
            'slug'        => Str::slug($data['name']) . '-' . uniqid(),
            'description' => strip_tags($data['description'] ?? ''),
            'price'       => $data['price'],
            'stock'       => $data['stock'],
            'category'    => strip_tags($data['category']),
            'image'       => $imagePath,
            'is_active'   => $request->boolean('is_active', true),
        ]);

        return redirect()->route('merchant.products')->with('success', 'Producto creado exitosamente.');
    }

    public function productEdit(Product $product)
    {
        if ($product->merchant_id !== Auth::id()) abort(403);
        return view('merchant.product-form', compact('product'));
    }

    public function productUpdate(Request $request, Product $product)
    {
        if ($product->merchant_id !== Auth::id()) abort(403);

        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'price'       => 'required|numeric|min:0',
            'stock'       => 'required|integer|min:0',
            'category'    => 'required|string|max:100',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'is_active'   => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            if ($product->image) Storage::disk('public')->delete($product->image);
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update([
            'name'        => strip_tags($data['name']),
            'description' => strip_tags($data['description'] ?? ''),
            'price'       => $data['price'],
            'stock'       => $data['stock'],
            'category'    => strip_tags($data['category']),
            'image'       => $data['image'] ?? $product->image,
            'is_active'   => $request->boolean('is_active', true),
        ]);

        return redirect()->route('merchant.products')->with('success', 'Producto actualizado.');
    }

    public function productDelete(Product $product)
    {
        if ($product->merchant_id !== Auth::id()) abort(403);
        if ($product->image) Storage::disk('public')->delete($product->image);
        $product->delete();
        return back()->with('success', 'Producto eliminado.');
    }

    public function orders()
    {
        $productIds = Auth::user()->products()->pluck('id');
        $orders = Order::whereHas('items', fn($q) => $q->whereIn('product_id', $productIds))
                       ->with('user','items.product')->latest()->paginate(10);
        return view('merchant.orders', compact('orders'));
    }

    public function orderUpdate(Request $request, Order $order)
    {
        $request->validate(['status' => 'required|in:pending,processing,shipped,delivered,cancelled']);
        
        if ($request->status === 'delivered' && $order->status !== 'delivered') {
            // Assign loyalty points (1 point per 1000 COP spent) for items belonging to this merchant
            $merchantId = Auth::id();
            $orderPoints = 0;
            foreach($order->items as $item) {
                if ($item->product && $item->product->merchant_id === $merchantId) {
                    $orderPoints += floor(($item->quantity * $item->price) / 1000);
                }
            }

            if ($orderPoints > 0) {
                $loyalty = \App\Models\LoyaltyPoint::firstOrCreate(
                    ['user_id' => $order->user_id, 'merchant_id' => $merchantId],
                    ['points' => 0]
                );
                $loyalty->increment('points', $orderPoints);
            }
        }

        $order->update(['status' => $request->status]);

        // Notify the consumer
        $order->user->notify(new OrderStatusChanged($order));

        return back()->with('success', 'Estado actualizado.');
    }

    public function reviews()
    {
        $productIds = Auth::user()->products()->pluck('id');
        $reviews = Review::whereIn('product_id', $productIds)
                         ->with('user', 'product')
                         ->latest()->paginate(20);
        $avgRating = Review::whereIn('product_id', $productIds)->avg('rating') ?? 0;
        return view('merchant.reviews', compact('reviews', 'avgRating'));
    }

    public function bannerRequest()
    {
        return view('merchant.request-banner');
    }

    public function bannerRequestStore(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png|max:3072',
            'notes' => 'nullable|string|max:1000'
        ]);

        $imagePath = $request->file('image')->store('banners/requests', 'public');
        $paymentPath = 'paid_online_' . time(); // Pago en línea simulado

        $bannerRequest = \App\Models\BannerRequest::create([
            'user_id' => Auth::id(),
            'image_path' => $imagePath,
            'payment_proof_path' => $paymentPath,
            'notes' => strip_tags($request->notes),
            'status' => 'pending'
        ]);

        $analysts = \App\Models\User::where('role', 'analyst')->get();
        foreach($analysts as $analyst) {
            $analyst->notify(new \App\Notifications\BannerRequestNotification($bannerRequest));
        }

        return redirect()->route('merchant.store.edit')->with('success', 'Solicitud de banner enviada y pagada correctamente.');
    }

    public function inventory()
    {
        $products = Auth::user()->products()->orderBy('name')->get();
        return view('merchant.inventory', compact('products'));
    }

    public function updateInventory(Request $request)
    {
        $data = $request->validate([
            'stocks' => 'required|array',
            'stocks.*' => 'required|integer|min:0'
        ]);

        $merchantId = Auth::id();

        foreach ($data['stocks'] as $productId => $stock) {
            Product::where('id', $productId)->where('merchant_id', $merchantId)->update(['stock' => $stock]);
        }

        return redirect()->route('merchant.inventory')->with('success', 'Inventario actualizado rápidamente.');
    }
}
