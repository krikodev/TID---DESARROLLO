<?php
function traducir_mes($mes)
{
    $meses = [
        'January' => 'Enero',
        'February' => 'Febrero',
        'March' => 'Marzo',
        'April' => 'Abril',
        'May' => 'Mayo',
        'June' => 'Junio',
        'July' => 'Julio',
        'August' => 'Agosto',
        'September' => 'Septiembre',
        'October' => 'Octubre',
        'November' => 'Noviembre',
        'December' => 'Diciembre'
    ];

    return $meses[$mes] ?? 'Mes no válido';
}

function formato_12_horas($hora_24)
{
    $hora = strtotime($hora_24);
    return date("g:i A", $hora);
}

function formato_fecha_hora_12($fecha): string
{
    return date('d/m/Y h:i A', strtotime($fecha));
}

function normalizar_estado($valor)
{
    if ($valor === null)
        return $valor;

    $v = trim((string) $valor);
    if ($v === '')
        return $v;

    $lower = mb_strtolower($v, 'UTF-8');

    // Si ya es '1' o '0', devolverlo tal cual
    if ($lower === '1' || $lower === '0') {
        return $lower;
    }

    // --- Deshabilitado ---
    // Coincidirá con cualquier cosa que empiece en "desh" o "des"
    if (preg_match('/^des(h.*)?$/i', $lower)) {
        return '0';
    }

    // --- Habilitado ---
    // Coincidirá con cualquier cosa que empiece en "hab"
    if (preg_match('/^hab.*/i', $lower)) {
        return '1';
    }

    // Si no coincide, devolver original (o null si prefieres ignorarlo)
    return $valor;
}

function limpiar_observacion_sunat($texto)
{
    // 1. Quitamos tildes y eñes (según tu lista de recomendaciones)
    $originales = array('á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú', 'ñ', 'Ñ');
    $limpios = array('a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U', 'n', 'N');
    $texto = str_replace($originales, $limpios, $texto);

    // 2. Quitamos saltos de línea y tabulaciones
    $texto = str_replace(array("\r", "\n", "\t"), ' ', $texto);

    // 3. Regex: Mantenemos SOLO letras básicas, números, espacios, puntos, comas y guiones.
    // HE QUITADO los corchetes [] y otros signos que mencionaste como "a evitar".
    $texto_limpio = preg_replace('/[^a-zA-Z0-9\s.,-]/u', '', $texto);

    // 4. Colapsar espacios dobles
    $texto_limpio = preg_replace('/\s+/', ' ', $texto_limpio);

    // 5. Limitar longitud y limpiar bordes
    return htmlspecialchars(mb_substr(trim($texto_limpio), 0, 200, 'UTF-8'), ENT_QUOTES, 'UTF-8');
}

function puedeVerModulo($campoPermiso, $modulos, $permisos)
{
    if (!isset($modulos[$campoPermiso])) {
        return false;
    }

    $modulo = $modulos[$campoPermiso];

    return $modulo["estado"] == 1
        && $modulo["visible_menu"] == 1
        && !empty($permisos[$campoPermiso]);
}

function puedeVerMenu($permisosMenu, $modulos, $permisos)
{
    foreach ($permisosMenu as $permiso) {
        if (puedeVerModulo($permiso, $modulos, $permisos)) {
            return true;
        }
    }

    return false;
}