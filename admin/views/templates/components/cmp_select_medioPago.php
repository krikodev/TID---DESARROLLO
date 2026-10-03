<div class="<?php echo $col ?> my-2" id="div_parentMedioPago">
    <label>M. Pago<span class="requiredField">*</span></label>
    <select class="form-select" id="medio_pago" name="medio_pago" required>
        <option value="">Seleccione</option>
        <?php if ($this->medio_pago["success"]) : ?>
            <?php for ($i = 0; $i < count($this->medio_pago["message"]); $i++) : ?>
                <?php $text = $this->medio_pago["message"][$i]["descripcion"] ?>
                <option value="<?php echo $this->medio_pago["message"][$i]["id_medio_pago"] ?>"><?php echo $text ?></option>
            <?php endfor ?>
        <?php endif ?>
    </select>
</div>