<div class="<?php echo $col ?> my-2" id="parentSelectEmpresa">
    <label>Empresa<span class="requiredField">*</span></label>
    <select class="form-select" name="empresa" id="empresa" required>
        <?php if (isset($this->empresa) && $this->empresa["success"]) : ?>
            <?php for ($i = 0; $i < count($this->empresa["message"]); $i++) : ?>
                <?php $text = $this->empresa["message"][$i] ?>
                <option value="<?php echo $text["id_empresa"] ?>"><?php echo $text["num_docu"] ?> | <?php echo $text["nombre"] ?></option>
            <?php endfor ?>
        <?php endif ?>
    </select>
</div>