<?php
require_once __DIR__ . '/desbloqueador_asientos.php'; 

$generador = new GestorReservas();
$resultado = $generador->revisar_asientos();

$logFile = __DIR__ . '/cron_log.txt';

file_put_contents(
    $logFile,
    date('Y-m-d H:i:s') . " - Resultado: " . json_encode($resultado) . "\n",
    FILE_APPEND
);

echo "Proceso completado.\n";