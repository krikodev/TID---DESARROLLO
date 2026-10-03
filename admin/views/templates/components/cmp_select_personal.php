<div class="<?php echo $col ?> my-2" id="div_parentPersonal">
    <label>Personal<span class="requiredField">*</span></label>
    <select class="form-select" id="personal" name="personal" required>
        <option value="">Seleccione</option>
        <?php if ($this->personal["success"]) : ?>
            <?php for ($i = 0; $i < count($this->personal["message"]); $i++) : ?>
                <?php $text = $this->personal["message"][$i]["nombres"] . " " . $this->personal["message"][$i]["apellidos"] . " - " . $this->personal["message"][$i]["num_docu"] . " - " . $this->personal["message"][$i]["tp_usuario"] ?>
                <option value="<?php echo $this->personal["message"][$i]["id_usuario"] ?>"><?php echo $text ?></option>
            <?php endfor ?>
        <?php endif ?>
    </select>
</div>