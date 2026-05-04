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

$mainImageUrl = $product->image ? url('storage/' . $product->image) : null;
$hasImage     = !!$mainImageUrl;
$avgRating    = $product->reviews->avg('rating') ?? 0;
$icon = ['Ropa'=>'checkroom','Electrónica'=>'devices','Hogar'=>'chair','Deportes'=>'sports_soccer',
         'Alimentos'=>'lunch_dining','Belleza'=>'spa','Juguetes'=>'toys','Libros'=>'menu_book'][$product->category] ?? 'inventory_2';

$specIcons = [
    'material'   => 'texture', 'gender' => 'person', 'brand' => 'branding_watermark',
    'model'      => 'tag', 'screen' => 'screenshot_monitor', 'ram' => 'memory',
    'storage'    => 'storage', 'processor' => 'cpu', 'os' => 'settings_input_component',
    'weight'     => 'weight', 'dims' => 'straighten', 'exp' => 'event',
    'origin'     => 'public', 'age' => 'child_care', 'batteries' => 'battery_charging_full'
];

$specLabels = [
    'material'  => 'Material', 'gender' => 'Género', 'fit' => 'Corte',
    'brand'     => 'Marca', 'model' => 'Modelo', 'screen' => 'Pantalla',
    'ram'       => 'Memoria RAM', 'storage' => 'Almacenamiento', 'os' => 'S. Operativo',
    'exp'       => 'Vencimiento', 'origin' => 'Origen', 'dims' => 'Dimensiones',
    'age'       => 'Edad', 'batteries' => 'Baterías'
];
@endphp

@section('content')

