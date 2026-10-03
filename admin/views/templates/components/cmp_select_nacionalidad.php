<div class="<?php echo $col ?> my-2" id="div_parentPaises">
    <label>Nacionalidad<span class="requiredField">*</span></label>
    <select class="form-select" id="nacionalidad" name="nacionalidad" required>
        <?php if ($this->paises["success"]) : ?>
            <option value="">Seleccione</option>
            <?php foreach ($this->paises["message"] as $pais) : ?>
                <?php $text = $pais["nombre_pais"] ?>
                <option value="<?= $pais["nombre_pais"] ?>"><?= $text ?></option>
            <?php endforeach ?>
        <?php endif ?>
    </select>
</div>