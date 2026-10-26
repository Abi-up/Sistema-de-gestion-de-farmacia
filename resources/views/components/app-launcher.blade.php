{{-- Componente: Lanzador de Aplicaciones (9 Puntos) --}}
<div id="app-launcher-modal" class="hidden absolute top-16 right-4 sm:right-6 w-80 sm:w-96 bg-white dark:bg-[#0d1e2b] border border-[#e8eff5] dark:border-[#1a3549] rounded-2xl shadow-clinical-lg z-50 p-4 transition-all">
  <div class="flex items-center justify-between pb-3 mb-3 border-b border-[#e8eff5] dark:border-[#1a3549]">
    <div class="flex items-center gap-2">
      <span class="w-2.5 h-2.5 rounded-full bg-[#1f7a8c]"></span>
      <span class="font-semibold text-sm text-[#022b3a] dark:text-[#ffffff] tracking-wide">Módulos del Sistema</span>
    </div>
    <span class="text-xs text-[#526e7d] dark:text-[#a1b8c7]">Vitalis PharmaSys</span>
  </div>

  {{-- Cuadrícula 3x3 de Módulos con Iconos Vectoriales Clínicos (Sin Emojis) --}}
  <div class="grid grid-cols-3 gap-2.5">
    
    {{-- 1. Dashboard --}}
    <a href="{{ url('/dashboard') }}" 
       class="flex flex-col items-center justify-center p-3 rounded-xl border border-transparent transition-all group {{ request()->is('dashboard') ? 'bg-[#bfdbf7]/30 dark:bg-[#1f7a8c]/30 border-[#1f7a8c]' : 'hover:bg-[#bfdbf7]/20 dark:hover:bg-[#132838]' }}">
      <div class="w-10 h-10 rounded-xl bg-[#022b3a] dark:bg-[#1f7a8c] text-white flex items-center justify-center mb-1.5 shadow-sm group-hover:scale-105 transition-transform">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
        </svg>
      </div>
      <span class="text-xs font-semibold text-[#022b3a] dark:text-[#ffffff] text-center">Dashboard</span>
    </a>

    {{-- 2. Medicamentos --}}
    <a href="{{ url('/dashboard#medicamentos') }}" 
       class="flex flex-col items-center justify-center p-3 rounded-xl border border-transparent hover:bg-[#bfdbf7]/20 dark:hover:bg-[#132838] transition-all group">
      <div class="w-10 h-10 rounded-xl bg-[#1f7a8c] text-white flex items-center justify-center mb-1.5 shadow-sm group-hover:scale-105 transition-transform">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
        </svg>
      </div>
      <span class="text-xs font-semibold text-[#022b3a] dark:text-[#ffffff] text-center">Medicamentos</span>
    </a>

    {{-- 3. Inventario / Lotes --}}
    <a href="{{ url('/dashboard#inventario') }}" 
       class="flex flex-col items-center justify-center p-3 rounded-xl border border-transparent hover:bg-[#bfdbf7]/20 dark:hover:bg-[#132838] transition-all group">
      <div class="w-10 h-10 rounded-xl bg-[#022b3a] dark:bg-[#1f7a8c] text-white flex items-center justify-center mb-1.5 shadow-sm group-hover:scale-105 transition-transform">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
        </svg>
      </div>
      <span class="text-xs font-semibold text-[#022b3a] dark:text-[#ffffff] text-center">Inventario</span>
    </a>

    {{-- 4. Ventas / POS --}}
    <a href="{{ url('/dashboard#ventas') }}" 
       class="flex flex-col items-center justify-center p-3 rounded-xl border border-transparent hover:bg-[#bfdbf7]/20 dark:hover:bg-[#132838] transition-all group">
      <div class="w-10 h-10 rounded-xl bg-[#1f7a8c] text-white flex items-center justify-center mb-1.5 shadow-sm group-hover:scale-105 transition-transform">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
        </svg>
      </div>
      <span class="text-xs font-semibold text-[#022b3a] dark:text-[#ffffff] text-center">Caja & Ventas</span>
    </a>

    {{-- 5. Recetas Médicas --}}
    <a href="{{ url('/dashboard#recetas') }}" 
       class="flex flex-col items-center justify-center p-3 rounded-xl border border-transparent hover:bg-[#bfdbf7]/20 dark:hover:bg-[#132838] transition-all group">
      <div class="w-10 h-10 rounded-xl bg-[#022b3a] dark:bg-[#1f7a8c] text-white flex items-center justify-center mb-1.5 shadow-sm group-hover:scale-105 transition-transform">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
      </div>
      <span class="text-xs font-semibold text-[#022b3a] dark:text-[#ffffff] text-center">Recetas</span>
    </a>

    {{-- 6. Pacientes / Clientes --}}
    <a href="{{ url('/dashboard#clientes') }}" 
       class="flex flex-col items-center justify-center p-3 rounded-xl border border-transparent hover:bg-[#bfdbf7]/20 dark:hover:bg-[#132838] transition-all group">
      <div class="w-10 h-10 rounded-xl bg-[#1f7a8c] text-white flex items-center justify-center mb-1.5 shadow-sm group-hover:scale-105 transition-transform">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
        </svg>
      </div>
      <span class="text-xs font-semibold text-[#022b3a] dark:text-[#ffffff] text-center">Pacientes</span>
    </a>

    {{-- 7. Compras & Proveedores --}}
    <a href="{{ url('/dashboard#compras') }}" 
       class="flex flex-col items-center justify-center p-3 rounded-xl border border-transparent hover:bg-[#bfdbf7]/20 dark:hover:bg-[#132838] transition-all group">
      <div class="w-10 h-10 rounded-xl bg-[#022b3a] dark:bg-[#1f7a8c] text-white flex items-center justify-center mb-1.5 shadow-sm group-hover:scale-105 transition-transform">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/>
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
        </svg>
      </div>
      <span class="text-xs font-semibold text-[#022b3a] dark:text-[#ffffff] text-center">Proveedores</span>
    </a>

    {{-- 8. Reportes & Auditoría --}}
    <a href="{{ url('/dashboard#reportes') }}" 
       class="flex flex-col items-center justify-center p-3 rounded-xl border border-transparent hover:bg-[#bfdbf7]/20 dark:hover:bg-[#132838] transition-all group">
      <div class="w-10 h-10 rounded-xl bg-[#1f7a8c] text-white flex items-center justify-center mb-1.5 shadow-sm group-hover:scale-105 transition-transform">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
        </svg>
      </div>
      <span class="text-xs font-semibold text-[#022b3a] dark:text-[#ffffff] text-center">Auditoría</span>
    </a>

    {{-- 9. Usuarios & Seguridad --}}
    <a href="{{ url('/dashboard#seguridad') }}" 
       class="flex flex-col items-center justify-center p-3 rounded-xl border border-transparent hover:bg-[#bfdbf7]/20 dark:hover:bg-[#132838] transition-all group">
      <div class="w-10 h-10 rounded-xl bg-[#022b3a] dark:bg-[#1f7a8c] text-white flex items-center justify-center mb-1.5 shadow-sm group-hover:scale-105 transition-transform">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
        </svg>
      </div>
      <span class="text-xs font-semibold text-[#022b3a] dark:text-[#ffffff] text-center">Seguridad</span>
    </a>

  </div>

  <div class="mt-3 pt-2.5 border-t border-[#e8eff5] dark:border-[#1a3549] flex items-center justify-between text-xs text-[#526e7d] dark:text-[#a1b8c7]">
    <span>Módulos autorizados</span>
    <a href="{{ url('/') }}" class="hover:text-[#1f7a8c] transition-colors font-semibold">Ir al Portal Público &rarr;</a>
  </div>
</div>
