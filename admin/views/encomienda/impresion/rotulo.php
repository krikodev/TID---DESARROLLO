<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
        }

        /* ── Rótulo ─────────────────────────────────── */
        .rotulo {
            width: 100mm;
            min-height: 150mm;
            border: 2px solid #000;
            display: flex;
            flex-direction: column;
            margin-bottom: 10px;
        }

        /* ── Header ─────────────────────────────────── */
        .header {
            border-bottom: 2px solid #000;
            display: flex;
        }

        .header div {
            width: 50%;
            padding: 8px;
            font-size: 22px;
            font-weight: bold;
        }

        .header span {
            display: block;
            font-size: 30px;
            margin-top: 3px;
        }

        /* ── Destinatario / Remitente ────────────────── */
        .destinatario {
            padding: 8px;
            border-bottom: 2px solid #000;
        }

        .destinatario h2 {
            font-size: 16px;
            margin-bottom: 4px;
        }

        .destinatario table {
            width: 100%;
            font-size: 14px;
            border-collapse: collapse;
        }

        .destinatario td {
            padding: 2px 0;
        }

        .nombre {
            font-size: 18px;
            font-weight: bold;
        }

        .nombre_remitente .numero_doc_remitente {
            font-size: 15px;
            font-weight: bold;
        }

        .telefono {
            font-size: 16px;
        }

        /* ── Productos ───────────────────────────────── */
        .productos {
            padding: 8px;
            border-bottom: 2px solid #000;
        }

        .productos h3 {
            font-size: 16px;
            margin-bottom: 5px;
        }

        .productos table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        .productos th {
            background: #eee;
            border: 1px solid #000;
            padding: 3px 4px;
        }

        .productos td {
            border: 1px solid #000;
            padding: 3px 4px;
        }

        .cantidad {
            width: 55px;
            text-align: center;
        }

        /* ── Documento / Barcode ─────────────────────── */
        .documento {
            padding: 8px;
            border-bottom: 2px solid #000;
        }

        .documento h3 {
            font-size: 16px;
            margin-bottom: 5px;
        }

        .codigo {
            text-align: center;
        }

        .codigo svg {
            width: 100%;
            height: 60px;
        }

        .numero {
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 2px;
            margin-top: 3px;
        }

        /* ── Footer ──────────────────────────────────── */
        .footer {
            display: flex;
            height: 90px;
            margin-top: auto;
            /* pega al fondo */
        }

        .logo {
            width: 40%;
            background: #8e8e8e;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 5px;
        }

        .infoQR {
            width: 60%;
            display: flex;
            align-items: center;
            justify-content: space-around;
            padding: 4px;
        }

        .infoQR img {
            width: 78px;
            height: 78px;
        }

        .tracking {
            font-size: 10px;
            width: 85px;
            word-break: break-all;
        }

        /* ── Impresión ───────────────────────────────── */
        @media print {
            .rotulo {
                margin-bottom: 0;
                page-break-after: always;
            }

            .rotulo:last-child {
                page-break-after: avoid;
            }
        }
    </style>
</head>

<script>
    window.addEventListener("load", () => {
        window.print();
    });
</script>

<body>

    <?php
    $info = $this->data['data'];
    $total = $info['total_rotulos'];
    ?>

    <?php foreach ($info['rotulos'] as $index => $rotulo): ?>
        <?php $posicion = $index + 1; ?>

        <div class="rotulo">

            <!-- ORIGEN / DESTINO -->
            <div class="header">
                <div>
                    ORIGEN:
                    <span><?= strtoupper($info['origen']) ?></span>
                </div>
                <div>
                    DESTINO:
                    <span><?= strtoupper($info['destino']) ?></span>
                </div>
            </div>

            <!-- REMITENTE -->
            <div class="destinatario">
                <h2>REMITENTE:</h2>
                <table>
                    <tr>
                        <td width="80">Nombre:</td>
                        <td class="nombre_remitente"><?= strtoupper($info['remitente']) ?></td>
                    </tr>
                    <tr>
                        <td><?= $info['tipo_documento_rem'] ?>:</td>
                        <td class="numero_doc_remitente"><?= $info['doc_remitente'] ?></td>
                    </tr>
                </table>
            </div>

            <!-- DESTINATARIO -->
            <div class="destinatario">
                <h2>DESTINATARIO:</h2>
                <table>
                    <tr>
                        <td width="80">Nombre:</td>
                        <td class="nombre"><?= strtoupper($info['destinatario']) ?></td>
                    </tr>
                    <tr>
                        <td><?= $info['tipo_documento_dest'] ?>:</td>
                        <td class="nombre"><?= $info['doc_destinatario'] ?></td>
                    </tr>
                    <tr>
                        <td>Celular:</td>
                        <td class="telefono"><?= $info['telefono'] ?></td>
                    </tr>
                </table>
            </div>

            <!-- CONTENIDO -->
            <div class="productos">
                <h3>
                    CONTENIDO
                    <small style="font-weight:normal; font-size:13px;">
                        — Bulto <?= $posicion ?>/<?= $total ?>
                    </small>
                </h3>
                <table>
                    <tr>
                        <th>Descripción</th>
                        <th class="cantidad">Cant.</th>
                    </tr>
                    <tr>
                        <td><?= strtoupper($rotulo['descripcion']) ?></td>
                        <td class="cantidad">
                            <?= $rotulo['unidad'] ?>/<?= $rotulo['cantidad'] ?>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- DOCUMENTO + BARCODE -->
            <div class="documento">
                <h3>DOCUMENTO <?= $rotulo['codigo'] ?></h3>
                <div class="codigo">
                    <?= $rotulo['barcode'] /* SVG directo */ ?>
                    <div class="numero"><?= $rotulo['codigo'] ?></div>
                </div>
            </div>

            <!-- FOOTER -->
            <div class="footer">

                <div class="logo">
                    <img src="<?= URL . $info['logo_empresa'] ?>">
                </div>

                <div class="infoQR">
                    <img src="<?= $info['qr'] ?>">
                    <div class="tracking">
                        <b>TRACKING</b><br><br>
                        <?= $info['tracking'] ?>
                    </div>
                </div>

            </div>

        </div>

    <?php endforeach; ?>

</body>
<script>
    window.addEventListener("load", () => {
        window.print();
    });

    window.addEventListener("afterprint", () => {
        window.close();
    });
</script>

</html>