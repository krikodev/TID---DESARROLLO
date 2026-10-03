<div class="<?php echo $col ?> my-2" id="div_parentTpComprobante">
    <label>Tipo comprobante<span class="requiredField">*</span></label>
    <select class="form-select" id="tp_comprobante" name="tp_comprobante" required>
        <option value="">Seleccione</option>
        <?php if ($this->tp_comprobante["success"]) : ?>
            <?php for ($i = 0; $i < count($this->tp_comprobante["message"]); $i++) : ?>
                <?php $text = $this->tp_comprobante["message"][$i]["descripcion"] ?>
                <option value="<?php echo $this->tp_comprobante["message"][$i]["id_tp_comprobante"] ?>"><?php echo $text ?></option>
            <?php endfor ?>
        <?php endif ?>
    </select>
</div>