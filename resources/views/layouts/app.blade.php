<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'Vitalis PharmaSys — Panel de Gestión Farmacéutica')</title>

  {{-- Tipografías: Neuton & Plus Jakarta Sans --}}
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Neuton:ital,wght@0,400;0,700;1,400&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  {{-- Script de detección temprana de tema --}}
  <script>
    if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
      document.documentElement.classList.add('dark');
    } else {
      document.documentElement.classList.remove('dark');
    }
  </script>

  {{-- Estilos & Scripts (Vite con Fallback Autónomo) --}}
  @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
    @vite(['resources/css/app.css', 'resources/js/app.js'])
  @else
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        darkMode: 'class',
        theme: {
          extend: {
            colors: {
              'jet-black': '#022b3a',
              'teal': '#1f7a8c',
              'pale-sky': '#bfdbf7',
              'lavender': '#e1e5f2',
            },
            fontFamily: {
              brand: ['Neuton', 'Georgia', 'serif'],
              sans: ['Plus Jakarta Sans', 'ui-sans-serif', 'system-ui', 'sans-serif'],
            }
          }
        }
      }
    </script>
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
    <script src="{{ asset('js/app.js') }}" defer></script>
  @endif
</head>
<body class="bg-[#f4f7f9] dark:bg-[#08141d] text-[#022b3a] dark:text-[#ffffff] min-h-screen flex flex-col font-sans antialiased transition-colors duration-200">

  {{-- 1. Encabezado Superior del Sistema (Boceto 1 & 2: Plantilla Blade) --}}
  <header class="w-full bg-white dark:bg-[#0b1a24] border-b border-[#e8eff5] dark:border-[#1a3549] h-16 sticky top-0 z-40 shadow-clinical-sm">
    <div class="h-full px-4 sm:px-6 lg:px-8 flex items-center justify-between gap-4">
      
      {{-- Lado Izquierdo: Logotipo del Sistema e Identificador de Sucursal/Caja --}}
      <div class="flex items-center gap-3 sm:gap-4 shrink-0">
        <a href="{{ url('/dashboard') }}" class="flex items-center gap-2.5 group">
          <div class="w-9 h-9 rounded-xl bg-[#022b3a] dark:bg-[#1f7a8c] flex items-center justify-center text-white shadow-sm transition-transform group-hover:scale-105">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
            </svg>
          </div>
          <div class="flex flex-col">
            <span class="font-brand font-bold text-lg sm:text-xl text-[#022b3a] dark:text-white leading-none">
              PharmaSys
            </span>
            <span class="text-[10px] font-semibold text-[#526e7d] dark:text-[#a1b8c7] tracking-wider uppercase">
              Panel Administrativo
            </span>
          </div>
        </a>

        {{-- Sucursal / Caja (Desktop) --}}
        <div class="hidden xl:flex items-center gap-2 px-3 py-1 bg-[#f4f7f9] dark:bg-[#132838] border border-[#e8eff5] dark:border-[#1a3549] rounded-lg text-xs">
          <svg class="w-4 h-4 text-[#1f7a8c]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
          </svg>
          <span class="font-semibold text-[#022b3a] dark:text-white">Sucursal Central</span>
          <span class="text-[#526e7d] dark:text-[#a1b8c7]">• Caja 01</span>
        </div>
      </div>

      {{-- Centro: Buscador Global (Boceto 1) --}}
      <div class="flex-1 max-w-xl mx-2 sm:mx-6">
        <div class="relative flex items-center">
          <span class="absolute left-3.5 text-[#526e7d] dark:text-[#a1b8c7]">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
          </span>
          <input type="search" 
                 placeholder="Buscar medicamentos, lotes, clientes o recetas..." 
                 class="w-full bg-[#f4f7f9] dark:bg-[#132838] text-xs sm:text-sm text-[#022b3a] dark:text-white placeholder-[#526e7d] dark:placeholder-[#a1b8c7] rounded-full pl-10 pr-12 py-2 border border-[#e8eff5] dark:border-[#1a3549] focus:outline-none focus:border-[#1f7a8c] focus:ring-2 focus:ring-[#bfdbf7]/50 dark:focus:ring-[#1f7a8c]/40 transition-all">
          <div class="hidden sm:flex items-center absolute right-3 text-[10px] font-semibold text-[#526e7d] dark:text-[#a1b8c7] bg-white dark:bg-[#0b1a24] px-1.5 py-0.5 rounded border border-[#e8eff5] dark:border-[#1a3549]">
            ⌘K
          </div>
        </div>
      </div>

      {{-- Lado Derecho: Controles, Notificaciones, Avatar y Botón 9 Puntos (Boceto 1 & 2) --}}
      <div class="flex items-center gap-2 sm:gap-3 shrink-0 relative">
        
        {{-- Selector Modo Claro / Oscuro --}}
        <button type="button" 
                data-toggle-theme 
                class="p-2 rounded-xl text-[#526e7d] dark:text-[#a1b8c7] hover:bg-[#e1e5f2]/40 dark:hover:bg-[#132838] hover:text-[#022b3a] dark:hover:text-white transition-colors"
                title="Cambiar Modo Claro/Oscuro"
                aria-label="Cambiar tema">
          <svg data-icon-theme-sun class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
          </svg>
          <svg data-icon-theme-moon class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
          </svg>
        </button>

        {{-- Campana de Notificaciones (Boceto 1) --}}
        <button type="button" 
                class="relative p-2 rounded-xl text-[#526e7d] dark:text-[#a1b8c7] hover:bg-[#e1e5f2]/40 dark:hover:bg-[#132838] hover:text-[#022b3a] dark:hover:text-white transition-colors"
                title="Notificaciones">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
          </svg>
          <span class="absolute top-1.5 right-1.5 w-4 h-4 bg-[#ba1a1a] text-white text-[10px] font-bold rounded-full flex items-center justify-center">
            3
          </span>
        </button>

        <div class="h-6 w-px bg-[#e8eff5] dark:bg-[#1a3549]"></div>

        {{-- Avatar de Usuario (Boceto 1) --}}
        <div class="flex items-center gap-2">
          <div class="w-8 h-8 rounded-full bg-[#022b3a] dark:bg-[#1f7a8c] text-white flex items-center justify-center text-xs font-bold shadow-sm">
            AM
          </div>
          <div class="hidden lg:flex flex-col text-left">
            <span class="text-xs font-semibold text-[#022b3a] dark:text-white leading-tight">Dr. Alejandro M.</span>
            <span class="text-[10px] text-[#526e7d] dark:text-[#a1b8c7]">Farmacéutico Reg.</span>
          </div>
        </div>

        {{-- Botón Lanzador de Aplicaciones de 9 Puntos (Boceto 1 & 2) --}}
        <button id="app-launcher-btn" 
                type="button" 
                aria-expanded="false" 
                title="Lanzador de Módulos (9 Puntos)" 
                class="p-2 rounded-xl text-[#022b3a] dark:text-white bg-[#f4f7f9] dark:bg-[#132838] hover:bg-[#bfdbf7]/40 dark:hover:bg-[#1f7a8c]/30 border border-[#e8eff5] dark:border-[#1a3549] transition-all focus:ring-2 focus:ring-[#1f7a8c]">
          {{-- Matriz 3x3 de Puntos SVG --}}
          <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
            <circle cx="5" cy="5" r="2"/>
            <circle cx="12" cy="5" r="2"/>
            <circle cx="19" cy="5" r="2"/>
            <circle cx="5" cy="12" r="2"/>
            <circle cx="12" cy="12" r="2"/>
            <circle cx="19" cy="12" r="2"/>
            <circle cx="5" cy="19" r="2"/>
            <circle cx="12" cy="19" r="2"/>
            <circle cx="19" cy="19" r="2"/>
          </svg>
        </button>

        {{-- Dropdown Modal del Lanzador de Aplicaciones (Componente Reutilizable) --}}
        <x-app-launcher />

      </div>

    </div>
  </header>

  {{-- 2. Espacio de Trabajo Principal (Full-Canvas con Contenedor Central del Boceto 1) --}}
  <main class="flex-1 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    @yield('content')
  </main>

  {{-- 3. Barra Inferior del Sistema (Boceto 1) --}}
  <footer class="w-full bg-white dark:bg-[#0b1a24] border-t border-[#e8eff5] dark:border-[#1a3549] py-3 text-xs text-[#526e7d] dark:text-[#a1b8c7]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-2">
      <div class="flex items-center gap-2">
        <span class="font-semibold text-[#022b3a] dark:text-white">Vitalis PharmaSys v2.4</span>
        <span>•</span>
        <span class="text-[#0f6b4f] flex items-center gap-1 font-medium">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
          </svg>
          Conexión Segura SSL
        </span>
        <span>•</span>
        <span>Terminal ID: POS-CENTRAL-01</span>
      </div>
      <div>
        <span>Soporte Farmacéutico: 0800-PHARMA (Anexo 104)</span>
      </div>
    </div>
  </footer>

</body>
</html>
