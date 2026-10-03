<?php
require __DIR__ . '/../../vendor/autoload.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use React\Socket\Server as ReactSocket;
use React\Socket\SecureServer;

class WebSocketServer implements MessageComponentInterface
{
    protected $clients;
    protected $logger;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;

        // Configuración de Monolog
        $this->logger = new Logger('websocket');
        $this->logger->pushHandler(new StreamHandler(__DIR__ . '/../../logs/websocket.log', Logger::DEBUG));
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);
        $this->logger->info("Nueva conexión: {$conn->resourceId}");
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $this->logger->info("Nuevo mensaje de {$from->resourceId}: $msg");

        foreach ($this->clients as $client) {
            if ($from !== $client) {
                $client->send($msg);
            }
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);
        $this->logger->info("Conexión cerrada: {$conn->resourceId}");
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        $this->logger->error("Error en la conexión {$conn->resourceId}: {$e->getMessage()}");
        $conn->close();
    }
}

// Crear el loop de React
$loop = React\EventLoop\Factory::create();

// Crear el socket sin SSL en puerto 8082 (opcional)
$webSock = new ReactSocket('0.0.0.0:8082', $loop);

// Configurar el socket seguro (wss://) con SSL en puerto 8082
$secureSock = new SecureServer($webSock, $loop, [
    'local_cert' => '/home/admin/conf/web/tid.net.pe/ssl/tid.net.pe.crt',  // Ruta al certificado
    'local_pk' => '/home/admin/conf/web/tid.net.pe/ssl/tid.net.pe.key',    // Ruta a la clave privada
    'allow_self_signed' => false,  // Si es autofirmado, cambiar a true
    'verify_peer' => false        // No verificar el peer en caso de ser autofirmado
]);

// Configurar el servidor WebSocket
$server = new IoServer(
    new HttpServer(
        new WsServer(
            new WebSocketServer()
        )
    ),
    $secureSock,  // Usar el socket seguro
    $loop
);

// Ejecutar el loop del servidor
$loop->run();
