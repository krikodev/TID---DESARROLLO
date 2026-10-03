import * as instance from "../../../public/js/instance.js"

let btn_add = document.querySelector("#add_element");

let disminuir = document.querySelector("#disminuir");
let aumentar = document.querySelector("#aumentar");
let asiento = document.getElementById("asiento_config");
let btn_grid_horizontal = document.querySelector("#btn_grid_horizontal");
let btn_grid_vertical = document.querySelector("#btn_grid_vertical");

const parent_vehiculo = document.querySelector("#parent_vehiculo");

let form_vehiculo = document.querySelector("#form_vehiculo");
let id_vehiculo_selected = document.querySelector("#id_vehiculo_selected");
let piso_config = document.querySelector("#piso_config");
let button_cancel_config = document.querySelector("#button_cancel_config");
let button_save_config = document.querySelector("#button_save_config");
let button_loadSaveConfig = document.querySelector("#button_loadSaveConfig");

// Dimensiones independientes por piso
let floorDimensions = {
    1: { height: 500, width: 300 },
    2: { height: 500, width: 300 }
};

const resizableContainer = interact('#parent_vehiculo')
const draggable = interact('.draggable')

// Reset formulario al Cerrar modal
$("#modal_configuracion").on("hidden.bs.modal", (e) => {
    $("#modal_configuracion").find("form").trigger("reset")
    let forms = document.querySelectorAll(".needs-validation")
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.classList.remove("was-validated")
    })
    $("#piso_config option").remove()
    parent_vehiculo.style.width = "300px"
    Object.values(parent_vehiculo.childNodes).map((e) => {
        parent_vehiculo.removeChild(e)
    })
    id_vehiculo_selected.value = "";
    $(".img_vehiculo").removeClass("d-none")
    parent_vehiculo.classList.remove("vehiculo_segundoPiso")

    // Resetear dimensiones por piso
    floorDimensions = {
        1: { height: 500, width: 300 },
        2: { height: 500, width: 300 }
    };
})

draggable.on('tap', function (event) {
    draggable.reflow({ name: 'drag', axis: 'xy' })
})

resizableContainer.resizable({
    edges: { bottom: false, right: false },
    modifiers: [
        interact.modifiers.restrictEdges({
            outer: 'html',
        }),
        interact.modifiers.restrictEdges({
            min: { width: 200, height: 200 }
        })
    ],
    listeners: {
        move: function (event) {
            let { x, y } = event.target.dataset

            x = (parseFloat(x) || 0) + event.deltaRect.left
            y = (parseFloat(y) || 0) + event.deltaRect.top

            Object.assign(event.target.style, {
                width: `${event.rect.width}px`,
                height: `${event.rect.height}px`,
                transform: `translate(${x}px, ${y}px)`
            })

            Object.assign(event.target.dataset, { x, y })
        }
    }
})

draggable.draggable({
    inertia: true,
    modifiers: [
        interact.modifiers.restrictRect({
            restriction: document.querySelector('#parent_vehiculo'),
            endOnly: false,
        }),
    ],
    listeners: {
        move(event) {
            let x = (parseFloat(event.target.style.left) || 0) + event.dx
            let y = (parseFloat(event.target.style.top) || 0) + event.dy

            event.target.style.cssText =
                `
                transform: ${event.target.style.transform};
                left:${x}px;
                top:${y}px;
            `
            Object.assign(event.target.style.left, { x })
            Object.assign(event.target.style.top, { y })
        },
    }
})

// Agregar asiento
btn_add.addEventListener("click", (e) => {
    if (asiento.value && asiento.value != 0 && asiento.value > 0) {
        let asientos = document.querySelectorAll(".draggable .text_obj");
        let errors = 0;

        if (asientos) {
            asientos.forEach(element => {
                if (element.textContent.trim() === asiento.value.trim()) {
                    instance.Toast.operacion_erronea('El número de asiento ya existe');
                    errors += 1;
                }
            });

            if (errors === 0) {
                crear_objeto(instance.CONSTS.ICONOS.ASIENTO, asiento.value.trim(), 'asiento');
                asiento.value = "";
            }
        }
    } else {
        instance.Toast.operacion_erronea('Ingrese un número de asiento válido');
    }
});

// Agregar objetos
let herramientas = document.querySelectorAll("#herramientas button");
Object.values(herramientas).forEach(btn => {
    btn.addEventListener("click", (e) => {
        switch (btn.textContent.trim()) {
            case "Baño":
                crear_objeto(instance.CONSTS.ICONOS.BAÑO);
                break;
            case "Escalera":
                crear_objeto(instance.CONSTS.ICONOS.SCALERA);
                break;
            case "Televisión":
                crear_objeto(instance.CONSTS.ICONOS.TELEVISION);
                break;
            case "Refrigeradora":
                crear_objeto(instance.CONSTS.ICONOS.REFRIGERADORA);
                break;
            case "Terramoza":
                crear_objeto(instance.CONSTS.ICONOS.TERRAMOZA);
                break;
            default:
                break;
        }
    })
});

