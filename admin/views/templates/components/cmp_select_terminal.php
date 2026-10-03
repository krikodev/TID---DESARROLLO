<div class="<?php echo $col ?> my-2" id="div_parentTerminal">
    <label>Terminal<span class="requiredField">*</span></label>
    <select class="form-select" id="terminal" name="terminal" required>
        <?php if ($this->terminal["success"]) : ?>
            <option value="">Seleccione</option>
            <?php for ($i = 0; $i < count($this->terminal["message"]); $i++) : ?>
                <?php $text = $this->terminal["message"][$i]["nombre"] ?>
                <option value="<?php echo $this->terminal["message"][$i]["id_terminal"] ?>"><?php echo $text ?></option>
            <?php endfor ?>
        <?php endif ?>
    </select>
</div>