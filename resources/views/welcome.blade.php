@extends('layouts.public')

@section('title', 'Vitalis PharmaSys — Portal Farmacéutico & Dispensación Clínica')

@section('content')

{{-- 1. Hero Section con Visor 3D Inmersivo y Profesional --}}
<section id="inicio" class="relative w-full overflow-hidden py-10 lg:py-16 bg-[#f4f7f9] dark:bg-[#08141d]">
  
  {{-- Blobs de luz ambiental médica controlada (sin neones ni degradados estridentes) --}}
  <div class="absolute -top-20 left-1/4 w-80 h-80 rounded-full bg-[#bfdbf7]/25 dark:bg-[#1f7a8c]/10 blur-3xl pointer-events-none"></div>
  <div class="absolute bottom-0 right-10 w-96 h-96 rounded-full bg-[#bfdbf7]/20 dark:bg-[#022b3a]/30 blur-3xl pointer-events-none"></div>

  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
      
      {{-- Columna Izquierda: Mensaje Central y Llamadas a la Acción --}}
      <div class="lg:col-span-7 flex flex-col space-y-6">
        
        {{-- Badge de Certificación --}}
        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white dark:bg-[#0d1e2b] border border-[#e8eff5] dark:border-[#1a3549] shadow-clinical-sm self-start">
          <svg class="w-4 h-4 text-[#1f7a8c]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
          </svg>
          <span class="text-xs font-bold text-[#1f7a8c] dark:text-[#bfdbf7] uppercase tracking-wider">
            Certificación Sanitaria & Trazabilidad de Fármacos
          </span>
        </div>

        {{-- Titular en Fuente Neuton --}}
        <h1 class="font-brand font-bold text-4xl sm:text-5xl lg:text-6xl text-[#022b3a] dark:text-[#ffffff] leading-[1.1] tracking-tight">
          Tu salud y bienestar en <span class="text-[#1f7a8c] dark:text-[#bfdbf7]">manos expertas</span>
        </h1>

        <p class="text-base sm:text-lg text-[#526e7d] dark:text-[#a1b8c7] max-w-xl leading-relaxed">
          Dispensación garantizada con receta digital, control estricto de cadena de frío y el catálogo de medicamentos bioequivalentes más confiable del país.
        </p>

        {{-- Botones de Acción --}}
        <div class="flex flex-wrap items-center gap-3.5 pt-2">
          <a href="#productos" 
             class="inline-flex items-center gap-2.5 px-6 py-3 rounded-xl font-semibold text-sm bg-[#1f7a8c] hover:bg-[#175f6d] text-white shadow-clinical-md transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
            </svg>
            <span>Explorar Medicamentos</span>
          </a>

          <a href="#servicios" 
             class="inline-flex items-center gap-2.5 px-6 py-3 rounded-xl font-semibold text-sm bg-white dark:bg-[#0d1e2b] text-[#022b3a] dark:text-[#bfdbf7] border border-[#e8eff5] dark:border-[#1a3549] hover:bg-[#bfdbf7]/20 transition-all">
            <svg class="w-5 h-5 text-[#1f7a8c]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <span>Subir Receta Médica</span>
          </a>
        </div>

        {{-- Métricas de Confianza (Números Tabulares) --}}
        <div class="pt-6 grid grid-cols-3 gap-4 border-t border-[#e8eff5] dark:border-[#1a3549] max-w-lg">
          <div>
            <span class="font-bold text-2xl sm:text-3xl text-[#022b3a] dark:text-white tabular-nums">99.8%</span>
            <p class="text-xs text-[#526e7d] dark:text-[#a1b8c7]">Disponibilidad Stock</p>
          </div>
          <div>
            <span class="font-bold text-2xl sm:text-3xl text-[#1f7a8c] dark:text-[#bfdbf7] tabular-nums">&lt; 45m</span>
            <p class="text-xs text-[#526e7d] dark:text-[#a1b8c7]">Despacho Exprés</p>
          </div>
          <div>
            <span class="font-bold text-2xl sm:text-3xl text-[#022b3a] dark:text-white tabular-nums">120k+</span>
            <p class="text-xs text-[#526e7d] dark:text-[#a1b8c7]">Pacientes Atendidos</p>
          </div>
        </div>

      </div>

      {{-- Columna Derecha: Componente 3D Inmersivo y Profesional --}}
      <div class="lg:col-span-5 relative flex flex-col items-center justify-center">
        
        {{-- Marco Contenedor del Render 3D --}}
        <div class="relative w-full aspect-square max-w-md rounded-2xl bg-white dark:bg-[#0d1e2b] border border-[#e8eff5] dark:border-[#1a3549] shadow-clinical-lg p-2 overflow-hidden flex items-center justify-center">
          
          {{-- Canvas WebGL 3D Dinámico --}}
          <div id="pharmacy-3d-canvas" class="w-full h-full cursor-grab active:cursor-grabbing"></div>

          {{-- Badge Flotante 1: Validación Sanitaria --}}
          <div class="absolute top-4 left-4 bg-white/90 dark:bg-[#0d1e2b]/90 backdrop-blur-md px-3.5 py-2 rounded-xl shadow-clinical-sm border border-[#e8eff5] dark:border-[#1a3549] flex items-center gap-2.5 pointer-events-none">
            <div class="w-8 h-8 rounded-lg bg-[#bfdbf7]/40 dark:bg-[#1f7a8c]/30 flex items-center justify-center text-[#1f7a8c] dark:text-[#bfdbf7]">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
            <div>
              <p class="text-[11px] font-bold text-[#022b3a] dark:text-white leading-tight">Dispensación 100%</p>
              <p class="text-[10px] text-[#1f7a8c] dark:text-[#bfdbf7]">Control Farmacéutico Reg.</p>
            </div>
          </div>

          {{-- Badge Flotante 2: Cadena de Frío --}}
          <div class="absolute bottom-4 right-4 bg-white/90 dark:bg-[#0d1e2b]/90 backdrop-blur-md px-3.5 py-2 rounded-xl shadow-clinical-sm border border-[#e8eff5] dark:border-[#1a3549] flex items-center gap-2.5 pointer-events-none">
            <div class="w-8 h-8 rounded-lg bg-[#022b3a] text-white flex items-center justify-center">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
              </svg>
            </div>
            <div>
              <p class="text-[11px] font-bold text-[#022b3a] dark:text-white leading-tight">Cadena de Frío 2°C - 8°C</p>
              <p class="text-[10px] text-[#526e7d] dark:text-[#a1b8c7]">Sensores de precisión IoT</p>
            </div>
          </div>

        </div>

        {{-- Nota de Interactividad --}}
        <p class="text-[11px] text-[#526e7d] dark:text-[#a1b8c7] mt-3 flex items-center gap-1.5">
          <svg class="w-3.5 h-3.5 text-[#1f7a8c]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"/>
          </svg>
          Mueve el cursor sobre la cápsula para rotar el modelo interactivo
        </p>

      </div>

    </div>
  </div>
</section>

{{-- 2. Categorías Rápidas de Medicamentos --}}
<section class="py-14 bg-white dark:bg-[#0b1a24] border-t border-[#e8eff5] dark:border-[#1a3549]">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-8 gap-3">
      <div>
        <span class="text-xs font-bold text-[#1f7a8c] uppercase tracking-wider">Catálogo Farmacéutico</span>
        <h2 class="font-brand font-bold text-2xl sm:text-3xl text-[#022b3a] dark:text-white mt-1">Categorías de Dispensación</h2>
      </div>
      <p class="text-xs sm:text-sm text-[#526e7d] dark:text-[#a1b8c7] max-w-sm">
        Accede a tratamientos autorizados bajo estrictas normas de almacenamiento y posología.
      </p>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3.5">
      
      {{-- Categoría 1 --}}
      <a href="#productos" class="p-4 rounded-xl bg-[#f4f7f9] dark:bg-[#132838] border border-[#e8eff5] dark:border-[#1a3549] hover:border-[#1f7a8c] transition-all text-center group">
        <div class="w-12 h-12 rounded-xl bg-white dark:bg-[#0d1e2b] text-[#1f7a8c] flex items-center justify-center mx-auto mb-2.5 shadow-clinical-sm group-hover:scale-105 transition-transform">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
          </svg>
        </div>
        <span class="text-xs font-bold text-[#022b3a] dark:text-white block">Con Receta</span>
        <span class="text-[10px] text-[#526e7d] dark:text-[#a1b8c7]">1,420 ítems</span>
      </a>

      {{-- Categoría 2 --}}
      <a href="#productos" class="p-4 rounded-xl bg-[#f4f7f9] dark:bg-[#132838] border border-[#e8eff5] dark:border-[#1a3549] hover:border-[#1f7a8c] transition-all text-center group">
        <div class="w-12 h-12 rounded-xl bg-white dark:bg-[#0d1e2b] text-[#022b3a] dark:text-[#bfdbf7] flex items-center justify-center mx-auto mb-2.5 shadow-clinical-sm group-hover:scale-105 transition-transform">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
        </div>
        <span class="text-xs font-bold text-[#022b3a] dark:text-white block">Bioequivalentes</span>
        <span class="text-[10px] text-[#526e7d] dark:text-[#a1b8c7]">890 ítems</span>
      </a>

      {{-- Categoría 3 --}}
      <a href="#productos" class="p-4 rounded-xl bg-[#f4f7f9] dark:bg-[#132838] border border-[#e8eff5] dark:border-[#1a3549] hover:border-[#1f7a8c] transition-all text-center group">
        <div class="w-12 h-12 rounded-xl bg-white dark:bg-[#0d1e2b] text-[#1f7a8c] flex items-center justify-center mx-auto mb-2.5 shadow-clinical-sm group-hover:scale-105 transition-transform">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
          </svg>
        </div>
        <span class="text-xs font-bold text-[#022b3a] dark:text-white block">Trat. Crónicos</span>
        <span class="text-[10px] text-[#526e7d] dark:text-[#a1b8c7]">640 ítems</span>
      </a>

      {{-- Categoría 4 --}}
      <a href="#productos" class="p-4 rounded-xl bg-[#f4f7f9] dark:bg-[#132838] border border-[#e8eff5] dark:border-[#1a3549] hover:border-[#1f7a8c] transition-all text-center group">
        <div class="w-12 h-12 rounded-xl bg-white dark:bg-[#0d1e2b] text-[#022b3a] dark:text-[#bfdbf7] flex items-center justify-center mx-auto mb-2.5 shadow-clinical-sm group-hover:scale-105 transition-transform">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
        <span class="text-xs font-bold text-[#022b3a] dark:text-white block">Cadena de Frío</span>
        <span class="text-[10px] text-[#526e7d] dark:text-[#a1b8c7]">180 ítems</span>
      </a>

      {{-- Categoría 5 --}}
      <a href="#productos" class="p-4 rounded-xl bg-[#f4f7f9] dark:bg-[#132838] border border-[#e8eff5] dark:border-[#1a3549] hover:border-[#1f7a8c] transition-all text-center group">
        <div class="w-12 h-12 rounded-xl bg-white dark:bg-[#0d1e2b] text-[#1f7a8c] flex items-center justify-center mx-auto mb-2.5 shadow-clinical-sm group-hover:scale-105 transition-transform">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
          </svg>
        </div>
        <span class="text-xs font-bold text-[#022b3a] dark:text-white block">Pediátricos</span>
        <span class="text-[10px] text-[#526e7d] dark:text-[#a1b8c7]">310 ítems</span>
      </a>

      {{-- Categoría 6 --}}
      <a href="#productos" class="p-4 rounded-xl bg-[#f4f7f9] dark:bg-[#132838] border border-[#e8eff5] dark:border-[#1a3549] hover:border-[#1f7a8c] transition-all text-center group">
        <div class="w-12 h-12 rounded-xl bg-white dark:bg-[#0d1e2b] text-[#022b3a] dark:text-[#bfdbf7] flex items-center justify-center mx-auto mb-2.5 shadow-clinical-sm group-hover:scale-105 transition-transform">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
          </svg>
        </div>
        <span class="text-xs font-bold text-[#022b3a] dark:text-white block">Urgencias</span>
        <span class="text-[10px] text-[#526e7d] dark:text-[#a1b8c7]">220 ítems</span>
      </a>

    </div>
  </div>
</section>

{{-- 3. Catálogo de Medicamentos Destacados --}}
<section id="productos" class="py-14 bg-[#f4f7f9] dark:bg-[#08141d]">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    
    <div class="flex items-center justify-between mb-8">
      <div>
        <span class="text-xs font-bold text-[#1f7a8c] uppercase tracking-wider">Disponibilidad en Tiempo Real</span>
        <h2 class="font-brand font-bold text-2xl sm:text-3xl text-[#022b3a] dark:text-white mt-1">Medicamentos Más Solicitados</h2>
      </div>
      <a href="{{ url('/login') }}" class="text-xs font-semibold text-[#1f7a8c] hover:underline">
        Consultar inventario completo &rarr;
      </a>
    </div>

    {{-- Cuadrícula de Medicamentos --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      
      {{-- Card 1: Amoxicilina --}}
      <div class="bg-white dark:bg-[#0d1e2b] border border-[#e8eff5] dark:border-[#1a3549] rounded-2xl p-4 shadow-clinical-sm flex flex-col justify-between">
        <div>
          <div class="flex items-center justify-between mb-3">
            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#e6f7f2] text-[#0f6b4f] border border-[#b8ebd9]">
              En Stock
            </span>
            <span class="text-[10px] text-[#526e7d] dark:text-[#a1b8c7]">Lote: AMX-2026-09</span>
          </div>
          <h3 class="font-bold text-sm text-[#022b3a] dark:text-white">Amoxicilina + Ác. Clavulánico</h3>
          <p class="text-xs text-[#526e7d] dark:text-[#a1b8c7] mt-1">500mg / 125mg • 21 Comprimidos Recubiertos</p>
          <div class="mt-3">
            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold bg-[#bfdbf7]/30 text-[#022b3a] dark:text-[#bfdbf7]">
              Receta Médica Retenida
            </span>
          </div>
        </div>
        <div class="mt-4 pt-3 border-t border-[#e8eff5] dark:border-[#1a3549] flex items-center justify-between">
          <span class="font-bold text-base text-[#022b3a] dark:text-white tabular-nums">$ 12.890</span>
          <a href="{{ url('/login') }}" class="px-3 py-1.5 rounded-lg bg-[#1f7a8c] hover:bg-[#175f6d] text-white text-xs font-semibold transition-colors">
            Dispensar
          </a>
        </div>
      </div>

      {{-- Card 2: Paracetamol Bioequivalente --}}
      <div class="bg-white dark:bg-[#0d1e2b] border border-[#e8eff5] dark:border-[#1a3549] rounded-2xl p-4 shadow-clinical-sm flex flex-col justify-between">
        <div>
          <div class="flex items-center justify-between mb-3">
            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#e6f7f2] text-[#0f6b4f] border border-[#b8ebd9]">
              En Stock
            </span>
            <span class="text-[10px] text-[#526e7d] dark:text-[#a1b8c7]">Lote: PCT-8841</span>
          </div>
          <h3 class="font-bold text-sm text-[#022b3a] dark:text-white">Paracetamol Bioequivalente</h3>
          <p class="text-xs text-[#526e7d] dark:text-[#a1b8c7] mt-1">500mg • 20 Comprimidos Ranurados</p>
          <div class="mt-3">
            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold bg-[#e1e5f2]/50 text-[#022b3a] dark:text-white">
              Venta Directa
            </span>
          </div>
        </div>
        <div class="mt-4 pt-3 border-t border-[#e8eff5] dark:border-[#1a3549] flex items-center justify-between">
          <span class="font-bold text-base text-[#022b3a] dark:text-white tabular-nums">$ 1.490</span>
          <a href="{{ url('/login') }}" class="px-3 py-1.5 rounded-lg bg-[#1f7a8c] hover:bg-[#175f6d] text-white text-xs font-semibold transition-colors">
            Dispensar
          </a>
        </div>
      </div>

      {{-- Card 3: Losartán Potásico --}}
      <div class="bg-white dark:bg-[#0d1e2b] border border-[#e8eff5] dark:border-[#1a3549] rounded-2xl p-4 shadow-clinical-sm flex flex-col justify-between">
        <div>
          <div class="flex items-center justify-between mb-3">
            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#e6f7f2] text-[#0f6b4f] border border-[#b8ebd9]">
              En Stock
            </span>
            <span class="text-[10px] text-[#526e7d] dark:text-[#a1b8c7]">Lote: LSR-2026-11</span>
          </div>
          <h3 class="font-bold text-sm text-[#022b3a] dark:text-white">Losartán Potásico</h3>
          <p class="text-xs text-[#526e7d] dark:text-[#a1b8c7] mt-1">50mg • 30 Comprimidos Recubiertos</p>
          <div class="mt-3">
            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold bg-[#bfdbf7]/30 text-[#022b3a] dark:text-[#bfdbf7]">
              Tratamiento Crónico
            </span>
          </div>
        </div>
        <div class="mt-4 pt-3 border-t border-[#e8eff5] dark:border-[#1a3549] flex items-center justify-between">
          <span class="font-bold text-base text-[#022b3a] dark:text-white tabular-nums">$ 3.990</span>
          <a href="{{ url('/login') }}" class="px-3 py-1.5 rounded-lg bg-[#1f7a8c] hover:bg-[#175f6d] text-white text-xs font-semibold transition-colors">
            Dispensar
          </a>
        </div>
      </div>

      {{-- Card 4: Insulina NPH (Cadena de Frío) --}}
      <div class="bg-white dark:bg-[#0d1e2b] border border-[#e8eff5] dark:border-[#1a3549] rounded-2xl p-4 shadow-clinical-sm flex flex-col justify-between">
        <div>
          <div class="flex items-center justify-between mb-3">
            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#fef6e7] text-[#a05e03] border border-[#fcdca7]">
              Bajo Stock (4 viales)
            </span>
            <span class="text-[10px] text-[#526e7d] dark:text-[#a1b8c7]">Lote: INS-004</span>
          </div>
          <h3 class="font-bold text-sm text-[#022b3a] dark:text-white">Insulina Humana NPH</h3>
          <p class="text-xs text-[#526e7d] dark:text-[#a1b8c7] mt-1">100 UI/ml • Vial 10ml Inyectable</p>
          <div class="mt-3">
            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold bg-[#ffdad6] text-[#ba1a1a]">
              Refrigerado 2°C a 8°C
            </span>
          </div>
        </div>
        <div class="mt-4 pt-3 border-t border-[#e8eff5] dark:border-[#1a3549] flex items-center justify-between">
          <span class="font-bold text-base text-[#022b3a] dark:text-white tabular-nums">$ 18.500</span>
          <a href="{{ url('/login') }}" class="px-3 py-1.5 rounded-lg bg-[#1f7a8c] hover:bg-[#175f6d] text-white text-xs font-semibold transition-colors">
            Dispensar
          </a>
        </div>
      </div>

    </div>

  </div>
</section>

{{-- 4. Sección de Servicios Clínicos --}}
<section id="servicios" class="py-14 bg-white dark:bg-[#0b1a24] border-t border-[#e8eff5] dark:border-[#1a3549]">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center max-w-2xl mx-auto mb-10">
      <span class="text-xs font-bold text-[#1f7a8c] uppercase tracking-wider">Atención Sanitaria</span>
      <h2 class="font-brand font-bold text-3xl text-[#022b3a] dark:text-white mt-1">Servicios Profesionales de Farmacia</h2>
      <p class="text-xs sm:text-sm text-[#526e7d] dark:text-[#a1b8c7] mt-2">
        Supervisión farmacéutica continua para garantizar la adherencia y seguridad del tratamiento del paciente.
      </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      
      {{-- Servicio 1 --}}
      <div class="p-6 rounded-2xl bg-[#f4f7f9] dark:bg-[#132838] border border-[#e8eff5] dark:border-[#1a3549] shadow-clinical-sm">
        <div class="w-12 h-12 rounded-xl bg-[#022b3a] text-white flex items-center justify-center mb-4">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
        </div>
        <h3 class="font-bold text-base text-[#022b3a] dark:text-white mb-2">Validación de Receta Electrónica</h3>
        <p class="text-xs text-[#526e7d] dark:text-[#a1b8c7] leading-relaxed">
          Sincronización instantánea con plataformas del Ministerio de Salud para validar firma digital, posología y caducidad de recetas.
        </p>
      </div>

      {{-- Servicio 2 --}}
      <div class="p-6 rounded-2xl bg-[#f4f7f9] dark:bg-[#132838] border border-[#e8eff5] dark:border-[#1a3549] shadow-clinical-sm">
        <div class="w-12 h-12 rounded-xl bg-[#1f7a8c] text-white flex items-center justify-center mb-4">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
        <h3 class="font-bold text-base text-[#022b3a] dark:text-white mb-2">Despacho Termocontrolado</h3>
        <p class="text-xs text-[#526e7d] dark:text-[#a1b8c7] leading-relaxed">
          Vehículos refrigerados certificados con registro de temperatura en tiempo real para medicamentos biológicos e insulinas.
        </p>
      </div>

      {{-- Servicio 3 --}}
      <div class="p-6 rounded-2xl bg-[#f4f7f9] dark:bg-[#132838] border border-[#e8eff5] dark:border-[#1a3549] shadow-clinical-sm">
        <div class="w-12 h-12 rounded-xl bg-[#022b3a] text-white flex items-center justify-center mb-4">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
          </svg>
        </div>
        <h3 class="font-bold text-base text-[#022b3a] dark:text-white mb-2">Farmacovigilancia y Alertas</h3>
        <p class="text-xs text-[#526e7d] dark:text-[#a1b8c7] leading-relaxed">
          Canal de notificación directa sobre reacciones adversas o interacción farmacológica con orientación directa de químicos farmacéuticos.
        </p>
      </div>

    </div>
  </div>
</section>

{{-- 5. Ubicación y Contacto de Urgencia --}}
<section id="contacto" class="py-14 bg-[#f4f7f9] dark:bg-[#08141d] border-t border-[#e8eff5] dark:border-[#1a3549]">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
      
      <div>
        <span class="text-xs font-bold text-[#1f7a8c] uppercase tracking-wider">Red de Farmacias</span>
        <h2 class="font-brand font-bold text-2xl sm:text-3xl text-[#022b3a] dark:text-white mt-1">Sede Central y Turno Nocturno</h2>
        <p class="text-xs sm:text-sm text-[#526e7d] dark:text-[#a1b8c7] mt-2 leading-relaxed">
          Ventanilla de guardia sanitaria disponible toda la noche para urgencias con prescripción médica.
        </p>

        <div class="mt-6 space-y-3">
          <div class="flex items-start gap-3 p-3.5 rounded-xl bg-white dark:bg-[#0d1e2b] border border-[#e8eff5] dark:border-[#1a3549]">
            <svg class="w-5 h-5 text-[#1f7a8c] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <div>
              <span class="font-bold text-xs text-[#022b3a] dark:text-white">Dirección Oficial</span>
              <p class="text-xs text-[#526e7d] dark:text-[#a1b8c7]">Av. Providencia 1450, Santiago, Región Metropolitana</p>
            </div>
          </div>

          <div class="flex items-start gap-3 p-3.5 rounded-xl bg-white dark:bg-[#0d1e2b] border border-[#e8eff5] dark:border-[#1a3549]">
            <svg class="w-5 h-5 text-[#1f7a8c] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
            </svg>
            <div>
              <span class="font-bold text-xs text-[#022b3a] dark:text-white">Línea de Urgencia 24 Horas</span>
              <p class="text-xs text-[#526e7d] dark:text-[#a1b8c7]">+56 2 2800 4500 (Atención con Químico de Turno)</p>
            </div>
          </div>
        </div>
      </div>

      <div class="rounded-2xl overflow-hidden border border-[#e8eff5] dark:border-[#1a3549] bg-white dark:bg-[#0d1e2b] p-6 shadow-clinical-sm">
        <h3 class="font-bold text-base text-[#022b3a] dark:text-white mb-2">Consulta de Stock Inmediata</h3>
        <p class="text-xs text-[#526e7d] dark:text-[#a1b8c7] mb-4">
          Ingresa el nombre o principio activo del medicamento para verificar su disponibilidad inmediata en nuestra sucursal.
        </p>

        <form class="space-y-3" onsubmit="event.preventDefault();">
          <div>
            <label class="block text-xs font-semibold text-[#022b3a] dark:text-white mb-1">Nombre del Medicamento</label>
            <input type="text" placeholder="Ej: Metformina, Salbutamol, Losartán..." class="w-full text-xs rounded-xl px-3.5 py-2.5 bg-[#f4f7f9] dark:bg-[#132838] border border-[#e8eff5] dark:border-[#1a3549] text-[#022b3a] dark:text-white focus:outline-none focus:border-[#1f7a8c]">
          </div>
          <button type="submit" class="w-full py-2.5 rounded-xl font-semibold text-xs bg-[#1f7a8c] hover:bg-[#175f6d] text-white transition-all shadow-clinical-sm">
            Verificar Existencia en Turno
          </button>
        </form>
      </div>

    </div>
  </div>
</section>

@endsection
