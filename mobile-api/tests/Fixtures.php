<?php
declare(strict_types=1);
function fixture(string $company, bool $evidence = true): PDO
{
    $pdo = class_exists('Pdo\\Sqlite') ? Pdo\Sqlite::connect('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]) : new PDO('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
    if (method_exists($pdo, 'createFunction')) { $pdo->createFunction('CONCAT', static fn(...$values) => implode('', $values)); }
    else { $pdo->sqliteCreateFunction('CONCAT', static fn(...$values) => implode('', $values)); }
    $pdo->exec('CREATE TABLE tp_usuario (id_tp_usuario INTEGER, descripcion TEXT, tipo TEXT)');
    $pdo->exec("INSERT INTO tp_usuario VALUES (1, 'Operador', 'INTERNO'), (2, 'Cliente', 'EXTERNO')");
    $pdo->exec('CREATE TABLE usuario (id_usuario INTEGER PRIMARY KEY, id_terminal INTEGER, id_tp_usuario INTEGER, email TEXT, nombres TEXT, apellidos TEXT, num_docu TEXT, contrasena TEXT, estado INTEGER)');
    $password = base64_encode(openssl_encrypt(' p4ss ', 'AES-256-CBC', hash('sha256', 'test-key'), 0, substr(hash('sha256', 'test-iv'), 0, 16)));
    $insert = $pdo->prepare('INSERT INTO usuario VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $insert->execute([1, 1, 1, 'operador@example.com', $company, 'Operador', '11111111', $password, 1]);
    $insert->execute([2, 2, 2, 'cliente@example.com', 'Cliente', 'Prueba', '22222222', $password, 1]);
    $insert->execute([3, 1, 1, 'inactivo@example.com', 'Inactivo', 'Prueba', '33333333', $password, 0]);
    $insert->execute([4, 2, 1, 'otra@example.com', 'Otra', 'Terminal', '44444444', $password, 1]);
    $pdo->exec('CREATE TABLE terminal (id_terminal INTEGER, nombre TEXT)');
    $pdo->exec("INSERT INTO terminal VALUES (1, 'Huancayo'), (2, 'Lima'), (3, 'Chupaca')");
    $pdo->exec('CREATE TABLE encomienda (id_encomienda INTEGER PRIMARY KEY, codigo TEXT, estado TEXT, pago TEXT, id_remitente INTEGER, id_destinatario INTEGER, id_terminal_origen INTEGER, id_terminal_destino INTEGER, fecha_salida TEXT, fecha_entrega TEXT, fecha_registro TEXT' . ($evidence ? ', fotos_evidencia TEXT' : '') . ')');
    $statement = $pdo->prepare('INSERT INTO encomienda VALUES (' . implode(',', array_fill(0, $evidence ? 12 : 11, '?')) . ')');
    foreach ([
        [1, 'PENDIENTE', 'EN DESTINO', 'PAGADO'],
        [2, 'BLOQUE', 'EN DESTINO', 'PAGO EN BLOQUE'],
        [3, 'IMPAGO', 'EN DESTINO', 'POR PAGAR'],
        [4, 'OTRA-TERMINAL', 'EN DESTINO', 'PAGADO'],
        [5, 'ENTREGADO', 'ENTREGADO', 'PAGADO'],
        [6, 'AYER', 'ENTREGADO', 'PAGADO'],
    ] as $row) {
        $values = [$row[0], $row[1], $row[2], $row[3], 1, 2, 2, $row[0] === 4 ? 3 : 1, '2026-10-02 10:00:00', $row[0] === 5 ? '2026-10-03 11:00:00' : ($row[0] === 6 ? '2026-10-02 11:00:00' : null), '2026-10-03 09:00:00'];
        if ($evidence) { $values[] = $row[0] === 5 ? 'https://images.example.com/a.jpg,https://images.example.com/b.jpg' : null; }
        $statement->execute($values);
    }
    $pdo->exec('CREATE TABLE producto_encomienda (id_encomienda INTEGER, descripcion TEXT, obs TEXT)');
    $pdo->exec("INSERT INTO producto_encomienda VALUES (1, 'Caja', 'Frágil'), (1, 'Bolsa', 'Sellada'), (5, 'Caja entregada', '')");
    $pdo->exec('CREATE TABLE venta (id_venta INTEGER, id_terminal INTEGER, serie TEXT, correlativo TEXT, fecha_emision TEXT, fecha_registro TEXT)');
    $pdo->exec("INSERT INTO venta VALUES (1, 2, 'B001', '123', '2026-10-03 00:00:00', '2026-10-03 09:00:00')");
    $pdo->exec('CREATE TABLE dt_venta (id_venta INTEGER, id_encomienda INTEGER)');
    $pdo->exec('INSERT INTO dt_venta VALUES (1, 1), (1, 1), (1, 2)');
    return $pdo;
}
