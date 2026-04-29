@extends('layouts.app')
@section('title', $product->name)

@php
use Illuminate\Support\Facades\Storage;

$cats = [
    'Ropa'        => ['#7c3aed','#a855f7','rgba(124,58,237,.18)','rgba(168,85,247,.35)'],
    'Electrónica' => ['#1d4ed8','#06b6d4','rgba(29,78,216,.18)','rgba(6,182,212,.35)'],
    'Hogar'       => ['#d97706','#f59e0b','rgba(217,119,6,.18)','rgba(245,158,11,.35)'],
    'Deportes'    => ['#16a34a','#22c55e','rgba(22,163,74,.18)','rgba(34,197,94,.35)'],
    'Alimentos'   => ['#ca8a04','#84cc16','rgba(202,138,4,.18)','rgba(132,204,22,.35)'],
    'Belleza'     => ['#db2777','#f43f5e','rgba(219,39,119,.18)','rgba(244,63,94,.35)'],
    'Juguetes'    => ['#ea580c','#fb923c','rgba(234,88,12,.18)','rgba(251,146,60,.35)'],
    'Libros'      => ['#0f766e','#14b8a6','rgba(15,118,110,.18)','rgba(20,184,166,.35)'],
];
$c    = $cats[$product->category] ?? ['#006c47','#00b67a','rgba(0,108,71,.18)','rgba(0,182,122,.35)'];
$from = $c[0]; $to = $c[1]; $glow1 = $c[2]; $glow2 = $c[3];

$mainImageUrl = $product->image ? url('files/' . $product->image) : null;
$hasImage     = !!$mainImageUrl;
$avgRating    = $product->reviews->avg('rating') ?? 0;
$icon = ['Ropa'=>'checkroom','Electrónica'=>'devices','Hogar'=>'chair','Deportes'=>'sports_soccer',
         'Alimentos'=>'lunch_dining','Belleza'=>'spa','Juguetes'=>'toys','Libros'=>'menu_book'][$product->category] ?? 'inventory_2';
@endphp

@section('content')

