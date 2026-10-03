<?php
declare(strict_types=1);
namespace TidApi;

final class Repository
{
    private bool $hasEvidence;
    public function __construct(private readonly \PDO $pdo, private readonly array $tenant)
    {
        $columns = $pdo->getAttribute(\PDO::ATTR_DRIVER_NAME) === 'mysql'
            ? array_column($pdo->query('SHOW COLUMNS FROM encomienda')->fetchAll(), 'Field')
            : array_column($pdo->query('PRAGMA table_info(encomienda)')->fetchAll(), 'name');
        $this->hasEvidence = in_array('fotos_evidencia', $columns, true);
    }
    public function health(): void
    {
        foreach ([
            'SELECT id_usuario, id_terminal, id_tp_usuario, email, contrasena, estado, nombres, apellidos, num_docu FROM usuario LIMIT 0',
            'SELECT id_tp_usuario, descripcion, tipo FROM tp_usuario LIMIT 0',
            'SELECT id_encomienda, codigo, estado, pago, id_remitente, id_destinatario, id_terminal_origen, id_terminal_destino, fecha_salida, fecha_entrega, fecha_registro FROM encomienda LIMIT 0',
            'SELECT id_encomienda, descripcion, obs FROM producto_encomienda LIMIT 0',
            'SELECT id_terminal, nombre FROM terminal LIMIT 0',
            'SELECT id_venta, id_terminal, serie, correlativo, fecha_emision, fecha_registro FROM venta LIMIT 0',
            'SELECT id_venta, id_encomienda FROM dt_venta LIMIT 0',
        ] as $sql) { $this->pdo->query($sql)->closeCursor(); }
    }
    public function user(?string $email = null, ?int $id = null): ?array
    {
        $where = $email !== null ? 'u.email = ?' : 'u.id_usuario = ?';
        $stmt = $this->pdo->prepare('SELECT u.id_usuario, u.id_terminal, u.id_tp_usuario, u.email, u.nombres, u.apellidos, u.num_docu, u.contrasena, tp.descripcion AS tp_usuario FROM usuario u INNER JOIN tp_usuario tp ON tp.id_tp_usuario = u.id_tp_usuario WHERE u.estado = 1 AND tp.tipo IN (\'INTERNO\', \'DESARROLLO\') AND ' . $where . ' LIMIT 2');
        $stmt->execute([$email ?? $id]);
        $users = $stmt->fetchAll();
        // No elegir arbitrariamente una de varias cuentas con el mismo correo.
        if (count($users) !== 1) { return null; }
        $user = $users[0];
        if ($this->tenant['scope'] === 'terminal' && (int)$user['id_terminal'] <= 0) { return null; }
        return $user;
    }
    private function select(): string
    {
        $evidence = $this->hasEvidence ? 'e.fotos_evidencia' : 'NULL';
        return "SELECT e.id_encomienda, e.codigo AS tracking, e.codigo AS traking, e.estado, e.pago AS estado_pago,
            CONCAT(ur.nombres, ' ', ur.apellidos) AS remitente, ur.num_docu AS documentoRem,
            CONCAT(ud.nombres, ' ', ud.apellidos) AS destinatario, ud.num_docu AS documentoDes,
            tor.nombre AS origen, tde.nombre AS destino, e.fecha_registro, e.fecha_salida, e.fecha_entrega,
            $evidence AS fotos_evidencia,
            (SELECT GROUP_CONCAT(pe.descripcion) FROM producto_encomienda pe WHERE pe.id_encomienda = e.id_encomienda) AS producto,
            (SELECT GROUP_CONCAT(pe.obs) FROM producto_encomienda pe WHERE pe.id_encomienda = e.id_encomienda) AS observacion
            FROM encomienda e
            INNER JOIN usuario ur ON ur.id_usuario = e.id_remitente
            INNER JOIN usuario ud ON ud.id_usuario = e.id_destinatario
            LEFT JOIN terminal tor ON tor.id_terminal = e.id_terminal_origen
            LEFT JOIN terminal tde ON tde.id_terminal = e.id_terminal_destino";
    }
    private function scope(array $user, array &$params): string
    {
        if ($this->tenant['scope'] === 'company') { return ''; }
        $params[] = (int)$user['id_terminal'];
        $params[] = (int)$user['id_terminal'];
        return ' AND (e.id_terminal_origen = ? OR e.id_terminal_destino = ?)';
    }
    private function rows(string $sql, array $params, int $page, int $limit): array
    {
        $offset = ($page - 1) * $limit;
        $stmt = $this->pdo->prepare($sql . ' LIMIT ' . ($limit + 1) . ' OFFSET ' . $offset);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
        $more = count($rows) > $limit;
        return ['data' => array_slice($rows, 0, $limit), 'pagination' => ['page' => $page, 'limit' => $limit, 'has_more' => $more]];
    }
    public function pending(string $dni, array $user, int $page, int $limit): array
    {
        $params = [$dni];
        $scope = '';
        if ($this->tenant['scope'] === 'terminal') { $scope = ' AND e.id_terminal_destino = ?'; $params[] = (int)$user['id_terminal']; }
        return $this->rows($this->select() . " WHERE ud.num_docu = ? AND e.estado = 'EN DESTINO' AND e.pago IN ('PAGADO', 'PAGO EN BLOQUE')" . $scope . ' ORDER BY e.fecha_registro DESC, e.id_encomienda DESC', $params, $page, $limit);
    }
    public function history(string $date, string $filter, array $user, int $page, int $limit): array
    {
        $params = [$date . ' 00:00:00', (new \DateTimeImmutable($date))->modify('+1 day')->format('Y-m-d') . ' 00:00:00'];
        $sql = $this->select() . " WHERE e.estado = 'ENTREGADO' AND e.fecha_entrega >= ? AND e.fecha_entrega < ?";
        if ($filter !== '') { $sql .= ' AND (e.codigo LIKE ? OR ud.num_docu LIKE ?)'; $params[] = '%' . $filter . '%'; $params[] = '%' . $filter . '%'; }
        $sql .= $this->scope($user, $params);
        return $this->rows($sql . ' ORDER BY e.fecha_entrega DESC, e.id_encomienda DESC', $params, $page, $limit);
    }
    public function tracking(string $code, array $user): array
    {
        $params = [$code];
        return $this->rows($this->select() . ' WHERE e.codigo = ?' . $this->scope($user, $params) . ' ORDER BY e.id_encomienda', $params, 1, 100);
    }
    public function summary(string $date, array $user): array
    {
        $params = [$date . ' 00:00:00', (new \DateTimeImmutable($date))->modify('+1 day')->format('Y-m-d') . ' 00:00:00'];
        $sql = "SELECT COUNT(*) FROM encomienda e WHERE e.estado = 'ENTREGADO' AND e.fecha_entrega >= ? AND e.fecha_entrega < ?" . $this->scope($user, $params);
        $stmt = $this->pdo->prepare($sql); $stmt->execute($params);
        $delivered = (int)$stmt->fetchColumn();
        $params = [];
        $sql = "SELECT COUNT(*) FROM encomienda e WHERE e.estado = 'EN DESTINO' AND e.pago IN ('PAGADO', 'PAGO EN BLOQUE')";
        if ($this->tenant['scope'] === 'terminal') { $sql .= ' AND e.id_terminal_destino = ?'; $params[] = (int)$user['id_terminal']; }
        $stmt = $this->pdo->prepare($sql); $stmt->execute($params);
        return ['fecha' => $date, 'entregados' => $delivered, 'pendientes' => (int)$stmt->fetchColumn(), 'recent' => $this->history($date, '', $user, 1, 3)['data']];
    }
    public function receipt(string $series, string $number, string $date, array $user, int $page, int $limit): array
    {
        $params = [$series, $number, $date . ' 00:00:00', (new \DateTimeImmutable($date))->modify('+1 day')->format('Y-m-d') . ' 00:00:00'];
        $sql = $this->select() . ' WHERE EXISTS (SELECT 1 FROM dt_venta dv INNER JOIN venta v ON v.id_venta = dv.id_venta WHERE dv.id_encomienda = e.id_encomienda AND v.serie = ? AND v.correlativo = ? AND v.fecha_emision >= ? AND v.fecha_emision < ?)';
        return $this->rows($sql . $this->scope($user, $params) . ' ORDER BY e.id_encomienda', $params, $page, $limit);
    }
}
