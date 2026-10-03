import * as instance from "../../../public/js/instance.js"

document.getElementById("link_facturador").closest(".nav-item").querySelector("a.nav-link").classList.add("active")
document.getElementById("link_facturador").closest(".nav-item").querySelector("a.nav-link").classList.remove("collapsed")
document.getElementById("link_facturador").closest(".submenu").classList.add("show")
document.getElementById("link_facturador").classList.add("active")

document.addEventListener("DOMContentLoaded", function (event) {

    const table_productos = document.querySelector("#table_productos");

    let loading = document.querySelector("#loader")

    // Variables del modal producto
    let form_producto = document.querySelector("#form_producto")
    let afecto_icbper = document.querySelector("#afecto_icbper")

    // Variables Form
    let form = document.querySelector("#form")
    let precio_unitario = document.querySelector("#precio_unitario")
    let precio_unit = document.querySelector("#precio_unit")
    let importe_total = document.querySelector("#importe_total")

    let btnMinus = document.querySelector('#btn-minus');
    let btnPlus = document.querySelector('#btn-plus');
    let cantidad = document.querySelector('#cantidad');
    let prod_afectacion = document.querySelector('#prod_afectacion');
    let factor_icbper = document.querySelector('#factor_icbper');
    let icbper_prod = document.querySelector('#icbper_prod');

    let medio_pago = document.querySelector("#medio_pago")
    let forma_pago = document.querySelector("#forma_pago")

    let id_serie = document.querySelector("#id_serie")
    let tp_comprobante = document.querySelector("#tp_comprobante")
    let pagar = document.querySelector("#btnPagar")
    let cod_cliente = document.querySelector("#cod_cliente")
    let id_caja = document.querySelector("#id_caja")
    let cliente = document.querySelector("#cliente")
    let total = document.querySelector("#total")
    let num_docu = document.querySelector("#num_docu")
    let serie_venta = document.querySelector("#serie_venta")
    let add_producto = document.querySelector("#add_producto")

    let button_saveP = document.querySelector("#button_saveP")
    let button_cancelP = document.querySelector("#button_cancelP")
    let button_loadSaveP = document.querySelector("#button_loadSaveP")

    let igv_total = document.querySelector("#igv_total")
    let icbper_total = document.querySelector("#icbper_total")
    let gravada_total = document.querySelector("#gravada_total")
    let exonerada_total = document.querySelector("#exonerada_total")
    let inafecta_total = document.querySelector("#inafecta_total")
    let observacion = document.querySelector("#observacion");

    let tp_calculo = document.querySelector("input[name='metodo_detraccion']:checked");

    //Detracciones
    let ubigeo_origen_d = document.querySelector("#ubigeo_origen_d");
    let origen_detraccion = document.querySelector("#origen_detraccion");
    let ubigeo_destino_d = document.querySelector("#ubigeo_destino_d");
    let destino_detraccion = document.querySelector("#destino_detraccion");
    let medio_pago_detraccion = document.querySelector("#medio_pago_detraccion");
    let detalle_detraccion = document.querySelector("#detalle_detraccion");

    let monto_credito = document.getElementById("monto_credito")
    let div_cuota = document.getElementById("div_cuota")
    // Seccion de selects
    instance.select.createSelect("#producto", "#div_parentProducto")
    instance.select.createSelect("#sucursal", "#div_parentSucursal")
    instance.select.createSelect("#tp_comprobante", "#div_parentTpComprobante")
    instance.select.createSelect("#unidad_medida", "#div_parentUnidadM")
    instance.select.createSelect("#afectacion_prod", "#div_parentAfectacion")
    instance.select.createSelect("#medio_pago", "#div_parentMedioPago")

    //Verificacion caja 
    if (id_caja.value == 0) {
        Swal.fire({
            title: "Ooops..... No tiene una caja abierta",
            text: "Desea abrir una caja?",
            icon: "error",
            showDenyButton: true,
            showCancelButton: false,
            confirmButtonText: "Abrir caja",
            denyButtonText: `No quiero`,
            allowOutsideClick: false
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = instance._URL_ + 'caja_chica';
            } else if (result.isDenied) {
                window.location.href = instance._URL_ + 'dashboard';
            }
        });
    }

    $("#tp_comprobante").change(async function (e) {
        e.preventDefault();

        let total_comprobante = document.querySelector("#total")

        if (tp_comprobante.value == 2) {
            $('#tp_operacion_venta option[value="2"]').remove();
        } else if (total_comprobante.value > 400) {
            if ($('#tp_operacion_venta option[value="2"]').length === 0) {
                $('#tp_operacion_venta').append('<option value="2">Operación Sujeta a Detracción - Servicios de Transporte Carga</option>');
            }
        }

        let formData = new FormData();
        formData.set('tp_comprobante', tp_comprobante.value)
        await fetch(instance._URL_ + "facturador/get_serieForTpComprobante", {
            method: "POST",
            body: formData
        })
            .then(response => { if (response.ok) return response.json() })
            .then(data => {
                serie_venta.innerHTML = ""
                if (data.success && Array.isArray(data.message)) {
                    data.message.forEach(item => {
                        serie_venta.insertAdjacentHTML('beforeend', `<option value="${item.id_serie}">${item.serie}</option>`);
                    });
                }
            });

        $('#medio_pago').prop('disabled', forma_pago.value === "2").trigger('change');

        if (tp_comprobante.value == 2) {
            $('#forma_pago option[value="2"]').remove();
        } else {
            if ($('#forma_pago option[value="2"]').length === 0) {
                $('#forma_pago').append('<option value="2">CREDITO</option>');
            }
        }
    });

    $("#tp_comprobante").val("2").trigger("change");

    $(".open_modal_producto").click(function (e) {
        e.preventDefault();
        $("#modal_producto").modal("show")
    });

    window.selectNacionalidad = new TomSelect("#nacionalidad", {
            valueField: "nombre_pais",
            labelField: "nombre_pais",
            searchField: "nombre_pais",
            maxOptions: 5,
            plugins: ['clear_button'],
            preload: 'focus',
    
            shouldLoad: function (query) {
                return query.length >= 2;
            },
    
            load: function (query, callback) {
                fetch(instance._URL_ + "facturador/buscar_nacionalidad?q=" + encodeURIComponent(query))
                    .then(res => res.json())
                    .then(json => callback(json))
                    .catch(() => callback());
            },
    
            onInitialize: function () {
                this.addOption({
                    cod_pais: "174",
                    nombre_pais: "PERÚ"
                });
    
                this.setValue("PERÚ");
            }
        });

    // Envio de formulario producto
    form_producto.addEventListener("submit", (e) => {
        e.preventDefault();
        let formData = new FormData(form_producto);

        let data = [
            formData.get("nombre_producto"),
            formData.get("precio_unitario"),
            formData.get("afectacion_prod"),
            formData.get("unidad_medida"),
            formData.get("estado_prod")
        ];

        if (afecto_icbper.checked) {
            formData.append("afecto_icbper", 1);
            data.push(formData.get("factor_icbper"));
        } else {
            formData.append("afecto_icbper", 0);
        }

        if (instance.Validate.validateData(data)) {
            button_saveP.classList.add("d-none");
            button_cancelP.classList.add("d-none");
            button_loadSaveP.classList.remove("d-none");

            fetch(instance._URL_ + 'facturador/crear_producto', {
                method: 'POST',
                body: formData
            }).then(response => {
                if (!response.ok) throw new Error(response.status);
                return response.json();
            }).then(data => {
                if (data.error) {
                    instance.Toast.operacion_erronea(data.error);
                } else {
                    if (data.success) {
                        recargar_productos();
                        instance.Toast.operacion_exitosa(data.message);
                        $("#modal_producto").modal('toggle')
                    } else {
                        instance.Toast.operacion_erronea(data.message);
                    }
                }
            }).catch(error => instance.Toast.operacion_erronea(error.message))
                .finally(() => {
                    button_saveP.classList.remove("d-none");
                    button_cancelP.classList.remove("d-none");
                    button_loadSaveP.classList.add("d-none");
                });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Operación errónea',
                text: 'Rellene correctamente los campos',
            });
        }
    });

    // Recargando el select de productos
    function recargar_productos() {
        const $select = $("#producto");

        fetch(instance._URL_ + "facturador/get_productosF")
            .then(response => {
                if (!response.ok) throw new Error(response.status);
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    const productos = Object.values(data.message);
                    const primero = productos.length > 0 ? productos[0].id : null;

                    $select.empty();
                    $select.append('<option value="" precio="0.00">Seleccione</option>');

                    productos.forEach(p => {
                        $select.append(
                            `<option value="${p.id}" precio="${p.valor_unitario}" afectacion="${p.tipo_afectacion_id}" factor_icbper="${p.factor_icbper}">${p.nombre}</option>`
                        );
                    });

                    if (primero) {
                        $select.val(primero).trigger("change");
                    }
                }
            })
            .catch(error => instance.Toast.operacion_erronea(error.message));
    }

    // Cierre de modal producto
    $("#modal_producto").on("hidden.bs.modal", (e) => {
        $("#modal_producto").find("form").trigger("reset")
        let forms = document.querySelectorAll(".needs-validation")
        Array.prototype.slice.call(forms).forEach(function (form_asien) {
            form_asien.classList.remove("was-validated")
        })
        $("#afectacion_prod").val("1").trigger("change");
        $("#unidad_medida").val("1").trigger("change");
        $("#div_factIcbper").addClass("d-none");
    })

    // Evento para el checked ICBPER
    $("#afecto_icbper").on("change", function () {
        if (this.checked) {
            $("#div_factIcbper").removeClass("d-none");
        } else {
            $("#div_factIcbper").addClass("d-none");
        }
    });

    $(".open_modal_cliente").click(function (e) {
        e.preventDefault();
        $("#modal_cliente").modal("show")
    });

    $("#producto").on("change", function () {
        const precio = parseFloat($(this).find("option:selected").attr("precio")) || 0;
        const afectacion = parseFloat($(this).find("option:selected").attr("afectacion")) || "";
        const attr = $(this).find("option:selected").attr("factor_icbper");
        const f_icbper = isNaN(parseFloat(attr)) ? 0.00 : parseFloat(attr);

        $("#precio_unit").val(precio.toFixed(2));
        prod_afectacion.value = afectacion
        factor_icbper.value = f_icbper

        const cantidad = parseFloat($("#cantidad").val()) || 0;
        actualizar_importe(precio, cantidad, f_icbper);
    });

    $("#cantidad").on("input change", function () {
        const cantidad = parseFloat($(this).val()) || 0;
        const precio = parseFloat($("#precio_unit").val()) || 0;
        const f_icbper = parseFloat($("#factor_icbper").val()) || 0;
        actualizar_importe(precio, cantidad, f_icbper);
    });

    $("#precio_unit").on("input change", function () {
        const precio = parseFloat($(this).val()) || 0;
        const cantidad = parseFloat($("#cantidad").val()) || 0;
        const f_icbper = parseFloat($("#factor_icbper").val()) || 0;
        actualizar_importe(precio, cantidad, f_icbper);
    });

    function actualizar_importe(p, c, i) {
        const total = p * c;
        const t_icbper = i * c;
        $("#icbper_prod").val(t_icbper > 0 ? t_icbper.toFixed(2) : 0.00);
        $("#importe_total").val(total > 0 ? total.toFixed(2) : 0.00);
    }

    function redondear(numero, decimales = 10) {
        return Math.round(numero * Math.pow(10, decimales)) / Math.pow(10, decimales);
    }

    //Tabla de Productos
    add_producto.addEventListener("click", (event) => {
        let tbody = table_productos.getElementsByTagName("tbody")[0]
        let all_productos = tbody.querySelectorAll("tr .producto")
        let selectedOption = producto.options[producto.selectedIndex];
        let cantidad_producto = cantidad.value;
        let afect_producto = instance._P_SELVA_ == 1 ? 2 : prod_afectacion.value;
        let fact_icbper = factor_icbper.value
        let prod_icbper = icbper_prod.value;
        let precio_producto = precio_unit.value;
        let precio_total = importe_total.value;
        let valor_producto = 0;
        let valor_total = 0;
        let igv_producto = 0;
        let total_igv = 0;
        const igv = instance._IGV_SESION
        if (selectedOption.value == "") {
            return false;
        }

        // Calculos - Asegúrate que sean números
        valor_producto = redondear(
            afect_producto == '1'
                ? Number(precio_producto) / (1 + Number(igv) / 100)
                : Number(precio_producto));
        valor_total = redondear(
            afect_producto == '1'
                ? Number(precio_total) / (1 + Number(igv) / 100)
                : Number(precio_total));
        igv_producto = redondear(
            afect_producto == '1'
                ? Number(precio_total) - valor_total
                : 0.00);

        if (all_productos.length == 0) {
            tbody.insertAdjacentHTML('afterbegin', `
              <tr>
                   <td><label class="producto" id-producto="${producto.value}">${producto.options[producto.selectedIndex].text}</label></td>
                   <td><label class="cantidad_producto">${cantidad_producto}</label></td>
                   <td><label class="valor_producto">${valor_producto}</label></td>
                   <td><label class="precio_producto">${Number(precio_producto)}</label></td>
                   <td><label class="valor_total">${valor_total.toFixed(2)}</label></td>
                   <td><label class="precio_total">${Number(precio_total).toFixed(2)}</label></td>
                   <td><button type='button' class='btn button_deleteItem p-0 pt-2'><i class='bi bi-trash text-danger fs-5'></i></button></td>
                   <td><label class="d-none igv_prod">${igv_producto}</label></td>
                   <td><label class="d-none afect_prod">${afect_producto}</label></td>
                   <td><label class="d-none icbper">${prod_icbper}</label></td>
                   <td><label class="d-none fact_icbper">${fact_icbper}</label></td>
                   </tr>
           `);
        } else {
            if (Object.values(all_productos).some((e, i, array) => e.textContent == producto.options[producto.selectedIndex].text)) {
                instance.Toast.operacion_erronea('El producto ya se encuentra agregado')
            } else {
                tbody.insertAdjacentHTML('afterbegin', `
                        <tr>
                           <td><label class="producto" id-producto="${producto.value}">${producto.options[producto.selectedIndex].text}</label></td>
                           <td><label class="cantidad_producto">${cantidad_producto}</label></td>
                           <td><label class="valor_producto">${valor_producto}</label></td>
                           <td><label class="precio_producto">${Number(precio_producto)}</label></td>
                           <td><label class="valor_total">${valor_total.toFixed(2)}</label></td>
                           <td><label class="precio_total">${Number(precio_total).toFixed(2)}</label></td>
                           <td><button type='button' class='btn button_deleteItem p-0 pt-2'><i class='bi bi-trash text-danger fs-5'></i></button></td>
                           <td><label class="d-none igv_prod">${igv_producto}</label></td>
                           <td><label class="d-none afect_prod">${afect_producto}</label></td>
                           <td><label class="d-none icbper">${prod_icbper}</label></td>
                           <td><label class="d-none fact_icbper">${fact_icbper}</label></td>
                         </tr>
                        `)
            }
        }

        switch (String(afect_producto)) {
            case "1":
                calcularTotal(tbody, "1", "gravada_total");
                break;
            case "2":
                calcularTotal(tbody, "2", "exonerada_total");
                break;
            case "3":
                calcularTotal(tbody, "3", "inafecta_total");
                break;
        }

        let igvs = Array.from(tbody.querySelectorAll("tr .igv_prod"))
            .map(e => parseFloat(e.textContent) || 0);

        let igv_total = redondear(igvs.reduce((acum, val) => acum + val, 0));

        tbody.querySelector("#igv_total").textContent = igv_total.toFixed(2);

        // Suma de icbper
        let icbpers = Array.from(tbody.querySelectorAll("tr .icbper"))
            .map(e => parseFloat(e.textContent) || 0);

        let icbper_total = redondear(icbpers.reduce((a, b) => a + b, 0));
        tbody.querySelector("#icbper_total").textContent = icbper_total.toFixed(2);

        // Suma de precios
        let precios = Array.from(tbody.querySelectorAll("tr .precio_total"))
            .map(e => parseFloat(e.textContent) || 0);

        let costo_total = redondear(precios.reduce((a, b) => a + b, 0));

        // Suma final → precios + icbper
        let total_final = redondear(costo_total + icbper_total);

        tbody.querySelector("#costo_total").textContent = total_final.toFixed(2);
        $("#total").val(total_final.toFixed(2)).trigger('change');
        monto_credito.value = total_final;

        limpiar_campos_producto();
    })

    function calcularTotal(tbody, afectCode, targetId) {
        var total = Array.from(tbody.querySelectorAll("tr"))
            .map(function (tr) {
                var tdAfect = tr.querySelector(".afect_prod");
                var tdPrecio = tr.querySelector(".valor_total");
                var afect = tdAfect ? tdAfect.textContent.trim() : "";
                var precio = tdPrecio ? tdPrecio.textContent : 0;
                return afect === afectCode ? parseFloat(precio) : 0;
            })
            .reduce(function (a, b) {
                return a + b;
            }, 0);

        var target = tbody.querySelector("#" + targetId);
        if (target) {
            target.textContent = total.toFixed(2);
        }
    }

    $('#table_productos tbody').on('click', '.button_deleteItem', function (e) {
        let tbody = table_productos.getElementsByTagName("tbody")[0]
        document.querySelector("#table_productos tbody").removeChild(this.closest("tr"))

        // Recalcular los totales de cada tipo de afectación
        calcularTotal(tbody, "1", "gravada_total");
        calcularTotal(tbody, "2", "exonerada_total");
        calcularTotal(tbody, "3", "inafecta_total");

        let igvs = Object.values(tbody.querySelectorAll("tr .igv_prod")).map((e, i, array) => e.textContent)
        tbody.querySelector("#igv_total").textContent = igvs.reduce((acum, e) => parseFloat(acum) + parseFloat(e), 0).toFixed(2)

        let precios = Object.values(tbody.querySelectorAll("tr .precio_total")).map((e, i, array) => e.textContent)
        tbody.querySelector("#costo_total").textContent = precios.reduce((acum, e) => parseFloat(acum) + parseFloat(e), 0).toFixed(2)
        $("#total").val(tbody.querySelector("#costo_total").textContent).trigger('change');
    });


    function limpiar_campos_producto() {
        $("#producto").val("").trigger("change");
        cantidad.value = 1
        updateButtonStates();
        prod_afectacion.value = ""
        factor_icbper.value = ""
    }

    function filterOptions(component) {
        const tpDocu = component;
        if (tpDocu == 1) {
            $("#tipo_com option[value='1']").hide();
            $("#tipo_com option[value='3']").show();
        } else if (tpDocu == 6) {
            $("#tipo_com option").show();
        } else {
            $("#tipo_com option").hide();
            $("#tipo_com option[value='']").show();
        }

        $("#tipo_com").select2({
            minimumResultsForSearch: Infinity,
            templateResult: function (option) {
                if (!option.id) {
                    return option.text;
                }
                if ((tpDocu == 1 && option.id != '1') || tpDocu == 6) {
                    return option.text;
                }
                return null;
            }
        });
    }

    $("#tp_docu").val("1").trigger("change");

    $("#tp_docu").on("change", function () {
        const tpDocu = $(this).val();
        if (tpDocu == 7) {
            document.querySelector("#num_docu").removeAttribute("onkeypress");
        } else {
            document.querySelector("#num_docu").setAttribute("onkeypress", "return controlTag(event)");
        }
        const maxLength = tpDocu == 6 ? 11 : 8;
        $("#num_docu").val('');
        $("#num_docu").attr("maxlength", maxLength);

        filterOptions($("#tp_docu").val());
    });


    // Pagar
    $('#btnPagar').on('click', function (e) {
        let formData = new FormData(form)

        let tp_calculo_element = document.querySelector("input[name='metodo_detraccion']:checked");
        let tp_calculo_value = tp_calculo_element ? tp_calculo_element.value : null;

        let camposRequeridos = [
            { value: formData.get("serie_venta"), nombre: "Serie de venta" },
            { value: formData.get("fecha_emision"), nombre: "Fecha de emisión" },
            { value: formData.get("fecha_vencimiento"), nombre: "Fecha de vencimiento" },
            { value: formData.get("cliente_id"), nombre: "Cliente" },
            ...(forma_pago.value === '1' ? [
                { value: medio_pago.value, nombre: "Medio de pago" },
            ] : []),
            ...(forma_pago.value === '2' ? [
                { value: monto_credito.value, nombre: "Monto crédito" },
            ] : []),
            { value: forma_pago.value, nombre: "Forma de pago" },
            ...(tp_operacion_venta.value == 2 ? (
                tp_calculo_value === 'sunat' ? [
                    { value: ubigeo_origen_d.value, nombre: "Ubigeo origen" },
                    { value: origen_detraccion.value, nombre: "Dirección origen" },
                    { value: ubigeo_destino_d.value, nombre: "Ubigeo destino" },
                    { value: destino_detraccion.value, nombre: "Dirección destino" },
                    { value: medio_pago_detraccion.value, nombre: "Medio pago detracción" },
                    { value: detalle_detraccion.value, nombre: "Detalle detracción" },
                    { value: total_operacion.value, nombre: "Total operación" },
                    { value: tm_detraccion.value, nombre: "TM detracción" },
                    { value: config_vehiculo.value, nombre: "Config vehículo" },
                    { value: monto_detraccion.value, nombre: "Monto detracción" },
                    { value: ruta_origen_select.value, nombre: "Ruta origen" },
                    { value: ruta_destino_select.value, nombre: "Ruta destino" },
                    { value: v_ref_carga_efectiva.value, nombre: "Carga efectiva" },
                    { value: v_ref_carga_util.value, nombre: "Carga útil" },
                    { value: v_ref_servicio.value, nombre: "Servicio" }
                ] : tp_calculo_value === 'directo' ? [
                    { value: ubigeo_origen_d.value, nombre: "Ubigeo origen" },
                    { value: origen_detraccion.value, nombre: "Dirección origen" },
                    { value: ubigeo_destino_d.value, nombre: "Ubigeo destino" },
                    { value: destino_detraccion.value, nombre: "Dirección destino" },
                    { value: medio_pago_detraccion.value, nombre: "Medio pago detracción" },
                    { value: detalle_detraccion.value, nombre: "Detalle detracción" },
                    { value: monto_detraccion.value, nombre: "Monto detracción" },
                    { value: v_ref_carga_efectiva.value, nombre: "Carga efectiva" },
                    { value: v_ref_carga_util.value, nombre: "Carga útil" },
                    { value: v_ref_servicio.value, nombre: "Servicio" }
                ] : []
            ) : [])
        ];

        // Validar campos vacíos
        const camposFaltantes = camposRequeridos
            .filter(campo => !campo.value || campo.value.trim() === '')
            .map(campo => campo.nombre);

        if (camposFaltantes.length > 0) {
            const mensaje = camposFaltantes.length === 1
                ? `Falta completar el campo: ${camposFaltantes[0]}`
                : `Faltan completar los siguientes campos:\n• ${camposFaltantes.join('\n• ')}`;

            Swal.fire({
                icon: 'warning',
                title: 'Campos incompletos',
                html: mensaje.replace(/\n/g, '<br>'),
            });
            return;
        }

        if (document.querySelectorAll(".producto").length == 0) {
            instance.Toast.operacion_erronea("Ingrese productos")
            return;
        }

        if (document.querySelectorAll(".producto").length == 0) {
            instance.Toast.operacion_erronea("Ingrese productos o servicios")
            return
        }

        if (tp_operacion_venta.value == 2) {
            if (v_ref_carga_efectiva.value <= 0 || v_ref_carga_util.value <= 0 || v_ref_servicio.value <= 0 || monto_detraccion.value <= 0) {
                Swal.fire({
                    icon: 'warning',
                    title: '¡¡Atencion!!',
                    text: 'Los valores referenciales deben ser mayor a 0.00',
                });
                return;
            }

            if (v_ref_carga_efectiva.value < 1) {
                Swal.fire({
                    icon: 'info',
                    title: '¡¡Atencion!!',
                    text: 'El valor referencial carga efectiva no puede ser menor a 1 revise su TM detraccion',
                });
                return;
            }
        }

        let producto = document.querySelectorAll(".producto");
        let cantidad_producto = document.querySelectorAll(".cantidad_producto");
        let valorProducto = document.querySelectorAll(".valor_producto");
        let precioProducto = document.querySelectorAll(".precio_producto");
        let valorTotal = document.querySelectorAll(".valor_total");
        let precioTotal = document.querySelectorAll(".precio_total");
        let igvProducto = document.querySelectorAll(".igv_prod");
        let afectProducto = document.querySelectorAll(".afect_prod");
        let icbperProducto = document.querySelectorAll(".icbper");
        let factIcbper = document.querySelectorAll(".fact_icbper");

        let productos = [];

        Object.values(producto).forEach((el, i) => {
            const cantidadEl = cantidad_producto[i] || null;
            const valorprodEl = valorProducto[i] || null;
            const precioprodEl = precioProducto[i] || null;
            const valortotalEl = valorTotal[i] || null;
            const preciototalEl = precioTotal[i] || null;
            const igvEl = igvProducto[i] || null;
            const afectEl = afectProducto[i] || null;
            const icbperEl = icbperProducto[i] || null;
            const facticbperEl = factIcbper[i] || null;

            productos.push([
                el.getAttribute("id-producto") || '',
                el.textContent.trim(),
                cantidadEl ? cantidadEl.textContent.trim() : '',
                valorprodEl ? valorprodEl.textContent.trim() : '',
                precioprodEl ? precioprodEl.textContent.trim() : '',
                valortotalEl ? valortotalEl.textContent.trim() : '',
                preciototalEl ? preciototalEl.textContent.trim() : '',
                igvEl ? igvEl.textContent.trim() : '',
                afectEl ? afectEl.textContent.trim() : '',
                icbperEl ? icbperEl.textContent.trim() : '',
                facticbperEl ? facticbperEl.textContent.trim() : '',
            ]);
        });

        loading.classList.remove('d-none');
        let total = document.querySelector("#total");

        formData.set("productos", JSON.stringify(productos))
        formData.set("medio_pago", medio_pago.value)
        formData.set("observacion", observacion.value)
        formData.set("id_caja", id_caja.value)
        formData.set("forma_pago", forma_pago.value)
        formData.set("total_igv", igv_total.textContent)
        formData.set("total_icbper", icbper_total.textContent)
        formData.set("total_gravadas", gravada_total.textContent)
        formData.set("total_exoneradas", exonerada_total.textContent)
        formData.set("total_inafectas", inafecta_total.textContent)
        formData.set("importe_total", total.value)
        formData.set("tp_operacion_venta", tp_operacion_venta.value)
        formData.set("ubigeo_origen_d", ubigeo_origen_d.value)
        formData.set("origen_detraccion", origen_detraccion.value)
        formData.set("ubigeo_destino_d", ubigeo_destino_d.value)
        formData.set("destino_detraccion", destino_detraccion.value)
        formData.set("medio_pago_detraccion", medio_pago_detraccion.value)
        formData.set("detalle_detraccion", detalle_detraccion.value)
        formData.set("total_operacion", total_operacion.value)
        formData.set("tm_detraccion", tm_detraccion.value)
        formData.set("config_vehiculo", config_vehiculo.value)
        formData.set("monto_detraccion", monto_detraccion.value)
        formData.set("ruta_origen", ruta_origen_select.value)
        formData.set("ruta_destino", ruta_destino_select.value)
        formData.set("v_ref_carga_efectiva", v_ref_carga_efectiva.value)
        formData.set("v_ref_carga_util", v_ref_carga_util.value)
        formData.set("v_ref_servicio", v_ref_servicio.value)
        formData.set("base_detraccion", base_detraccion.value)
        $('#btnPagar').prop('disabled', true);

        fetch(instance._URL_ + 'facturador/generar_comprobante', {
            method: 'POST',
            body: formData
        }).then(response => {
            if (!response.ok) {
                return response.text().then(text => {
                    throw new Error(`Error ${response.status}: ${text}`);
                });
            }
            return response.json();
        }).then(data => {
            if (data.success) {
                loading.classList.add('d-none');
                $("form").trigger("reset");
                limpiar_tabla_productos();
                limpiar_totales();
                limpiar_pagos();
                let forms = document.querySelectorAll(".needs-validation");
                Array.prototype.slice.call(forms).forEach(function (form) {
                    form.classList.remove("was-validated");
                });
                modal_printComprobante('¡Venta completada!', data.estado_sunat, data.message_sunat, data.id_venta, data.tp_comprobante || parseInt(tp_comprobante.value));
                console.log("DEBUG - Llamando a modal_printComprobante con:", {
                    id_venta: data.id_venta,
                    tp_comprobante: tp_comprobante.value,
                    tp_comprobante_int: parseInt(tp_comprobante.value),
                    link_pdf: data.link_comprobante
                });
                if ([1, 3].includes(tp_comprobante.value)) instance.Toast.operacion_erronea(data.message_sunat);
                else instance.Toast.operacion_exitosa(data.message);
                total.value = '';
                limpiar_campos_detraccion();
            } else {
                loading.classList.add('d-none');
                Swal.fire({
                    icon: 'error',
                    title: 'Operación erronea',
                    text: data.message,
                });
            }
        }).catch(error => instance.Toast.operacion_erronea(error.message))
            .finally(() => {
                button_saveP.classList.remove("d-none");
                button_cancelP.classList.remove("d-none");
                button_loadSaveP.classList.add("d-none");
                loading.classList.add('d-none');
                $('#btnPagar').prop('disabled', false);
            });
    });

    function limpiar_tabla_productos() {
        const tbody = table_productos.querySelector("tbody");

        tbody.querySelectorAll("tr").forEach(tr => {
            if (tr.querySelector(".producto")) {
                tr.remove();
            }
        });
    }

    function limpiar_totales() {
        const totales = [
            "igv_total",
            "icbper_total",
            "gravada_total",
            "exonerada_total",
            "inafecta_total",
            "costo_total"
        ];

        totales.forEach(id => {
            const cell = document.getElementById(id);
            if (cell) cell.textContent = "0.00";
        });
    }

    function limpiar_pagos() {
        $("#observacion").val("");
        $("#tp_comprobante").val("2").trigger("change");
        $("#medio_pago").val("").trigger("change");
        $("#forma_pago").val("1").trigger("change");
    }

    const modal_printComprobante = (title, estado_sunat = 0, message_sunat = "", id_comprobante = 0, tp_comprobante = 2) => {
        console.log("DEBUG - modal_printComprobante recibió:", {
            title,
            id_comprobante,
            tp_comprobante: parseInt(tp_comprobante) || 2,
        });

        let html_rspt_sunat = '';
        if (estado_sunat) {
            html_rspt_sunat = `<p class="fw-bolder fs-6">${message_sunat}</p>`;
        }

        // DECLARAR AMBAS VARIABLES AL INICIO
        let tipo_impresion_normal = '';
        let tipo_impresion_a4 = '';

        // Determinar tipo de impresión para ambos casos (válido e inválido)
        switch (parseInt(tp_comprobante)) {
            case 1: // Factura electrónica
            case 3: // Boleta electrónica
                tipo_impresion_normal = 'comprobante';
                tipo_impresion_a4 = 'comprobante_a4';
                break;
            case 2: // Nota de venta
                tipo_impresion_normal = 'nota_venta';
                tipo_impresion_a4 = 'nota_venta_a4';
                break;
            default:
                tipo_impresion_normal = 'comprobante';
                tipo_impresion_a4 = 'comprobante_a4';
                break;
        }

        // Verificar que el ID sea válido
        if (!id_comprobante || id_comprobante <= 0) {
            console.error("ID de comprobante inválido o no proporcionado:", id_comprobante);

            // Crear un link_pdf básico para el caso inválido
            const link_pdf_basico = instance._URL_ + `facturador/impresion/${tipo_impresion_normal}/0`;

            // Si no hay ID válido, mostrar solo el botón normal
            Swal.fire({
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
                html: `<div class="d-flex flex-column justify-content-center align-items-center pt-3 pb-0 mb-0">
                <i class="fa-solid fa-circle-check py-3" style="font-size:50px; color:#34d16e"></i>
                <label></label>
                <h5>${title}</h5>
                
                <!-- Solo botón normal (sin A4) -->
                <a href="${link_pdf_basico}" 
                   class="d-flex flex-column text-decoration-none cursor-pointer py-4" 
                   data-wow-iteration="infinite" 
                   data-wow-duration="500ms" 
                   target="_blank">
                    <i class="fa-light fa-receipt fs-2 text-secondary"></i>
                    <label class="text-secondary cursor-pointer mt-2">Imprimir</label>
                </a>
                
                <section>
                    ${html_rspt_sunat}                        
                    <p class="text-secondary">Enviar comprobante por WhatsApp</p>
                    <div class="input-group mb-3">
                        <span class="input-group-text py-0">+51</span>
                        <input type="text" class="form-control" id="celular_clienteWsp" placeholder="999 999 999" minlength="9" maxlength="9">
                        <button type="button" class="btn text-white" id="btn_sendComprobanteWsp" style="background-color:#34d16e">Enviar <i class="fa-brands fa-whatsapp"></i></button>
                    </div>
                </section>
                </div>
                `,
                confirmButtonText: 'Continuar'
            });
            return; // Salir de la función
        }

        // Link para impresión normal
        const link_normal = instance._URL_ + `facturador/impresion/${tipo_impresion_normal}/${id_comprobante}`;
        const link_a4 = instance._URL_ + `facturador/impresion/${tipo_impresion_a4}/${id_comprobante}`;

        console.log("DEBUG - Links calculados:", {
            tipo_comprobante: tp_comprobante,
            tipo_normal: tipo_impresion_normal,
            tipo_a4: tipo_impresion_a4,
            link_normal: link_normal,
            link_a4: link_a4
        });

        Swal.fire({
            allowOutsideClick: false,
            allowEscapeKey: false,
            allowEnterKey: false,
            html: `
            <div class="d-flex flex-column justify-content-center align-items-center pt-3 pb-0 mb-0">
            <i class="fa-solid fa-circle-check py-3" style="font-size:50px; color:#34d16e"></i>
            <label></label>
            <h5>${title}</h5>
            
            <!-- Contenedor para ambos botones de impresión -->
            <div class="d-flex flex-row justify-content-center align-items-center gap-3 py-4">
                <!-- Botón Imprimir (original) -->
                <a href="${link_normal}" 
                    class="d-flex flex-column text-decoration-none cursor-pointer" 
                    data-wow-iteration="infinite" 
                    data-wow-duration="500ms" 
                    target="_blank">
                    <i class="fa-light fa-receipt fs-2 text-secondary"></i>
                    <label class="text-secondary cursor-pointer mt-2">Imprimir</label>
                </a>
                
                <!-- Botón Imprimir A4 (nuevo) -->
                <a href="${link_a4}" 
                   class="d-flex flex-column text-decoration-none cursor-pointer" 
                   data-wow-iteration="infinite" 
                   data-wow-duration="500ms" 
                   target="_blank">
                    <i class="fa-solid fa-print fs-2 text-primary"></i>
                    <label class="text-primary cursor-pointer mt-2">Imprimir A4</label>
                </a>
            </div>
            
            <section>
                ${html_rspt_sunat}                        
                <p class="text-secondary">Enviar comprobante por WhatsApp</p>
                <div class="input-group mb-3">
                    <span class="input-group-text py-0">+51</span>
                    <input type="text" class="form-control" id="celular_clienteWsp" placeholder="999 999 999" minlength="9" maxlength="9">
                    <button type="button" class="btn text-white" id="btn_sendComprobanteWsp" style="background-color:#34d16e">Enviar <i class="fa-brands fa-whatsapp"></i></button>
                </div>
            </section>
            </div>
            `,
            confirmButtonText: 'Continuar'
        });

        // Resto del código para manejar WhatsApp...
        instance.Validate.allowInputNum(["#celular_clienteWsp"]);
        let celular_clienteWsp = document.querySelector("#celular_clienteWsp");
        let btn_sendComprobanteWsp = document.querySelector("#btn_sendComprobanteWsp");
        btn_sendComprobanteWsp.addEventListener("click", (event) => {
            if (celular_clienteWsp.value.length == 9) {
                window.open(instance.CONSTS.URL.ENVIAR_COMPROBANTE_WHATSAPP
                    .replace('$number$', `+51${celular_clienteWsp.value}`)
                    .replace('$message$', instance.CONSTS.TEXTO.WHATSAPP_COMPROBANTE
                        .replace('$link_comprobante$', link_normal)), "_blank");
            } else {
                instance.Toast.operacion_erronea('Por favor ingrese un número de celular valido');
            }
        });
    }

    // Botones de cantidad
    function updateButtonStates() {
        const value = parseInt(cantidad.value) || 0;
        const min = parseInt(cantidad.min) || -Infinity;
        const max = parseInt(cantidad.max) || Infinity;

        btnMinus.disabled = value <= min;
        btnPlus.disabled = value >= max;
    }

    btnMinus.addEventListener('click', function () {
        let currentValue = parseInt(cantidad.value) || 0;
        let min = parseInt(cantidad.min) || 1;

        if (currentValue > min) {
            cantidad.value = currentValue - 1;
            cantidad.dispatchEvent(new Event('change'));
            updateButtonStates();
        }
    });

    btnPlus.addEventListener('click', function () {
        let currentValue = parseInt(cantidad.value) || 0;
        let max = parseInt(cantidad.max) || Infinity;

        if (currentValue < max) {
            cantidad.value = currentValue + 1;
            cantidad.dispatchEvent(new Event('change'));
            updateButtonStates();
        }
    });

    cantidad.addEventListener('input', updateButtonStates);
    cantidad.addEventListener('change', updateButtonStates);

    updateButtonStates();

    // Función para formatear fecha
    function formatDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    const hoy = new Date();

    // Fecha de emisión: 3 días antes hasta hoy
    const tresDiasAntes = new Date(hoy);
    tresDiasAntes.setDate(hoy.getDate() - 3);

    $('#fecha_emision').attr({
        'min': formatDate(tresDiasAntes),
        'max': formatDate(hoy)
    });

    // Fecha de vencimiento: hoy hasta 3 días después
    const tresDiasDespues = new Date(hoy);
    tresDiasDespues.setDate(hoy.getDate() + 3);

    $('#fecha_vencimiento').attr({
        'min': formatDate(hoy),
        'max': formatDate(tresDiasDespues)
    });

    //===================APARTADO DE DETRACCIONES ======================
    instance.select.createSelect("#medio_pago_detraccion", "#parentMedioPagoDetraccion")
    instance.select.createSelect("#config_vehiculo", "#ParentConfigVehiculo")


    let total_operacion = document.querySelector("#total_operacion")
    let tm_detraccion = document.querySelector("#tm_detraccion")
    let v_ref_carga_efectiva = document.querySelector("#v_ref_carga_efectiva")
    let v_ref_carga_util = document.querySelector("#v_ref_carga_util")
    let v_ref_servicio = document.querySelector("#v_ref_servicio")
    let config_vehiculo = document.querySelector("#config_vehiculo")
    let monto_detraccion = document.querySelector("#monto_detraccion")
    let ruta_origen_select = document.querySelector("#ruta_origen")
    let ruta_destino_select = document.querySelector("#ruta_destino")
    let base_detraccion = document.querySelector("#base_detraccion")
    let tp_operacion_venta = document.querySelector("#tp_operacion_venta")
    let div_detraccion = document.querySelector("#div_detraccion")

    let tpOperacionAnterior = $("#tp_operacion_venta").val();

    $("#tp_operacion_venta").on('focus', function () {
        tpOperacionAnterior = $(this).val();
    });

    $("#tp_operacion_venta").on('change', async function () {
        let tp_op = $(this).val();
        let costo_t = document.getElementById('costo_total');
        let costo_valor = parseFloat(costo_t.textContent.replace('S/', '')) || 0;

        if (tp_op != 2) {
            div_detraccion.classList.add('d-none');
            return;
        }

        if (costo_valor <= 400) {
            Swal.fire({
                icon: 'info',
                title: 'Atención',
                text: 'El monto total de la encomienda debe ser mayor a S/ 400.00 para aplicar detracción.'
            });

            $(this).val(tpOperacionAnterior);
            div_detraccion.classList.add('d-none');
            return;
        }

        const existeCuenta = await consultar_cuenta_detracciones();

        if (!existeCuenta) {
            $(this).val(tpOperacionAnterior);
            div_detraccion.classList.add('d-none');
            return;
        }

        div_detraccion.classList.remove('d-none');
        setUbigeoValue(ubigeoOrigenD, instance._UB_TERMINAL_SESION);
        origen_detraccion.value = instance._DIR_TERMINAL_SESION;
    });

    async function consultar_cuenta_detracciones() {
        try {
            const response = await fetch(instance._URL_ + 'encomienda/consultar_cuentaD');
            if (!response.ok) throw new Error('Error del servidor');

            const data = await response.json();

            if (!data.success) {
                instance.Toast.operacion_informativa(data.message);
                return false;
            }

            return true;

        } catch (error) {
            instance.Toast.operacion_erronea(error.message);
            return false;
        }
    }

    // Selects de detracciones
    let ubigeoOrigenD = initUbigeoSelect("#ubigeo_origen_d");
    let ubigeoDestinoD = initUbigeoSelect("#ubigeo_destino_d");

    // Select de ruta destino ANEXO 2 
    let rutaOrigenData = null;
    let rutaDestinoData = null;

    let ruta_origen = initRutaAnexo2Select("#ruta_origen", function (data) {
        rutaOrigenData = data;
        calcular_detraccion_encomienda();
    });

    let ruta_destino = initRutaAnexo2Select("#ruta_destino", function (data) {
        rutaDestinoData = data;
        calcular_detraccion_encomienda();
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

    // Función para asignar valor al ubigeo (modo edición)
    function setUbigeoValue(ts, codUbigeo) {
        if (!codUbigeo) {
            ts.clear(); // limpia si no hay valor
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
                    ts.addOption(item);          // añade la opción
                    ts.setValue(item.cod_ubigeo); // selecciona el valor
                }
            });
    }

    function initRutaAnexo2Select(selector, onSelect) {
        return new TomSelect(selector, {
            valueField: "id",
            labelField: "nombre",
            searchField: "nombre",
            placeholder: "Buscar ruta...",
            maxItems: 1,
            closeAfterSelect: true,
            onItemAdd(value) {
                const selectedData = this.options[value];
                onSelect(selectedData);
                this.blur();
            },

            load(query, callback) {
                if (!query.length) return callback();

                let fd = new FormData();
                fd.set("q", query);

                fetch(instance._URL_ + "encomienda/buscar_ruta_anexo2", {
                    method: "POST",
                    body: fd
                })
                    .then(res => res.json())
                    .then(json => callback(json.success ? json.items : []))
                    .catch(() => callback([]));
            }
        });
    }

    // Flag para prevenir loops
    let isCalculatingDetraccion = false;

    // Metodos de calculo de detracciones
    function calcular_detraccion_encomienda() {
        if (isCalculatingDetraccion) {
            return;
        }

        isCalculatingDetraccion = true;

        try {
            let tp_calculo_el = document.querySelector("input[name='metodo_detraccion']:checked");
            let tp_calculo_v = tp_calculo_el ? tp_calculo_el.value : null;


            // ===== MÉTODO DIRECTO 4% =====
            if (tp_calculo_v === 'directo') {

                let precio_valor = parseFloat($("#total").val()) || 0;

                $("#v_ref_carga_efectiva").val(precio_valor.toFixed(2));
                $("#v_ref_carga_util").val(precio_valor.toFixed(2));
                $("#v_ref_servicio").val(precio_valor.toFixed(2));

                let m_detraccion = precio_valor * 0.04;

                $("#base_detraccion").val(precio_valor.toFixed(2));
                $("#monto_detraccion").val(m_detraccion.toFixed(2));

                return; // Salir aquí
            }

            // ===== MÉTODO SUNAT =====
            if (tp_calculo_v === 'sunat') {
                // Validar que existan los datos necesarios
                if (!rutaDestinoData || !Number.isFinite(rutaDestinoData.id)) {
                    return;
                }

                // VALORES GENERALES
                let v_ref_carga_ef = 0.00;
                let v_ref_carga_utilN = 0.00;
                let v_x_TM = 0.00;

                // Calcular valor por TM
                if (ruta_origen_select.value == 0) {
                    v_x_TM = parseFloat(rutaDestinoData.valor_por_tm) || 0.00;
                } else {
                    // Datos de validacion
                    let id_ruta_destino = rutaDestinoData.ruta_anexo2_id;
                    let id_ruta_origen = rutaOrigenData.ruta_anexo2_id;
                    let misma_tabla = id_ruta_origen == id_ruta_destino ? true : false;
                    let v_x_TM_origen = parseFloat(rutaOrigenData.valor_por_tm) || 0.00;
                    let v_x_TM_destino = parseFloat(rutaDestinoData.valor_por_tm) || 0.00;

                    if (misma_tabla) {
                        const minuendo = Math.max(v_x_TM_origen, v_x_TM_destino);
                        const sustraendo = Math.min(v_x_TM_origen, v_x_TM_destino);
                        v_x_TM = minuendo - sustraendo;
                    } else {
                        v_x_TM = v_x_TM_origen + v_x_TM_destino;
                    }
                }

                let importe_operacion = parseFloat($("#total_operacion").val()) || 0.00;
                let peso_total = parseFloat($("#tm_detraccion").val()) || 0.00;

                let capacidad_carga_UN = parseFloat(
                    config_vehiculo.options[config_vehiculo.selectedIndex]
                        .dataset.cargaUtilTm
                ) || 0.00;

                /* CALCULO VALOR REFERENCIAL EN FUNCION DE LA CARGA EFECTIVA */
                v_ref_carga_ef = v_x_TM * peso_total;
                $("#v_ref_carga_efectiva").val(v_ref_carga_ef.toFixed(2));

                /* CALCULO VALOR REFERENCIAL EN FUNCION A LA CARGA UTIL NOMINAL */
                v_ref_carga_utilN = 0.7 * (v_x_TM * capacidad_carga_UN);
                $("#v_ref_carga_util").val(v_ref_carga_utilN.toFixed(2));

                /* SELECCION DEL VALOR REFERENCIAL MAYOR */
                let valor_referencial_final = Math.max(v_ref_carga_ef, v_ref_carga_utilN);
                $("#v_ref_servicio").val(valor_referencial_final.toFixed(2));

                /* SELECCION DEL VALOR REFERENCIAL MAYOR VS COSTO DE SERVICIO */
                let monto_calculo = Math.max(valor_referencial_final, importe_operacion);
                let monto_detrac = 0.04 * monto_calculo;

                $("#base_detraccion").val(monto_calculo.toFixed(2));
                $("#monto_detraccion").val(monto_detrac.toFixed(2));

            }

        } catch (error) {
            console.error("Error al calcular detracción:", error);
        } finally {
            isCalculatingDetraccion = false;
        }
    }

    // Eventos optimizados
    $("#total_operacion, #tm_detraccion").on("change", function () {
        let tp_calculo_el = document.querySelector("input[name='metodo_detraccion']:checked");
        let tp_calculo_v = tp_calculo_el ? tp_calculo_el.value : null;

        // Solo calcular si es método SUNAT
        if (tp_calculo_v === 'sunat') {
            calcular_detraccion_encomienda();
        }
    });

    $("#config_vehiculo").on("change", function () {
        let tp_calculo_el = document.querySelector("input[name='metodo_detraccion']:checked");
        let tp_calculo_v = tp_calculo_el ? tp_calculo_el.value : null;

        // Solo calcular si es método SUNAT
        if (tp_calculo_v === 'sunat') {
            calcular_detraccion_encomienda();
        }
    });

    $("#total").on("change", function () {
        let total_comprobante = parseFloat(this.value) || 0;
        let opcionDetraccion = $('#tp_operacion_venta option[value="2"]');
        let tp_operacion_actual = $("#tp_operacion_venta").val();

        if (tp_comprobante.value == 2) {
            // Remover opción si es boleta
            opcionDetraccion.remove();

            if (tp_operacion_actual == 2) {
                $("#tp_operacion_venta").val("1").trigger("change");
                limpiar_campos_detraccion();
            }

        } else if (total_comprobante > 400) {
            // Agregar opción si no existe
            if (opcionDetraccion.length === 0) {
                $('#tp_operacion_venta').append(
                    '<option value="2">Operación Sujeta a Detracción - Servicios de Transporte Carga</option>'
                );
            }

            if (tp_operacion_actual == 2) {
                let tp_calculo_el = document.querySelector("input[name='metodo_detraccion']:checked");
                let tp_calculo_v = tp_calculo_el ? tp_calculo_el.value : null;

                // Recalcular según el método activo
                if (tp_calculo_v === 'directo') {
                    calcular_detraccion_encomienda();
                } else if (tp_calculo_v === 'sunat') {
                    // Para SUNAT, el total afecta solo si hay datos completos
                    let totalOp = parseFloat($("#total_operacion").val()) || 0;
                    let tm = parseFloat($("#tm_detraccion").val()) || 0;

                    if (totalOp > 0 && tm > 0 && rutaDestinoData) {
                        calcular_detraccion_encomienda();
                    }
                }
            }

        } else {
            // total <= 400: remover opción y limpiar
            opcionDetraccion.remove();

            if (tp_operacion_actual == 2) {
                $("#tp_operacion_venta").val("1").trigger("change");
                limpiar_campos_detraccion();
            }
        }
    });
    // MutationObserver mejorado
    const costoTd = document.getElementById('costo_total');

    const observer = new MutationObserver(() => {
        if (!isCalculatingDetraccion) {
            let costoTotal = parseFloat(costoTd.textContent.replace('S/', '')) || 0;
            let valorActual = parseFloat($("#total_operacion").val()) || 0;

            // Solo actualizar si cambió
            if (costoTotal !== valorActual) {
                $("#total_operacion").val(costoTotal);

                // Solo calcular si es método SUNAT y está visible
                let tp_calculo_el = document.querySelector("input[name='metodo_detraccion']:checked");
                let tp_calculo_v = tp_calculo_el ? tp_calculo_el.value : null;

                if (tp_calculo_v === 'sunat' && $("#tp_operacion_venta").val() == 2) {
                    calcular_detraccion_encomienda();
                }
            }
        }
    });

    observer.observe(costoTd, { childList: true });

    function limpiar_campos_detraccion(resetearMetodo = true) {

        // Prevenir cálculos durante la limpieza
        isCalculatingDetraccion = true;

        // Limpiar campos comunes
        $("#config_vehiculo").val("1").trigger("change");
        $("#ubigeo_destino").val("").trigger("change");
        $("#destino_detraccion").val("");
        $("#medio_pago_detraccion").val("1").trigger("change");
        $("#detalle_detraccion").val("");
        $("#total_operacion").val("");
        $("#tm_detraccion").val("");

        // Limpiar valores de referencia y base
        $("#v_ref_carga_efectiva").val("");
        $("#v_ref_carga_util").val("");
        $("#v_ref_servicio").val("");
        $("#base_detraccion").val("");
        $("#monto_detraccion").val("");
        $("#tiempo_credito").trigger('change');
        $("#monto_credito").val("0.00").trigger("change");
        // Limpiar selects personalizados
        if (typeof ubigeoOrigenD !== 'undefined') ubigeoOrigenD.clear(true);
        if (typeof ubigeoDestinoD !== 'undefined') ubigeoDestinoD.clear(true);
        if (typeof ruta_destino !== 'undefined') ruta_destino.clear(true);
        if (typeof ruta_origen !== 'undefined') ruta_origen.setValue('0', true);

        // Limpiar datos de rutas
        rutaDestinoData = null;
        rutaOrigenData = null;

        div_detraccion.classList.add("d-none");

        // Resetear método solo si se solicita
        if (resetearMetodo) {
            $("input[name='metodo_detraccion'][value='sunat']").prop("checked", true);
            $("#div_calculos_detraccion").removeClass("d-none");
        }

        isCalculatingDetraccion = false;
    }

    /* Detracciones directas 4% */
    $("input[name='metodo_detraccion']").on("change", function () {
        let valorSeleccionado = $(this).val();

        if (valorSeleccionado === "sunat") {
            $("#div_calculos_detraccion").removeClass("d-none");

            $("#v_ref_carga_efectiva").val('');
            $("#v_ref_carga_util").val('');
            $("#v_ref_servicio").val('');
            $("#base_detraccion").val('');
            $("#monto_detraccion").val('');

        } else if (valorSeleccionado === "directo") {
            $("#div_calculos_detraccion").addClass("d-none");

            $("#v_ref_carga_efectiva").val('');
            $("#v_ref_carga_util").val('');
            $("#v_ref_servicio").val('');

            calcular_detraccion_encomienda();
        }
    });

    //Apartado de cuotas
    $(function () {

        const $tbodyCuotas = $('#tbody_cuotas');
        const $btnAgregar = $('#btn_agregar_cuota');
        const $totalCuotasEl = $('#total_cuotas_monto');
        const $tiempoCredito = $('#tiempo_credito');

        let diasCredito = 0; // días del "tiempo de crédito" actualmente seleccionado

        // --- Helpers de fecha ---
        function obtenerDiasPorTiempoCredito(valor) {
            switch (valor) {
                case "1": return 30;
                case "2": return 0;
                case "3": return 15;
                case "4": return 45;
                case "5": return 60;
                default: return 0;
            }
        }

        function formatearFecha(date) {
            const yyyy = date.getFullYear();
            const mm = String(date.getMonth() + 1).padStart(2, '0');
            const dd = String(date.getDate()).padStart(2, '0');
            return `${yyyy}-${mm}-${dd}`;
        }

        function calcularFechaDesdeHoy(diasOffset) {
            const hoy = new Date();
            hoy.setDate(hoy.getDate() + diasOffset);
            return formatearFecha(hoy);
        }

        // --- Plantilla de fila ---
        function crearFilaCuota() {
            return $(`
            <tr class="fila_cuota">
                <td class="text-center num_cuota"></td>
                <td>
                    <input type="date" class="form-control form-control-sm fecha_credito_item"
                        name="fecha_credito[]" aria-label="fecha_credito">
                </td>
                <td>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text py-0">$</span>
                        <input type="number" class="form-control monto_credito_item"
                            placeholder="0.00" step="0.01" name="monto_credito[]"
                            aria-label="monto_credito">
                    </div>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger btn_quitar_cuota">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
        `);
        }

        function renumerarCuotas() {
            const $filas = $tbodyCuotas.find('.fila_cuota');
            $filas.each(function (index) {
                $(this).find('.num_cuota').text(index + 1);
                $(this).find('.btn_quitar_cuota').prop('disabled', $filas.length === 1);
            });
        }

        function actualizarTotalCuotas() {
            let total = 0;
            $tbodyCuotas.find('.monto_credito_item').each(function () {
                const valor = parseFloat($(this).val());
                if (!isNaN(valor)) total += valor;
            });
            $totalCuotasEl.text('$ ' + total.toFixed(2));
            return total;
        }

        // --- Validación de fechas duplicadas ---
        function validarFechasDuplicadas() {
            const $inputs = $tbodyCuotas.find('.fecha_credito_item');
            const conteo = {};

            $inputs.each(function () {
                const val = $(this).val();
                if (val) conteo[val] = (conteo[val] || 0) + 1;
            });

            let hayDuplicados = false;
            $inputs.each(function () {
                const val = $(this).val();
                if (val && conteo[val] > 1) {
                    $(this).addClass('is-invalid');
                    hayDuplicados = true;
                } else {
                    $(this).removeClass('is-invalid');
                }
            });

            // Mensaje general debajo de la tabla (opcional, agrega el div si quieres usarlo)
            $('#cuotas_fecha_error').toggleClass('d-none', !hayDuplicados);

            return !hayDuplicados;
        }

        // --- Recalcula la fecha de TODAS las cuotas según el tiempo de crédito ---
        function recalcularFechasCuotas() {
            $tbodyCuotas.find('.fila_cuota').each(function (index) {
                const offset = diasCredito * (index + 1); // cuota1=dias, cuota2=2*dias...
                $(this).find('.fecha_credito_item').val(calcularFechaDesdeHoy(offset));
            });
            validarFechasDuplicadas();
        }

        // --- Eventos ---
        $tiempoCredito.on('change', function () {
            diasCredito = obtenerDiasPorTiempoCredito($(this).val());
            recalcularFechasCuotas();
        });

        $btnAgregar.on('click', function () {
            const $nuevaFila = crearFilaCuota();
            $tbodyCuotas.append($nuevaFila);

            // Auto-calcular fecha de la nueva cuota según su posición
            const numFilas = $tbodyCuotas.find('.fila_cuota').length;
            const offset = diasCredito * numFilas;
            $nuevaFila.find('.fecha_credito_item').val(calcularFechaDesdeHoy(offset));

            renumerarCuotas();
            actualizarTotalCuotas();
            validarFechasDuplicadas();
        });

        $tbodyCuotas.on('click', '.btn_quitar_cuota', function () {
            $(this).closest('.fila_cuota').remove();
            renumerarCuotas();
            actualizarTotalCuotas();
            validarFechasDuplicadas();
        });

        $tbodyCuotas.on('input', '.monto_credito_item', function () {
            actualizarTotalCuotas();
        });

        $tbodyCuotas.on('change', '.fecha_credito_item', function () {
            validarFechasDuplicadas();
        });

        renumerarCuotas();
        actualizarTotalCuotas();
    });

    // Logica de forma pago credito 
    $("#forma_pago").on('change', function () {
        let forma_pago = $(this).val();
        if (forma_pago == "2") {
            div_cuota.classList.remove('d-none');
            $('#medio_pago').prop('disabled', true).trigger('change');
        } else {
            div_cuota.classList.add('d-none');
            $('#medio_pago').prop('disabled', false).trigger('change');
        }
    });

    $(document).ready(function () {
        $("#tiempo_credito").trigger('change');
        $("#forma_pago").trigger('change');
    });

    $("#tiempo_credito").on('change', function () {
        let tiempo_credito = $(this).val();
        let dias = 0;

        switch (tiempo_credito) {
            case "1":
                dias = 30;
                break;
            case "2":
                dias = 0;
                break;
            case "3":
                dias = 15;
                break;
            case "4":
                dias = 45;
                break;
            case "5":
                dias = 60;
                break;
            default:
                dias = 0;
        }

        const hoy = new Date();
        hoy.setDate(hoy.getDate() + dias);

        const yyyy = hoy.getFullYear();
        const mm = String(hoy.getMonth() + 1).padStart(2, '0');
        const dd = String(hoy.getDate()).padStart(2, '0');

        const fecha_resultado = `${yyyy}-${mm}-${dd}`;

        $("#fecha_credito").val(fecha_resultado);
    });


})