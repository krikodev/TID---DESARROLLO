<!-- PHP -->
<?php if (isset($this->php)) : ?>
    <?php foreach ($this->php as $php) : ?>
        <?php require("views/" . $php) ?>
    <?php endforeach ?>
<?php endif ?>

<!-- SCRIPTS -->
<?php
// Cambia manualmente esta versión cuando actualices los scripts
$version = "1.1.1";
if (isset($this->js)) : ?>
    <?php foreach ($this->js as $js) : ?>
        <?php
        $arry = explode(".", $js);
        MINIFY_JS ? $arry[0] = $arry[0] . '.min.' : $arry[0] = $arry[0] . '.';
        $name_script = implode("", $arry);
        ?>
        <script src="<?php echo URL; ?>views/<?php echo $name_script ?>?v=<?= $version; ?>" type="module"></script>
    <?php endforeach ?>
<?php endif ?>
<script>
    function controlTag(e) {
        tecla = (document.all) ? e.keyCode : e.which;
        // Permite las teclas de retroceso y tabulación
        if (tecla == 8 || tecla == 9) return true;
        // Define el patrón para permitir solo números y comas
        patron = /^[0-9,]$/;
        n = String.fromCharCode(tecla);
        return patron.test(n);
    }

    function copiarTexto(idInput) {
        const input = document.getElementById(idInput);
        const btnIcon = input.nextElementSibling.querySelector('i');

        navigator.clipboard.writeText(input.value).then(() => {
            btnIcon.className = 'fa-solid fa-check';
            setTimeout(() => {
                btnIcon.className = 'fa-regular fa-copy';
            }, 1000);
        });
    }
</script>
<!-- LIBRERIES -->
<script src="<?php echo URL; ?>public/plugins/jquery v3.6.0/jquery.min.js?v=<?php echo $version; ?>"></script>
<script src="<?php echo URL; ?>public/plugins/bootstrap/bootstrap.bundle.min.js?v=<?php echo $version; ?>"></script>
<!-- <script src="https://code.jquery.com/ui/1.10.0/jquery-ui.js"></script> -->
<script src="<?php echo URL; ?>public/plugins/datatable/datatables.min.js?v=<?php echo $version; ?>"></script>
<script src="<?php echo URL; ?>public/plugins/datatable/plugins/responsive/dataTables.responsive.min.js"></script>
<script src="<?php echo URL; ?>public/plugins/datatable/plugins/sum/sum().js"></script>
<script src="<?php echo URL; ?>public/plugins/datatable/plugins/fixed_header/dataTables.fixedHeader.min.js"></script>
<script src="<?php echo URL; ?>public/plugins/datatable/plugins/select/dataTables.select.min.js"></script>
<script src="<?php echo URL; ?>public/plugins/datatable/plugins/buttons/dataTables.buttons.min.js"></script>
<script src="<?php echo URL; ?>public/plugins/datatable/plugins/buttons/jszip.min.js"></script>
<script src="<?php echo URL; ?>public/plugins/datatable/plugins/buttons/pdfmake.min.js"></script>
<script src="<?php echo URL; ?>public/plugins/datatable/plugins/buttons/vfs_fonts.js"></script>
<script src="<?php echo URL; ?>public/plugins/datatable/plugins/buttons/buttons.html5.min.js"></script>
<script src="<?php echo URL; ?>public/plugins/datatable/plugins/buttons/buttons.print.min.js"></script>

<script src="<?php echo URL; ?>public/plugins/toast/jquery.toast.min.js?v=<?php echo $version; ?>"></script>
<script src="<?php echo URL; ?>public/plugins/wow/wow.min.js"></script>
<script src="<?php echo URL; ?>public/plugins/select2/select2.min.js"></script>
<script src="<?php echo URL; ?>public/plugins/tom-select/tom-select.complete.min.js"></script>
<script src="<?php echo URL; ?>public/plugins/notify/notify.min.js"></script>
<script src="<?php echo URL; ?>public/plugins/luxon/luxon.min.js"></script>
<script src="<?php echo URL; ?>public/plugins/popper/popper.min.js"></script>
<script src="<?php echo URL; ?>public/plugins/tippy/tippy-bundle.umd.js"></script>
<script src="<?php echo URL; ?>public/plugins/sweetalert2/sweetalert2@11.js"></script>
<script src="<?php echo URL; ?>public/plugins/clockpicker/bootstrap-clockpicker.min.js"></script>
<script src="<?php echo URL; ?>public/plugins/interact/interact.min.js"></script>

<!-- SCRIPT -->
<script src="<?php echo URL; ?>public/js/app.min.js?v=<?php echo $version; ?>"></script>
<script src="<?php echo URL; ?>public/js/qz-tray.js"></script>
<script src="<?php echo URL; ?>public/js/main.min.js?v=<?php echo $version; ?>" type="module"></script>
<script src="<?php echo URL; ?>public/plugins/selectize/selectize.min.js"></script>
<script src="<?php echo URL; ?>public/plugins/summernote/summernote-bs5.min.js"></script>
<script src="<?php echo URL; ?>public/plugins/chartjs/chart.min.js"></script>

</body>

</html>