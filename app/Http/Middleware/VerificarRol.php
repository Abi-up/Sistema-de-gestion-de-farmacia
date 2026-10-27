<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class VerificarRol
{
    /**
     * Maneja una solicitud entrante y verifica si el usuario autenticado posee
     * al menos uno de los roles especificados y activos.
     *
     * @param  Closure(Request): (Response)  $next
     * @param  string  ...$roles  Nombres de roles autorizados (separados por coma, barra vertical o como argumentos múltiples).
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
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

        // 3. Procesar y normalizar los roles solicitados
        $allowedRoles = collect($roles)
            ->flatMap(fn (string $role) => preg_split('/[,|]/', $role))
            ->map(fn (string $role) => trim($role))
            ->filter()
            ->values();

        if ($allowedRoles->isEmpty()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Acceso denegado. No se especificó ningún rol autorizado para esta ruta.',
                ], 403);
            }

            abort(403, 'Acceso denegado. No se especificó ningún rol autorizado para esta ruta.');
        }

        // 4. Obtener los nombres de roles activos del usuario mediante la relación Eloquent
        $userRoleNames = $user->roles
            ->filter(fn ($rol) => (bool) $rol->activo)
            ->pluck('nombre')
            ->map(fn ($nombre) => $this->normalizeString((string) $nombre));

        // 5. Validar si el usuario posee al menos uno de los roles autorizados
        $hasAuthorizedRole = $allowedRoles->contains(function (string $allowedRole) use ($userRoleNames) {
            return $userRoleNames->contains($this->normalizeString($allowedRole));
        });

        if (! $hasAuthorizedRole) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Acceso no autorizado. No posee el rol requerido para realizar esta acción.',
                ], 403);
            }

            abort(403, 'Acceso no autorizado. No posee el rol requerido para realizar esta acción.');
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