// Disminuir altura del piso actual
disminuir.addEventListener("click", (e) => {
    const newHeight = parent_vehiculo.clientHeight - 25;
    parent_vehiculo.style.height = `${newHeight}px`;
    floorDimensions[piso_config.value].height = newHeight;
})

// Aumentar altura del piso actual
aumentar.addEventListener("click", (e) => {
    const newHeight = parent_vehiculo.clientHeight + 25;
    parent_vehiculo.style.height = `${newHeight}px`;
    floorDimensions[piso_config.value].height = newHeight;
})

const crear_objeto = (icon_obj, span_text_content = "", tp_obj = 'obj') => {
    let div = document.createElement("div");
    div.className = `draggable position-absolute objeto z-3`
    div.setAttribute("floor", piso_config.value)
    div.setAttribute("tp_obj", tp_obj)
    tp_obj == "asiento" ? div.setAttribute("tp_asiento", $('input:radio[name="tp_asiento"]:checked').val()) : null
    div.id = parseInt(parent_vehiculo.childNodes.length != 0 ? (parent_vehiculo.childNodes[parent_vehiculo.childElementCount - 1]).id : 0) + 1
    div.style.cssText = "top:10px; left:10px"

    div.insertAdjacentHTML("beforeend",
        `
            <i class="${icon_obj} icon_obj" class-icon="${icon_obj}" style="font-style: normal; font-size:${instance.CONSTS.OBJETOS.TAMANO}; color: ${tp_obj == "asiento" ? instance.CONSTS.COLORES.TIPO_ASIENTO[$('input:radio[name="tp_asiento"]:checked').val().toUpperCase()] : instance.CONSTS.COLORES.OBJETO_DEFAULT}">
                <span class="position-absolute start-50 translate-middle text_obj fw-bolder mt-3" style="font-size:${instance.CONSTS.OBJETOS.TAMANO_TEXT}">${span_text_content}</span>
            </i>
            
            <i class="bi bi-dash position-absolute top-0 start-100 translate-middle icon_delete d-none"></i>
            <i class="bi bi-arrow-clockwise position-absolute top-0 start-0 translate-middle icon_rotar d-none"></i>
`
    )

    parent_vehiculo.appendChild(div)

    // Eliminar div al dar click
    div.querySelector(".icon_delete").addEventListener("click", (e) => {
        Swal.fire({
            title: '¿Está seguro que desea eliminar el objeto seleccionado?',
            text: "No podrá revertir los cambios",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Si, eliminar'
        }).then((result) => {
            if (result.isConfirmed) {
                parent_vehiculo.removeChild(document.getElementById(div.id))
            }
        })
    })

    // Mostrar y ocultar icon_rotar al hacer hover
    div.addEventListener("mouseover", (e) => div.querySelector(".icon_rotar").classList.remove("d-none"))
    div.addEventListener("mouseout", (e) => div.querySelector(".icon_rotar").classList.add("d-none"))

    // Mostrar y ocultar icon_delete al hacer hover
    div.addEventListener("mouseover", (e) => div.querySelector(".icon_delete").classList.remove("d-none"))
    div.addEventListener("mouseout", (e) => div.querySelector(".icon_delete").classList.add("d-none"))

    let angle = 0;
    // Girar el div al dar click
    div.querySelector(".icon_rotar").addEventListener("click", (e) => {
        angle = (angle + 45) % 360;
        div.style.cssText = `
        transform: rotate(${angle}deg);
        left: ${div.style.left};
        top: ${div.style.top};
        `
    })
}

piso_config.addEventListener("change", () => {

    // Guardar dimensiones del piso anterior antes de cambiar
    const previousFloor = piso_config.value == 1 ? 2 : 1;
    floorDimensions[previousFloor].height = parent_vehiculo.clientHeight;
    floorDimensions[previousFloor].width = parent_vehiculo.clientWidth;

    const esPrimerPiso = piso_config.value == 1;

    $("div[floor='1']").toggleClass("d-none", !esPrimerPiso);
    $("div[floor='2']").toggleClass("d-none", esPrimerPiso);

    $(".img_vehiculo").toggleClass("d-none", !esPrimerPiso);

    parent_vehiculo.classList.toggle("vehiculo_segundoPiso", !esPrimerPiso);

    // Restaurar dimensiones independientes del nuevo piso
    parent_vehiculo.style.height = floorDimensions[piso_config.value].height + "px";
    parent_vehiculo.style.width = floorDimensions[piso_config.value].width + "px";
});


