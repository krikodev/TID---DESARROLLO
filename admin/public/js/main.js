import { Tippy } from "../../public/js/lib/tippy.min.js";


document.addEventListener("DOMContentLoaded", function (event) {
    new WOW().init();

    let buttonRefresh = document.querySelectorAll(".buttonRefreshPagina");
    buttonRefresh.forEach(element => {
        $(element).click(function (e) {
            e.preventDefault();
            location.reload();
        });
    });

    //Tippy
    let tooltip = new Tippy();
    let obj = [
        {
            "label": ".buttonRefreshTable",
            "obj": {
                "content": "Recargar la tabla",
                "animation": "scale",
                "placement": "top",
            }
        },
        {
            "label": ".buttonRefreshPagina",
            "obj": {
                "content": "Recargar la página",
                "animation": "scale",
                "placement": "top",
            }
        },
        {
            "label": ".buttonPaginaWeb",
            "obj": {
                "content": "Visitar sitio web",
                "animation": "scale",
                "placement": "top",
            }
        },
        {
            "label": ".sucursal_session",
            "obj": {
                "content": `${$("#cod_sucursal_sesion").val()} | ${$("#descripcion_sesion").val()} | ${$("#tipo_sesion").val()} `,
                "animation": "scale",
                "placement": "bottom",
            }
        },
        {
            "label": ".infoEmailPass",
            "obj": {
                "content": `En caso desea trabajar con el envío de correos electrónicos, debe rellenar correctamente este campo.`,
                "animation": "scale",
                "placement": "top",
            }
        }
    ];

    for (let i = 0; i < obj.length; i++) {
        tooltip.init(obj[i].label, obj[i].obj);
    }

    //Autofocus Select2
    $(document).on('select2:open', () => {
        document.querySelector('.select2-search__field').focus();
    });

    // Fetch all the forms we want to apply custom Bootstrap validation styles to
    let forms = document.querySelectorAll('.needs-validation')

    // Loop over them and prevent submission
    Array.prototype.slice.call(forms)
        .forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }

                form.classList.add('was-validated')
            }, false)
        })
});