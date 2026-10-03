import * as instance from "../../../public/js/instance.js"

document.addEventListener("DOMContentLoaded", function (event) {
    $('#fr_comprobante').summernote({
        toolbar: [
            ['style', ['bold', 'italic', 'underline', 'strikethrough']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['fontsize', ['fontsize']],
            ['font', ['fontname']],
            ['height', ['height']]
        ],
        lineHeights: ['0', '0.3', '0.5', '0.7', '1.0', '1.2', '1.5', '2.0', '3.0'],
        placeholder: 'Hello Bootstrap 5',
        tabsize: 2,
        height: 100
    });

    $('#nro_cuenta_bancaria').summernote({
        toolbar: [
            ['style', ['bold', 'italic', 'underline', 'strikethrough']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['fontsize', ['fontsize']],
            ['font', ['fontname']],
            ['height', ['height']],
            ['insert', ['link', 'picture', 'hr']],  // Opcional: permite insertar enlaces e imágenes
            ['misc', ['fullscreen', 'codeview']]     // Opcional: vista completa y código
        ],
        placeholder: 'Ingrese los datos bancarios (cuentas, números de transferencia, etc.)...',
        tabsize: 2,
        height: 150,
        callbacks: {
            onChange: function (contents) {
                // Actualizar el campo hidden cada vez que cambia el contenido
                $('#nro_cuenta_bancaria_hidden').val(contents);
            }
        }
    });

    function initUbigeoSelect(selectId) {
        let ts = new TomSelect(selectId, {
            valueField: "cod_ubigeo",
            labelField: "nombre",
            searchField: "nombre",
            placeholder: "Buscar ubigeo...",
            load: function (query, callback) {
                if (!query.length) return callback();
                let FormD = new FormData();
                FormD.set("q", query);
                fetch(instance._URL_ + "encomienda/buscar_ubigeo", {
                    method: "POST",
                    body: FormD
                })
                    .then(res => res.json())
                    .then(json => {
                        if (json.success) {
                            callback(json.items);
                        } else {
                            callback();
                        }
                    })
                    .catch(() => { callback(); });
            }
        });
        return ts;
    }

    function setUbigeoValue(ts, codUbigeo) {
        if (!codUbigeo) {
            ts.clear();
            return;
        }

        let FormD = new FormData();
        FormD.set("pre", codUbigeo);

        fetch(instance._URL_ + "encomienda/buscar_ubigeo", {
            method: "POST",
            body: FormD
        })
            .then(res => res.json())
            .then(json => {
                if (json.success && json.items.length) {
                    let item = json.items[0];
                    ts.addOption(item);
                    ts.setValue(item.cod_ubigeo);
                }
            });
    }

    instance.Validate.allowInputNum(["#num_docu"])

    let form = document.querySelector("#form")
    let show_logo = document.querySelector("#show_logo")
    let logo = document.querySelector("#logo")
    let logo_before = document.querySelector("#logo_before")
    let link_logo = document.querySelector("#link_logo")
    let show_logo_encomienda = document.querySelector("#show_logo_encomienda")
    let logo_encomienda = document.querySelector("#logo_encomienda")
    let logo_before_encomienda = document.querySelector("#logo_before_encomienda")
    let link_logo_encomienda = document.querySelector("#link_logo_encomienda")
    let show_cdt = document.querySelector("#show_cdt")
    let cdt = document.querySelector("#cdt")
    let cdt_before = document.querySelector("#cdt_before")
    let cdt_clave = document.querySelector("#cdt_clave")
    let link_cdt = document.querySelector("#link_cdt")
    let show_cdt_encomienda = document.querySelector("#show_cdt_encomienda")
    let cdt_encomienda = document.querySelector("#cdt_encomienda")
    let cdt_before_encomienda = document.querySelector("#cdt_before_encomienda")
    let cdt_clave_encomienda = document.querySelector("#cdt_clave_encomienda")
    let link_cdt_encomienda = document.querySelector("#link_cdt_encomienda")
    let num_docu = document.querySelector("#num_docu")
    let num_docu_encomienda = document.querySelector("#num_docu_encomienda")
    let nombre = document.querySelector("#nombre")
    let razon_social = document.querySelector("#razon_social")
    let razon_social_encomienda = document.querySelector("#razon_social_encomienda")
    let condicion = document.querySelector("#condicion")
    let condicion_encomienda = document.querySelector("#condicion_encomienda")
    let estado = document.querySelector("#estado")
    let estado_encomienda = document.querySelector("#estado_encomienda")
    let direccion = document.querySelector("#direccion")
    let direccion_encomienda = document.querySelector("#direccion_encomienda")
    let modo = document.querySelector("#modo")
    let usuario_sol = document.querySelector("#usuario_sol")
    let pass_sol = document.querySelector("#pass_sol")
    let cpe_id = document.querySelector("#cpe_id")
    let cpe_clave = document.querySelector("#cpe_clave")
    let guia_id = document.querySelector("#guia_id")
    let div_ose = document.querySelector("#div_link_ose")
    let guia_clave = document.querySelector("#guia_clave")
    let usuario_sol_encomienda = document.querySelector("#usuario_sol_encomienda")
    let pass_sol_encomienda = document.querySelector("#pass_sol_encomienda")
    let cpe_id_encomienda = document.querySelector("#cpe_id_encomienda")
    let cpe_clave_encomienda = document.querySelector("#cpe_clave_encomienda")
    let guia_id_encomienda = document.querySelector("#guia_id_encomienda")
    let guia_clave_encomienda = document.querySelector("#guia_clave_encomienda")
    let ose = document.getElementById('envio_ose')
    let guia = document.getElementById('envio_guia')
    let check_ose = document.querySelector('input[type="hidden"][name="envio_ose"]')
    let check_guia = document.querySelector('input[type="hidden"][name="envio_guia"]')
    let m_terminales = document.getElementById('m_terminales')
    let check_m_terminales = document.querySelector('input[type="hidden"][name="m_terminales"]')
    let m_terminalesManifi = document.getElementById('m_terminalesManifi')
    let check_m_terminalesManifi = document.querySelector('input[type="hidden"][name="m_terminalesManifi"]')
    let ruc_encomienda_separado = document.getElementById('ruc_encomienda_separado');
    let ruc_encomienda_separado_check = document.getElementById('ruc_encomienda_separado_check');
    let div_ruc_encomienda = document.getElementById("div_ruc_encomienda");
    let porcent_venta = document.getElementById('porcent_venta');
    let porcent_venta_check = document.getElementById('porcent_venta_check');
    let nro_cuenta = document.querySelector("#nro_cuenta")
    let nro_cuenta_bancaria = document.querySelector("#nro_cuenta_bancaria")
    let telefono_empresa = document.querySelector("#telefono_empresa")
    let button_save = document.querySelector("#button_save")
    let button_loadSave = document.querySelector("#button_loadSave")

    instance.Upload.uploadImage(logo, show_logo);
    instance.Upload.uploadImage(logo_encomienda, show_logo_encomienda);
    instance.Upload.uploadFile(cdt, show_cdt, "pfx");
    instance.Upload.uploadFile(cdt_encomienda, show_cdt_encomienda, "pfx");


    // Inicialización global (se hace solo una vez)
    let ubigeoG = initUbigeoSelect("#ubigeo");
    let ubigeoE = initUbigeoSelect("#ubigeo_encomienda");

    ruc_encomienda_separado_check.addEventListener('change', function () {
        ruc_encomienda_separado.value = this.checked ? 1 : 0;
        if (this.checked) {
            div_ruc_encomienda.classList.remove("d-none");
        } else {
            div_ruc_encomienda.classList.add("d-none");
        }
    });

    (function () {
        $.ajax({
            type: "get",
            url: instance._URL_ + "empresa/get_data",
            success: function (response) {
                let reply = JSON.parse(response)
                if (reply.success) {
                    if (reply.message.logo) {
                        logo_before.value = reply.message.logo
                        show_logo.style.backgroundImage = `url('${instance._URL_ + reply.message.logo}')`
                        link_logo.classList.remove("d-none")
                        link_logo.href = instance._URL_ + reply.message.logo
                    }
                    if (reply.message.logo_encomienda) {
                        logo_before_encomienda.value = reply.message.logo_encomienda
                        show_logo_encomienda.style.backgroundImage = `url('${instance._URL_ + reply.message.logo_encomienda}')`
                        link_logo_encomienda.classList.remove("d-none")
                        link_logo_encomienda.href = instance._URL_ + reply.message.logo_encomienda
                    }
                    if (reply.message.cdt_file) {
                        cdt_before.value = reply.message.cdt_file
                        show_cdt.style.backgroundImage = `url(${instance._URL_}public/image/upload/upload_filepfx.png)`
                        link_cdt.classList.remove("d-none")
                        link_cdt.download = true
                        link_cdt.href = instance._URL_ + reply.message.cdt_file
                    }
                    if (reply.message.cdt_file_encomienda) {
                        cdt_before_encomienda.value = reply.message.cdt_file_encomienda
                        show_cdt_encomienda.style.backgroundImage = `url(${instance._URL_}public/image/upload/upload_filepfx.png)`
                        link_cdt_encomienda.classList.remove("d-none")
                        link_cdt_encomienda.download = true
                        link_cdt_encomienda.href = instance._URL_ + reply.message.cdt_file_encomienda
                    }
                    num_docu.value = reply.message.num_docu
                    num_docu_encomienda.value = reply.message.num_docu_encomienda
                    razon_social.value = reply.message.razon_social
                    razon_social_encomienda.value = reply.message.razon_social_encomienda
                    condicion.value = reply.message.condicion
                    condicion_encomienda.value = reply.message.condicion_encomienda
                    estado.value = reply.message.estado
                    estado_encomienda.value = reply.message.estado_encomienda
                    direccion.value = reply.message.direccion_fiscal
                    direccion_encomienda.value = reply.message.direccion_fiscal_encomienda
                    setUbigeoValue(ubigeoG, reply.message.ubigeo);
                    setUbigeoValue(ubigeoE, reply.message.ubigeo_encomienda);
                    modo.value = reply.message.modo_sistema
                    usuario_sol.value = reply.message.user_sol
                    pass_sol.value = reply.message.pass_sol
                    cpe_id.value = reply.message.cpe_id
                    cpe_clave.value = reply.message.cpe_clave
                    guia_id.value = reply.message.guia_id
                    guia_clave.value = reply.message.guia_clave
                    cdt_clave.value = reply.message.cdt_clave
                    nro_cuenta.value = reply.message.nro_cuenta_BN
                    correo.value = reply.message.correo
                    usuario_sol_encomienda.value = reply.message.user_sol_encomienda
                    pass_sol_encomienda.value = reply.message.pass_sol_encomienda
                    cpe_id_encomienda.value = reply.message.cpe_id_encomienda
                    cpe_clave_encomienda.value = reply.message.cpe_clave_encomienda
                    guia_id_encomienda.value = reply.message.guia_id_encomienda
                    guia_clave_encomienda.value = reply.message.guia_clave_encomienda
                    cdt_clave_encomienda.value = reply.message.cdt_clave_encomienda
                    nro_cuenta_encomienda.value = reply.message.nro_cuenta_BN_encomienda
                    telefono_empresa.value = reply.message.telefono_empresa || ''
                    check_ose.value = reply.message.envio_ose
                    check_guia.value = reply.message.permiso_guia
                    ose.value = reply.message.envio_ose
                    guia.value = reply.message.permiso_guia
                    m_terminales.value = reply.message.m_terminales
                    m_terminalesManifi.value = reply.message.m_terminalesManifi
                    ruc_encomienda_separado.value = reply.message.ruc_encomienda_separado
                    porcent_venta.value = reply.message.porcent_venta
                    link_ose.value = reply.message.link_ose
                    if (ose.value == "1") {
                        ose.checked = true;
                        div_link_ose.classList.remove("d-none");
                    }
                    if (guia.value == "1") {
                        guia.checked = true;
                    }
                    if (m_terminales.value == "1") {
                        m_terminales.checked = true;
                    }
                    if (m_terminalesManifi.value == "1") {
                        m_terminalesManifi.checked = true;
                    }
                    if (reply.message.nro_cuenta_bancaria) {
                        $('#nro_cuenta_bancaria').summernote('code', reply.message.nro_cuenta_bancaria);
                        $('#nro_cuenta_bancaria_hidden').val(reply.message.nro_cuenta_bancaria);
                    } else {
                        $('#nro_cuenta_bancaria').summernote('code', '');
                        $('#nro_cuenta_bancaria_hidden').val('');
                    }

                    ruc_encomienda_separado_check.checked = ruc_encomienda_separado.value == "1";
                    ruc_encomienda_separado_check.dispatchEvent(new Event('change'));
                    porcent_venta_check.checked = porcent_venta.value == "1" ? true : false;

                    if (reply.message.fr_comprobante) {
                        $('#fr_comprobante').summernote('code', reply.message.fr_comprobante);
                    }
                }
            }
        });
    }())

    //logica
    if (ose.value === "1") {
        div_link_ose.classList.remove("d-none");
    }
    ose.addEventListener('change', function () {
        check_ose.value = this.checked ? '1' : '0';
        if (check_ose.value == 1) {
            div_ose.classList.remove("d-none");
        } else {
            div_ose.classList.add("d-none");
            link_ose.value = ''
        }
    });

    guia.addEventListener('change', function () {
        check_guia.value = this.checked ? '1' : '0';
    });

    m_terminales.addEventListener('change', function () {
        check_m_terminales.value = this.checked ? '1' : '0';
    });

    m_terminalesManifi.addEventListener('change', function () {
        check_m_terminalesManifi.value = this.checked ? '1' : '0';
    });

    porcent_venta_check.addEventListener('change', function () {
        porcent_venta.value = this.checked ? 1 : 0;
    });

    // Envio de registro
    form.addEventListener("submit", (e) => {
        e.preventDefault()
        let formData = new FormData(form)
        let data = [
            formData.get("num_docu"), formData.get("razon_social"), formData.get("direccion"),
            formData.get("ubigeo"), formData.get("modo")
        ];

        // Capturar el contenido del Summernote
        let contenidoSummernote = $('#fr_comprobante').summernote('code');
        formData.append("fr_comprobante", contenidoSummernote);

        let contenidoBancario = $('#nro_cuenta_bancaria').summernote('code');
        formData.append("nro_cuenta_bancaria", contenidoBancario);

        if (check_ose.value == 1) {
            data.push(formData.get("link_ose"))
        }

        let reply_val = instance.Validate.validateData(data)
        if (reply_val) {
            $.ajax({
                type: "post",
                url: instance._URL_ + "empresa/crud_register",
                contentType: false,
                cache: false,
                processData: false,
                data: formData,
                beforeSend: function (e) {
                    button_save.classList.add("d-none")
                    button_loadSave.classList.remove("d-none")
                },
                success: function (response) {
                    let reply = JSON.parse(response)
                    if (reply["success"]) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Operación exitosa',
                            text: reply["message"],
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // Recargar la página después de cerrar la alerta
                                location.reload();
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Operación erronea',
                            text: reply["message"],
                        })
                    }
                }, complete: function (e) {
                    button_save.classList.remove("d-none")
                    button_loadSave.classList.add("d-none")
                }
            })
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Operación erronea',
                text: 'Rellene correctamente los campos',
            })
        }
    })

    // Consulta SUNAT
    document.querySelector("#button_search").addEventListener("click", (e) => {
        buscar_ruc('#button_search', '#button_loadSearch', num_docu.value, '#razon_social', '#condicion', '#estado', '#direccion', ubigeoG);
    })

    document.querySelector("#button_searchE").addEventListener("click", (e) => {
        buscar_ruc('#button_searchE', '#button_loadSearchE', num_docu_encomienda.value, '#razon_social_encomienda', '#condicion_encomienda', '#estado_encomienda', '#direccion_encomienda', ubigeoE);
    })

    function buscar_ruc(boton_buscar, loader_buscar, numero_doc, razon_social_empresa, condicion_empresa, estado_empresa, direccion_empresa, ubigeo_empresa) {
        let reply_val = instance.Validate.validateData([numero_doc])
        if (reply_val && numero_doc.length == 11) {
            $.ajax({
                type: "post",
                url: instance._URL_ + "api/sunat",
                data: {
                    docu: numero_doc
                },
                beforeSend: function () {
                    $(boton_buscar).addClass("d-none");
                    $(loader_buscar).removeClass("d-none");
                },
                success: function (response) {
                    let data = JSON.parse(response)
                    if (data["success"]) {
                        $(razon_social_empresa).val(data.data.nombre_o_razon_social)
                        $(condicion_empresa).val(data.data.condicion)
                        $(estado_empresa).val(data.data.estado)
                        $(direccion_empresa).val(data.data.direccion)
                        setUbigeoValue(ubigeo_empresa, data.data.ubigeo[2]);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Operación erronea',
                            text: 'Rellene el campo correctamente',
                        })
                    }
                },
                complete: function () {
                    $(boton_buscar).removeClass("d-none");
                    $(loader_buscar).addClass("d-none");
                }
            })
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Operación erronea',
                text: 'Rellene el campo correctamente',
            })
        }
    }
})