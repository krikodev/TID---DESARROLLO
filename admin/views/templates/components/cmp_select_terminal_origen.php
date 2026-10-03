<div class="<?php echo $col ?> my-2" id="div_parentTerminalOrigen">
    <label>Terminal Origen<span class="requiredField">*</span></label>
    <select class="form-select" id="terminal_origen" name="terminal_origen" required>
        <?php if ($this->terminal_origen["success"]) : ?>
            <option value="">Seleccione</option>
            <?php for ($i = 0; $i < count($this->terminal_origen["message"]); $i++) : ?>
                <?php $text = $this->terminal_origen["message"][$i]["nombre"] ?>
                <option value="<?php echo $this->terminal_origen["message"][$i]["id_terminal"] ?>"><?php echo $text ?></option>
            <?php endfor ?>
        <?php endif ?>
    </select>
</div>