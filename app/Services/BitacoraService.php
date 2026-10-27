<?php

namespace App\Services;

use App\Models\Bitacora;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Throwable;

class BitacoraService
{
    /**
     * Palabras clave sensibles que deben ofuscarse o suprimirse de los registros.
     */
    protected const CLAVES_SENSIBLES = [
        'password',
        'contrasena',
        'contrasena_hash',
        'token',
        'remember_token',
        'secret',
        'key',
    ];

    /**
     * Registra un evento de auditoría de forma segura y tolerante a fallos.
     * Garantiza que una eventualidad en la bitácora jamás aborte la transacción principal de negocio.
     *
     * @param User|int $usuario
     * @param string $modulo Nombre del módulo funcional (ej: 'INVENTARIO', 'VENTAS', 'COMPRAS')
     * @param string $accion Verbo descriptivo de la acción (ej: 'CREAR', 'CANCELAR', 'DISPENSAR')
     * @param string $descripcion Detalle claro del evento
     * @param array<string, mixed> $metadatos Parámetros adicionales a registrar
     * @return Bitacora|null
     */
    public function registrar(
        User|int $usuario,
        string $modulo,
        string $accion,
        string $descripcion,
        array $metadatos = []
    ): ?Bitacora {
        try {
            $usuarioId = $usuario instanceof User ? $usuario->id : $usuario;

            $descripcionFinal = trim($descripcion);
            if (!empty($metadatos)) {
                $metadatosSanitizados = $this->sanitizarDatosSensibles($metadatos);
                $descripcionFinal .= ' | Datos: ' . json_encode($metadatosSanitizados, JSON_UNESCAPED_UNICODE);
            }

            return Bitacora::create([
                'usuario_id' => $usuarioId,
                'fecha_hora' => Carbon::now(),
                'modulo' => strtoupper(substr(trim($modulo), 0, 50)),
                'accion' => strtoupper(substr(trim($accion), 0, 100)),
                'descripcion' => $descripcionFinal,
            ]);
        } catch (Throwable $e) {
            // Se registra el fallo en el log del sistema para no derribar la transacción primaria
            Log::error('Fallo al registrar entrada en bitácora: ' . $e->getMessage(), [
                'modulo' => $modulo,
                'accion' => $accion,
            ]);

            return null;
        }
    }

    /**
     * Consulta registros de auditoría filtrados por módulo y rango de fechas.
     *
     * @param string|null $modulo
     * @param string|null $fechaInicio
     * @param string|null $fechaFin
     * @param int $limite
     * @return Collection<int, Bitacora>
     */
    public function consultar(
        ?string $modulo = null,
        ?string $fechaInicio = null,
        ?string $fechaFin = null,
        int $limite = 100
    ): Collection {
        return Bitacora::query()
            ->with('usuario')
            ->when($modulo !== null, fn ($q) => $q->where('modulo', strtoupper($modulo)))
            ->when($fechaInicio !== null, fn ($q) => $q->where('fecha_hora', '>=', Carbon::parse($fechaInicio)->startOfDay()))
            ->when($fechaFin !== null, fn ($q) => $q->where('fecha_hora', '<=', Carbon::parse($fechaFin)->endOfDay()))
            ->orderBy('fecha_hora', 'desc')
            ->limit(max(1, min(500, $limite)))
            ->get();
    }

    /**
     * Elimina recursivamente contraseñas, tokens y claves sensibles de los metadatos.
     *
     * @param array<string, mixed> $datos
     * @return array<string, mixed>
     */
    protected function sanitizarDatosSensibles(array $datos): array
    {
        $sanitizado = [];

        foreach ($datos as $clave => $valor) {
            $claveLower = strtolower((string) $clave);
            $esSensible = false;

            foreach (self::CLAVES_SENSIBLES as $patron) {
                if (str_contains($claveLower, $patron)) {
                    $esSensible = true;
                    break;
                }
            }

            if ($esSensible) {
                $sanitizado[$clave] = '********';
            } elseif (is_array($valor)) {
                $sanitizado[$clave] = $this->sanitizarDatosSensibles($valor);
            } else {
                $sanitizado[$clave] = $valor;
            }
        }

        return $sanitizado;
    }
}
