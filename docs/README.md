# Sistema de Diseño: Clinical Precision UI
## Vitalis PharmaSys — Sistema de Gestión de Farmacia

Este documento condensa y formaliza todas las especificaciones de diseño, tokens visuales, componentes de interfaz y arquetipos de navegación derivados de la documentación en `docs/` (`DESIGN.md`, `code.html`, `screen.png`) y los bocetos estructurales del sistema.

---

## 1. Filosofía y Principios de Diseño

El sistema está diseñado bajo el paradigma **Modern Clinical Minimalism & Refined Utility**, concebido para equilibrar la autoridad institucional médica con una experiencia de uso ágil, humana y libre de fricción.

### Principios Fundamentales
* **Precisión y Verificabilidad:** Los datos clínicos, dosis, números de lote, fechas de vencimiento y montos monetarios se presentan con alto contraste y sin ambigüedades.
* **Reducción de Carga Cognitiva:** Se sustituyen las barras laterales pesadas y permanentes por un **espacio de trabajo Full-Canvas**, complementado por un **Lanzador de Aplicaciones global de 9 puntos**.
* **Estilo Sobrio y Profesional:**
  * **Prohibido el uso de emojis:** Toda la señalética utiliza iconografía vectorial SVG de trazo fino clínico.
  * **Colores planos y controlados:** Se prohíben degradados estridentes, saturaciones de neón o tonos pasteles ajenos a la paleta médica oficial.
  * **Soporte Bipolar:** Modo Claro (*Light Mode*) de descanso visual y Modo Oscuro (*Dark Mode*) de alta definición.

---

## 2. Paleta de Colores y Tokens

La identidad cromática se articula en torno a tonos petroleros profundos, azulados clínicos y superficies limpias con certificación de accesibilidad WCAG AA.

### 2.1 Colores Fundamentales

| Nombre | HEX | HSL | Rol Principal |
| :--- | :--- | :--- | :--- |
| **Deep Petroleum Navy** | `#022B3A` | `hsl(196, 93%, 12%)` | Encabezados principales, títulos de alto énfasis, superficies administrativas oscuras. |
| **Clinical Teal** | `#1F7A8C` | `hsl(190, 64%, 34%)` | Acciones primarias, botones interactivos, estados activos y confirmaciones. |
| **Pale Sky** | `#BFDBF7` | `hsl(210, 78%, 86%)` | Acento atmosférico, panel visual de autenticación, selección de pastillas y fondos suaves. |
| **Lavender / Canvas Base** | `#E1E5F2` / `#F4F7F9` | `hsl(226, 40%, 92%)` | Lienzo de fondo del sistema, otorgando descanso ocular frente al blanco puro. |
| **Pure White** | `#FFFFFF` | `hsl(0, 0%, 100%)` | Tarjetas de trabajo, modales, formularios y superficies de lectura. |

### 2.2 Variables CSS del Sistema (`app.css`)

```css
:root {
  /* Paleta Base */
  --jet-black: #022b3a;
  --teal: #1f7a8c;
  --pale-sky: #bfdbf7;
  --lavender: #e1e5f2;
  --white: #ffffff;

  /* Tokens Semánticos Superficies */
  --surface: #f7fafc;
  --surface-container-lowest: #ffffff;
  --surface-container-low: #f1f4f6;
  --surface-container: #ebeef0;
  --surface-container-high: #e5e9eb;
  --surface-container-highest: #e0e3e5;
  --surface-border: #e8eff5;

  /* Tokens Tipográficos */
  --on-surface: #181c1e;
  --on-surface-variant: #42484b;
  --text-muted: #526e7d;

  /* Estados Clínicos */
  --color-success: #0f6b4f;
  --bg-success: #e6f7f2;
  --border-success: #b8ebd9;

  --color-warning: #a05e03;
  --bg-warning: #fef6e7;
  --border-warning: #fcdca7;

  --color-danger: #ba1a1a;
  --bg-danger: #ffdad6;
  --border-danger: #ffb4ab;
}

/* Modo Oscuro */
.dark {
  --surface: #0a1118;
  --surface-container-lowest: #0d1822;
  --surface-container-low: #13222e;
  --surface-container: #172a39;
  --surface-container-high: #1c3345;
  --surface-container-highest: #223c52;
  --surface-border: #1f3547;
  --on-surface: #eef1f3;
  --on-surface-variant: #a9bcc7;
  --text-muted: #829aa8;
}
```