// Grilla horizontal
btn_grid_horizontal.addEventListener("click", (e) => {
    $("#grid_horizontal").remove()
    parent_vehiculo.insertAdjacentHTML('beforeend', `<canvas class="position-absolute top-0 start-0" id="grid_horizontal" style="width: 100%; height:100%" ></canvas>`)
    let canvas = document.getElementById("grid_horizontal")
    let context = canvas.getContext("2d")
    context.lineWidth = 0.07
    context.strokeStyle = "#212121"
    if (!btn_grid_horizontal.classList.contains("view")) {
        btn_grid_horizontal.classList.replace("btnColorGray", "btnColorViolet")
        btn_grid_horizontal.classList.add("view")
        for (let x = 1; x < parent_vehiculo.clientWidth; x += (parent_vehiculo.clientWidth >= 700 ? 15 : 15)) {
            context.moveTo(0, x)
            context.lineTo(500, x)
        }
        context.stroke();
    } else {
        btn_grid_horizontal.classList.replace("btnColorViolet", "btnColorGray")
        btn_grid_horizontal.classList.remove("view")
        parent_vehiculo.removeChild(canvas)
    }
})

// Grilla vertical
btn_grid_vertical.addEventListener("click", (e) => {
    $("#grid_vertical").remove()
    parent_vehiculo.insertAdjacentHTML('beforeend', `<canvas class="position-absolute top-0 start-0" id="grid_vertical" style="width: 100%; height:100%"></canvas>`)
    let canvas = document.getElementById("grid_vertical")
    let context = canvas.getContext("2d")
    context.lineWidth = 0.08
    context.strokeStyle = "#212121"
    if (!btn_grid_vertical.classList.contains("view")) {
        btn_grid_vertical.classList.replace("btnColorGray", "btnColorViolet")
        btn_grid_vertical.classList.add("view")
        for (let y = 1; y < parent_vehiculo.clientHeight; y += (parent_vehiculo.clientHeight >= 700 ? 15 : 50)) {
            context.moveTo(y, 0)
            context.lineTo(y, 500)
        }
        context.stroke();
    } else {
        btn_grid_vertical.classList.replace("btnColorViolet", "btnColorGray")
        btn_grid_vertical.classList.remove("view")
        parent_vehiculo.removeChild(canvas)
    }
})

form_vehiculo.addEventListener("submit", (e) => {
    e.preventDefault()
    let formData = new FormData(form_vehiculo);
    let vehiculo_objs = [];
    Object.values(parent_vehiculo.querySelectorAll(".draggable")).map((e, i, array) => {
        return vehiculo_objs.push({
            'id_obj_vehiculo': e.id,
            'id_vehiculo': id_vehiculo_selected.value,
            'piso': e.getAttribute("floor"),
            'left_obj': e.style.left,
            'top_obj': e.style.top,
            'rotate_obj': e.style.transform ? ((e.style.transform).split("(")[1]).split(")")[0] : '',
            'icon_obj': e.querySelector(".icon_obj").getAttribute("class-icon"),
            'text_obj': e.querySelector(".text_obj").textContent,
            'tp_obj': e.getAttribute("tp_obj"),
            'tp_asiento': e.getAttribute("tp_asiento")
        })
    })

    // Guardar dimensiones del piso activo antes de enviar
    floorDimensions[piso_config.value].height = parent_vehiculo.clientHeight;
    floorDimensions[piso_config.value].width = parent_vehiculo.clientWidth;

    formData.set("id_vehiculo", id_vehiculo_selected.value)

    // Enviar dimensiones de cada piso por separado
    formData.set("vehiculo_width_piso1", floorDimensions[1].width + "px");
    formData.set("vehiculo_height_piso1", floorDimensions[1].height + "px");
    formData.set("vehiculo_width_piso2", floorDimensions[2].width + "px");
    formData.set("vehiculo_height_piso2", floorDimensions[2].height + "px");

    formData.set("vehiculo_objs", JSON.stringify(vehiculo_objs))

    button_save_config.classList.add("d-none")
    button_cancel_config.classList.add("d-none")
    button_loadSaveConfig.classList.remove("d-none")
    fetch(instance._URL_ + "vehiculo/config_vehiculo", {
        method: 'POST',
        body: formData
    })
        .then(response => {
            if (!response.ok) throw new Error(response.status)
            return response.json()
        })
        .then(data => {
            if (data && data.success) {
                instance.Toast.operacion_exitosa(data.message)
                $('#modal_configuracion').modal('toggle')

            } else {
                instance.Toast.operacion_erronea(data.message)
            }
            button_save_config.classList.remove("d-none")
            button_cancel_config.classList.remove("d-none")
            button_loadSaveConfig.classList.add("d-none")
        }).catch(error => {
            instance.Toast.operacion_erronea(error.message)
            button_save_config.classList.remove("d-none")
            button_cancel_config.classList.remove("d-none")
            button_loadSaveConfig.classList.add("d-none")
        })
})