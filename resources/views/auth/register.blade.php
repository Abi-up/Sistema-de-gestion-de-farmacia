<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registro de Operador — Vitalis PharmaSys</title>

  {{-- Tipografías: Neuton & Plus Jakarta Sans --}}
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Neuton:ital,wght@0,400;0,700;1,400&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

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
<body class="bg-[#f4f7f9] dark:bg-[#08141d] text-[#022b3a] dark:text-[#ffffff] min-h-screen flex items-center justify-center p-4 sm:p-6 font-sans">

  {{-- Estructura Split-Screen (Boceto 5: Registro) --}}
  <div class="w-full max-w-4xl bg-white dark:bg-[#0d1e2b] border border-[#e8eff5] dark:border-[#1a3549] rounded-2xl shadow-clinical-lg overflow-hidden grid grid-cols-1 lg:grid-cols-12 min-h-[640px]">
    
    {{-- Columna Izquierda: Panel Visual Pale Sky (#BFDBF7) con Acentos Médicos --}}
    <div class="lg:col-span-5 bg-[#bfdbf7] dark:bg-[#132838] p-8 flex flex-col justify-between border-b lg:border-b-0 lg:border-r border-[#e8eff5] dark:border-[#1a3549]">
      <div>
        <div class="flex items-center gap-2.5 text-[#022b3a] dark:text-white">
          <div class="w-10 h-10 rounded-xl bg-[#022b3a] dark:bg-[#1f7a8c] flex items-center justify-center text-white shadow-sm">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
            </svg>
          </div>
          <span class="font-brand font-bold text-2xl text-[#022b3a] dark:text-white">
            Vitalis PharmaSys
          </span>
        </div>

        <div class="mt-8 space-y-4">
          <h2 class="font-brand font-bold text-2xl text-[#022b3a] dark:text-white leading-tight">
            Registro de Personal Clínico y Auxiliar
          </h2>
          <p class="text-xs text-[#022b3a]/80 dark:text-[#a1b8c7] leading-relaxed">
            Completa tus antecedentes profesionales para solicitar acceso de dispensación a la jefatura de farmacia.
          </p>
        </div>
      </div>

      {{-- Normativas Clínicas --}}
      <div class="mt-8 pt-6 border-t border-[#022b3a]/15 dark:border-[#1a3549] space-y-2 text-xs text-[#022b3a] dark:text-[#a1b8c7]">
        <div class="flex items-center gap-2">
          <svg class="w-4 h-4 text-[#1f7a8c] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
          </svg>
          <span>Registro Nacional de Prestadores Individuales</span>
        </div>
        <div class="flex items-center gap-2">
          <svg class="w-4 h-4 text-[#1f7a8c] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
          </svg>
          <span>Asignación de roles bajo principio de privilegio mínimo</span>
        </div>
      </div>
    </div>

    {{-- Columna Derecha: Formulario de Registro --}}
    <div class="lg:col-span-7 p-6 sm:p-10 flex flex-col justify-between">
      
      {{-- Barra Superior con botón "Inicio" (Boceto 5) --}}
      <div class="flex items-center justify-between pb-4 border-b border-[#e8eff5] dark:border-[#1a3549]">
        <span class="text-xs font-semibold text-[#526e7d] dark:text-[#a1b8c7]">Creación de Cuenta Operativa</span>
        
        {{-- Botón "Inicio" que regresa al portal público --}}
        <a href="{{ url('/') }}" 
           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-[#f4f7f9] dark:bg-[#132838] border border-[#e8eff5] dark:border-[#1a3549] text-[#022b3a] dark:text-white hover:bg-[#e1e5f2]/50 transition-colors">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
          </svg>
          <span>Inicio</span>
        </a>
      </div>

      {{-- Formulario --}}
      <div class="py-6 max-w-sm w-full mx-auto">
        <div class="mb-5">
          <h1 class="font-brand font-bold text-2xl text-[#022b3a] dark:text-white">Registrar Operador</h1>
          <p class="text-xs text-[#526e7d] dark:text-[#a1b8c7] mt-1">Ingresa tus datos conforme a la tabla de usuarios</p>
        </div>

        <form action="{{ url('/dashboard') }}" method="GET" class="space-y-3.5">
          {{-- Nombre y Apellidos --}}
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-semibold text-[#022b3a] dark:text-white mb-1">Nombre</label>
              <input type="text" name="nombre" required placeholder="Carlos" class="w-full text-xs rounded-xl px-3 py-2 bg-[#f4f7f9] dark:bg-[#132838] border border-[#e8eff5] dark:border-[#1a3549] text-[#022b3a] dark:text-white focus:outline-none focus:border-[#1f7a8c]">
            </div>
            <div>
              <label class="block text-xs font-semibold text-[#022b3a] dark:text-white mb-1">Apellidos</label>
              <input type="text" name="apellidos" required placeholder="Gómez" class="w-full text-xs rounded-xl px-3 py-2 bg-[#f4f7f9] dark:bg-[#132838] border border-[#e8eff5] dark:border-[#1a3549] text-[#022b3a] dark:text-white focus:outline-none focus:border-[#1f7a8c]">
            </div>
          </div>

          {{-- Correo Electrónico --}}
          <div>
            <label class="block text-xs font-semibold text-[#022b3a] dark:text-white mb-1">Correo Institucional</label>
            <input type="email" name="correo" required placeholder="cgomez@farmacia.cl" class="w-full text-xs rounded-xl px-3 py-2 bg-[#f4f7f9] dark:bg-[#132838] border border-[#e8eff5] dark:border-[#1a3549] text-[#022b3a] dark:text-white focus:outline-none focus:border-[#1f7a8c]">
          </div>

          {{-- Usuario y Teléfono --}}
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-semibold text-[#022b3a] dark:text-white mb-1">Usuario</label>
              <input type="text" name="usuario" required placeholder="cgomez" class="w-full text-xs rounded-xl px-3 py-2 bg-[#f4f7f9] dark:bg-[#132838] border border-[#e8eff5] dark:border-[#1a3549] text-[#022b3a] dark:text-white focus:outline-none focus:border-[#1f7a8c]">
            </div>
            <div>
              <label class="block text-xs font-semibold text-[#022b3a] dark:text-white mb-1">Teléfono</label>
              <input type="tel" name="telefono" placeholder="+56 9 1234 5678" class="w-full text-xs rounded-xl px-3 py-2 bg-[#f4f7f9] dark:bg-[#132838] border border-[#e8eff5] dark:border-[#1a3549] text-[#022b3a] dark:text-white focus:outline-none focus:border-[#1f7a8c]">
            </div>
          </div>

          {{-- Contraseña --}}
          <div>
            <label class="block text-xs font-semibold text-[#022b3a] dark:text-white mb-1">Contraseña</label>
            <input type="password" name="password" required placeholder="Mínimo 8 caracteres" class="w-full text-xs rounded-xl px-3 py-2 bg-[#f4f7f9] dark:bg-[#132838] border border-[#e8eff5] dark:border-[#1a3549] text-[#022b3a] dark:text-white focus:outline-none focus:border-[#1f7a8c]">
          </div>

          {{-- Botón Registrar --}}
          <button type="submit" 
                  class="w-full py-2.5 rounded-xl font-semibold text-xs sm:text-sm bg-[#1f7a8c] hover:bg-[#175f6d] text-white shadow-clinical-sm transition-all mt-2">
            Crear Cuenta de Operador
          </button>
        </form>
      </div>

      {{-- Pie: Iniciar Sesión (Boceto 5) --}}
      <div class="pt-4 border-t border-[#e8eff5] dark:border-[#1a3549] text-center">
        <p class="text-xs text-[#526e7d] dark:text-[#a1b8c7]">
          ¿Ya tienes cuenta autorizada?
          <a href="{{ url('/login') }}" class="font-bold text-[#1f7a8c] hover:underline ml-1">
            Iniciar sesión
          </a>
        </p>
      </div>

    </div>

  </div>

</body>
</html>
