import * as instance from "../../../public/js/instance.js"

document.addEventListener("DOMContentLoaded", function (event) {
    // Variables Form
    let form = document.querySelector("#form")
    let id_ctg_encomienda = document.querySelector("#id_ctg_encomienda")
    let descripcion = document.querySelector("#descripcion")
    let precio = document.querySelector("#precio")
    let afectacion = document.querySelector("#afectacion")

    let button_save = document.querySelector("#button_save_p")
    let button_cancel = document.querySelector("#button_cancel_p")
    let button_loadSave = document.querySelector("#button_loadSave_p")

    instance.Modal.change_name(["#modal"])
    instance.Validate.allowInputMoney(["#precio"])

    // Reset formulario al Cerrar modal
    $("#modal_ctgEncomienda").on("hidden.bs.modal", (e) => {
        $("#modal_ctgEncomienda").find("form").trigger("reset")
        //Eliminando el was-validated
        let forms = document.querySelectorAll(".needs-validation")
        Array.prototype.slice.call(forms).forEach(function (form) {
            form.classList.remove("was-validated")
        })
        id_ctg_encomienda.value = ""
    })

    // Envio de regitro
    form.addEventListener("submit", (e) => {
        e.preventDefault()
        let formData = new FormData(form)
        let data = [
            formData.get("descripcion"), formData.get("precio"),
            formData.get("afectacion")
        ]
        if (instance.Validate.validateData(data)) {
            button_save.classList.add("d-none")
            button_cancel.classList.add("d-none")
            button_loadSave.classList.remove("d-none")
            fetch(instance._URL_ + 'categoria_encomienda/crud_register', {
                method: 'POST',
                body: formData
            }).then(response => {
                if (!response.ok) throw new Error(response.status())
                return response.json()
            }).then(data => {
                if (data.success) {
                    get_ctgEncomienda(data.data);
                    instance.Toast.operacion_exitosa(data.message)
                    $('#modal_ctgEncomienda').modal('toggle')
                } else {
                    instance.Toast.operacion_erronea(data.message)
                }
            }).catch(error => instance.Toast.operacion_erronea(error.message))
                .finally(() => {
                    button_save.classList.remove("d-none")
                    button_cancel.classList.remove("d-none")
                    button_loadSave.classList.add("d-none")
                })
        } else {
            instance.Toast.operacion_erronea("Rellene correctamente los campos")
        }
    })

    const get_ctgEncomienda = (nuevoProducto = null) => {
        if (nuevoProducto) {
            if (!window.selectProducto.options[nuevoProducto.id_ctg_encomienda]) {
                window.selectProducto.addOption({
                    id_ctg_encomienda: nuevoProducto.id_ctg_encomienda,
                    descripcion: nuevoProducto.descripcion,
                    precio: nuevoProducto.precio
                });
            }
            window.selectProducto.setValue(nuevoProducto.id_ctg_encomienda);
            return;
        }
    }
})