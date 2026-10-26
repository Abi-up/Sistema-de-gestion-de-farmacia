---
name: Clinical Precision UI
colors:
  surface: '#f7fafc'
  surface-dim: '#d7dadc'
  surface-bright: '#f7fafc'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f1f4f6'
  surface-container: '#ebeef0'
  surface-container-high: '#e5e9eb'
  surface-container-highest: '#e0e3e5'
  on-surface: '#181c1e'
  on-surface-variant: '#42484b'
  inverse-surface: '#2d3133'
  inverse-on-surface: '#eef1f3'
  outline: '#72787c'
  outline-variant: '#c1c7cc'
  surface-tint: '#416373'
  primary: '#00151f'
  on-primary: '#ffffff'
  primary-container: '#022b3a'
  on-primary-container: '#7193a5'
  inverse-primary: '#a9ccdf'
  secondary: '#006879'
  on-secondary: '#ffffff'
  secondary-container: '#99e9fd'
  on-secondary-container: '#006b7c'
  tertiary: '#001423'
  on-tertiary: '#ffffff'
  tertiary-container: '#0a293f'
  on-tertiary-container: '#7691ab'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#c4e8fc'
  primary-fixed-dim: '#a9ccdf'
  on-primary-fixed: '#001f2b'
  on-primary-fixed-variant: '#294b5b'
  secondary-fixed: '#a9edff'
  secondary-fixed-dim: '#83d2e6'
  on-secondary-fixed: '#001f26'
  on-secondary-fixed-variant: '#004e5b'
  tertiary-fixed: '#cce5ff'
  tertiary-fixed-dim: '#aecae5'
  on-tertiary-fixed: '#001d31'
  on-tertiary-fixed-variant: '#2e4960'
  background: '#f7fafc'
  on-background: '#181c1e'
  surface-variant: '#e0e3e5'
typography:
  display-hero:
    fontFamily: Plus Jakarta Sans
    fontSize: 48px
    fontWeight: '800'
    lineHeight: 56px
    letterSpacing: -0.02em
  display-hero-mobile:
    fontFamily: Plus Jakarta Sans
    fontSize: 32px
    fontWeight: '800'
    lineHeight: 40px
    letterSpacing: -0.01em
  headline-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 32px
    fontWeight: '700'
    lineHeight: 40px
    letterSpacing: -0.015em
  headline-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
    letterSpacing: -0.01em
  headline-sm:
    fontFamily: Plus Jakarta Sans
    fontSize: 20px
    fontWeight: '600'
    lineHeight: 28px
  title-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 16px
    fontWeight: '600'
    lineHeight: 24px
  body-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 26px
  body-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 22px
  body-sm:
    fontFamily: Plus Jakarta Sans
    fontSize: 13px
    fontWeight: '400'
    lineHeight: 18px
  label-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 13px
    fontWeight: '600'
    lineHeight: 18px
    letterSpacing: 0.01em
  label-sm:
    fontFamily: Plus Jakarta Sans
    fontSize: 11px
    fontWeight: '700'
    lineHeight: 16px
    letterSpacing: 0.04em
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  gutter: 1.5rem
  gutter-mobile: 1rem
  margin: 2rem
  margin-mobile: 1rem
  space-xs: 0.25rem
  space-sm: 0.5rem
  space-md: 1rem
  space-lg: 1.5rem
  space-xl: 2.5rem
---

## Brand & Style

This design system establishes a clinical, contemporary, and dependable aesthetic engineered for pharmacy management and healthcare delivery. It balances institutional authority with accessible, human-centered usability.

### Brand Personality & Mood
- **Authoritative & Trustworthy:** Precision-led interface details, crisp data hierarchy, and high contrast eliminate ambiguity in medication handling and clinical operations.
- **Approachable & Human:** Softened geometry, spacious touch points, and calm azure undertones relieve cognitive load during high-pressure workflows.
- **Fluid & Responsive:** Eliminates cramped enterprise conventions (such as persistent, heavy sidebars) in favor of a full-canvas administrative model driven by global quick-action launchers and contextual modals.

### Core Visual Movement
A tailored blend of **Modern Clinical Minimalism** and **Refined Utility**. Structural depth is conveyed through layered card architectures, light-reactive borders, and diffuse shadows rather than dense borders or visual clutter.

## Colors

The palette leverages a carefully balanced medical color hierarchy:

- **Deep Petroleum Navy (`#022B3A`):** The primary color anchor. Applied to top application headers, high-level administrative surfaces, primary titles, and high-emphasis typographic layers.
- **Clinical Teal (`#1F7A8C`):** The functional action and secondary accent color. Used for primary buttons, active interactive states, key callouts, and system-level alerts requiring confident acknowledgment.
- **Pale Sky (`#BFDBF7`):** The soft atmospheric tint. Applied to subtle accent backgrounds, authentication presentation panels, active pill selections, and informational container states.
- **Canvas Base (`#F4F7F9`):** The overall system canvas tone, providing contrast against pure white cards without straining the eye.
- **Subtle Surface Border (`#E8EFF5`):** Structural divider and boundary tone for clean card separation.
- **Muted Slate (`#4B6370` / `#526E7D`):** Controlled body typography, labels, metadata, and helper text that maintain WCAG AA compliance across all light surfaces.
- **Pure White (`#FFFFFF`):** Work surfaces, modal trays, elevated cards, and floating interactive containers.

## Typography

The type system is powered by **Plus Jakarta Sans**, chosen for its friendly geometric clarity, open apertures, and exceptional legibility when scanning dense clinical tables, batch numbers, and dosage amounts.

- **Numerals & Metrics:** Rendered with tabular figures (`tnum`) in administrative dashboards and billing registers to guarantee vertical alignment across financial and stock tables.
- **Hierarchical Balance:** Display headers utilize tighter letter-spacing (`-0.02em`) to maintain editorial impact, while operational metadata and status labels rely on tracked uppercase or semi-bold weights for rapid legibility.

## Layout & Spacing

The layout is built upon an 8-point harmonic spatial grid supporting three primary operational viewports:

### System Archetypes

1. **Administrative Workspace (Blade Full-Canvas):**
   - **Top Navigation Bar:** Persistent 68px height bar set against `#022B3A` or crisp `#FFFFFF` with `#E8EFF5` bottom border. Features an integrated global search, notification hubs, user avatar, and an accessible 9-dot App Launcher (`3x3` grid modal trigger).
   - **No Persistent Sidebar:** The primary work region stretches across the full horizontal canvas (12-column fluid grid, `2rem` outer padding), maximizing horizontal real estate for inventory tables, prescription processing, and reporting.
   - **App Launcher Modal (9 Puntos):** A floating surface overlay positioned immediately beneath the 9-dot trigger. Built as a 3x3 icon grid with subtle hover transitions (`background: #BFDBF7` at 25% opacity), granting instant access to core modules (Inventario, Ventas, Clientes, Facturación, etc.).

2. **Public Portal:**
   - **Top Fluid Header:** Clean horizontal brand bar with linked navigational items (`Inicio`, `Productos`, `Promociones`, `Servicios`, `Contacto`) and a direct high-contrast `#1F7A8C` button for portal access / login.
   - **Content Canvas:** Centered 1200px max-width container with responsive side gutters scaling down gracefully from desktop to mobile.

3. **Authentication Split Layout:**
   - **Desktop (>= 1024px):** A balanced 50/50 split canvas. The left column is an immersive brand panel draped in Pale Sky (`#BFDBF7`) featuring soft geometric medical visual accents; the right column hosts an expansive, centered card with form controls, direct links, and clear action hierarchies.
   - **Mobile (< 1024px):** Single-column stacked layout with the Pale Sky brand element condensed into a sleek header card above the login/register form.

## Elevation & Depth

This design system eschews harsh drop shadows, utilizing low-opacity clinical shadows with subtle slate-teal tints:

- **Surface Level 0 (Canvas Base):** Flat `#F4F7F9` background.
- **Surface Level 1 (Resting Cards & Worksurfaces):** `#FFFFFF` background with a crisp border: `1px solid #E8EFF5`. Shadow: `0 1px 3px rgba(2, 43, 58, 0.04), 0 1px 2px rgba(2, 43, 58, 0.02)`.
- **Surface Level 2 (Interactive Hover & Elevated Modules):** `#FFFFFF` with shadow: `0 10px 25px -5px rgba(2, 43, 58, 0.08), 0 8px 10px -6px rgba(2, 43, 58, 0.04)`.
- **Surface Level 3 (App Launcher Grid & Dropdowns):** Floating container with `box-shadow: 0 20px 30px -10px rgba(2, 43, 58, 0.14)` and a `1px solid rgba(191, 219, 247, 0.6)` outline.
- **Surface Level 4 (Modals & Prescription Overlays):** Centered high-elevation container accompanied by a dimming backdrop overlay (`background: rgba(2, 43, 58, 0.45)` with `backdrop-filter: blur(4px)`).