---

## 3. Tipografía y Jerarquía Textual

El sistema combina la sobriedad editorial de **Neuton** con la claridad técnica de **Plus Jakarta Sans**.

* **Neuton:** Utilizada para la identidad de marca, encabezados editoriales e identificadores institucionales.
* **Plus Jakarta Sans:** Empleada en interfaces de usuario, formularios, metadatos y visualización de datos clínicos.
* **Cifras Tabulares (`tnum`):** Imprescindibles para tablas de inventario, stock, dosificación y registros de venta para garantizar la alineación vertical de números.

### Escala Tipográfica de Referencia

| Nivel | Tamaño | Altura de Línea | Peso | Letter Spacing | Uso Principal |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Display Hero** | 48px | 56px | 800 | `-0.02em` | Título del portal público (escritorio). |
| **Display Hero Móvil**| 32px | 40px | 800 | `-0.01em` | Título del portal público (móvil). |
| **Headline Large** | 32px | 40px | 700 | `-0.015em` | Título de secciones de catálogo y reportes. |
| **Headline Medium**| 24px | 32px | 600 | `-0.01em` | Cabeceras de módulo y paneles de dashboard. |
| **Headline Small** | 20px | 28px | 600 | `0` | Títulos de tarjetas y modales. |
| **Title Medium** | 16px | 24px | 600 | `0` | Subtítulos y encabezados de grupos en formulario. |
| **Body Large** | 16px | 26px | 400 | `0` | Descripciones introductorias del portal. |
| **Body Medium** | 14px | 22px | 400 | `0` | Texto estándar del sistema e inputs. |
| **Body Small** | 13px | 18px | 400 | `0` | Registros secundarios de tablas y ayuda. |
| **Label Medium** | 13px | 18px | 600 | `+0.01em` | Botones, tabs y enlaces de navegación. |
| **Label Small** | 11px | 16px | 700 | `+0.04em` | Badges de estado, tags y notas al pie. |

---

## 4. Rejilla Espacial, Geometría y Elevación

### 4.1 Rejilla de Espaciado (Base armónica de 8 puntos)
* `space-xs`: `0.25rem` (4px) — Micro-separadores y márgenes de tags.
* `space-sm`: `0.5rem` (8px) — Espaciado interno de controles y botones pequeños.
* `space-md`: `1.0rem` (16px) — Relleno estándar entre bloques y campos.
* `space-lg`: `1.5rem` (24px) — Relleno de tarjetas y modales.
* `space-xl`: `2.5rem` (40px) — Separación de secciones mayores.
* `gutter`: `1.5rem` (24px escritorio / 16px móvil)
* `margin`: `2.0rem` (32px escritorio / 16px móvil)

### 4.2 Radios de Curvatura (*Border Radius*)
* **Micro-elementos (Badges, Tags, Checkboxes):** `6px` a `8px` (`rounded-md`).
* **Controles Interactivos (Inputs, Botones, Selects):** `10px` a `12px` (`rounded-lg`).
* **Tarjetas y Contenedores:** `16px` (`rounded-xl`).
* **Modales, Menú Lanzador de 9 Puntos y Split-Auth:** `20px` a `24px` (`rounded-2xl`).
* **Pastillas y Badges Circulares:** `9999px` (`rounded-full`).

### 4.3 Niveles de Elevación (*Clinical Shadows*)
Se descartan sombras negras pesadas; se utilizan difusiones suaves teñidas en tono petróleo (`#022B3A`):
* **Nivel 0 (Base Canvas):** Plano (`#F4F7F9` / `#0A1118`).
* **Nivel 1 (Tarjetas en reposo):** `border: 1px solid #E8EFF5` + sombra `0 1px 3px rgba(2, 43, 58, 0.04)`.
* **Nivel 2 (Hover interactivo):** Sombra `0 10px 25px -5px rgba(2, 43, 58, 0.08)`.
* **Nivel 3 (Lanzador de Apps y Dropdowns):** Sombra `0 20px 30px -10px rgba(2, 43, 58, 0.14)` con contorno sutil `1px solid rgba(191, 219, 247, 0.6)`.
* **Nivel 4 (Modals y Diálogos):** Contenedor central elevado con telón de fondo (`background: rgba(2, 43, 58, 0.45)` con `backdrop-filter: blur(4px)`).

