<div class="<?php echo $col ?> my-2" id="div_parentConductor">
    <label>Conductor<span class="requiredField">*</span></label>
    <select class="form-select" id="conductor" name="conductor" required>
        <option value="">Seleccione</option>
        <?php if ($this->conductor["success"]) : ?>
            <?php for ($i = 0; $i < count($this->conductor["message"]); $i++) : ?>
                <?php $text = $this->conductor["message"][$i]["nombres"] . " | " . $this->conductor["message"][$i]["apellidos"] . " - " . $this->conductor["message"][$i]["num_docu"] ?>
                <option value="<?php echo $this->conductor["message"][$i]["id_usuario"] ?>"><?php echo $text ?></option>
            <?php endfor ?>
        <?php endif ?>
    </select>
</div>