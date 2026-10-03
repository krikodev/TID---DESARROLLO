<div class="<?php echo $col ?> my-2" id="div_parentAlmacen">
    <label>Almacén<span class="requiredField">*</span></label>
    <select class="form-select" id="almacen" name="almacen" required>
        <?php if ($this->almacen["success"]) : ?>
            <?php for ($i = 0; $i < count($this->almacen["message"]); $i++) : ?>
                <?php $text = $this->almacen["message"][$i]["descripcion"] ?>
                <option value="<?php echo $this->almacen["message"][$i]["id_almacen"] ?>"><?php echo $text ?></option>
            <?php endfor ?>
        <?php endif ?>
    </select>
</div>