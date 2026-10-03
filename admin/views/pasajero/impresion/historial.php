<?php
//ob_start();
require_once('public/plugins/tc/tcpdf.php');
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);

class PassengerTicket extends TCPDF
{
    // Passenger and travel data
    private $empresaData;
    private $clientData;
    private $travelData;

    public function __construct($empresaData, $clientData, $travelData)
    {
        parent::__construct();
        $this->empresaData = $empresaData;
        $this->clientData = $clientData;
        $this->travelData = $travelData;
    }

    public function Header()
    {
        if ($this->empresaData["logo"] && file_exists($this->empresaData["logo"])) {
            $this->Image($this->empresaData["logo"], 8, 5, 35, 15);
        }
        // Add logo or header if needed
        $this->SetFont('helvetica', 'B', 12);
        $this->Cell(0, 19, 'HISTORIAL DE ' . $this->clientData['cliente_nombres'] . ' ' . $this->clientData['cliente_apellidos'], 0, 1, 'C');
    }

    public function generateTicket()
    {
        $this->AddPage();
        $this->SetFont('helvetica', '', 10);

        // Client Information Section
        $this->Ln(14);
        $this->SetFont('helvetica', 'B', 8);
        $this->SetX(5);
        $this->Cell(40, 0, "NOMBRES Y APELLIDOS: ", 0, 0);
        $this->SetFont('helvetica', '', 8);
        $this->Cell(90, 0, $this->clientData['cliente_nombres'] . " " . $this->clientData['cliente_apellidos'], 0, 0);
        $this->SetFont('helvetica', 'B', 8);
        $this->Cell(20, 0, "DNI:", 0, 0);
        $this->SetFont('helvetica', '', 8);
        $this->Cell(46, 0, $this->clientData['cliente_num_docu'], 0, 1);
        $this->SetFont('helvetica', 'B', 8);
        $this->SetX(5);
        $this->Cell(40, 0, "DIRECCION:", 0, 0);
        $this->SetFont('helvetica', '', 8);
        $this->Cell(90, 0, $this->clientData['cliente_direccion'], 0, 0);
        $this->SetFont('helvetica', 'B', 8);
        $this->Cell(30, 0, "NACIONALIDAD:", 0, 0);
        $this->SetFont('helvetica', '', 8);
        $this->Cell(46, 0, $this->clientData['cliente_nacionalidad'], 0, 1);
        $this->SetFont('helvetica', 'B', 8);
        $this->SetX(5);
        $this->Cell(40, 0, "FECHA NACIMIENTO: ", 0, 0);
        $this->SetFont('helvetica', '', 8);
        $this->Cell(46, 0, $this->clientData['cliente_fecha_nacimiento'], 0, 1);

        $this->Ln(2);
        $this->SetFont('helvetica', 'B', 7);
        $this->SetX(5);
        $this->Cell(50, 0, 'VENDEDOR', 1, 0, 'C');
        $this->Cell(20, 0, 'SERIE - C.', 1, 0, 'C');
        $this->Cell(20, 0, 'FECHA E.', 1, 0, 'C');
        $this->Cell(35, 0, 'ORIGEN', 1, 0, 'C');
        $this->Cell(35, 0, 'DESTINO', 1, 0, 'C');
        $this->Cell(20, 0, 'FECHA S.', 1, 0, 'C');
        $this->Cell(18, 0, 'HORA S.', 1, 1, 'C');
        $this->SetFont('helvetica', '', 7);

        foreach ($this->travelData as $viaje) {
            $this->SetX(5);
            $this->Cell(50, 5, $viaje['vendedor_nombres'] . " " . $viaje['vendedor_apellidos'], 1, 0, 'L');
            $this->Cell(20, 5, $viaje["serie"] . " - " . str_pad($viaje["correlativo"], 7, "0", STR_PAD_LEFT), 1, 0, 'L');
            $this->Cell(20, 5, $viaje['fecha_emision_format'], 1, 0, 'C');
            $this->Cell(35, 5, $viaje["terminal_origen"], 1, 0, 'C');
            $this->Cell(35, 5, $viaje["terminal_destino"], 1, 0, 'C');
            $this->Cell(20, 5, $viaje["programacion_fecha_salida"], 1, 0, 'C');
            $this->Cell(18, 5, $viaje["programacion_hora_salida"], 1, 1, 'C');
        }

    }

    public function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Página ' . $this->getAliasNumPage() . ' de ' . $this->getAliasNbPages(), 0, 0, 'C');
    }
}

// Example usage
$empresaData = $this->data['empresa'];
$clientData = $this->data['cliente'];
$travelData = $this->data['viajes'];

$pdf = new PassengerTicket($empresaData, $clientData, $travelData);
$pdf->generateTicket();
$pdf->Output('historial.pdf', 'I');
//ob_end_clean();
