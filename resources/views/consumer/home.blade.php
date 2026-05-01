@extends('layouts.app')
@section('title','Inicio')
@section('content')

{{-- Global Admin Banners --}}
@if(isset($globalBanners) && $globalBanners->isNotEmpty())
  {{-- ── Banner Carousel ── --}}
  <div class="w-full relative bg-surface" id="global-banner-wrap">
    <div class="grid w-full">
      @foreach($globalBanners as $gi => $gb)
        @php $url = asset('storage/'.$gb->image_path); @endphp
        <div class="col-start-1 row-start-1 transition-opacity duration-1000 ease-in-out {{ $gi===0 ? 'opacity-100 z-10' : 'opacity-0 z-0' }}" id="gb-{{$gi}}">
          @if($gb->link_url)
            <a href="{{ $gb->link_url }}" target="_blank" class="block w-full h-full">
              <img src="{{ $url }}" class="w-full h-auto block" alt="{{ $gb->title ?? 'Banner' }}" 
                   loading="{{ $gi===0 ? 'eager' : 'lazy' }}" 
                   fetchpriority="{{ $gi===0 ? 'high' : 'auto' }}"
                   decoding="async">
            </a>
          @else
            <img src="{{ $url }}" class="w-full h-auto block" alt="{{ $gb->title ?? 'Banner' }}" 
                 loading="{{ $gi===0 ? 'eager' : 'lazy' }}" 
                 fetchpriority="{{ $gi===0 ? 'high' : 'auto' }}"
                 decoding="async">
          @endif

          @if(($gb->title && $gb->title !== 'Bienvenido') || (isset($gb->subtitle) && $gb->subtitle))
            <div class="absolute bottom-0 left-0 right-0 p-6 md:p-10 pointer-events-none bg-gradient-to-t from-black/40 to-transparent">
              <div class="max-w-7xl mx-auto">
                @if($gb->title && $gb->title !== 'Bienvenido')
                  <p class="text-white font-black text-2xl md:text-5xl drop-shadow-lg leading-tight mb-2" style="font-family:'Manrope',sans-serif">{{ $gb->title }}</p>
                @endif
                @if(isset($gb->subtitle) && $gb->subtitle)
                  <p class="text-white/85 text-sm md:text-xl font-medium drop-shadow">{{ $gb->subtitle }}</p>
                @endif
              </div>
            </div>
          @endif
        </div>
      @endforeach
    </div>

    {{-- Dot indicators --}}
    @if($globalBanners->count() > 1)
      <div class="absolute bottom-4 left-1/2 -translate-x-1/2 z-20 flex gap-2" id="gb-dots">
        @foreach($globalBanners as $gi => $gb)
          <button onclick="gbGoTo({{ $gi }})" id="gb-dot-{{ $gi }}" class="w-2 h-2 rounded-full transition-all duration-300 {{ $gi===0 ? 'bg-white scale-125' : 'bg-white/40' }}"></button>
        @endforeach
      </div>
    @endif
  </div>

  @if($globalBanners->count() > 1)
  <script>
  (function(){
    let ci = 0, total = {{ $globalBanners->count() }}, timer;
    function gbGoTo(idx) {
      document.getElementById('gb-'+ci)?.classList.replace('opacity-100','opacity-0');
      document.getElementById('gb-'+ci)?.classList.replace('z-10','z-0');
      document.getElementById('gb-dot-'+ci)?.classList.remove('bg-white','scale-125');
      document.getElementById('gb-dot-'+ci)?.classList.add('bg-white/40');
      ci = (idx + total) % total;
      document.getElementById('gb-'+ci)?.classList.replace('opacity-0','opacity-100');
      document.getElementById('gb-'+ci)?.classList.replace('z-0','z-10');
      document.getElementById('gb-dot-'+ci)?.classList.remove('bg-white/40');
      document.getElementById('gb-dot-'+ci)?.classList.add('bg-white','scale-125');
    }
    window.gbGoTo = gbGoTo;
    timer = setInterval(() => gbGoTo(ci + 1), 5000);
  })();
  </script>
  @endif