---

## 5. Arquetipos de Navegación y Vistas (Bocetos de Referencia)

### 5.1 Arquetipo 1: Plantilla Blade Administrativa (Full-Canvas)
* **Barra Superior Persistente (64px - 68px):**
  * Logotipo e indicador institucional.
  * Buscador global centrado (`rounded-full`, prefijo de lupa SVG, atajo visible `[⌘K]`).
  * Indicador de Sucursal / Caja activa.
  * Campana de notificaciones con badge numérico.
  * Interruptor de Modo Claro / Modo Oscuro.
  * Avatar de usuario con nombre y rol clínico.
  * **Botón Lanzador de Aplicaciones (9 puntos):** Matriz geométrica 3x3 para abrir el panel de módulos.
* **Área de Trabajo:**
  * Rejilla fluida de 12 columnas que aprovecha el 100% del ancho de pantalla sin una barra lateral rígida.

### 5.2 Arquetipo 2: Lanzador de Aplicaciones (App Launcher 9 Puntos)
* **Comportamiento:** Panel flotante desplegable (`340px x 380px`, `rounded-2xl`, Nivel 3 de elevación) posicionado inmediatamente bajo el botón de 9 puntos.
* **Estructura Interna:** Cuadrícula de 3x3 módulos representados por iconos SVG clínicos y etiquetas claras:
  1. `Dashboard` (Vista ejecutiva)
  2. `Inventario` (Lotes, caducidades y existencias)
  3. `Ventas` (Punto de venta y facturación)
  4. `Recetas` (Dispensación médica controlada)
  5. `Clientes` (Pacientes y convenios)
  6. `Compras` (Proveedores y recepciones)
  7. `Reportes` (Auditoría y bitácora)
  8. `Usuarios` (Roles y permisos)
  9. `Ajustes` (Configuración de sucursal)
* **Estados Visuales:**
  * Opción activa: Borde acentuado en `#1F7A8C` y fondo suave `#BFDBF7` al 30%.
  * Opción en reposo: Fondo transparente con transición suave a fondo `#BFDBF7` (15%) en hover.

### 5.3 Arquetipo 3: Portal Público con 3D Inmersivo
* **Estructura Superior:**
  * Encabezado institucional con logotipo, razón social y estado de turno 24/7.
  * Barra de navegación horizontal: `Inicio · Productos · Promociones · Servicios · Contacto`.
  * Botón de llamada a la acción: "Iniciar Sesión" (`#1F7A8C`).
* **Hero Section con 3D Inmersivo:**
  * Columna izquierda: Titular de impacto en **Neuton**, propuesta de valor sanitaria y métricas clínicas de confianza (Disponibilidad 99.8%, Despacho < 45 min).
  * Columna derecha: Visor interactivo 3D (render farmacéutico o Canvas WebGL con cápsula/molécula médica con materiales sobrios en Teal `#1F7A8C`, Navy `#022B3A` y reflejos en Pale Sky `#BFDBF7`, con iluminación limpia de estudio y rotación interactiva).
* **Secciones Complementarias:**
  * Exploración rápida de categorías farmacéuticas.
  * Cuadrícula de productos y bioequivalentes con badges de receta obligatoria.
  * Localización de sucursales con mapa e información de turno nocturno.
  * Pie de página institucional con resoluciones sanitarias, términos y certificaciones.

### 5.4 Arquetipo 4 y 5: Vistas de Autenticación Split-Screen (Login y Registro)
* **Disposición 50/50 (Escritorio >= 1024px):**
  * **Columna Izquierda (Panel Visual):** Fondo sólido en Pale Sky (`#BFDBF7`), motivos geométricos médicos sobrios, credenciales sanitarias y garantía de seguridad en datos de salud.
  * **Columna Derecha (Formulario):**
    * Barra superior con botón directo de retorno a `Inicio` (portal público).
    * Título y subtítulo descriptivo del acceso.
    * Campos de formulario con labels claros (`rounded-lg`, borde `#E8EFF5`, focus en `#1F7A8C`).
    * Acciones secundarias: "¿Olvidaste tu contraseña?" y alternancia entre "Iniciar Sesión" y "Registrarme".
    * Botón de acción principal en Teal `#1F7A8C` sólido.
