<div class="<?php echo $col ?> my-2" id="div_parentTpServicioPasaje">
    <label>Tipo servicio pasaje<span class="requiredField">*</span></label>
    <select class="form-select" id="tp_servicio_pasaje" name="tp_servicio_pasaje" required>
        <option value="">Seleccione</option>
        <?php if ($this->tp_servicio_pasaje["success"]) : ?>
            <?php for ($i = 0; $i < count($this->tp_servicio_pasaje["message"]); $i++) : ?>
                <?php $text = $this->tp_servicio_pasaje["message"][$i]["descripcion"] ?>
                <option value="<?php echo $this->tp_servicio_pasaje["message"][$i]["id_tp_servicio_pasaje"] ?>"><?php echo $text ?></option>
            <?php endfor ?>
        <?php endif ?>
    </select>
</div>