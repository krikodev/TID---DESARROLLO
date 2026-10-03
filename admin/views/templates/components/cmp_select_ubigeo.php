<div class="<?php echo $col ?> my-2" id="div_parentUbigeo">
    <label>Ubigeo<span class="requiredField">*</span></label>
    <select class="form-select" id="ubigeo" name="ubigeo" required>
        <?php if ($this->ubigeo["success"]) : ?>
            <option value="">Seleccione</option>
            <?php for ($i = 0; $i < count($this->ubigeo["message"]); $i++) : ?>
                <?php $text = $this->ubigeo["message"][$i]["cod_ubigeo"] . " | " . $this->ubigeo["message"][$i]["depa"] . " | " . $this->ubigeo["message"][$i]["provi"] . " | " . $this->ubigeo["message"][$i]["distri"] ?>
                <option value="<?php echo $this->ubigeo["message"][$i]["cod_ubigeo"] ?>"><?php echo $text ?></option>
            <?php endfor ?>
        <?php endif ?>
    </select>
</div>