<div class="app-wrapper">
	<div class="app-content pt-3 p-md-3 p-lg-4">
		<div class="col-md-12 mb-3">
			<div class="app-card app-card-stat shadow-sm h-100">
				<div class="app-card-body p-3 p-lg-4">
					<div class="row g-3">

						<div class="col-12 col-md-3" id="terminalParent">
							<label for="terminal" class="form-label">
								Terminal <span class="requiredField">*</span>
							</label>
							<select class="form-select" id="terminal" name="terminal" required>
								<option value="todos">TODOS</option>
								<?php if ($this->terminal["success"]): ?>
									<?php foreach ($this->terminal["message"] as $terminal): ?>
										<option value="<?= $terminal["id_terminal"] ?>">
											<?= $terminal["nombre"] ?>
										</option>
									<?php endforeach ?>
								<?php endif ?>
							</select>
						</div>

						<div class="col-12 col-md-3" id="periodoParent">
							<label for="periodo" class="form-label">Periodo</label>
							<select name="periodo" id="periodo" class="form-select">
								<option value="todos">Todos</option>
								<option value="ultima_semana">Última semana</option>
								<option value="por_mes">Por mes</option>
								<option value="entre_meses">Entre meses</option>
								<option value="por_fecha">Por fecha</option>
								<option value="entre_fechas">Entre fechas</option>
							</select>
						</div>

						<div class="col-12 col-md-3" id="ParentInicio">
							<label for="fecha_inicio" class="form-label">Desde</label>
							<input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio">
						</div>

						<div class="col-12 col-md-3" id="ParentFin">
							<label for="fecha_fin" class="form-label">Hasta</label>
							<input type="date" class="form-control" id="fecha_fin" name="fecha_fin">
						</div>

					</div>
				</div>
			</div>
		</div>
		<div class="row g-4 mb-4" id="cardsDashboard">
		</div>
		<div class="col-md-12 row g-3 mb-3" id="parentGraficos1">
			<div class="col-md-6 lg-3">
				<div class="app-card app-card-stat shadow-sm h-100">
					<div class="app-card-body p-3 p-lg-4">
						<h5>Totales de ventas por empresa</h5>
						<canvas id="Grafico1" width="400" height="200"></canvas>
					</div>
				</div>
			</div>
			<div class="col-md-6 lg-3">
				<div class="app-card app-card-stat shadow-sm h-100">
					<div class="app-card-body p-3 p-lg-4">
						<h5>Totales de ventas por terminales</h5>
						<canvas id="Grafico2" width="400" height="200"></canvas>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<section class="col-md-3" id="empresaDashboard">
			</section>
			<section class="col-md-9">
				<div class="card-body shadow-sm p-3 mb-3 bg-white rounded p-0 position-relative">
					<div class="row g-2 d-flex justify-content-between my-2">
						<div class="col-md-4">
							<h6>Programaciones</h6>
						</div>
						<div class="col-md-4">
							<input type="text" class="form-control inputSearchProgramacion" placeholder="Buscar">
						</div>
					</div>
					<table id="table_programacion" class="table display responsive" cellspacing="0" style="width:100%">
						<thead>
							<tr>
								<th>Terminal Origen</th>
								<th>Terminal Destino</th>
								<th>Vehiculo</th>
								<th>Fecha Salida</th>
								<th>Hora Salida</th>
							</tr>
						</thead>
						<tbody>
						</tbody>
					</table>
				</div>
			</section>
			<section class="col-md-6" id="parentVentasTab">
				<div class="card-body shadow-sm p-3 mb-3 bg-white rounded p-0 position-relative">
					<div class="row g-2 d-flex justify-content-between my-2">
						<div class="col-md-4">
							<h6>Ventas</h6>
						</div>
					</div>
					<table id="table_totales" class="table display responsive" cellspacing="0" style="width:100%">
						<thead>
							<tr>
								<th>Mes</th>
								<th>Ventas SUNAT</th>
								<th>Ventas Internas</th>
								<th>Compras + Gastos</th>
							</tr>
						</thead>
						<tbody>
						</tbody>
					</table>
				</div>
			</section>
			<section class="col-md-6 mb-3" id="parentMesesTotales">
				<div class="app-card app-card-stat shadow-sm h-100">
					<div id="loading" class="loading">Cargando datos...</div>
					<div class="app-card-body p-3 p-lg-4 chart-container">
						<h5>Totales por meses VENTAS</h5>
						<canvas id="grafico_meses"></canvas>
					</div>
				</div>
			</section>
		</div>
		<div class="col-md-12 row g-3 mb-3" id="parentUsarioTotal">
			<div class="app-card app-card-stat shadow-sm h-100">
				<div class="app-card-body p-3 p-lg-4">
					<h5>Totales de ventas por usuarios</h5>
					<canvas id="Grafico3" width="400" height="200"></canvas>
				</div>
			</div>
		</div>
	</div>
</div>