{{-- HERO --}}
<div class="pd-hero">
  <div class="pd-bg" style="background: radial-gradient(ellipse 55% 55% at 78% 48%, {{ $glow1 }} 0%, transparent 70%), radial-gradient(ellipse 35% 40% at 22% 75%, {{ $glow2 }} 0%, transparent 65%), linear-gradient(145deg,#0c0e0d 0%,#111a15 100%);"></div>
  <canvas id="pd-canvas" class="pd-canvas" aria-hidden="true"></canvas>

  <div class="max-w-7xl mx-auto px-4 md:px-6 py-10 relative z-10">
    <nav class="flex items-center gap-2 text-xs text-white/40 mb-8 font-medium">
      <a href="{{ route('consumer.catalog') }}" class="hover:text-white/80 transition-colors">Catálogo</a>
      <span class="material-symbols-outlined text-[13px]">chevron_right</span>
      <span>{{ $product->category }}</span>
      <span class="material-symbols-outlined text-[13px]">chevron_right</span>
      <span class="text-white/70 truncate max-w-[200px]">{{ $product->name }}</span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-start">
      {{-- Imagen --}}
      <div class="pd-stage-wrap sticky top-24">
        <div class="pd-stage" id="pd-stage">
          <div class="pd-product-img" id="pd-product-img">
            @if($hasImage)
              <img id="main-img" src="{{ $mainImageUrl }}" alt="{{ e($product->name) }}" class="pd-img" loading="eager" fetchpriority="high" decoding="async" onerror="this.style.display='none';document.getElementById('pd-fallback').style.removeProperty('display')"/>
              <div id="pd-fallback" class="pd-fallback" style="display:none">
                <span class="material-symbols-outlined" style="font-size:6rem;color:white;font-variation-settings:'FILL' 1">{{ $icon }}</span>
              </div>
            @else
              <div class="pd-fallback"><span class="material-symbols-outlined" style="font-size:6rem;color:white;font-variation-settings:'FILL' 1">{{ $icon }}</span></div>
            @endif
          </div>
          <div class="pd-shadow" style="background:{{ $from }}"></div>
        </div>

        @php
           $galleryImages = $product->images ?? [];
           if($product->available_options) {
             foreach($product->available_options as $opt) {
               if(($opt['type'] ?? '') === 'color') {
                 foreach($opt['values'] as $v) { if(is_array($v) && !empty($v['image'])) $galleryImages[] = $v['image']; }
               }
             }
           }
           $galleryImages = array_unique($galleryImages);
        @endphp

        @if(count($galleryImages) > 0)
          <div class="flex gap-2 mt-4 justify-center pd-fi" style="--d:.4s">
            @if($mainImageUrl)
              <button onclick="changeMainImg('{{ $mainImageUrl }}')" class="w-14 h-14 rounded-lg border-2 border-white/20 overflow-hidden hover:border-white transition-all bg-white/5"><img src="{{ $mainImageUrl }}" class="w-full h-full object-cover"></button>
            @endif
            @foreach($galleryImages as $img)
              @php $galleryUrl = url('storage/' . $img); @endphp
              <button onclick="changeMainImg('{{ $galleryUrl }}')" class="w-14 h-14 rounded-lg border-2 border-white/10 overflow-hidden hover:border-white transition-all bg-white/5"><img src="{{ $galleryUrl }}" class="w-full h-full object-cover"></button>
            @endforeach
          </div>
        @endif
      </div>

      {{-- Info --}}
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

        <h1 class="text-3xl md:text-5xl font-black text-white leading-tight pd-fi" style="--d:.1s">{{ e($product->name) }}</h1>

        <div class="flex items-center gap-2 pd-fi" style="--d:.14s">
          @for($i=1;$i<=5;$i++)
            <span class="material-symbols-outlined text-[18px]" style="font-variation-settings:'FILL' {{ $i<=round($avgRating)?1:0 }};color:{{ $i<=round($avgRating)?'#feb700':'rgba(255,255,255,.2)' }}">star</span>
          @endfor
          <span class="text-sm font-bold text-white ml-1">{{ number_format($avgRating,1) }}</span>
          <span class="text-sm text-white/40">({{ $product->reviews->count() }} reseñas)</span>
        </div>

        <div class="pd-fi" style="--d:.18s">
          <div class="flex items-baseline gap-2">
            <span class="text-5xl font-black text-white">${{ number_format($product->price,0,',','.') }}</span>
            <span class="text-sm text-white/40">COP</span>
          </div>
        </div>

        <a href="{{ route('consumer.merchant.profile', $product->merchant_id) }}" class="pd-merchant pd-fi" style="--d:.21s">
          @if($product->merchant->companyProfile?->logo_path)
            <img src="{{ Storage::url($product->merchant->companyProfile->logo_path) }}" class="w-10 h-10 rounded-full object-cover">
          @else
            <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-black" style="background:{{ $from }}">{{ strtoupper(substr($product->merchant->name,0,1)) }}</div>
          @endif
          <div class="flex-1 min-w-0">
            <p class="text-[9px] font-black text-white/30 uppercase tracking-widest">Vendido por</p>
            <p class="font-bold text-sm text-white truncate">{{ e($product->merchant->companyProfile?->company_name ?? $product->merchant->name) }}</p>
          </div>
          <span class="material-symbols-outlined text-white/40">chevron_right</span>
        </a>

        @if($product->description)
          <p class="text-white/60 leading-relaxed text-sm pd-fi" style="--d:.24s">{{ e($product->description) }}</p>
        @endif

        {{-- Opciones --}}
        @if($product->available_options && count($product->available_options) > 0)
          <div class="space-y-6 pd-fi" style="--d:.26s">
            @foreach($product->available_options as $opt)
              @php $type = $opt['type'] ?? 'text'; @endphp
              <div>
                <p class="text-[10px] font-black text-white/30 uppercase mb-3">{{ $opt['name'] }}</p>
                <div class="flex flex-wrap gap-3">
                  @foreach($opt['values'] as $val)
                    @php 
                      $isColor = $type === 'color';
                      $vName = $isColor ? (is_array($val) ? ($val['name'] ?? 'Color') : $val) : $val;
                      $vHex = $isColor ? (is_array($val) ? ($val['hex'] ?? '#808080') : '#808080') : null;
                      $vImg = $isColor && is_array($val) && !empty($val['image']) ? url('storage/'.$val['image']) : '';
                      
                      if($isColor && !is_array($val)) {
                          $vHex = match(strtolower($val)) { 'blanco'=>'#FFFFFF','negro'=>'#000000','rojo'=>'#FF0000','azul'=>'#0000FF','verde'=>'#008000','amarillo'=>'#FFFF00','gris'=>'#808080',default=>'#808080' };
                      }
                      $vId = 'opt-'.Str::slug($opt['name']).'-'.Str::slug($vName);
                    @endphp
                    <div class="relative">
                      <input type="radio" id="{{ $vId }}" name="options-ui[{{ $opt['name'] }}]" value="{{ $vName }}" class="hidden peer" {{ $loop->first ? 'checked' : '' }} data-image="{{ $vImg }}" onchange="updateVariant(this, '{{ $opt['name'] }}')">
                      
                      @if($isColor)
                        <label for="{{ $vId }}" class="block w-10 h-10 rounded-full border-4 border-white/10 p-0.5 transition-all peer-checked:border-white peer-checked:scale-110 shadow-lg cursor-pointer" title="{{ $vName }}">
                          <div class="w-full h-full rounded-full" style="background:{{ $vHex }}"></div>
                        </label>
                      @else
                        <label for="{{ $vId }}" class="variant-btn block px-5 py-2.5 bg-white/5 border border-white/10 rounded-xl text-xs font-bold text-white/80 cursor-pointer peer-checked:bg-white peer-checked:text-black peer-checked:border-white peer-checked:scale-105 transition-all">
                          {{ $vName }}
                        </label>
                      @endif
                    </div>
                  @endforeach
                </div>
              </div>
            @endforeach
          </div>
        @endif

        {{-- Especificaciones Técnicas --}}
        @if($product->specifications && count(array_filter($product->specifications)) > 0)
          <div class="space-y-4 pd-fi" style="--d:.28s">
            <h3 class="text-[10px] font-black text-white/30 uppercase tracking-widest flex items-center gap-2">
              <span class="material-symbols-outlined text-[14px]">analytics</span> Especificaciones Técnicas
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              @foreach($product->specifications as $key => $val)
                @if(!empty($val))
                  <div class="flex items-center gap-3 p-4 rounded-2xl bg-white/5 border border-white/10 group hover:bg-white/10 transition-all">
                    <span class="material-symbols-outlined text-white/40 group-hover:text-primary transition-colors">{{ $specIcons[$key] ?? 'info' }}</span>
                    <div>
                      <p class="text-[9px] font-black text-white/30 uppercase leading-none mb-1">{{ $specLabels[$key] ?? ucwords($key) }}</p>
                      <p class="text-xs font-bold text-white">{{ $val }}</p>
                    </div>
                  </div>
                @endif
              @endforeach
            </div>
          </div>
        @endif

        @if($product->stock > 0)
          @auth
            @if(auth()->user()->isConsumer())
              <form id="add-to-cart-form" method="POST" action="{{ route('consumer.cart.add') }}" class="pd-fi" style="--d:.3s">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <div id="selected-options-inputs">
                  @if($product->available_options)
                    @foreach($product->available_options as $opt)
                      @php $firstVal = $opt['values'][0]; $valName = is_array($firstVal) ? $firstVal['name'] : $firstVal; @endphp
                      <input type="hidden" name="options[{{ $opt['name'] }}]" id="opt-input-{{ Str::slug($opt['name']) }}" value="{{ $valName }}">
                    @endforeach
                  @endif
                </div>
                <div class="flex items-center gap-3">
                  <div class="pd-qty">
                    <button type="button" onclick="const i=document.getElementById('qty');if(i.value>1)i.value--" class="pd-qty-btn"><span class="material-symbols-outlined text-[18px]">remove</span></button>
                    <input type="number" id="qty" name="quantity" value="1" min="1" max="{{ $product->stock }}" class="w-12 text-center bg-transparent border-0 font-black text-white focus:ring-0 text-sm p-0 m-0 [appearance:textfield]">
                    <button type="button" onclick="const i=document.getElementById('qty');if(i.value<{{ $product->stock }})i.value++" class="pd-qty-btn"><span class="material-symbols-outlined text-[18px]">add</span></button>
                  </div>
                  <button type="submit" class="add-to-cart" style="--background: linear-gradient(135deg,{{ $from }},{{ $to }}); --shadow: {{ $glow2 }}">
                    <span class="default">Añadir al carrito</span><span class="success">¡Listo!</span>
                    <div class="cart"><svg viewBox="0 0 36 26"><polyline points="1 2.5 6 2.5 10 18.5 25.5 18.5 28.5 7.5 7.5 7.5"></polyline><circle cx="15" cy="23" r="2"></circle><circle cx="21" cy="23" r="2"></circle></svg></div>
                  </button>
                </div>
              </form>
            @endif
          @else
            <div class="pd-fi"><a href="{{ route('login') }}?intended={{ urlencode(request()->fullUrl()) }}" class="add-to-cart no-underline" style="--background: linear-gradient(135deg,{{ $from }},{{ $to }}); --shadow: {{ $glow2 }}"><span class="default">Añadir al carrito</span></a></div>
          @endauth
        @endif
      </div>
    </div>
  </div>
