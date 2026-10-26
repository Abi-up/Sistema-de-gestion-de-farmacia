@extends('layouts.app')

@section('title', 'Dashboard — Vitalis PharmaSys')

@section('content')

{{-- Tarjeta Central Principal del Sistema (Boceto 1: Contenedor Central del Panel Blade) --}}
<div class="bg-white dark:bg-[#0d1e2b] border border-[#e8eff5] dark:border-[#1a3549] rounded-2xl p-6 sm:p-8 shadow-clinical-sm">
  
  {{-- Cabecera del Módulo --}}
  <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 mb-6 border-b border-[#e8eff5] dark:border-[#1a3549] gap-4">
    <div>
      <div class="flex items-center gap-2">
        <span class="w-3 h-3 rounded-full bg-[#1f7a8c]"></span>
        <h1 class="font-brand font-bold text-2xl sm:text-3xl text-[#022b3a] dark:text-white">
          Panel de Control Farmacéutico
        </h1>
      </div>
      <p class="text-xs sm:text-sm text-[#526e7d] dark:text-[#a1b8c7] mt-1">
        Dispensación de medicamentos, control de lotes y trazabilidad sanitaria en tiempo real.
      </p>
    </div>

    {{-- Indicador Operativo y Botón de Acción Rápida --}}
    <div class="flex items-center gap-3">
      <div class="text-right hidden sm:block">
        <span class="text-xs font-bold text-[#022b3a] dark:text-white block">Turno Activo • Caja 01</span>
        <span class="text-[11px] text-[#526e7d] dark:text-[#a1b8c7]">Farmacéutico Reg. en Turno</span>
      </div>
      <a href="{{ url('/dashboard#nueva-venta') }}" 
         class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold bg-[#1f7a8c] hover:bg-[#175f6d] text-white shadow-clinical-sm transition-all">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        <span>Nueva Dispensación</span>
      </a>
    </div>
  </div>

  {{-- 4 Tarjetas de Métricas Operativas (Números Tabulares) --}}
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    
    {{-- Métrica 1: Ventas del Día --}}
    <div class="p-4 rounded-xl bg-[#f4f7f9] dark:bg-[#132838] border border-[#e8eff5] dark:border-[#1a3549]">
      <div class="flex items-center justify-between mb-2">
        <span class="text-xs font-semibold text-[#526e7d] dark:text-[#a1b8c7]">Ventas del Día</span>
        <div class="w-8 h-8 rounded-lg bg-[#e6f7f2] dark:bg-[#1f7a8c]/20 text-[#0f6b4f] dark:text-[#bfdbf7] flex items-center justify-center">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
      </div>
      <span class="text-2xl font-bold text-[#022b3a] dark:text-white tabular-nums block">$ 1.420.500</span>
      <span class="text-[11px] text-[#0f6b4f] font-semibold mt-1 block">128 comprobantes emitidos</span>
    </div>

    {{-- Métrica 2: Recetas Validadas --}}
    <div class="p-4 rounded-xl bg-[#f4f7f9] dark:bg-[#132838] border border-[#e8eff5] dark:border-[#1a3549]">
      <div class="flex items-center justify-between mb-2">
        <span class="text-xs font-semibold text-[#526e7d] dark:text-[#a1b8c7]">Recetas Médicas</span>
        <div class="w-8 h-8 rounded-lg bg-[#bfdbf7]/30 dark:bg-[#1f7a8c]/20 text-[#1f7a8c] dark:text-[#bfdbf7] flex items-center justify-center">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
        </div>
      </div>
      <span class="text-2xl font-bold text-[#022b3a] dark:text-white tabular-nums block">84</span>
      <span class="text-[11px] text-[#1f7a8c] dark:text-[#bfdbf7] font-semibold mt-1 block">100% validadas con ISP</span>
    </div>

    {{-- Métrica 3: Alertas de Stock Bajo --}}
    <div class="p-4 rounded-xl bg-[#f4f7f9] dark:bg-[#132838] border border-[#e8eff5] dark:border-[#1a3549]">
      <div class="flex items-center justify-between mb-2">
        <span class="text-xs font-semibold text-[#526e7d] dark:text-[#a1b8c7]">Stock Crítico</span>
        <div class="w-8 h-8 rounded-lg bg-[#fef6e7] dark:bg-[#a05e03]/20 text-[#a05e03] flex items-center justify-center">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
          </svg>
        </div>
      </div>
      <span class="text-2xl font-bold text-[#022b3a] dark:text-white tabular-nums block">12</span>
      <span class="text-[11px] text-[#a05e03] font-semibold mt-1 block">Requieren orden de compra</span>
    </div>

    {{-- Métrica 4: Lotes Próximos a Vencer --}}
    <div class="p-4 rounded-xl bg-[#f4f7f9] dark:bg-[#132838] border border-[#e8eff5] dark:border-[#1a3549]">
      <div class="flex items-center justify-between mb-2">
        <span class="text-xs font-semibold text-[#526e7d] dark:text-[#a1b8c7]">Vencimientos (30 días)</span>
        <div class="w-8 h-8 rounded-lg bg-[#ffdad6] dark:bg-[#ba1a1a]/20 text-[#ba1a1a] flex items-center justify-center">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
          </svg>
        </div>
      </div>
      <span class="text-2xl font-bold text-[#022b3a] dark:text-white tabular-nums block">5</span>
      <span class="text-[11px] text-[#ba1a1a] font-semibold mt-1 block">Prioridad de rotación PEPS</span>
    </div>

  </div>

  {{-- Tabla de Trazabilidad de Lotes y Medicamentos --}}
  <div class="space-y-4">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div>
        <h2 class="font-bold text-base text-[#022b3a] dark:text-white">
          Trazabilidad de Medicamentos & Lotes Activos
        </h2>
        <p class="text-xs text-[#526e7d] dark:text-[#a1b8c7]">
          Monitoreo continuo de presentaciones, fechas de expiración y stock en sala de ventas.
        </p>
      </div>

      <div class="flex items-center gap-2">
        <button type="button" class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-[#f4f7f9] dark:bg-[#132838] border border-[#e8eff5] dark:border-[#1a3549] text-[#022b3a] dark:text-white hover:bg-[#e1e5f2]/40 transition-colors">
          Filtrar por Laboratorio
        </button>
        <button type="button" class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-[#022b3a] dark:bg-[#1f7a8c] text-white hover:bg-[#175f6d] transition-colors">
          Exportar Bitácora
        </button>
      </div>
    </div>

    {{-- Tabla Responsive --}}
    <div class="overflow-x-auto rounded-xl border border-[#e8eff5] dark:border-[#1a3549]">
      <table class="w-full text-left text-xs">
        <thead class="bg-[#f4f7f9] dark:bg-[#132838] text-[#526e7d] dark:text-[#a1b8c7] uppercase font-bold tracking-wider border-b border-[#e8eff5] dark:border-[#1a3549]">
          <tr>
            <th class="py-3 px-4">Medicamento / Presentación</th>
            <th class="py-3 px-4">Lote</th>
            <th class="py-3 px-4">Caducidad</th>
            <th class="py-3 px-4">Stock Sala</th>
            <th class="py-3 px-4">Precio Venta</th>
            <th class="py-3 px-4">Estado</th>
            <th class="py-3 px-4 text-right">Acción</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-[#e8eff5] dark:divide-[#1a3549]">
          
          {{-- Fila 1 --}}
          <tr class="hover:bg-[#f4f7f9]/50 dark:hover:bg-[#132838]/50 transition-colors">
            <td class="py-3 px-4">
              <span class="font-bold text-[#022b3a] dark:text-white block">Amoxicilina + Ác. Clavulánico</span>
              <span class="text-[11px] text-[#526e7d] dark:text-[#a1b8c7]">500mg / 125mg • Lab. Chile</span>
            </td>
            <td class="py-3 px-4 font-mono font-medium text-[#022b3a] dark:text-[#bfdbf7]">AMX-2026-09</td>
            <td class="py-3 px-4 tabular-nums text-[#022b3a] dark:text-white">15/09/2027</td>
            <td class="py-3 px-4 tabular-nums font-semibold text-[#022b3a] dark:text-white">45 unidades</td>
            <td class="py-3 px-4 tabular-nums font-bold text-[#022b3a] dark:text-white">$ 12.890</td>
            <td class="py-3 px-4">
              <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#e6f7f2] text-[#0f6b4f] border border-[#b8ebd9]">
                En Stock
              </span>
            </td>
            <td class="py-3 px-4 text-right">
              <a href="#" class="font-semibold text-[#1f7a8c] hover:underline">Dispensar</a>
            </td>
          </tr>

          {{-- Fila 2 --}}
          <tr class="hover:bg-[#f4f7f9]/50 dark:hover:bg-[#132838]/50 transition-colors">
            <td class="py-3 px-4">
              <span class="font-bold text-[#022b3a] dark:text-white block">Insulina Humana NPH</span>
              <span class="text-[11px] text-[#526e7d] dark:text-[#a1b8c7]">100 UI/ml • Eli Lilly (Refrigerado)</span>
            </td>
            <td class="py-3 px-4 font-mono font-medium text-[#022b3a] dark:text-[#bfdbf7]">INS-004</td>
            <td class="py-3 px-4 tabular-nums text-[#ba1a1a] font-semibold">28/11/2026</td>
            <td class="py-3 px-4 tabular-nums font-semibold text-[#a05e03]">4 viales</td>
            <td class="py-3 px-4 tabular-nums font-bold text-[#022b3a] dark:text-white">$ 18.500</td>
            <td class="py-3 px-4">
              <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#fef6e7] text-[#a05e03] border border-[#fcdca7]">
                Bajo Stock
              </span>
            </td>
            <td class="py-3 px-4 text-right">
              <a href="#" class="font-semibold text-[#1f7a8c] hover:underline">Pedir Lote</a>
            </td>
          </tr>

          {{-- Fila 3 --}}
          <tr class="hover:bg-[#f4f7f9]/50 dark:hover:bg-[#132838]/50 transition-colors">
            <td class="py-3 px-4">
              <span class="font-bold text-[#022b3a] dark:text-white block">Clonazepam</span>
              <span class="text-[11px] text-[#526e7d] dark:text-[#a1b8c7]">2mg • 30 Comprimidos (Psicotrópico)</span>
            </td>
            <td class="py-3 px-4 font-mono font-medium text-[#022b3a] dark:text-[#bfdbf7]">CLZ-9021</td>
            <td class="py-3 px-4 tabular-nums text-[#022b3a] dark:text-white">10/05/2028</td>
            <td class="py-3 px-4 tabular-nums font-semibold text-[#022b3a] dark:text-white">20 cajas</td>
            <td class="py-3 px-4 tabular-nums font-bold text-[#022b3a] dark:text-white">$ 6.450</td>
            <td class="py-3 px-4">
              <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#bfdbf7]/40 text-[#022b3a] dark:text-[#bfdbf7] border border-[#bfdbf7]">
                Receta Retenida
              </span>
            </td>
            <td class="py-3 px-4 text-right">
              <a href="#" class="font-semibold text-[#1f7a8c] hover:underline">Validar</a>
            </td>
          </tr>

          {{-- Fila 4 --}}
          <tr class="hover:bg-[#f4f7f9]/50 dark:hover:bg-[#132838]/50 transition-colors">
            <td class="py-3 px-4">
              <span class="font-bold text-[#022b3a] dark:text-white block">Paracetamol Bioequivalente</span>
              <span class="text-[11px] text-[#526e7d] dark:text-[#a1b8c7]">500mg • 20 Comprimidos</span>
            </td>
            <td class="py-3 px-4 font-mono font-medium text-[#022b3a] dark:text-[#bfdbf7]">PCT-8841</td>
            <td class="py-3 px-4 tabular-nums text-[#022b3a] dark:text-white">30/08/2028</td>
            <td class="py-3 px-4 tabular-nums font-semibold text-[#022b3a] dark:text-white">180 cajas</td>
            <td class="py-3 px-4 tabular-nums font-bold text-[#022b3a] dark:text-white">$ 1.490</td>
            <td class="py-3 px-4">
              <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#e6f7f2] text-[#0f6b4f] border border-[#b8ebd9]">
                En Stock
              </span>
            </td>
            <td class="py-3 px-4 text-right">
              <a href="#" class="font-semibold text-[#1f7a8c] hover:underline">Dispensar</a>
            </td>
          </tr>

        </tbody>
      </table>
    </div>

  </div>

</div>

@endsection
