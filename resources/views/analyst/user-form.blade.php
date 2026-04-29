@extends('layouts.app')
@section('title', $user ? 'Editar Usuario' : 'Nuevo Usuario')
@section('content')
<div class="max-w-2xl mx-auto px-6 py-8">
  <div class="flex items-center gap-4 mb-8">
    <a href="{{ route('analyst.users') }}" class="w-10 h-10 bg-white rounded-xl flex items-center justify-center shadow-sm hover:bg-[#f0eded] transition-colors">
      <span class="material-symbols-outlined text-[#3c4a41]">arrow_back</span>
    </a>
    <h1 class="text-3xl font-['Manrope'] font-bold text-[#1b1c1c]">{{ $user ? 'Editar Usuario' : 'Nuevo Usuario' }}</h1>
  </div>

  @if($errors->any())
    <div class="bg-red-50 text-red-700 px-4 py-3 rounded-xl mb-6 text-sm">
      @foreach($errors->all() as $e)<p>• {{ $e }}</p>@endforeach
    </div>
  @endif

  <div class="bg-white rounded-2xl p-8 shadow-sm">
    <form method="POST" action="{{ $user ? route('analyst.users.update', $user->id) : route('analyst.users.store') }}" class="space-y-5">
      @csrf
      @if($user) @method('PUT') @endif

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        <div class="sm:col-span-2">
          <label class="block text-sm font-semibold text-[#1b1c1c] mb-2">Nombre completo *</label>
          <input type="text" name="name" value="{{ old('name', $user?->name) }}" required
            class="w-full px-4 py-3 bg-[#f0eded] rounded-xl border-0 outline-none focus:ring-2 focus:ring-[#006c47]" placeholder="Nombre completo"/>
        </div>
        <div class="sm:col-span-2">
          <label class="block text-sm font-semibold text-[#1b1c1c] mb-2">Correo electrónico *</label>
          <input type="email" name="email" value="{{ old('email', $user?->email) }}" required
            class="w-full px-4 py-3 bg-[#f0eded] rounded-xl border-0 outline-none focus:ring-2 focus:ring-[#006c47]" placeholder="correo@ejemplo.com"/>
        </div>
        <div>
          <label class="block text-sm font-semibold text-[#1b1c1c] mb-2">Rol *</label>
          <select name="role" required class="w-full px-4 py-3 bg-[#f0eded] rounded-xl border-0 outline-none focus:ring-2 focus:ring-[#006c47]">
            @foreach(['consumer'=>'Consumidor','merchant'=>'Comerciante','analyst'=>'Analista/Admin'] as $val=>$label)
              <option value="{{ $val }}" {{ old('role',$user?->role)===$val?'selected':'' }}>{{ $label }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="block text-sm font-semibold text-[#1b1c1c] mb-2">Teléfono</label>
          <input type="tel" name="phone" value="{{ old('phone', $user?->phone) }}"
            class="w-full px-4 py-3 bg-[#f0eded] rounded-xl border-0 outline-none focus:ring-2 focus:ring-[#006c47]" placeholder="+57 300 000 0000"/>
        </div>
        <div>
          <label class="block text-sm font-semibold text-[#1b1c1c] mb-2">
            Contraseña {{ $user ? '(dejar vacío para no cambiar)' : '*' }}
          </label>
          <input type="password" name="password" {{ !$user ? 'required' : '' }}
            class="w-full px-4 py-3 bg-[#f0eded] rounded-xl border-0 outline-none focus:ring-2 focus:ring-[#006c47]" placeholder="Mínimo 8 caracteres"/>
        </div>
      </div>

      <div class="flex gap-4 pt-2">
        <button type="submit" class="flex-1 py-3.5 bg-gradient-to-r from-[#006c47] to-[#00b67a] text-white font-semibold rounded-xl hover:opacity-90 active:scale-95 transition-all flex items-center justify-center gap-2 shadow-md">
          <span class="material-symbols-outlined text-sm">{{ $user ? 'save' : 'person_add' }}</span>
          {{ $user ? 'Guardar cambios' : 'Crear usuario' }}
        </button>
        <a href="{{ route('analyst.users') }}" class="px-6 py-3.5 bg-[#f0eded] text-[#1b1c1c] font-semibold rounded-xl hover:bg-[#e5e2e1] transition-colors text-center">
          Cancelar
        </a>
      </div>
    </form>
  </div>
</div>
@endsection
