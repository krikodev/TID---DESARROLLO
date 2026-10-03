import { Validate } from "../../public/js/lib/validate.js";
document.addEventListener("DOMContentLoaded", function (event) {
    let validate = new Validate();
    validate.allowInputNum(["#celular_pf"]);

    let _URL_ = document.querySelector("#url").value;
    let id_usuario = document.querySelector("#id_usuario_sesion");
    let form = document.querySelector("#form_user");
    let button_save = document.querySelector("#button_save_profile");
    let button_loadSave = document.querySelector("#button_loadSave_profile");
    let buttons = document.querySelectorAll(".view_profile");
    let inputs = document.querySelectorAll("#form_user input");
    buttons.forEach(e => {
        e.addEventListener("click", (e) => {
            $.ajax({
                type: "post",
                url: _URL_ + "dashboard/get_data_user",
                data: {
                    id_usuario: id_usuario.value
                },
                success: function (response) {
                    let data = Object.values(JSON.parse(response));
                    for (let i = 0; i < inputs.length; i++) {
                        inputs[i].value = data[i]
                    }
                }
            });
        })
    });

    button_save.addEventListener("click", (e) => {
        e.preventDefault();
        let formData = new FormData(form);
        let reply_val = validate.validateData(
            Object.values(inputs).map((item, index, array) => {
                return item.value;
            })
        )
        if (reply_val) {
            $.ajax({
                type: "post",
                url: _URL_ + "dashboard/update_profile",
                contentType: false,
                cache: false,
                processData: false,
                data: formData,
                beforeSend: function (e) {
                    button_save.classList.add("d-none");
                    button_loadSave.classList.remove("d-none");
                },
                success: function (response) {
                    let reply = JSON.parse(response);
                    if (reply["success"]) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Operación exitosa',
                            text: "Perfil modificado con éxito",
                        })
                        $('#modal_perfil').offcanvas('hide');
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Operación erronea',
                            text: reply["message"],
                        })
                    }
                }, complete: function (e) {
                    button_save.classList.remove("d-none");
                    button_loadSave.classList.add("d-none");
                }
            });
        }
        else {
            Swal.fire({
                icon: 'error',
                title: 'Operación erronea',
                text: "Rellene correctamente los campos",
            })
        }
    })
});