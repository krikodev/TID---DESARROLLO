<div class="row g-2">
    <div class="col-md-12 shadow-sm p-3 mb-3 bg-white rounded p-0 d-flex justify-content-between align-items-center header-page">
        <h2>
            <a href="<?php echo URL ?>" title="Ir al Dashboard"><i class="bi bi-speedometer2"></i></a>
            <?php echo $title ?>
        </h2>
        <?php if ($show_btnadd) : ?>
            <div>
                <button type="button" class="btn btnAddRegis" data-bs-toggle="modal" data-bs-target="#modal" data-bs-whatever="<?php echo $title_modal ?>" id="button_newgister"><i class="fa-solid fa-plus"></i> Nuevo</button>
            </div>
        <?php endif ?>
    </div>
</div>