## Shapes

The interface embraces a rounded, contemporary shape profile (`Level 2` base) scaled gracefully across interface tiers:

- **Micro Elements (Badges, Tags, Checkboxes):** `6px` to `8px` (`rounded-md`).
- **Standard Controls (Input fields, Buttons, Selects):** `10px` to `12px` (`rounded-lg`).
- **Containers & Cards (Data tables, Content blocks):** `16px` (`rounded-xl`).
- **App Launcher Modal, Split Auth Panels & Main Hero Containers:** `20px` to `24px` (`rounded-2xl`).
- **Pills & Status Indicators:** `9999px` full round.

## Components

### Buttons
- **Primary Action:** Solid Teal `#1F7A8C`, text `#FFFFFF`, font weight 600, padding `10px 20px`, border radius `10px`. Hover state: `#175F6D` with a subtle elevation shift. Focus: `ring-2 ring-[#BFDBF7] ring-offset-2`.
- **Administrative Dark Button:** Deep Petroleum `#022B3A`, text `#FFFFFF`. Used for global exports, critical approvals, and top-bar actions.
- **Secondary / Soft Button:** Background `rgba(191, 219, 247, 0.35)`, text `#022B3A`. Hover: `rgba(191, 219, 247, 0.6)`.
- **Ghost Button:** Borderless, text `#1F7A8C`, hover background `rgba(232, 239, 245, 0.5)`.

### Input Fields
- **Resting:** Background `#FFFFFF`, border `1.5px solid #E8EFF5`, text `#022B3A`, placeholder `#526E7D`, border-radius `10px`, height `44px`.
- **Focus:** Border color `#1F7A8C`, box-shadow `0 0 0 3px rgba(31, 122, 140, 0.15)`.
- **Search Header Bar:** Pill-shaped (`rounded-full`), height `40px`, prefix magnifying glass in `#526E7D`, background `#F4F7F9` on white headers or `rgba(255, 255, 255, 0.12)` with white text on dark headers.

### App Launcher (9-Dot Grid Modal)
- **Trigger:** `40x40px` circular or rounded-xl button with 9 geometric dots (3x3 matrix) in `#022B3A` or `#FFFFFF`.
- **Dropdown Panel:** Positioned anchored top-right, dimensions `340px x 380px`, background `#FFFFFF`, border radius `16px`, padding `16px`. Hosts a 3x3 icon grid with labels underneath (`Dashboard`, `Inventario`, `Ventas`, `Recetas`, `Pacientes`, `Caja`, `Reportes`, `Proveedores`, `Ajustes`).
- **Module Tile:** Flex column, centered, padding `12px`, border-radius `12px`, hover background `rgba(191, 219, 247, 0.35)`, transition `transform 150ms ease, background 150ms ease`.

### Cards & Content Surfaces
- **Base Card:** Background `#FFFFFF`, border `1px solid #E8EFF5`, padding `20px` to `24px`, border radius `16px`.
- **Catalog Card (Public Portal):** Image container with soft `#F4F7F9` background, `#022B3A` titles, `#1F7A8C` pricing highlights, and a quick-add action button.

### Split Auth Views (Login & Register)
- **Left Panel (Brand & Visual Context):** Background `#BFDBF7`, featuring micro-illustrations or high-end typographic pharmacy credos, organic soft geometry, and friendly assurance messaging.
- **Right Panel (Form Workspace):** Centered layout, max-width `420px`, containing clean typography, stacked input groups, persistent remember-me checkbox, secondary links (`¿Olvidaste tu contraseña?`, `Registrarme`), and the primary submit button in `#1F7A8C`.

### Badges & Medication Status Tags
- **En Stock / Activo:** Background `#E6F7F2`, text `#0F6B4F`, border `1px solid #B8EBD9`.
- **Bajo Stock / Precaución:** Background `#FEF6E7`, text `#A05E03`, border `1px solid #FCDCA7`.
- **Receta Médica Obligatoria:** Background `rgba(191, 219, 247, 0.4)`, text `#022B3A`, border `1px solid #BFDBF7`.