</div>

{{-- Opiniones --}}
<div class="max-w-7xl mx-auto px-4 md:px-6 py-12">
  <div class="border-b border-white/10 pb-4 mb-8 flex items-center justify-between"><h2 class="text-2xl font-bold text-white">Opiniones</h2></div>
  @forelse($product->reviews as $rev)
    <div class="flex gap-4 py-5 border-b border-white/5 last:border-0">
      <div class="w-10 h-10 bg-white/10 rounded-full flex items-center justify-center font-bold text-white shrink-0">{{ strtoupper(substr($rev->user->name ?? 'A',0,1)) }}</div>
      <div class="flex-1">
        <div class="flex items-center justify-between mb-1"><p class="font-bold text-sm text-white">{{ e($rev->user->name) }}</p><div class="flex gap-0.5">@for($i=1;$i<=5;$i++)<span class="material-symbols-outlined text-[14px]" style="font-variation-settings:'FILL' {{ $i<=$rev->rating?1:0 }};color:{{ $i<=$rev->rating?'#feb700':'rgba(255,255,255,.1)' }}">star</span>@endfor</div></div>
        <p class="text-sm text-white/60">{{ e($rev->comment) }}</p>
      </div>
    </div>
  @empty
    <p class="text-sm text-white/40 italic py-8 text-center">Aún no hay reseñas.</p>
  @endforelse