{{-- ───────────── HERO ───────────── --}}
<div class="pd-hero">

  {{-- Fondo con gradientes estáticos (sin color-mix, sin JS) --}}
  <div class="pd-bg" style="
    background:
      radial-gradient(ellipse 55% 55% at 78% 48%, {{ $glow1 }} 0%, transparent 70%),
      radial-gradient(ellipse 35% 40% at 22% 75%, {{ $glow2 }} 0%, transparent 65%),
      linear-gradient(145deg,#0c0e0d 0%,#111a15 100%);
  "></div>

  {{-- Glow que sigue cursor (Canvas, no DOM) --}}
  <canvas id="pd-canvas" class="pd-canvas" aria-hidden="true"></canvas>

  <div class="max-w-7xl mx-auto px-4 md:px-6 py-10 relative z-10">

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-2 text-xs text-white/40 mb-8 font-medium">
      <a href="{{ route('consumer.catalog') }}" class="hover:text-white/80 transition-colors">Catálogo</a>
      <span class="material-symbols-outlined text-[13px]">chevron_right</span>
      <span>{{ $product->category }}</span>
      <span class="material-symbols-outlined text-[13px]">chevron_right</span>
      <span class="text-white/70 truncate max-w-[200px]">{{ $product->name }}</span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">

      {{-- ── Imagen ── --}}
      <div class="pd-stage-wrap">
        <div class="pd-stage" id="pd-stage">
          <div class="pd-product-img" id="pd-product-img">
            @if($hasImage)
              <img id="main-img" src="{{ $mainImageUrl }}" alt="{{ e($product->name) }}"
                   class="pd-img" loading="eager" fetchpriority="high" decoding="async"
                   onerror="this.style.display='none';document.getElementById('pd-fallback').style.removeProperty('display')"/>
              <div id="pd-fallback" class="pd-fallback" style="display:none">
                <span class="material-symbols-outlined" style="font-size:6rem;color:white;font-variation-settings:'FILL' 1">{{ $icon }}</span>
              </div>
            @else
              <div class="pd-fallback">
                <span class="material-symbols-outlined" style="font-size:6rem;color:white;font-variation-settings:'FILL' 1">{{ $icon }}</span>
              </div>
            @endif
          </div>

          <div class="pd-shadow" style="background:{{ $from }}"></div>
        </div>
      </div>

      {{-- ── Info ── --}}
      <div class="pd-info">

        <div class="flex items-center gap-2 flex-wrap pd-fi" style="--d:.05s">
          <span class="pd-badge" style="background:{{ $glow1 }};border-color:{{ $glow2 }}">{{ $product->category }}</span>
          @if($product->stock <= 5 && $product->stock > 0)
            <span class="pd-badge" style="background:rgba(252,211,77,.12);border-color:rgba(252,211,77,.3);color:#fde68a">⚠ Solo {{ $product->stock }} disp.</span>
          @elseif($product->stock <= 0)
            <span class="pd-badge" style="background:rgba(186,26,26,.12);border-color:rgba(186,26,26,.3);color:#fca5a5">Agotado</span>
          @else
            <span class="pd-badge" style="background:rgba(110,252,185,.1);border-color:rgba(110,252,185,.25);color:#6efcb9">✓ En stock</span>
          @endif
        </div>

        <h1 class="text-3xl md:text-4xl font-black text-white leading-tight pd-fi" style="--d:.1s">{{ e($product->name) }}</h1>

        <div class="flex items-center gap-2 pd-fi" style="--d:.14s">
          @for($i=1;$i<=5;$i++)
            <span class="material-symbols-outlined text-[18px]"
              style="font-variation-settings:'FILL' {{ $i<=round($avgRating)?1:0 }};color:{{ $i<=round($avgRating)?'#feb700':'rgba(255,255,255,.2)' }}">star</span>
          @endfor
          <span class="text-sm font-bold text-white ml-1">{{ number_format($avgRating,1) }}</span>
          <span class="text-sm text-white/40">({{ $product->reviews->count() }} reseñas)</span>
        </div>

        <div class="pd-fi" style="--d:.18s">
          <p class="text-[10px] font-bold text-white/30 uppercase tracking-widest mb-1">Precio</p>
          <div class="flex items-baseline gap-2">
            <span class="text-5xl font-black text-white">${{ number_format($product->price,0,',','.') }}</span>
            <span class="text-sm text-white/40">COP</span>
          </div>
        </div>

        <a href="{{ route('consumer.merchant.profile', $product->merchant_id) }}"
           class="pd-merchant pd-fi" style="--d:.21s">
          @if($product->merchant->companyProfile?->logo_path)
            <img src="{{ Storage::url($product->merchant->companyProfile->logo_path) }}" class="w-10 h-10 rounded-full object-cover shrink-0">
          @else
            <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-black shrink-0" style="background:{{ $from }}">
              {{ strtoupper(substr($product->merchant->name,0,1)) }}
            </div>
          @endif
          <div class="flex-1 min-w-0">
            <p class="text-[9px] font-black text-white/30 uppercase tracking-widest">Vendido por</p>
            <p class="font-bold text-sm text-white truncate">{{ e($product->merchant->companyProfile?->company_name ?? $product->merchant->name) }}</p>
          </div>
          <span class="material-symbols-outlined text-white/40 text-[18px]">chevron_right</span>
        </a>

        @if($product->description)
          <p class="text-white/60 leading-relaxed text-sm pd-fi" style="--d:.24s">{{ e($product->description) }}</p>
        @endif

        @if($product->stock > 0)
          @auth
            @if(auth()->user()->isConsumer())
              <form id="add-to-cart-form" method="POST" action="{{ route('consumer.cart.add') }}" class="pd-fi" style="--d:.27s">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <div class="flex items-center gap-3">
                  <div class="pd-qty">
                    <button type="button" onclick="const i=document.getElementById('qty');if(i.value>1)i.value--" class="pd-qty-btn">
                      <span class="material-symbols-outlined text-[18px]">remove</span>
                    </button>
                    <input type="number" id="qty" name="quantity" value="1" min="1" max="{{ $product->stock }}"
                      class="w-12 text-center bg-transparent border-0 font-black text-white focus:ring-0 text-sm p-0 m-0 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                    <button type="button" onclick="const i=document.getElementById('qty');if(i.value<{{ $product->stock }})i.value++" class="pd-qty-btn">
                      <span class="material-symbols-outlined text-[18px]">add</span>
                    </button>
                  </div>
                  <button type="submit" class="add-to-cart" style="--background: linear-gradient(135deg,{{ $from }},{{ $to }}); --shadow: {{ $glow2 }}">
                    <span class="default">Añadir al carrito</span>
                    <span class="success">¡Añadido!</span>
                    <div class="cart">
                      <svg viewBox="0 0 36 26">
                        <polyline points="1 2.5 6 2.5 10 18.5 25.5 18.5 28.5 7.5 7.5 7.5"></polyline>
                        <circle cx="15" cy="23" r="2"></circle>
                        <circle cx="21" cy="23" r="2"></circle>
                      </svg>
                    </div>
                  </button>
                </div>
              </form>
            @else
              <div class="pd-fi p-4 rounded-2xl bg-white/5 border border-white/10 text-center" style="--d:.27s">
                <p class="text-white/60 text-sm font-medium flex items-center justify-center gap-2">
                  <span class="material-symbols-outlined text-amber-500">info</span>
                  Inicia sesión como <span class="text-primary font-bold">Cliente</span> para comprar.
                </p>
              </div>
            @endif
          @else
            <div class="flex items-center gap-3 pd-fi" style="--d:.27s">
              <div class="pd-qty opacity-50 cursor-not-allowed">
                <button type="button" class="pd-qty-btn"><span class="material-symbols-outlined text-[18px]">remove</span></button>
                <input type="number" value="1" disabled class="w-12 text-center bg-transparent border-0 font-black text-white text-sm p-0 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                <button type="button" class="pd-qty-btn"><span class="material-symbols-outlined text-[18px]">add</span></button>
              </div>
              <a href="{{ route('login') }}?intended={{ urlencode(request()->fullUrl()) }}" class="add-to-cart no-underline" style="--background: linear-gradient(135deg,{{ $from }},{{ $to }}); --shadow: {{ $glow2 }}">
                <span class="default">Añadir al carrito</span>
              </a>
            </div>
          @endauth
        @else
          <div class="py-4 text-center rounded-2xl border" style="background:rgba(186,26,26,.08);border-color:rgba(186,26,26,.2)">
            <p class="text-red-300 font-bold text-sm">Producto agotado</p>
          </div>
        @endif

        <div class="flex items-center gap-3 pd-fi" style="--d:.3s">
          @auth
            <a href="{{ route('chat.show', $product->merchant_id) }}?product_id={{ $product->id }}" class="pd-contact-btn">
              <span class="material-symbols-outlined text-[18px]">chat</span> Preguntar al vendedor
            </a>
          @else
            <a href="{{ route('login') }}" class="pd-contact-btn">
              <span class="material-symbols-outlined text-[18px]">chat</span> Preguntar al vendedor
            </a>
          @endauth
          @if($product->merchant->companyProfile?->phone)
            <a href="https://wa.me/57{{ preg_replace('/\D/','', $product->merchant->companyProfile->phone) }}?text={{ urlencode('Hola! Vi el producto: '.$product->name.' en FusaShop.') }}"
               target="_blank" class="pd-wa-btn">
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.025.507 3.927 1.397 5.591L0 24l6.545-1.714A11.943 11.943 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 22c-1.844 0-3.579-.477-5.095-1.316L2 22l1.333-4.834A9.955 9.955 0 012 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z"/></svg>
            </a>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>

{{-- ───────────── REVIEWS ───────────── --}}
<div class="max-w-7xl mx-auto px-4 md:px-6 py-12">
  <div class="border-b border-surface-container pb-4 mb-8 flex items-center justify-between flex-wrap gap-3">
    <h2 class="text-2xl font-bold text-on-background">Opiniones de Clientes</h2>
    @if($product->reviews->count() > 0)
      <div class="flex items-center gap-2 bg-amber-50 px-4 py-2 rounded-xl border border-amber-100">
        <span class="material-symbols-outlined text-amber-500 text-[20px]" style="font-variation-settings:'FILL' 1">star</span>
        <span class="font-black text-amber-800">{{ number_format($product->reviews->avg('rating'),1) }}</span>
        <span class="text-amber-700 text-xs">/ 5</span>
      </div>
    @endif
  </div>

  @if($hasPurchased)
    <div class="mb-8 p-6 bg-surface-container-low rounded-2xl border border-surface-container">
      <h3 class="font-bold mb-4 flex items-center gap-2">
        <span class="material-symbols-outlined text-primary text-[20px]">rate_review</span>
        Comparte tu experiencia
      </h3>
      <form action="{{ route('consumer.product.review', $product->id) }}" method="POST" class="space-y-4">
        @csrf
        <div class="flex items-center gap-2" x-data="{rating:0}">
          @for($i=1;$i<=5;$i++)
            <label class="cursor-pointer">
              <input type="radio" name="rating" value="{{ $i }}" required class="sr-only" x-model="rating">
              <span class="material-symbols-outlined text-2xl transition-colors"
                :style="`color:${rating>={{ $i }}?'#feb700':'#e5e2e1'};font-variation-settings:'FILL' ${rating>={{ $i }}?1:0}`"
                @mouseover="rating={{ $i }}"
                @mouseleave="rating=parseInt($el.closest('[x-data]').__x.$data.rating)||0">star</span>
            </label>
          @endfor
        </div>
        <textarea name="comment" rows="3" class="w-full p-4 bg-white border border-surface-container rounded-xl text-sm outline-none focus:ring-2 focus:ring-primary resize-none" placeholder="Cuéntanos tu experiencia..."></textarea>
        <button type="submit" class="px-6 py-2.5 bg-primary text-white font-bold rounded-xl text-sm hover:opacity-90 transition-all">Enviar reseña</button>
      </form>
    </div>
  @endif

  @forelse($product->reviews as $rev)
    <div class="flex gap-4 py-5 border-b border-surface-container-low last:border-0">
      <div class="w-10 h-10 bg-surface-container rounded-full flex items-center justify-center font-bold text-on-surface-variant shrink-0">
        {{ strtoupper(substr($rev->user->name ?? 'A',0,1)) }}
      </div>
      <div class="flex-1 min-w-0">
        <div class="flex items-center justify-between mb-1 flex-wrap gap-2">
          <p class="font-bold text-sm text-on-surface">{{ e($rev->user->name) }}</p>
          <div class="flex gap-0.5">
            @for($i=1;$i<=5;$i++)
              <span class="material-symbols-outlined text-[14px]"
                style="font-variation-settings:'FILL' {{ $i<=$rev->rating?1:0 }};color:{{ $i<=$rev->rating?'#feb700':'#e5e2e1' }}">star</span>
            @endfor
          </div>
        </div>
        <p class="text-sm text-on-surface-variant leading-relaxed">{{ e($rev->comment) }}</p>
        <p class="text-[10px] text-on-surface-variant/50 mt-2">{{ $rev->created_at->diffForHumans() }}</p>
      </div>
    </div>
  @empty
    <p class="text-sm text-on-surface-variant italic py-8 text-center">Aún no hay reseñas. ¡Sé el primero en opinar!</p>
  @endforelse

  @if(!empty($related) && $related->count() > 0)
    <div class="mt-16">
      <h2 class="text-2xl font-bold mb-8 text-on-background">Productos Relacionados</h2>
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-5">
        @foreach($related as $rp)
          <x-product-card :product="$rp" :showStock="false"/>
        @endforeach
      </div>
    </div>
  @endif
</div>

{{-- ───────────── CSS ───────────── --}}
<style>
/* Uso de GPU: transform + opacity únicamente en animaciones */
.pd-hero {
  position: relative;
  overflow: hidden;
  padding-bottom: 3rem;
  contain: layout style;
}
.pd-bg {
  position: absolute;
  inset: 0;
  will-change: auto; /* estático, sin cambio */
}
.pd-canvas {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  pointer-events: none;
  opacity: .6;
}

/* Stage */
.pd-stage-wrap { display: flex; justify-content: center; }
.pd-stage {
  position: relative;
  width: min(420px, 92vw);
  aspect-ratio: 1/1;
  display: flex;
  align-items: center;
  justify-content: center;
  will-change: auto;
}

/* Círculo — solo transform en la animación (GPU) */
.pd-circle {
  position: absolute;
  width: 62%;
  height: 62%;
  border-radius: 50%;
  will-change: transform;
  animation: pd-float 5.5s ease-in-out infinite;
  box-shadow: 0 20px 60px rgba(0,0,0,.35);
}
@keyframes pd-float {
  0%,100% { transform: scale(1) translateY(0); }
  50%      { transform: scale(1.04) translateY(-9px); }
}

/* Imagen — solo transform */
.pd-product-img {
  position: relative;
  z-index: 2;
  width: 80%;
  height: 80%;
  display: flex;
  align-items: center;
  justify-content: center;
  will-change: transform;
  animation: pd-img-float 5.5s ease-in-out infinite;
  filter: drop-shadow(0 24px 48px rgba(0,0,0,.45));
  transform-style: preserve-3d;
}
@keyframes pd-img-float {
  0%,100% { transform: translateY(0) rotate(-1deg); }
  50%      { transform: translateY(-12px) rotate(1deg); }
}
.pd-img { width:100%; height:100%; object-fit:contain; border-radius:.75rem; }
.pd-fallback { display:flex; align-items:center; justify-content:center; width:100%; height:100%; }

/* Sombra blob — solo transform + opacity */
.pd-shadow {
  position: absolute;
  bottom: 5%;
  left: 50%;
  width: 46%;
  height: 14px;
  border-radius: 50%;
  filter: blur(14px);
  will-change: transform, opacity;
  animation: pd-shadow 5.5s ease-in-out infinite;
  transform: translateX(-50%);
  opacity: .4;
}
@keyframes pd-shadow {
  0%,100% { transform:translateX(-50%) scaleX(1);   opacity:.4; }
  50%      { transform:translateX(-50%) scaleX(.72); opacity:.2; }
}

/* Info layout */
.pd-info { display:flex; flex-direction:column; gap:1.25rem; }

/* Badges */
.pd-badge {
  display: inline-flex;
  padding: .2rem .8rem;
  border-radius: 9999px;
  border: 1px solid;
  color: white;
  font-size: .68rem;
  font-weight: 800;
  letter-spacing: .05em;
  text-transform: uppercase;
}

/* Merchant pill */
.pd-merchant {
  display: flex;
  align-items: center;
  gap: .75rem;
  padding: 1rem;
  border-radius: 1rem;
  border: 1px solid rgba(255,255,255,.1);
  background: rgba(255,255,255,.07);
  text-decoration: none;
  transition: background .2s, border-color .2s;
}
.pd-merchant:hover { background:rgba(255,255,255,.13); border-color:rgba(255,255,255,.22); }

/* Qty */
.pd-qty {
  display: flex;
  align-items: center;
  background: rgba(255,255,255,.08);
  border: 1px solid rgba(255,255,255,.12);
  border-radius: .875rem;
  overflow: hidden;
}
.pd-qty-btn {
  padding: .75rem 1rem;
  color: rgba(255,255,255,.6);
  background: transparent;
  border: none;
  cursor: pointer;
  transition: background .15s, color .15s;
}
.pd-qty-btn:hover { background:rgba(255,255,255,.1); color:white; }

/* --- Botón Animado (User Requested) --- */
.add-to-cart {
  --color: #fff;
  --icon: #fff;
  --cart: #fff;
  --dots: #fff;
  /* background y shadow vienen de inline styles */
  
  flex: 1;
  cursor: pointer;
  position: relative;
  border: none;
  background: var(--background);
  padding: .9rem 1.5rem;
  border-radius: .875rem;
  color: var(--color);
  font-weight: 800;
  font-size: 1rem;
  transition: transform 0.2s, background 0.3s, box-shadow 0.3s;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 8px 28px var(--shadow);
}

.add-to-cart:hover {
  transform: translateY(-2px) scale(1.02);
  opacity: 0.92;
}

.add-to-cart:active {
  transform: scale(0.95);
}

.add-to-cart .default, .add-to-cart .success {
  display: block;
  transition: transform 0.4s ease, opacity 0.4s ease;
}

.add-to-cart .success {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%) translateY(20px);
  opacity: 0;
}

