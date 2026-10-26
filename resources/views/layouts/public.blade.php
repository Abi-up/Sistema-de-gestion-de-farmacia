<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'Vitalis PharmaSys — Farmacia & Cuidado Integral')</title>

  {{-- Tipografías: Neuton (Identidad Editorial) & Plus Jakarta Sans (Interfaz Clínica) --}}
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Neuton:ital,wght@0,400;0,700;1,400&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  {{-- Script de detección temprana de modo oscuro --}}
  <script>
    if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
      document.documentElement.classList.add('dark');
    } else {
      document.documentElement.classList.remove('dark');
    }
  </script>

  {{-- Three.js para el componente 3D inmersivo del portal --}}
  <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>

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
<body class="bg-[#f4f7f9] dark:bg-[#08141d] text-[#022b3a] dark:text-[#ffffff] min-h-screen flex flex-col font-sans transition-colors duration-200">

  {{-- 1. Barra Superior Principal del Portal Público --}}
  <header class="w-full bg-white dark:bg-[#0b1a24] border-b border-[#e8eff5] dark:border-[#1a3549] sticky top-0 z-40 shadow-clinical-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      
      {{-- Fila 1: Logotipo, Razón Social, Acciones y Acceso --}}
      <div class="h-16 flex items-center justify-between gap-4">
        
        {{-- Logotipo y Razón Social --}}
        <a href="{{ url('/') }}" class="flex items-center gap-3 group">
          <div class="w-10 h-10 rounded-xl bg-[#022b3a] dark:bg-[#1f7a8c] flex items-center justify-center text-white shadow-sm transition-transform group-hover:scale-105">
            {{-- Icono Cruz Clínica SVG (Sin Emojis) --}}
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
            </svg>
          </div>
          <div class="flex flex-col">
            <span class="font-brand font-bold text-xl sm:text-2xl text-[#022b3a] dark:text-[#ffffff] leading-tight tracking-tight">
              Vitalis <span class="text-[#1f7a8c] dark:text-[#bfdbf7]">PharmaSys</span>
            </span>
            <span class="text-[11px] font-semibold text-[#526e7d] dark:text-[#a1b8c7] tracking-wider uppercase -mt-0.5">
              Farmacia & Dispensación Clínica
            </span>
          </div>
        </a>

        {{-- Estado 24/7 de turno (Desktop) --}}
        <div class="hidden md:flex items-center gap-2 px-3 py-1.5 rounded-full bg-[#f4f7f9] dark:bg-[#132838] border border-[#e8eff5] dark:border-[#1a3549]">
          <span class="w-2.5 h-2.5 rounded-full bg-[#1f7a8c] animate-pulse"></span>
          <span class="text-xs font-semibold text-[#022b3a] dark:text-[#bfdbf7]">Turno Oficial Abierto 24/7</span>
          <span class="text-xs text-[#526e7d] dark:text-[#a1b8c7]">| Central Providencia</span>
        </div>

        {{-- Acciones Derecha: Selector de Modo Claro/Oscuro y Botón Iniciar Sesión --}}
        <div class="flex items-center gap-3">
          
          {{-- Interruptor Modo Claro / Modo Oscuro --}}
          <button type="button" 
                  data-toggle-theme 
                  class="p-2 rounded-xl text-[#526e7d] dark:text-[#a1b8c7] hover:bg-[#e1e5f2]/40 dark:hover:bg-[#132838] hover:text-[#022b3a] dark:hover:text-[#ffffff] transition-colors" 
                  aria-label="Cambiar tema">
            {{-- Icono Sol (Modo Oscuro Activo) --}}
            <svg data-icon-theme-sun class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            {{-- Icono Luna (Modo Claro Activo) --}}
            <svg data-icon-theme-moon class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
            </svg>
          </button>

          {{-- Botón Iniciar Sesión (Según Boceto 3) --}}
          <a href="{{ url('/login') }}" 
             class="inline-flex items-center gap-2 px-4 sm:px-5 py-2 rounded-xl font-semibold text-xs sm:text-sm bg-[#1f7a8c] hover:bg-[#175f6d] text-white shadow-clinical-sm transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
            </svg>
            <span>Iniciar Sesión</span>
          </a>

          {{-- Botón Hamburguesa Móvil --}}
          <button id="mobile-menu-btn" 
                  class="md:hidden p-2 rounded-lg text-[#022b3a] dark:text-white hover:bg-[#f4f7f9] dark:hover:bg-[#132838]" 
                  aria-label="Abrir menú">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
          </button>
        </div>

      </div>

      {{-- Fila 2: Sub-barra de Navegación Horizontal (Boceto 3) --}}
      {{-- inicio · productos · promociones · servicios · contacto --}}
      <nav class="hidden md:flex items-center justify-center gap-1 sm:gap-2 py-2 border-t border-[#e8eff5] dark:border-[#1a3549]">
        <a href="{{ url('/#inicio') }}" 
           class="px-4 py-1.5 rounded-lg text-sm font-semibold text-[#022b3a] dark:text-[#ffffff] hover:text-[#1f7a8c] dark:hover:text-[#bfdbf7] hover:bg-[#e1e5f2]/30 dark:hover:bg-[#132838] transition-colors">
          Inicio
        </a>
        <span class="text-[#bfdbf7] dark:text-[#1a3549] select-none">•</span>

        <a href="{{ url('/#productos') }}" 
           class="px-4 py-1.5 rounded-lg text-sm font-semibold text-[#526e7d] dark:text-[#a1b8c7] hover:text-[#022b3a] dark:hover:text-[#ffffff] hover:bg-[#e1e5f2]/30 dark:hover:bg-[#132838] transition-colors">
          Productos
        </a>
        <span class="text-[#bfdbf7] dark:text-[#1a3549] select-none">•</span>

        <a href="{{ url('/#promociones') }}" 
           class="px-4 py-1.5 rounded-lg text-sm font-semibold text-[#526e7d] dark:text-[#a1b8c7] hover:text-[#022b3a] dark:hover:text-[#ffffff] hover:bg-[#e1e5f2]/30 dark:hover:bg-[#132838] transition-colors">
          Promociones
        </a>
        <span class="text-[#bfdbf7] dark:text-[#1a3549] select-none">•</span>

        <a href="{{ url('/#servicios') }}" 
           class="px-4 py-1.5 rounded-lg text-sm font-semibold text-[#526e7d] dark:text-[#a1b8c7] hover:text-[#022b3a] dark:hover:text-[#ffffff] hover:bg-[#e1e5f2]/30 dark:hover:bg-[#132838] transition-colors">
          Servicios
        </a>
        <span class="text-[#bfdbf7] dark:text-[#1a3549] select-none">•</span>

        <a href="{{ url('/#contacto') }}" 
           class="px-4 py-1.5 rounded-lg text-sm font-semibold text-[#526e7d] dark:text-[#a1b8c7] hover:text-[#022b3a] dark:hover:text-[#ffffff] hover:bg-[#e1e5f2]/30 dark:hover:bg-[#132838] transition-colors">
          Contacto
        </a>
      </nav>

      {{-- Menú Desplegable Móvil --}}
      <div id="mobile-nav" class="hidden md:hidden py-3 border-t border-[#e8eff5] dark:border-[#1a3549] flex flex-col gap-1">
        <a href="{{ url('/#inicio') }}" class="px-3 py-2 rounded-lg text-sm font-semibold text-[#022b3a] dark:text-white hover:bg-[#f4f7f9] dark:hover:bg-[#132838]">Inicio</a>
        <a href="{{ url('/#productos') }}" class="px-3 py-2 rounded-lg text-sm font-semibold text-[#526e7d] dark:text-[#a1b8c7] hover:bg-[#f4f7f9] dark:hover:bg-[#132838]">Productos</a>
        <a href="{{ url('/#promociones') }}" class="px-3 py-2 rounded-lg text-sm font-semibold text-[#526e7d] dark:text-[#a1b8c7] hover:bg-[#f4f7f9] dark:hover:bg-[#132838]">Promociones</a>
        <a href="{{ url('/#servicios') }}" class="px-3 py-2 rounded-lg text-sm font-semibold text-[#526e7d] dark:text-[#a1b8c7] hover:bg-[#f4f7f9] dark:hover:bg-[#132838]">Servicios</a>
        <a href="{{ url('/#contacto') }}" class="px-3 py-2 rounded-lg text-sm font-semibold text-[#526e7d] dark:text-[#a1b8c7] hover:bg-[#f4f7f9] dark:hover:bg-[#132838]">Contacto</a>
      </div>

    </div>
  </header>

  {{-- Contenido Dinámico del Portal Público --}}
  <main class="flex-1 w-full">
    @yield('content')
  </main>

  {{-- Pie de Página Institucional --}}
  <footer class="w-full bg-[#022b3a] text-[#bfdbf7] pt-14 pb-10 border-t border-[#022b3a]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 pb-10 border-b border-[#1f7a8c]/30">
        
        {{-- Columna 1: Identidad --}}
        <div class="space-y-3">
          <div class="flex items-center gap-2 text-white">
            <div class="w-8 h-8 rounded-lg bg-[#1f7a8c] flex items-center justify-center text-white">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
              </svg>
            </div>
            <span class="font-brand font-bold text-xl text-white">Vitalis PharmaSys</span>
          </div>
          <p class="text-xs text-[#bfdbf7]/80 leading-relaxed">
            Plataforma farmacéutica certificada para la dispensación segura, trazabilidad de lotes y atención médica de precisión.
          </p>
          <div class="pt-2 flex flex-col gap-1 text-[11px] text-[#bfdbf7]">
            <span>• Resolución Sanitaria Exenta N° 8492</span>
            <span>• Certificación ISO 9001:2015 en Cadena de Frío</span>
          </div>
        </div>

        {{-- Columna 2: Medicamentos --}}
        <div>
          <h4 class="font-semibold text-white text-sm uppercase tracking-wider mb-3">Medicamentos</h4>
          <ul class="space-y-2 text-xs">
            <li><a href="{{ url('/#productos') }}" class="hover:text-white transition-colors">Venta con Receta Médica</a></li>
            <li><a href="{{ url('/#productos') }}" class="hover:text-white transition-colors">Bioequivalentes Genéricos</a></li>
            <li><a href="{{ url('/#productos') }}" class="hover:text-white transition-colors">Tratamientos Crónicos</a></li>
            <li><a href="{{ url('/#productos') }}" class="hover:text-white transition-colors">Línea Pediátrica</a></li>
            <li><a href="{{ url('/#productos') }}" class="hover:text-white transition-colors">Cadena de Frío Monitoreada</a></li>
          </ul>
        </div>

        {{-- Columna 3: Servicios Clínicos --}}
        <div>
          <h4 class="font-semibold text-white text-sm uppercase tracking-wider mb-3">Servicios Clínicos</h4>
          <ul class="space-y-2 text-xs">
            <li><a href="{{ url('/#servicios') }}" class="hover:text-white transition-colors">Validación de Receta Digital</a></li>
            <li><a href="{{ url('/#servicios') }}" class="hover:text-white transition-colors">Farmacovigilancia Activa</a></li>
            <li><a href="{{ url('/#servicios') }}" class="hover:text-white transition-colors">Despacho Domiciliario Seguro</a></li>
            <li><a href="{{ url('/#servicios') }}" class="hover:text-white transition-colors">Convenios y Aseguradoras</a></li>
            <li><a href="{{ url('/#servicios') }}" class="hover:text-white transition-colors">Turnos Oficiales Nocturnos</a></li>
          </ul>
        </div>

        {{-- Columna 4: Atención & Contacto --}}
        <div>
          <h4 class="font-semibold text-white text-sm uppercase tracking-wider mb-3">Atención de Urgencia</h4>
          <div class="space-y-2 text-xs">
            <p><span class="text-white font-semibold">Central Telefónica:</span> +56 2 2800 4500</p>
            <p><span class="text-white font-semibold">Dirección:</span> Av. Providencia 1450, Sede Central</p>
            <p><span class="text-white font-semibold">Horario:</span> Continuado 24 horas, Lunes a Domingo</p>
            <div class="pt-2">
              <a href="{{ url('/login') }}" class="inline-block px-3 py-1.5 rounded-lg bg-[#1f7a8c] text-white text-xs font-semibold hover:bg-[#175f6d] transition-colors">
                Acceso Personal Autorizado &rarr;
              </a>
            </div>
          </div>
        </div>

      </div>

      <div class="pt-6 flex flex-col sm:flex-row items-center justify-between text-xs text-[#bfdbf7]/70 gap-3">
        <span>© {{ date('Y') }} Vitalis PharmaSys S.A. Todos los derechos reservados.</span>
        <span>Dispensación sujeta a la legislación sanitaria vigente</span>
      </div>
    </div>
  </footer>

</body>
</html>
