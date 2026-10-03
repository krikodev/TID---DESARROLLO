import * as instance from "../../admin/public/js/instance.js"
document.addEventListener("DOMContentLoaded", () => {
    document.querySelector("#form_login").addEventListener("submit", async (e) => {
        e.preventDefault()
        let form = document.querySelector("#form_login")
        let btn_send = document.querySelector("#button_send")
        let btn_load = document.querySelector("#button_load")
        let formData = new FormData(form)
        if (instance.Validate.validateData([formData.get("email"), formData.get("pass")])) {
            btn_send.classList.add("d-none")
            btn_load.classList.remove("d-none")
            await fetch(instance._URL_ + "login/log_in", {
                method: "POST",
                body: formData
            })
                .then(response => {
                    if (!response.ok) throw new Error(response.status)
                    return response.json()
                })
                .then(data => {
                    if (data.success) {
                        window.location.href = './admin_reclamo';
                    } else {
                        instance.Toast.basic('error', 'Operación erronea', data.message)
                    }
                    btn_send.classList.remove("d-none")
                    btn_load.classList.add("d-none")
                })
                .catch(error => {
                    instance.Toast.basic('error', 'Operación erronea', error.message)
                    btn_send.classList.remove("d-none")
                    btn_load.classList.add("d-none")
                })
        }
        else {
            instance.Toast.basic('error', 'Operación erronea', 'Ingrese sus credenciales')
        }
    });
});