<?php
declare(strict_types=1);
namespace TidApi;

final class ApiException extends \RuntimeException
{
    public function __construct(public readonly int $status, public readonly string $errorCode, string $message) { parent::__construct($message); }
}

final class Request
{
    public function __construct(public readonly string $method, public readonly string $path, public readonly array $headers = [], public readonly array $data = [], public readonly string $ip = '127.0.0.1', public readonly bool $https = false) {}
    public function header(string $name): string
    {
        foreach ($this->headers as $key => $value) { if (strcasecmp($key, $name) === 0) { return (string)$value; } }
        return '';
    }
    public function text(string $key, int $max = 100, bool $required = true): string
    {
        $value = $this->data[$key] ?? '';
        if (!is_string($value) || strlen($value) > $max || ($required && trim($value) === '')) {
            throw new ApiException(422, 'VALIDATION_ERROR', 'Parámetro inválido: ' . $key);
        }
        return trim($value);
    }
    public function integer(string $key, int $default, int $max): int
    {
        $value = $this->data[$key] ?? $default;
        if (filter_var($value, FILTER_VALIDATE_INT) === false || (int)$value < 1 || (int)$value > $max) {
            throw new ApiException(422, 'VALIDATION_ERROR', 'Parámetro inválido: ' . $key);
        }
        return (int)$value;
    }
    public function date(string $key): string
    {
        $value = $this->text($key, 10);
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        if (!$date || $date->format('Y-m-d') !== $value) { throw new ApiException(422, 'VALIDATION_ERROR', 'Fecha inválida: ' . $key); }
        return $value;
    }
    public static function fromGlobals(): self
    {
        $headers = function_exists('getallheaders') ? getallheaders() : [];
        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) { $headers[str_replace('_', '-', substr($key, 5))] = $value; }
        }
        $headers['Content-Type'] = $_SERVER['CONTENT_TYPE'] ?? '';
        $headers['Authorization'] = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? $headers['Authorization'] ?? '';
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $data = $_GET;
        if ($method === 'POST') {
            if ((int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 65536) { throw new ApiException(413, 'BODY_TOO_LARGE', 'Solicitud demasiado grande.'); }
            $type = strtolower(explode(';', (string)$headers['Content-Type'])[0]);
            if ($type === 'application/json') {
                $raw = file_get_contents('php://input', false, null, 0, 65537);
                if ($raw === false || strlen($raw) > 65536) { throw new ApiException(413, 'BODY_TOO_LARGE', 'Solicitud demasiado grande.'); }
                try { $decoded = json_decode($raw, false, 32, JSON_THROW_ON_ERROR); }
                catch (\JsonException) { throw new ApiException(400, 'INVALID_JSON', 'JSON inválido.'); }
                if (!$decoded instanceof \stdClass) { throw new ApiException(400, 'INVALID_JSON', 'Se requiere un objeto JSON.'); }
                $data = (array)$decoded;
            } elseif ($type === 'application/x-www-form-urlencoded') { $data = $_POST; }
            else { throw new ApiException(415, 'UNSUPPORTED_MEDIA_TYPE', 'Usa application/json.'); }
        }
        $path = (string)parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        // Funciona bajo /mobile-api/public/ en XAMPP y bajo / con php -S.
        $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php')), '/.');
        if ($base !== '' && str_starts_with($path, $base . '/')) { $path = substr($path, strlen($base)); }
        return new self($method, '/' . ltrim($path, '/'), $headers, $data, $_SERVER['REMOTE_ADDR'] ?? '', !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    }
}

final class Response
{
    public function __construct(public readonly int $status, public readonly array $body, public readonly array $headers = []) {}
    public function send(): void
    {
        http_response_code($this->status);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        header('X-Content-Type-Options: nosniff');
        foreach ($this->headers as $key => $value) { header($key . ': ' . $value); }
        if ($this->status !== 204) { echo json_encode($this->body, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR); }
    }
}
