<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class VerificarPermiso
{
    /**
     * Maneja una solicitud entrante y verifica si el usuario autenticado posee
     * al menos uno de los permisos requeridos mediante sus roles activos.
     *
     * @param  Closure(Request): (Response)  $next
     * @param  string  ...$permisos  Nombres de permisos requeridos (separados por coma, barra vertical o como argumentos múltiples).
     */
    public function handle(Request $request, Closure $next, string ...$permisos): Response
    {
        $user = $request->user();

        // 1. Exigir usuario autenticado
        if (! $user) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'No autenticado.',
                ], 401);
            }

            return Route::has('login')
                ? redirect()->guest(route('login'))
                : abort(401, 'No autenticado.');
        }

        // 2. Verificar estado del usuario si el campo existe
        if (isset($user->estado) && strtoupper((string) $user->estado) !== 'ACTIVO') {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Acceso denegado. La cuenta de usuario se encuentra inactiva.',
                ], 403);
            }

            abort(403, 'Acceso denegado. La cuenta de usuario se encuentra inactiva.');
        }

        // 3. Procesar y normalizar los permisos requeridos
        $requiredPermissions = collect($permisos)
            ->flatMap(fn (string $permiso) => preg_split('/[,|]/', $permiso))
            ->map(fn (string $permiso) => trim($permiso))
            ->filter()
            ->values();

        if ($requiredPermissions->isEmpty()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Acceso denegado. No se especificó ningún permiso para validar esta ruta.',
                ], 403);
            }

            abort(403, 'Acceso denegado. No se especificó ningún permiso para validar esta ruta.');
        }

        // 4. Cargar relaciones sin sobrecargar consultas si el modelo existe en la base de datos
        if ($user->exists && (! $user->relationLoaded('roles') || $user->roles->contains(fn ($rol) => ! $rol->relationLoaded('permisos')))) {
            $user->loadMissing('roles.permisos');
        }

        // 5. Extraer los permisos asociados a los roles activos del usuario
        $userPermissions = $user->roles
            ->filter(fn ($rol) => (bool) $rol->activo)
            ->flatMap(fn ($rol) => $rol->permisos)
            ->map(function ($permiso) {
                return [
                    'nombre' => (string) $permiso->nombre,
                    'modulo' => (string) ($permiso->modulo ?? ''),
                ];
            });

        // 6. Validar si el usuario posee al menos uno de los permisos requeridos
        $hasRequiredPermission = $requiredPermissions->contains(function (string $required) use ($userPermissions) {
            $normRequired = $this->normalizeString($required);

            return $userPermissions->contains(function (array $permiso) use ($normRequired) {
                $normNombre = $this->normalizeString($permiso['nombre']);
                $normModulo = $this->normalizeString($permiso['modulo']);

                // Comprobación sobre la columna 'nombre' (ej: 'inventario.consultar' o 'consultar')
                if ($normNombre === $normRequired) {
                    return true;
                }

                // Compatibilidad si la definición combina módulo + acción (ej: modulo='inventario', nombre='consultar')
                if ($normModulo !== '' && "{$normModulo}.{$normNombre}" === $normRequired) {
                    return true;
                }

                return false;
            });
        });

        if (! $hasRequiredPermission) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Acceso denegado. No posee los permisos requeridos para realizar esta acción.',
                ], 403);
            }

            abort(403, 'Acceso denegado. No posee los permisos requeridos para realizar esta acción.');
        }

        return $next($request);
    }

    /**
     * Normaliza una cadena para comparaciones insensibles a mayúsculas/minúsculas y acentos.
     */
    protected function normalizeString(string $value): string
    {
        $clean = mb_strtolower(trim($value));

        return str_replace(
            ['á', 'é', 'í', 'ó', 'ú', 'ä', 'ë', 'ï', 'ö', 'ü'],
            ['a', 'e', 'i', 'o', 'u', 'a', 'e', 'i', 'o', 'u'],
            $clean
        );
    }
}
