<div id="seguimiento-modal" class="seguimiento-modal seguimiento-oculto">
    <div class="seguimiento-contenedor">
        <!--  ENCABEZADO ELEGANTE SUPERIOR -->
        <div class="seguimiento-encabezado-superior">
            <h2 class="seguimiento-titulo-superior">REALIZA TU <span>SEGUIMIENTO</span></h2>
        </div>
        <!--  CONTENIDO PRINCIPAL -->
        <div class="seguimiento-der" style="width: 100%; padding: 30px;">
            <div class="seguimiento-header">
                <span class="seguimiento-pestana active" onclick="mostrarFormulario('comprobante')">COMPROBANTE DE PAGO</span>
                <span class="seguimiento-pestana active" onclick="mostrarFormulario('tracking')">TRACKING</span>
                <span class="seguimiento-cerrar" onclick="cerrarSeguimientoModal()">×</span>
            </div>
            <!-- FORMULARIO -->
            <form id="formulario">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label><b>Serie</b></label>
                            <input type="text" name="serie" id="serie" class="form-control" placeholder="Ingrese Serie" maxlength="4" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label><b>Correlativo</b></label>
                            <input type="text" name="correlativo" id="correlativo" class="form-control" placeholder="Ingrese Correlativo" maxlength="20" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label><b>Fecha</b></label>
                            <input type="date" name="fecha" id="fecha" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-12 mt-3">
                        <div class="form-group">
                            <button type="button" name="buscar" id="buscar" class="btn btn-primary" onclick="BuscarEnco()">Buscar</button>
                            <button type="button" class="btn btn-success" onclick="document.getElementById('formulario').reset()">Limpiar</button>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Traking -->
            <form id="tracking" class="seguimiento-form seguimiento-oculto">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label><b>Tracking</b></label>
                            <input type="text" name="trackingCode" id="trackingCode" class="form-control" placeholder="Ingrese el código de tracking" required>
                        </div>
                    </div>
                    <div class="col-md-12 mt-3">
                        <div class="form-group">
                            <button type="button" name="buscar_Tracking" id="buscar_Tracking" class="btn btn-primary" onclick="BuscarTracking()">Buscar</button>
                            <button type="button" class="btn btn-success" onclick="document.getElementById('tracking').reset()">Limpiar</button>
                        </div>
                    </div>
                </div>
            </form>
            <!-- RESULTADOS -->
            <div class="tabla-contenedor">
                <table class="tabla-consulta">
                    <thead>
                        <tr>
                            <th>DocumentoRem</th>
                            <th>Remitente</th>
                            <th>DocumentoDes</th>
                            <th>Destinatario</th>
                            <th>Terminal</th>
                            <th>Origen</th>
                            <th>Destino</th>
                            <th>Fecha de salida</th>
                            <th>Fecha de Registro</th>
                            <th>Fecha de Entrega</th>
                            <th>Estado</th>
                            <th>Producto</th>
                            <th>Observación</th>
                            <th>Traking</th>
                        </tr>
                    </thead>
                    <tbody id="resultado">
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>