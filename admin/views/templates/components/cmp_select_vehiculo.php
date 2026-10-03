<div class="<?php echo $col ?> my-2" id="div_parentVehiculo">
    <label>Vehículo<span class="requiredField">*</span></label>
    <select class="form-select" id="vehiculo" name="vehiculo" required>
        <option value="">Seleccione</option>
        <?php if ($this->vehiculo["success"]) : ?>
            <?php for ($i = 0; $i < count($this->vehiculo["message"]); $i++) : ?>
                <?php $text = $this->vehiculo["message"][$i]["descripcion"] . " | " . $this->vehiculo["message"][$i]["placa"] ?>
                <option value="<?php echo $this->vehiculo["message"][$i]["id_vehiculo"] ?>" num-piso="<?php echo $this->vehiculo["message"][$i]["num_piso"] ?>"><?php echo $text ?></option>
            <?php endfor ?>
        <?php endif ?>
    </select>
</div>