</div>

<style>
.pd-hero { position: relative; overflow: hidden; padding-bottom: 3rem; contain: layout style; }
.pd-bg { position: absolute; inset: 0; }
.pd-canvas { position: absolute; inset: 0; width: 100%; height: 100%; pointer-events: none; opacity: .6; }
.pd-stage-wrap { display: flex; flex-direction: column; align-items: center; }
.pd-stage { position: relative; width: min(420px, 92vw); aspect-ratio: 1/1; display: flex; align-items: center; justify-content: center; }
.pd-product-img { position: relative; z-index: 2; width: 80%; height: 80%; display: flex; align-items: center; justify-content: center; will-change: transform; animation: pd-img-float 5.5s ease-in-out infinite; filter: drop-shadow(0 24px 48px rgba(0,0,0,.45)); }
@keyframes pd-img-float { 0%,100% { transform: translateY(0) rotate(-1deg); } 50% { transform: translateY(-12px) rotate(1deg); } }
.pd-img { width:100%; height:100%; object-fit:contain; border-radius:.75rem; transition: opacity .3s; }
.pd-fallback { display:flex; align-items:center; justify-content:center; width:100%; height:100%; }
.pd-shadow { position: absolute; bottom: 5%; left: 50%; width: 46%; height: 14px; border-radius: 50%; filter: blur(14px); will-change: transform, opacity; animation: pd-shadow 5.5s ease-in-out infinite; transform: translateX(-50%); opacity: .4; }
@keyframes pd-shadow { 0%,100% { transform:translateX(-50%) scaleX(1); opacity:.4; } 50% { transform:translateX(-50%) scaleX(.72); opacity:.2; } }
.pd-info { display:flex; flex-direction:column; gap:1.5rem; }
.pd-badge { display: inline-flex; padding: .2rem .8rem; border-radius: 9999px; border: 1px solid; color: white; font-size: .68rem; font-weight: 800; letter-spacing: .05em; text-transform: uppercase; }
.pd-merchant { display: flex; align-items: center; gap: .75rem; padding: 1rem; border-radius: 1rem; border: 1px solid rgba(255,255,255,.1); background: rgba(255,255,255,.07); text-decoration: none; transition: background .2s; }
.pd-qty { display: flex; align-items: center; background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.12); border-radius: .875rem; overflow: hidden; }
.pd-qty-btn { padding: .75rem 1rem; color: rgba(255,255,255,.6); background: transparent; border: none; cursor: pointer; }
.add-to-cart { cursor: pointer; position: relative; border: none; background: var(--background); padding: .9rem 1.5rem; border-radius: .875rem; color: #fff; font-weight: 800; font-size: 1rem; transition: transform 0.2s; overflow: hidden; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 28px var(--shadow); flex: 1; }
.add-to-cart.added .default { opacity: 0; transform: translateY(-20px); }
.add-to-cart.added .success { opacity: 1; transform: translate(-50%, -50%) translateY(0); transition-delay: 1.2s; }
.add-to-cart.added .cart { animation: drive 1.6s ease-in-out forwards; }
@keyframes drive { 0% { left: -30px; } 30% { left: 20%; } 70% { left: 80%; } 100% { left: 120%; } }
.pd-fi { opacity: 0; transform: translateY(14px); animation: pd-fi-kf .5s cubic-bezier(.22,1,.36,1) var(--d,0s) forwards; }
@keyframes pd-fi-kf { to { opacity:1; transform:translateY(0); } }

/* CLASES DE APOYO PARA SELECCIÓN */
.variant-btn.selected {
  background-color: white !important;
  color: black !important;
  border-color: white !important;
  transform: scale(1.05);
}
</style>

<script>
(function() {
  const canvas = document.getElementById('pd-canvas');
  if (canvas) {
    const ctx = canvas.getContext('2d'), hero = canvas.parentElement;
    let rx = -999, ry = -999, px = -999, py = -999;
    function res() { canvas.width = hero.offsetWidth; canvas.height = hero.offsetHeight; }
    res(); new ResizeObserver(res).observe(hero);
    hero.addEventListener('mousemove', e => { const r = hero.getBoundingClientRect(); px = e.clientX-r.left; py = e.clientY-r.top; }, {passive:true});
    function d() { rx+=(px-rx)*0.1; ry+=(py-ry)*0.1; ctx.clearRect(0,0,canvas.width,canvas.height); if(rx>0){ const g = ctx.createRadialGradient(rx,ry,0,rx,ry,260); g.addColorStop(0,'{{ $glow2 }}'); g.addColorStop(0.5,'{{ $glow1 }}'); g.addColorStop(1,'transparent'); ctx.fillStyle=g; ctx.fillRect(0,0,canvas.width,canvas.height); } requestAnimationFrame(d); }
    d();
  }
  const stage = document.getElementById('pd-stage'), imgEl = document.getElementById('pd-product-img');
  if (stage && imgEl) {
    stage.addEventListener('mousemove', e => { const r = stage.getBoundingClientRect(); const x = (e.clientX-r.left-r.width/2)/(r.width/2), y = (e.clientY-r.top-r.height/2)/(r.height/2); imgEl.style.transform = `rotateY(${x*13}deg) rotateX(${y*-9}deg) translateY(-10px)`; }, {passive:true});
    stage.addEventListener('mouseleave', () => imgEl.style.transform='');
  }
  window.changeMainImg = u => { const m = document.getElementById('main-img'); if(m && u && u !== ''){ m.style.opacity='0'; setTimeout(()=> { m.src=u; m.style.opacity='1'; }, 200); } };
  
  window.updateVariant = (r, n) => { 
    // Sincronizar con el formulario oculto para el carrito
    const s = n.toLowerCase().replace(/[^a-z0-9]/g,'-');
    const h = document.getElementById('opt-input-'+s); 
    if(h) h.value=r.value; 
    
    // Cambiar imagen si existe
    const img = r.getAttribute('data-image');
    if(img && img !== '' && img !== 'null') {
      changeMainImg(img);
    }

    // ACTUALIZAR ESTADO VISUAL MANUALMENTE (Para mayor seguridad)
    const container = r.closest('.flex-wrap');
    if(container) {
       container.querySelectorAll('.variant-btn').forEach(btn => btn.classList.remove('selected'));
       const label = container.querySelector(`label[for="${r.id}"]`);
       if(label) label.classList.add('selected');
    }
  };

  // Inicializar el estado "selected" para los primeros elementos
  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('input[type="radio"]:checked').forEach(r => {
      const container = r.closest('.flex-wrap');
      if(container) {
        const label = container.querySelector(`label[for="${r.id}"]`);
        if(label) label.classList.add('selected');
      }
    });
  });

  const cf = document.getElementById('add-to-cart-form');
  if (cf) { cf.addEventListener('submit', async e => { e.preventDefault(); const b = cf.querySelector('.add-to-cart'); if(b.classList.contains('added')) return; b.classList.add('added'); try { const r = await fetch(cf.action,{method:'POST',body:new FormData(cf),headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]')?.content||''}}); const d = await r.json(); if(d.success){ const ba = document.querySelector('.cart-badge'); if(ba) ba.textContent=d.cart_count; } setTimeout(()=>b.classList.remove('added'),3000); } catch(e){ b.classList.remove('added'); } }); }
})();
</script>
@endsection
