@extends('layouts.app')
@section('title', $product ? 'Editar Producto' : 'Nuevo Producto')
@section('content')
<div class="max-w-3xl mx-auto px-6 py-8">
  <div class="flex items-center gap-4 mb-8">
    <a href="{{ route('merchant.products') }}" class="w-10 h-10 bg-surface-container-low rounded-xl flex items-center justify-center hover:bg-surface-container transition-colors">
      <span class="material-symbols-outlined text-on-surface-variant">arrow_back</span>
    </a>
    <h1 class="text-3xl font-['Manrope'] font-bold text-on-background">
      {{ $product ? 'Editar Producto' : 'Nuevo Producto' }}
    </h1>
  </div>

  @if($errors->any())
    <div class="bg-[#ffdad6] text-[#ba1a1a] px-4 py-3 rounded-xl mb-6 text-sm">
      @foreach($errors->all() as $e)<p>• {{ $e }}</p>@endforeach
    </div>
  @endif

  <div class="bg-surface-container-lowest rounded-2xl p-8 shadow-[0_12px_32px_rgba(27,28,28,.06)]">
    <form method="POST"
      action="{{ $product ? route('merchant.products.update', $product->id) : route('merchant.products.store') }}"
      enctype="multipart/form-data" class="space-y-6">
      @csrf
      @if($product) @method('PUT') @endif

      <div>
        <label class="block text-sm font-semibold text-on-surface mb-3">Imagen del producto</label>
        <div class="relative border-2 border-dashed border-outline-variant/50 rounded-2xl p-8 text-center hover:border-primary transition-colors cursor-pointer" id="drop-zone">
          <input type="file" name="image" id="image-input" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"/>
          <div id="image-preview-container">
            {{-- ERROR CORREGIDO: Se añadió $product antes de ->image --}}
            @if($product && $product->image)
              <img src="{{ asset('storage/'.$product->image) }}" class="w-32 h-32 object-cover rounded-xl mx-auto mb-3"/>
              <p class="text-sm text-on-surface-variant">Clic para cambiar imagen</p>
            @else
              <span class="material-symbols-outlined text-on-surface-variant text-5xl mb-3 block">add_photo_alternate</span>
              <p class="font-semibold text-on-surface">Subir imagen</p>
              <p class="text-sm text-on-surface-variant mt-1">JPG, PNG, WebP · Máx 2MB</p>
            @endif
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        <div class="sm:col-span-2">
          <label class="block text-sm font-semibold text-on-surface mb-2">Nombre del producto *</label>
          <input type="text" name="name" value="{{ old('name', $product?->name) }}" required
            class="w-full px-4 py-3 bg-surface-container-highest rounded-xl border-0 outline-none focus:ring-2 focus:ring-primary"
            placeholder="Ej: Camiseta Andina Premium"/>
        </div>

        <div>
          <label class="block text-sm font-semibold text-on-surface mb-2">Precio (COP) *</label>
          <div class="relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant font-semibold text-sm">$</span>
            <input type="number" name="price" value="{{ old('price', $product?->price) }}" min="0" step="100" required
              class="w-full pl-8 pr-4 py-3 bg-surface-container-highest rounded-xl border-0 outline-none focus:ring-2 focus:ring-primary"
              placeholder="45000"/>
          </div>
        </div>

        <div>
          <label class="block text-sm font-semibold text-on-surface mb-2">Stock disponible *</label>
          <input type="number" name="stock" value="{{ old('stock', $product?->stock ?? 0) }}" min="0" required
            class="w-full px-4 py-3 bg-surface-container-highest rounded-xl border-0 outline-none focus:ring-2 focus:ring-primary"
            placeholder="50"/>
        </div>

        <div class="sm:col-span-2">
          <label class="block text-sm font-semibold text-on-surface mb-2">Categoría *</label>
          <select name="category" required
            class="w-full px-4 py-3 bg-surface-container-highest rounded-xl border-0 outline-none focus:ring-2 focus:ring-primary">
            <option value="">Seleccionar categoría</option>
            @foreach(['Ropa','Electrónica','Hogar','Deportes','Alimentos','Belleza','Juguetes','Libros','Otros'] as $cat)
              <option value="{{ $cat }}" {{ old('category', $product?->category) === $cat ? 'selected' : '' }}>{{ $cat }}</option>
            @endforeach
          </select>
        </div>

        <div class="sm:col-span-2">
          <label class="block text-sm font-semibold text-on-surface mb-2">Descripción</label>
          <textarea name="description" rows="4"
            class="w-full px-4 py-3 bg-surface-container-highest rounded-xl border-0 outline-none focus:ring-2 focus:ring-primary resize-none"
            placeholder="Describe tu producto: materiales, características, tallas...">{{ old('description', $product?->description) }}</textarea>
        </div>

        <div class="sm:col-span-2">
          <label class="flex items-center gap-3 cursor-pointer">
            <div class="relative">
              {{-- MEJORA: Input hidden para asegurar que se envíe '0' si el checkbox no está marcado --}}
              <input type="hidden" name="is_active" value="0">
              <input type="checkbox" name="is_active" value="1" class="sr-only peer"
                {{ old('is_active', $product?->is_active ?? true) ? 'checked' : '' }}/>
              <div class="w-11 h-6 bg-outline-variant rounded-full peer-checked:bg-primary transition-colors"></div>
              <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full transition-transform peer-checked:translate-x-5 shadow-sm"></div>
            </div>
            <span class="font-semibold text-on-surface">Producto activo (visible en catálogo)</span>
          </label>
        </div>
      </div>

      <div class="flex gap-4 pt-2">
        <button type="submit" class="flex-1 py-3.5 bg-primary-gradient text-white font-semibold rounded-xl hover:opacity-90 active:scale-95 transition-all flex items-center justify-center gap-2 shadow-md">
          <span class="material-symbols-outlined text-sm">{{ $product ? 'save' : 'add_circle' }}</span>
          {{ $product ? 'Guardar cambios' : 'Crear producto' }}
        </button>
        <a href="{{ route('merchant.products') }}" class="px-6 py-3.5 bg-surface-container-low text-on-surface font-semibold rounded-xl hover:bg-surface-container transition-colors text-center">
          Cancelar
        </a>
      </div>
    </form>
  </div>
</div>
@push('scripts')
<script>
document.getElementById('image-input').addEventListener('change', function(e) {
  const file = e.target.files[0];
  if (!file) return;
  const reader = new FileReader();
  reader.onload = function(ev) {
    document.getElementById('image-preview-container').innerHTML =
      `<img src="${ev.target.result}" class="w-32 h-32 object-cover rounded-xl mx-auto mb-3"/>
       <p class="text-sm text-on-surface-variant">${file.name}</p>`;
  };
  reader.readAsDataURL(file);
});
</script>
@endpush
@endsection