.add-to-cart .cart {
  position: absolute;
  left: -30px;
  top: 50%;
  transform: translateY(-50%);
  width: 24px;
  height: 24px;
  fill: none;
  stroke: var(--cart);
  stroke-width: 2;
  stroke-linecap: round;
  stroke-linejoin: round;
  z-index: 2;
}

/* Estado 'added' */
.add-to-cart.added .default {
  opacity: 0;
  transform: translateY(-20px);
}

.add-to-cart.added .success {
  opacity: 1;
  transform: translate(-50%, -50%) translateY(0);
  transition-delay: 1.2s;
}

.add-to-cart.added .cart {
  animation: drive 1.6s ease-in-out forwards;
}

@keyframes drive {
  0% { left: -30px; }
  30% { left: 20%; }
  70% { left: 80%; }
  100% { left: 120%; }
}

/* Contact */
.pd-contact-btn {
  flex: 1;
  padding: .75rem;
  background: rgba(255,255,255,.08);
  border: 1px solid rgba(255,255,255,.12);
  color: white;
  font-weight: 600;
  border-radius: .875rem;
  text-decoration: none;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: .5rem;
  font-size: .875rem;
  transition: background .2s;
}
.pd-contact-btn:hover { background:rgba(255,255,255,.14); }
.pd-wa-btn {
  width: 3rem; height: 3rem;
  background: #25D366;
  color: white;
  border-radius: .875rem;
  display: flex;
  align-items: center;
  justify-content: center;
  text-decoration: none;
  box-shadow: 0 4px 16px rgba(37,211,102,.35);
  transition: opacity .2s, transform .2s;
}
.pd-wa-btn:hover { opacity:.9; transform:scale(1.06); }