* **Disposición Móvil (< 1024px):** Columna única apilada con panel de acento superior compacto.

---

## 6. Componentes de Interfaz

### 6.1 Botones

* **Botón Primario (Clinical Teal):**
  ```css
  background-color: #1f7a8c;
  color: #ffffff;
  border-radius: 10px;
  font-weight: 600;
  padding: 10px 20px;
  ```
  *Hover:* `#175f6d` con elevación Nivel 2. *Focus:* `ring-2 ring-[#bfdbf7]`.
* **Botón Administrativo (Petroleum Navy):**
  ```css
  background-color: #022b3a;
  color: #ffffff;
  border-radius: 10px;
  ```
  Utilizado para exportaciones, autorizaciones clínicas o cierres de caja.
* **Botón Secundario / Suave:**
  ```css
  background-color: rgba(191, 219, 247, 0.35);
  color: #022b3a;
  ```
* **Botón Fantasma:**
  ```css
  background-color: transparent;
  color: #1f7a8c;
  ```

### 6.2 Campos de Entrada (*Input Fields*)
* **En Reposo:** Fondo `#FFFFFF`, borde `1.5px solid #E8EFF5`, texto `#022B3A`, altura `44px`, `border-radius: 10px`.
* **En Foco:** Borde `#1F7A8C`, `box-shadow: 0 0 0 3px rgba(31, 122, 140, 0.15)`.
* **Buscador de Cabecera:** Altura `40px`, pastilla completa (`rounded-full`), icono de lupa integrado en `#526E7D`.

### 6.3 Badges de Estado de Medicamento
* **En Stock / Activo:**
  * Fondo: `#E6F7F2` | Texto: `#0F6B4F` | Borde: `1px solid #B8EBD9`.
* **Bajo Stock / Precaución:**
  * Fondo: `#FEF6E7` | Texto: `#A05E03` | Borde: `1px solid #FCDCA7`.
* **Receta Médica Obligatoria (Controlado):**
  * Fondo: `rgba(191, 219, 247, 0.4)` | Texto: `#022B3A` | Borde: `1px solid #BFDBF7`.
* **Vencido / Inactivo:**
  * Fondo: `#FFDAD6` | Texto: `#BA1A1A` | Borde: `1px solid #FFB4AB`.

---

## 7. Mapeo con los Bocetos Originales del Usuario

| Boceto del Usuario | Plantilla Blade | Layout Base | Componentes Clave |
| :--- | :--- | :--- | :--- |
| **Plantilla blade** | `resources/views/dashboard.blade.php` | `layouts/app.blade.php` | Buscador, notificaciones, avatar, 9-dot trigger. |
| **Navegación de lanzador de apps** | Componente Blade | `components/app-launcher.blade.php` | Rejilla 3x3 de módulos con estado activo. |
| **Barra de navegación superior (portal)** | `resources/views/welcome.blade.php` | `layouts/public.blade.php` | Sub-nav (inicio, productos, promociones, etc.), CTA Login, 3D Hero. |
| **Inicio de sesión** | `resources/views/auth/login.blade.php` | Split-Screen Layout | Panel Pale Sky lateral, botón retorno "Inicio", formulario limpio. |
| **Registro** | `resources/views/auth/register.blade.php` | Split-Screen Layout | Panel Pale Sky lateral, botón retorno "Inicio", campos del modelo `User`. |

---

## 8. Guía de Implementación Técnica en Laravel

1. **Variables y Tailwind v4:** Mantener las variables definidas en `resources/css/app.css` y las fuentes *Neuton* y *Plus Jakarta Sans* desde Google Fonts.
2. **Separación de Layouts:**
   * `resources/views/layouts/public.blade.php` para la navegación abierta y catálogo público.
   * `resources/views/layouts/app.blade.php` para el sistema administrativo y de gestión con autenticación.
3. **Control del Lanzador:** El menú de 9 puntos se controla de forma reactiva con JavaScript ligero o Alpine sin dependencias invasivas.
4. **Visor 3D:** Integrado en el contenedor hero del portal público mediante un canvas WebGL / Three.js con paleta clínica controlada `#022B3A` / `#1F7A8C` / `#BFDBF7`.
