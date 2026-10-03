<?php

class ApiPasarela
{

    private $merchantCode = CODIGO_MERCH;
    private $publicKey = KEY_PASARELA;
    private $baseUrl;

    public function __construct($sandbox = true)
    {
        $this->baseUrl = $sandbox ?
            'https://sandbox-api-pw.izipay.pe' :
            'https://api-pw.izipay.pe';
    }

    public function generar_numero_orden()
    {
        $numero =  "R" . date('YmdHis');
        return $numero;
    }

    /**
     * Genera un token para procesar el pago con Izipay
     * 
     * @param string $orderNumber Número de orden único
     * @param float $amount Monto del pago
     * @param string $currency Moneda (por defecto PEN)
     * @return array Respuesta de la API
     */
    public function generarToken_pasarela($data)
    {
        $orderNumber = $this->generar_numero_orden();
        $amount =  $data['monto_pagar'];
        $currency = 'PEN';

        try {
            // Generar transactionId único
            $transactionId = $this->generateTransactionId();

            // URL del endpoint
            $url = $this->baseUrl . '/gateway/api/v1/proxy-cors/' .
                $this->baseUrl . '/security/v1/Token/Generate';

            // Datos para la solicitud
            $data = [
                'requestSource' => 'ECOMMERCE',
                'merchantCode' => $this->merchantCode,
                'orderNumber' => $orderNumber,
                'publicKey' => $this->publicKey,
                'amount' => number_format($amount, 2, '.', ''),
                'currency' => $currency
            ];

            // Headers
            $headers = [
                'Accept: application/json',
                'Content-Type: application/json',
                'transactionId: ' . $transactionId
            ];

            // Inicializar cURL
            $ch = curl_init();

            // Configurar cURL
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($data),
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2
            ]);

            // Ejecutar la solicitud
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            // Verificar errores de cURL
            if (curl_errno($ch)) {
                $error = curl_error($ch);
                curl_close($ch);
                throw new Exception('Error cURL: ' . $error);
            }

            curl_close($ch);

            // Decodificar respuesta
            $responseData = json_decode($response, true);

            if ($httpCode !== 200) {
                throw new Exception('Error API: ' . $httpCode . ' - ' . $response);
            }

            // Retornar respuesta estructurada
            return [
                'success' => true,
                'transactionId' => $transactionId,
                'data' => $responseData,
                'httpCode' => $httpCode
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Genera un ID de transacción único
     * 
     * @return string
     */
    private function generateTransactionId()
    {
        return time() . rand(1000, 9999);
    }

    /**
     * Procesa el pago completo (ejemplo de uso)
     * 
     * @param array $paymentData Datos del pago
     * @return array
     */
    public function procesarPago($paymentData)
    {
        // Validar datos requeridos
        $required = ['orderNumber', 'amount', 'customerEmail'];
        foreach ($required as $field) {
            if (!isset($paymentData[$field]) || empty($paymentData[$field])) {
                return [
                    'success' => false,
                    'error' => "Campo requerido faltante: {$field}"
                ];
            }
        }

        // Generar token
        $tokenResponse = $this->generarToken_pasarela(
            $paymentData['orderNumber'],
            $paymentData['amount'],
            $paymentData['currency'] ?? 'PEN'
        );

        if (!$tokenResponse['success']) {
            return $tokenResponse;
        }

        // Aquí puedes agregar lógica adicional para procesar el pago
        // Por ejemplo, redirigir al usuario a la página de pago de Izipay

        return $tokenResponse;
    }
}

// Ejemplo de uso
try {
    // Configuración (usa tus credenciales reales)
    $merchantCode = "4007701";
    $publicKey = "VErethUtraQuxas57wuMuquprADrAHAb";

    // Inicializar la clase
    $izipay = new ApiPasarela($merchantCode, $publicKey, true); // true para sandbox

    // Generar token
    $result = $izipay->generarToken_pasarela(15.00);

    if ($result['success']) {
        echo "Token generado exitosamente:\n";
        echo "Transaction ID: " . $result['transactionId'] . "\n";
        echo "Respuesta: " . json_encode($result['data'], JSON_PRETTY_PRINT) . "\n";
    } else {
        echo "Error: " . $result['error'] . "\n";
    }
} catch (Exception $e) {
    echo "Error general: " . $e->getMessage() . "\n";
}

// Ejemplo de uso con formulario de pago
function mostrarFormularioPago($token, $amount)
{
?>
    <form id="payment-form">
        <input type="hidden" id="token" value="<?php echo htmlspecialchars($token); ?>">
        <input type="hidden" id="amount" value="<?php echo htmlspecialchars($amount); ?>">

        <div>
            <label>Número de tarjeta:</label>
            <input type="text" id="card-number" placeholder="1234 5678 9012 3456" required>
        </div>

        <div>
            <label>Fecha de expiración:</label>
            <input type="text" id="expiry-date" placeholder="MM/YY" required>
        </div>

        <div>
            <label>CVV:</label>
            <input type="text" id="cvv" placeholder="123" required>
        </div>

        <button type="submit">Pagar S/. <?php echo number_format($amount, 2); ?></button>
    </form>

    <script>
        document.getElementById('payment-form').addEventListener('submit', function(e) {
            e.preventDefault();
            // Aquí integrarías con el SDK de Izipay para procesar el pago
            console.log('Procesando pago con token:', document.getElementById('token').value);
        });
    </script>
<?php
}

?>