/* Fade-in escalonado — solo transform + opacity */
.pd-fi {
  opacity: 0;
  transform: translateY(14px);
  animation: pd-fi-kf .5s cubic-bezier(.22,1,.36,1) var(--d,0s) forwards;
}
@keyframes pd-fi-kf { to { opacity:1; transform:translateY(0); } }

/* Pausa animaciones cuando la pestaña está oculta */
@media (prefers-reduced-motion: reduce) {
  .pd-circle, .pd-product-img, .pd-shadow { animation: none !important; }
}
</style>

{{-- ───────────── JS ultra-ligero ───────────── --}}
<script>
(function() {
  'use strict';

  // --- Glow con Canvas (NO DOM manipulation each frame) ---
  const canvas = document.getElementById('pd-canvas');
  const hero   = canvas ? canvas.parentElement : null;
  if (canvas && hero) {
    const ctx = canvas.getContext('2d');
    let mx = -999, my = -999;
    let rafGlow;

    function resizeCanvas() {
      canvas.width  = hero.offsetWidth;
      canvas.height = hero.offsetHeight;
    }
    resizeCanvas();
    new ResizeObserver(resizeCanvas).observe(hero);

    // Throttle: actualiza posición sólo en animationFrame
    let pendingX = -999, pendingY = -999;
    hero.addEventListener('mousemove', e => {
      const r = hero.getBoundingClientRect();
      pendingX = e.clientX - r.left;
      pendingY = e.clientY - r.top;
    }, { passive: true });

    hero.addEventListener('mouseleave', () => { pendingX = -999; pendingY = -999; });

    // Lerp suave
    let curX = -999, curY = -999;
    function drawGlow() {
      // Lerp = suavidad sin eventos extra
      curX += (pendingX - curX) * 0.1;
      curY += (pendingY - curY) * 0.1;

      ctx.clearRect(0, 0, canvas.width, canvas.height);
      if (curX > 0 && curX < canvas.width) {
        const grad = ctx.createRadialGradient(curX, curY, 0, curX, curY, 260);
        grad.addColorStop(0,   '{{ $glow2 }}');
        grad.addColorStop(0.5, '{{ $glow1 }}');
        grad.addColorStop(1,   'transparent');
        ctx.fillStyle = grad;
        ctx.fillRect(0, 0, canvas.width, canvas.height);
      }
      rafGlow = requestAnimationFrame(drawGlow);
    }
    drawGlow();

    // Pausa cuando pestaña oculta
    document.addEventListener('visibilitychange', () => {
      if (document.hidden) { cancelAnimationFrame(rafGlow); }
      else { drawGlow(); }
    });
  }

  // --- Rotación 3D: throttled por rAF ---
  const stage = document.getElementById('pd-stage');
  const imgEl = document.getElementById('pd-product-img');
  if (stage && imgEl) {
    let pending3dX = 0, pending3dY = 0, animating3d = false;

    function apply3d() {
      imgEl.style.transform = `rotateY(${pending3dX * 13}deg) rotateX(${pending3dY * -9}deg) translateY(-10px)`;
      animating3d = false;
    }

    stage.addEventListener('mousemove', e => {
      const r = stage.getBoundingClientRect();
      pending3dX = (e.clientX - r.left  - r.width  / 2) / (r.width  / 2);
      pending3dY = (e.clientY - r.top   - r.height / 2) / (r.height / 2);
      if (!animating3d) {
        animating3d = true;
        requestAnimationFrame(apply3d);
      }
    }, { passive: true });

    stage.addEventListener('mouseleave', () => {
      imgEl.style.transform = '';
      imgEl.style.animation = '';
      pending3dX = 0; pending3dY = 0; animating3d = false;
    });
  }

  // --- AJAX Add to Cart con Animación ---
  const cartForm = document.getElementById('add-to-cart-form');
  if (cartForm) {
    cartForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const btn = cartForm.querySelector('.add-to-cart');
      if (btn.classList.contains('added')) return;

      const formData = new FormData(cartForm);
      
      // Inicia animación
      btn.classList.add('added');

      try {
        const response = await fetch(cartForm.action, {
          method: 'POST',
          body: formData,
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
          }
        });

        const data = await response.json();
        
        if (data.success) {
          // Actualizar contador del carrito si existe en el layout
          const cartBadge = document.querySelector('.cart-badge');
          if (cartBadge && data.cart_count !== undefined) {
            cartBadge.textContent = data.cart_count > 9 ? '9+' : data.cart_count;
            cartBadge.classList.remove('hidden');
            cartBadge.classList.add('pulse');
            setTimeout(() => cartBadge.classList.remove('pulse'), 1000);
          }
        }

        // Volver al estado original después de un tiempo
        setTimeout(() => {
          btn.classList.remove('added');
        }, 3500);

      } catch (error) {
        console.error('Error adding to cart:', error);
        btn.classList.remove('added');
        alert('Hubo un error al añadir al carrito.');
      }
    });
  }
})();
</script>
@endsection
