<div class="<?php echo $col ?> my-2" id="div_parentFormaPago">
    <label>F. Pago<span class="requiredField">*</span></label>
    <select class="form-select" id="forma_pago" name="forma_pago" required>
        <option value="">Seleccione</option>
        <?php if ($this->forma_pago["success"]) : ?>
            <?php for ($i = 0; $i < count($this->forma_pago["message"]); $i++) : ?>
                <?php $text = $this->forma_pago["message"][$i]["descripcion"] ?>
                <option value="<?php echo $this->forma_pago["message"][$i]["id_forma_pago"] ?>"><?php echo $text ?></option>
            <?php endfor ?>
        <?php endif ?>
    </select>
</div>