@else
  {{-- Fallback hero banner when no banners configured --}}
  <div class="w-full relative overflow-hidden" style="height:clamp(220px,35vw,420px);background:linear-gradient(135deg,#004d33 0%,#006c47 50%,#00b67a 100%)">
    <div class="absolute inset-0 opacity-10" style="background-image:url(\"data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E\")"></div>
    <div class="absolute inset-0 flex flex-col items-center justify-center text-center px-6">
      <span class="inline-block px-4 py-1.5 rounded-full bg-white/20 text-white text-xs font-bold tracking-widest uppercase mb-4">Bienvenido a FusaShop</span>
      <h2 class="text-white font-black text-3xl md:text-5xl leading-tight mb-3" style="font-family:'Manrope',sans-serif">Cultivando tu<br><span class="text-[#6efcb9]">Crecimiento Digital.</span></h2>
      <p class="text-white/80 text-sm md:text-lg max-w-lg">Descubre productos únicos de comerciantes locales de Fusagasugá.</p>
      <a href="{{ route('consumer.catalog') }}" class="mt-6 inline-flex items-center gap-2 px-7 py-3 bg-white text-[#006c47] font-bold rounded-xl shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all text-sm md:text-base">
        <span class="material-symbols-outlined text-sm">storefront</span> Ver Catálogo
      </a>
    </div>
  </div>
@endif

{{-- Hero --}}
<section class="relative px-6 py-14 md:py-24 overflow-hidden bg-[#fcf9f8]">
  <div class="absolute top-0 right-0 w-[500px] h-[500px] rounded-full blur-3xl pointer-events-none" style="background:radial-gradient(circle,rgba(0,182,122,.12),transparent 70%)"></div>
  <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
    <div>
      <span class="inline-block px-4 py-1.5 rounded-full bg-[#ffdea8] text-[#271900] text-xs font-bold tracking-widest uppercase mb-5">Empowering MiPymes · Fusagasugá</span>
      <h1 class="text-5xl md:text-6xl font-['Manrope'] font-extrabold leading-tight text-[#1b1c1c] mb-5">
        Cultivando tu<br/><span style="color:#006c47" class="italic">Crecimiento Digital.</span>
      </h1>
      <p class="text-lg text-[#3c4a41] max-w-lg mb-8 leading-relaxed">Descubre productos únicos de comerciantes locales. Apoya las MiPymes de tu región.</p>
      <a href="{{ route('consumer.catalog') }}" class="inline-flex items-center gap-2 px-8 py-4 text-white font-semibold rounded-xl hover:opacity-90 active:scale-95 transition-all shadow-lg text-lg" style="background:linear-gradient(135deg,#006c47,#00b67a)">
        <span class="material-symbols-outlined text-sm">storefront</span> Ver Catálogo
      </a>
    </div>
    <div class="hidden lg:grid grid-cols-2 gap-4">
      @foreach($featured->take(4) as $p)
      <a href="{{ route('consumer.product', $p->id) }}" class="bg-white rounded-2xl overflow-hidden shadow-md hover:shadow-xl transition-all group">
        <div class="aspect-square overflow-hidden">
          <x-product-image :product="$p" class="w-full h-full group-hover:scale-105 transition-transform duration-300"/>
        </div>
        <div class="p-3">
          <p class="font-semibold text-[#1b1c1c] text-xs truncate">{{ e($p->name) }}</p>
          <p class="font-bold text-sm" style="color:#006c47">${{ number_format($p->price,0,',','.') }}</p>
        </div>
      </a>
      @endforeach
    </div>
  </div>
</section>

{{-- Categorías --}}
<section class="px-6 py-6 bg-[#f6f3f2]">
  <div class="max-w-7xl mx-auto">
    <div class="flex gap-3 flex-wrap">
      <a href="{{ route('consumer.catalog') }}" class="px-5 py-2 rounded-full text-white font-semibold text-sm" style="background:#006c47">Todos</a>
      @foreach($categories as $cat)
        <a href="{{ route('consumer.catalog',['category'=>$cat]) }}" class="px-5 py-2 rounded-full bg-white text-[#1b1c1c] font-medium text-sm hover:text-white transition-all shadow-sm" style="transition:all .2s" onmouseover="this.style.background='#006c47'" onmouseout="this.style.background='white'">{{ $cat }}</a>
      @endforeach
    </div>
  </div>
</section>

{{-- Productos --}}
<section class="px-6 py-10">
  <div class="max-w-7xl mx-auto">
    <div class="flex items-center justify-between mb-7">
      <h2 class="text-2xl font-['Manrope'] font-bold text-[#1b1c1c]">Productos Destacados</h2>
      <a href="{{ route('consumer.catalog') }}" class="font-semibold hover:underline flex items-center gap-1 text-sm" style="color:#006c47">
        Ver todos <span class="material-symbols-outlined text-sm">arrow_forward</span>
      </a>
    </div>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-5">
      @foreach($featured as $p)
      <x-product-card :product="$p" />
      @endforeach
    </div>
  </div>
</section>
@endsection
