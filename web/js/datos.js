
document.addEventListener('DOMContentLoaded', function () {


    // Manejador para cargar formularios cuando se hace clic en la pestaña de datos
    const btn_datos = document.getElementById("pills-datos-tab");
    const btn_pago = document.getElementById("pills-pago-tab");
    const buscador = document.getElementById("buscador");

    btn_datos.addEventListener("click", function () {
        cargarFormulariosPasajeros();
        buscador.classList.add('d-none');
    });

    let btnDatos = document.getElementById("pills-datos-tab");
    function verificarAsientos() {
        let dtaLocalStorage = obtenerDatosLocalStorage();

        if (Array.isArray(dtaLocalStorage.asientosSeleccionadosData) && dtaLocalStorage.asientosSeleccionadosData.length > 0) {
            btnDatos.removeAttribute("disabled"); // Habilitar el botón
        } else {
            btnDatos.setAttribute("disabled", "true"); // Deshabilitar el botón
            btn_pago.setAttribute("disabled", "true"); // Deshabilitar el botón
        }
    }

    /**
     * Obtiene datos almacenados en localStorage
     * @returns {Object} - Datos almacenados
     */
    function obtenerDatosLocalStorage() {
        return JSON.parse(localStorage.getItem('venta')) || {};
    }

    // Verificar al cargar la página
    verificarAsientos();

    // Monitorear cambios en localStorage dentro de la misma pestaña
    setInterval(verificarAsientos, 1000); // Cada 1 segundo revisa el almacenamiento

    // Opcional: Escuchar cambios en localStorage desde otras pestañas
    window.addEventListener("storage", verificarAsientos);


    // Función para cargar formularios de pasajeros
    function cargarFormulariosPasajeros() {
        // Verificar si hay datos guardados en localStorage
        if (!localStorage.getItem("venta")) {
            return; // No hay datos para mostrar
        }

        // Ocultar spinner (si existe)
        $('#spinner').removeClass('show');

        // Obtener datos desde localStorage usando la función auxiliar
        const dataLocalStorage = obtenerDatosLocalStorage();

        // Obtener datos adicionales (pueden estar en el objeto o en localStorage de forma separada)
        const NombreOrigen = dataLocalStorage.NombreO || localStorage.getItem('NombreO');
        const NombreDestino = dataLocalStorage.NombreD || localStorage.getItem('NombreD');
        const Descrip = dataLocalStorage.Descripcion || localStorage.getItem('Descripcion');

        if (dataLocalStorage) {
            // Usar el array unificado de asientos seleccionados
            let asientosSeleccionadosData = dataLocalStorage.asientosSeleccionadosData || [];
            let sumaTotal = dataLocalStorage.sumaTotal || 0;
            let origenN = NombreOrigen;
            let destinoN = NombreDestino;
            let fechaIda = dataLocalStorage.fechaIda || '';
            let descripcion = Descrip;
            let hora_salida = dataLocalStorage.hora_salida || '';

            // Actualizar UI: asignar valores a elementos de la página
            let origenSpan = document.getElementById('origen_p');
            let destinoSpan = document.getElementById('destino_p');
            let precioTotal = document.getElementById('precioTotal');
            let numAsientos = document.getElementById('numAsientos');
            let fechaIdaP = document.getElementById('fechaIda');
            let descripcionSpan = document.getElementById('descripcion');
            let hora_salidaSpan = document.getElementById('hora_salida');

            if (origenSpan) origenSpan.textContent = origenN.toUpperCase();
            if (destinoSpan) destinoSpan.textContent = destinoN.toUpperCase();
            if (precioTotal) precioTotal.textContent = sumaTotal;
            if (numAsientos) numAsientos.textContent = asientosSeleccionadosData.length;
            if (fechaIdaP) fechaIdaP.textContent = fechaIda;
            if (descripcionSpan) descripcionSpan.textContent = descripcion.toUpperCase();
            if (hora_salidaSpan) hora_salidaSpan.textContent = hora_salida;
        }

        // Obtener contenedor para los formularios
        const div_padreForm = document.getElementById("div_padreForm");

        // Limpiar contenedor antes de agregar nuevos formularios
        div_padreForm.innerHTML = '';

        // Obtener el array unificado de asientos
        const asientosData = dataLocalStorage.asientosSeleccionadosData || [];

        // Crear un formulario para cada pasajero basado en la información del asiento
        for (let i = 0; i < asientosData.length; i++) {
            const formId = "formUsuario" + i;
            const seat = asientosData[i]; // Cada elemento es un objeto con { idAsiento, numAsiento, tipoAsiento, precioAsiento }
            const numAsiento = seat.numAsiento;
            let tipoAsiento = seat.tipoAsiento;
            const idAsiento = seat.idAsiento;
            const precioUnitario = seat.precioAsiento;

            // Convertir el tipo de asiento a un formato legible y determinar la clase CSS correspondiente
            const tipoAsientoMostrar = tipoAsiento === "premium" ? "INDIVIDUAL" : "COMPARTIDO";
            const claseAsiento = tipoAsiento === "premium" ? "premiun" : "normal";

            // Insertar el HTML del formulario en el contenedor
            div_padreForm.insertAdjacentHTML('beforeend', `
            <div id="${formId}" class="passenger-form">
                <h6 class="passenger-number">PASAJERO ${i + 1}</h6>
                <span class="fw" style="font-size:12px;">ASIENTO ${numAsiento}</span>
                <span class="fw ${claseAsiento}" style="font-size:12px;">ASIENTO ${tipoAsientoMostrar}</span>
        
                <form class="formularioUser" method="POST" id="formUsuario">
                    <input type="hidden" name="id_asiento" value="${idAsiento}">
                    <input type="hidden" name="PrecioU" value="${precioUnitario}">
                    <input type="hidden" name="numAsiento" value="${numAsiento}">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>TIPO DE DOCUMENTO</label>
                            <select class="form-control tp_doc" name="tp_doc" required>
                                <option value="1">DNI</option>
                                <option value="4">Carnet extranjería</option>
                                <option value="6">RUC</option>
                                <option value="7">Pasaporte</option>
                                <option value="0">Otros documentos</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>NÚMERO DE DOCUMENTO</label>
                            <div class="search-input">
                                <input class="form-control num_doc" type="text" name="num_doc" placeholder="N° Documento" required maxlength="8">
                                <button class="search-btn" type="button" data-index="${i}">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>NOMBRES</label>
                            <input class="form-control nombres" type="text" name="nombres" placeholder="Nombres" required>
                        </div>
                        <div class="form-group">
                            <label>APELLIDOS</label>
                            <input class="form-control apellidos" type="text" name="apellidos" placeholder="Apellidos" required>
                        </div>
                    </div>
            
                    <div class="form-row">
                        <div class="form-group">
                            <label>FECHA DE NACIMIENTO</label>
                            <input class="form-control fecha_nacimiento" type="date" name="fecha_nacimiento" required>
                        </div>
                        <div class="form-group">
                            <label>GÉNERO</label>
                            <select class="form-control genero" name="genero" required>
                                <option value="M">Masculino</option>
                                <option value="F">Femenino</option>
                            </select>
                        </div>
                    </div>
            
                    <div class="form-row">
                        <div class="form-group">
                            <label>CORREO</label>
                            <input class="form-control email" type="email" name="email" placeholder="Correo electrónico" required>
                        </div>
                        <div class="form-group">
                            <label>TELÉFONO</label>
                            <input class="form-control telefono" type="text" name="telefono" placeholder="Número de teléfono" required maxlength="9">
                        </div>
                    </div>
                </form>
            </div>
            <hr>
        `);
        }

        // Aplicar validaciones a los campos del formulario
        aplicarValidaciones();

        // Agregar sección de términos y condiciones
        agregarTerminosYCondiciones(div_padreForm);

        // Configurar botones de búsqueda para auto-completar datos
        configurarBotonesBusqueda();
    }

    // Función para aplicar validaciones a los campos
    function aplicarValidaciones() {
        // Aplicar restricciones de entrada 
        if (typeof Validate !== 'undefined') {
            Validate.allowInputNum([".num_doc"]);
            Validate.allowInputStringSpace([".nombres"]);
            Validate.allowInputStringSpace([".apellidos"]);
            Validate.allowInputNum([".telefono"]);
        }
    }

    // Función para agregar la sección de términos y condiciones
    function agregarTerminosYCondiciones(contenedor) {
        contenedor.insertAdjacentHTML('beforeend', `
        <div class="terms-section d-flex flex-column">
            <div class="checkbox-wrapper">
                <input type="checkbox" id="miCheckbox" required>
                <label for="miCheckbox">Acepto los términos y condiciones</label>
            </div>
            
            <a href="#" class="terms-link readCondisi" id="miParrafo">Leer términos y condiciones</a>
            <button class="continue-btn btnEndDatos" id="botonValidar">Continuar</button>
        </div>
    `);

        // Configurar evento para mostrar términos y condiciones
        const mensajeCondisio = document.getElementById('miParrafo');
        mensajeCondisio.addEventListener('click', function () {
            Swal.fire({
                title: "Términos generales de la contratación del servicio de viaje",
                html: `
            <p class="text-start" style="font-size:13px;">
    La adquisición de un boleto de viaje en EMPRESA INTERNACIONAL LOS LIBERTADORES S.A.C., implica el total
conocimiento y aceptación de las condiciones que especificamos a continuación: <br> 
1. El cliente es responsable de verificar que los datos consignados en el boleto, estén de
acuerdo a su solicitud al momento de adquirirlo. <br>
2. El boleto de viaje es personal y transferible, conforme a ley, siendo el único comprobante
válido para viajar, en la fecha y hora impresa. <br>
3. Las tarifas pueden ser referenciales, sujetas a variación debido a la temporada sin previo
aviso. <br>
4. El boleto de viaje es personal y transferible, el trámite de cambio de titular (sea persona
jurídica o natural) se podrá realizar en la agencia donde se adquirió el boleto hasta 06
horas antes de la salida del bus consignada en el boleto de viaje, de no poder acercarse
el titular, podrá enviar a una persona a realizar el cambio, con una carta poder simple
adjuntando la copia de DNI del titular y el boleto impreso, asimismo, se realizará un cobro
de S/. 10.00 soles por concepto de gastos administrativos. <br>
5. El titular (sea persona jurídica o natural) podrá realizar la postergación del viaje en la
agencia donde se adquirió el boleto con un aviso anticipado de 06 horas de la salida del
bus consignada en el boleto de viaje, asimismo, se realizará un cobro de S/. 10.00 por
concepto de gastos administrativos, del mismo modo quedará sujeto a aceptar las nuevas
condiciones del servicio, cambios de tarifa, disponibilidad de asientos y tipo de servicio, de
no poder acercarse el titular, podrá enviar a una persona a realizar la postergación, con
una carta poder adjuntando la copia de DNI del titular y el boleto impreso. Las
postergaciones son validas hasta por 180 días calendarios, en caso no utilice el pasaje en
el tiempo acordado, el titular perderá el valor de boleto en su totalidad. <br>
6. Solo titular (sea persona jurídica o natural) del boleto de viaje podrá solicitar la devolución
del monto pagado por su boleto de viaje, en la misma agencia donde adquirido el boleto
hasta con 12 horas de anticipación de la salida del bus consignada en el boleto de viaje,
siendo que se realizará la retención de S/. 10.00 soles por concepto de gastos
administrativos; no se podrá realizar la solicitud de devolución para boletos de viaje
adquiridos en promociones, sorteos o con descuentos. <br>
7. El titular (persona natural o jurídica) acepta las condiciones de la habilitación como
reintegro de tarifas, costos administrativos, disponibilidad de asientos y tipos de servicios. <br>
8. El pasajero debe presentarse en la puerta de embarque del terminal 30 minutos antes de
la hora de viaje, debiendo presentar su boleto de Viaje de forma física o electrónica y
documento de identidad Vigente (DNI) para embarcarse, además de los documentos
migratorios de ser el caso (Carnet de Extranjería y/o Pasaporte), esta medida incluye a los
menores de edad. En caso de incumplimiento, perderá el derecho a viajar y el valor del
Boleto de Viaje pagado. <br>
9. En caso que el pasajero no se presente a la hora de embarque de acuerdo a la fecha y
hora señalada en su boleto de viaje, pierde su derecho a viajar y autoriza desde ya a la
EMPRESA INTERNACIONAL LOS LIBERTADORES S.A.C. a disponer del asiento no ocupado. <br>
10. No se permitirá el embarque y/o desembarque en puntos diferentes al registrado en el
Boleto de Viaje. Nuestros servicios realizan escalas comerciales en la ruta de acuerdo a las
estaciones de ruta y paraderos autorizados por el MTC. <br>
11. La hora de embarque y desembarque en puntos intermedios es referencial, está sujeta a
condiciones de la vía, climáticas, de tránsito y otros factores ajenos a la empresa. <br>
12. No se permitirá el embarque y/o desembarque en puntos diferentes al registrado en el
Boleto de Viaje. Nuestros servicios realizan escalas comerciales en la ruta de acuerdo a las
estaciones de ruta y paraderos autorizados por el MTC. <br>
13. De acuerdo a la Ley 27337, en su Artículo 111 “los menores de edad que viajen solos o en
compañía de un adulto que no sean sus padres deben presentar el permiso notarial de
viaje de sus padres”. El cliente acepta que es requisito indispensable la presentación del
permiso notarial (original y copia) al momento de la compra del boleto de viaje y al
momento del embarque del menor de edad que viaja sin acompañamiento de los padres. <br>
14. Los niños de 5 años o más pagarán Boleto de viaje completo con derecho a asiento, según
el D.S. 017-2009, Art. 42.1.9.3. los menores de 5 años que ocupan asiento pagarán el 100%
de un Boleto de viaje. Los menores de 5 años que no ocupan asiento, no tienen derecho a
alimentación, ni traslado de equipaje. <br>
15. El titular pierde su derecho a viajar y el valor del Boleto de Viaje cuando esté bajo influencia
de alcohol, drogas y/o estupefacientes o cuando su estado o condición física y/o
psicológica evidencie que puede poner en riesgo su integridad y la de los demás pasajeros. <br>
16. El pasajero está prohibido de abordar al bus con armas de fuego o elementos punzo
cortantes, así como materiales inflamables, explosivos, corrosivos, venenosos o similares. Es
obligación del pasajero permitir y dar todas las facilidades al personal de la empresa para
que efectúe la revisión de su equipaje de mano y de su persona. D.S. 017-2009 MTC.
Asimismo, los Pasajeros con Licencia de la DICSCAMEC, deberán brindar la facilidad para
que el arma sea custodiada por la empresa y será entregada al pasajero al finalizar el viaje. <br>
17. El pasajero está prohibido transportar en el bus (salón de la unidad y/o bodega) artículos
perecibles como frutas, verduras, flores, carnes, etc. En el caso de la remisión y/o traslado
de frutas y/o vegetales sometidos a control por parte de SENASA, la empresa está obligada
a restringir la remisión y/o traslado de dichos productos hospedantes de la Mosca de la
Fruta, siendo una restricción de una Autoridad Administrativa sobre el control de Salud, de
encontrarse alguno de estos artículos el pasajero no podrá abordar el bus. <br>
18. Está prohibido que el pasajero traslade animales o mascotas en el salón o bodega del bus,
salvo perros lazarillos debidamente acreditados en el Conadis. <br>
19. La empresa se reserva el derecho de trasladar a la bodega del vehículo equipaje u objetos
que el pasajero pretenda llevar en el salón del bus. <br>
20. El pasajero viaja asegurado mediante una póliza de accidentes personales (SOAT) cuya
cobertura es fijada por ley y el numero de la póliza esta descrita en su boleto de viaje. El
pasajero al viajar acepta las condiciones pactadas por la empresa con la compañía
aseguradora correspondiente. <br>
21. Si EMPRESA INTERNACIONAL LOS LIBERTADORES S.A.C. suspende la prestación de sus servicios por causas ajenas a
la empresa, se postergarán los boletos, pudiendo el pasajero realizar el viaje en fecha
posterior; sujeto a las condiciones de postergación señaladas en la Cláusula número 4 o en
su defecto se podrá realizar la respectiva devolución de su dinero, previa deducción del
costo administrativo. <br>
22. En caso de presentarse alguna eventualidad en el lugar de origen o durante el viaje, que
impida la prestación del servicio, EMPRESA INTERNACIONAL LOS LIBERTADORES S.A.C. realizará el transbordo en
otras unidades disponibles (propias o de terceros). Si por causas ajenas la Empresa se ve
imposibilitada de hacer el transbordo se reembolsará el monto equivalente al tramo de
viaje no recorrido, en las oficinas de la empresa. <br>
23. El pasajero tiene derecho hasta 15 kilos de equipaje libre para transportar en bodega, el
cual es personal e intransferible. el equipaje consta de maletas, maletines, mochilas y bolsos
con artículos de uso personal (Art. 2° D.S. 016-2006-EF – Reglamento de Equipaje y Menaje
de Casa) sin costo. El exceso de equipaje y carga acompañada será admitido previo pago
de la tarifa vigente publicada en nuestras oficinas y sujetas a la capacidad de las bodegas
del bus. El derecho de transportar equipaje aplica sólo para pasajeros que ocupen asiento.
Es responsabilidad del pasajero la custodia de sus equipajes de mano en el salón del bus,
así como del retiro de sus equipajes de la bodega del bus inmediatamente después de
desembarcar. La empresa no se responsabiliza por equipajes que no han sido declarados
para custodia. <br>
24. El pasajero tiene derecho a un equipaje de mano siempre y cuando cumpla con las
siguientes características (50cm de alto x 35cm de ancho y 21cm de profundidad, sin
ruedas y con un peso no mayor a 6kg), en caso el equipaje de mano no cumpla las
medidas indicadas se procederá indicar al pasajero que debe llevarlo en la bodega y en
caso el pasajero exceda los 20 kilos permitidos deberá realizar el pago del costo adicional
por exceso de equipaje. <br>
25. Es única y plena responsabilidad del pasajero la custodia de sus equipajes de mano en el
salón del bus. <br>
26. La empresa no se responsabiliza por dinero, alhajas, objetos de valor, artefactos, equipos
de audio, video, cómputo, laptops, tablets, etc…, transportados como equipaje que no
hayan sido declarados y puestos en custodia. En caso de pérdida, deterioro y/o sustracción
de equipajes en la bodega atribuibles a la empresa se aplicará lo establecido en el art. 76°
numeral 76.2.12 del D.S 017-2009-MTC. <br>
27. Está terminantemente prohibido el ingreso de drogas a las instalaciones de la empresa, así
como su traslado en los buses. El transporte de drogas y/o derivados es una actividad ilícita,
en caso de detección de droga, se aislará y custodiará el equipaje u objeto detectado e
informará a las autoridades correspondientes. <br>
28. El pasajero que desee consultar por algún objeto perdido, deberá acercarse a las agencias
de la empresa y solicitarlo indicado las características del objeto, el servicio, la ruta y fecha
en la que viajó; de ser ubicado el objeto, para que pueda ser entregado el pasajero
deberá presentar DNI/ Pasaporte (original y copia) y hacer una breve descripción del
objeto. <br>
29. Si la persona que viene a recoger un objeto y no es el propietario del mismo, deberá
presentar una autorización simple del titular y adjuntar a la misma una fotocopia de su DNI
del tercero y del titular. <br>
30. De presentarse intervenciones de autoridades administrativas o policiales a las unidades, el
servicio se verá interrumpido por un periodo de tiempo mientras dure la intervención; La
empresa no se hace responsable por dichas demoras y/o retrasos. <br>
31. La hora de llegada al destino es referencial <br>
32. La empresa no asume responsabilidad alguna por el estado físico o de salud del pasajero,
ni por cualquier trastorno o incidente que pudiera sobrevenir como consecuencia de dicho
estado no evidenciado. <br>
33. EMPRESA INTERNACIONAL LOS LIBERTADORES S.A.C. no asumirá responsabilidad alguna por los contenidos y ventas
de otras páginas web, ni garantizará la disponibilidad técnica, calidad, fiabilidad,
exactitud, veracidad, validez y constitucionalidad de cualquier material o información
contenida en el hipervínculo u otros lugares de Internet. <br>
34. La empresa no se responsabiliza por las fallas u omisión en la prestación de carga mediante
conector usb, pantallas de entretenimiento a bordo u otros que pudieran ocasionarse por
fallas ajenas a la representada. Estos servicios se ofrecen en calidad de cortesía debido a
que no es parte del servicio contratado. <br>
35. Si el pasajero decide desembarcar en ruta por algún motivo, será bajo su responsabilidad,
exonerando de Responsabilidad a EMPRESA INTERNACIONAL LOS LIBERTADORES S.A.C. y sólo podrá desembarcar
en un lugar Seguro. <br>
36. Las partes convienen que para las desavenencias que puedan surgir se someten en forma
incondicional a las normas y leyes de los jueces de Lima y Huancayo respectivamente,
declarando conocerlas y aceptarlas en su integridad.
    </p>
            `,
                confirmButtonText: 'Cerrar'
            });
        });

        // Configurar evento para el botón de validar/continuar
        const botonValidar = document.getElementById('botonValidar');
        if (botonValidar) {
            botonValidar.addEventListener('click', function () {
                validarYEnviarFormularios();
            });
        }
    }

    // Función para buscar pasajero y auto-completar datos
    function buscarPasajero(numDoc, formulario) {
        const numDocInput = formulario.querySelector('.num_doc');
        const tp_doc = formulario.querySelector('.tp_doc');
        const tipoDocumento = tp_doc.value == 6 ? 'RUC' : 'DNI';
        let tipoDoc = tp_doc.value == 6 ? 'sunat' : 'reniec';

        // Verificar que el número de documento sea válido
        if (numDocInput.value.trim() != "" && (numDocInput.value.length >= 8 || numDocInput.value.length <= 11)) {
            let formData = new FormData()
            formData.set("tp_docu", tp_doc.value)
            formData.set("docu", numDocInput.value)
            formData.set("num_docu", numDocInput.value)

            fetch($('#url_web').val() + 'api/' + tipoDoc, {
                method: 'POST',
                body: formData
            }).then(resp => {
                if (!resp.ok) throw new Error(resp.status)
                return resp.json()
            }).then(data => {
                if (data.success) {
                    const nombres = formulario.querySelector('.nombres');
                    const apellidos = formulario.querySelector('.apellidos');
                    const esunat = formulario.querySelector('.esunat');
                    const csunat = formulario.querySelector('.csunat');

                    // Actualizar campos según el tipo de documento
                    if (tipoDocumento === 'RUC') {
                        nombres.value = data.data.nombre_o_razon_social || '';
                        esunat.value = data.data.estado || '';
                        csunat.value = data.data.condicion || '';
                    } else {
                        nombres.value = data.data.nombres || '';
                        const apellidoPaterno = data.data.apellido_paterno || '';
                        const apellidoMaterno = data.data.apellido_materno || '';
                        apellidos.value = apellidoPaterno + (apellidoMaterno ? ' ' + apellidoMaterno : '');
                    }
                    toastr.success('Datos encontrados');
                } else {
                    toastr.error(data.message);
                }
            }).catch(error => toastr.error('Error al buscar los datos'));
        } else {
            toastr.error('Ingrese un número de documento válido.');
        }
    }

    // Función para configurar los botones de búsqueda
    function configurarBotonesBusqueda() {
        document.querySelectorAll('.search-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const index = this.getAttribute('data-index');
                const formulario = document.getElementById('formUsuario' + index);
                if (formulario) {
                    const numDoc = formulario.querySelector('.num_doc').value;
                    if (numDoc) {
                        buscarPasajero(numDoc, formulario);
                    } else {
                        mostrarAlerta('Error', 'Ingrese un número de documento', 'error');
                    }
                }
            });
        });
    }

    // Manejador para la pestaña de buses (limpiar localStorage)
    const btn_buses = document.getElementById("pills-buses-tab");
    btn_buses.addEventListener("click", function () {
        // Obtener la data actual del localStorage
        let ventaData = JSON.parse(localStorage.getItem('venta') || '{}');

        // // Preservar solo origen y destino
        // const datosAPreservar = {
        //     origen: ventaData.origen || null,
        //     destino: ventaData.destino || null,
        //     fechaIda: ventaData.fechaIda || null
        // };

        // // Guardar solo los datos necesarios
        // localStorage.setItem('venta', JSON.stringify(datosAPreservar));

        // Mostrar el buscador nuevamente
        buscador.classList.remove("d-none");
    });

    //(/)

    //Mensajes de alerta
    function mostrarAlerta(titulo, mensaje, icono) {
        Swal.fire({
            title: titulo,
            text: mensaje,
            icon: icono,
            confirmButtonText: 'Aceptar'
        });
    }


    // Obtener campos del formulario
    var tp_doc = document.querySelectorAll('.tp_doc');
    var formUsuario = document.getElementById("formUsuario");
    var nombresInput = document.getElementById("nombres");
    var numDocInput = document.querySelectorAll(".num_doc");
    var apellidosInput = document.getElementById("apellidos");
    var telefonoInput = document.getElementById("telefono");
    var fecha_nacimiento = document.getElementById("fecha_nacimiento");
    var DniMaxLength = 8; // Número máximo de dígitos permitidos
    const boton_buscar = document.querySelectorAll('.searchButton');

    //Validar formularios con un solo boton
    function validarFormularios(formulario) {
        const campos = formulario.querySelectorAll('input, select');
        let formularioValido = true;
        campos.forEach((campo) => {
            // Excluir validación para dos campos específicos (esunat y csunat)
            if (campo.id !== 'esunat' && campo.id !== 'csunat' && campo.id !== 'fecha_nacimiento' && campo.id !== 'apellidos') {
                if (campo.value.trim() === '') {
                    formularioValido = false;
                    campo.style.borderColor = 'red';
                    campo.addEventListener('input', function () {
                        campo.style.borderColor = '';
                    });
                }
            }
        });
        return formularioValido;
    }

    // Obtener los id de los formularios
    const formulariosSecundarios = document.querySelectorAll('.formularioUser');
    formulariosSecundarios.forEach(function (formulario) {
        const numDocInput = formulario.querySelector('.num_doc');
        const tp_doc = formulario.querySelector('.tp_doc');
        const boton_buscar = formulario.querySelector('.search-btn');
        const nombres = formulario.querySelector('.nombres');
        const apellidos = formulario.querySelector('.apellidos');
        const esunat = formulario.querySelector('.esunat');
        const csunat = formulario.querySelector('.csunat');
        const genero = formulario.querySelector('.genero');
        const fecha_nacimiento = formulario.querySelector('.fecha_nacimiento');
        // Evento de clic para buscar por DNI
        boton_buscar.addEventListener("click", (e) => {
            const tipoDocumento = tp_doc.value == 6 ? 'RUC' : 'DNI';
            let tipoDoc = tp_doc.value == 6 ? 'sunat' : 'reniec';
            // Llamar a buscarInformacion y pasar una función de devolución de llamada
            if (numDocInput.value.trim() != "" && (numDocInput.value.length >= 8 || numDocInput.value.length <= 11)) {
                let formData = new FormData()
                formData.set("tp_docu", tp_doc.value)
                formData.set("docu", numDocInput.value)
                formData.set("num_docu", numDocInput.value)
                fetch($('#url_web').val() + 'api/' + tipoDoc, {
                    method: 'POST',
                    body: formData
                }).then(resp => {
                    if (!resp.ok) throw new Error(resp.status)
                    return resp.json()
                }).then(data => {
                    if (data.success) {
                        // Actualizar el input de nombres solo si no es RUC
                        if (tipoDocumento === 'RUC') {
                            nombres.value = data.data.nombre_o_razon_social || '';
                            esunat.value = data.data.estado || '';
                            csunat.value = data.data.condicion || '';
                        } else {
                            nombres.value = data.data.nombres || '';
                            const apellidoPaterno = data.data.apellido_paterno || '';
                            const apellidoMaterno = data.data.apellido_materno || '';
                            apellidos.value = apellidoPaterno + (apellidoMaterno ? ' ' + apellidoMaterno : '');
                        }
                        toastr.success('Datos encontrados');
                    } else {
                        toastr.error(data.message);
                    }
                }).catch(error => toastr.error(data.message))
            } else {
                toastr.error('Ingrese un número de documento válido.');
            }
        });

        tp_doc.addEventListener("change", (e) => {
            numDocInput.value = "";
            nombres.value = "";
            apellidos.value = "";
            csunat.value = "";
            esunat.value = "";

            if (tp_doc.value == 6) {
                nombres.closest("div").querySelector("label").innerHTML = `RAZON SOCIAL`
                nombres.parentElement.classList.add("col-lg-6")
                nombres.parentElement.classList.add("col-lg-12")
                numDocInput.setAttribute("minlength", 11);
                numDocInput.setAttribute("maxlength", 11);
                nombres.setAttribute("readonly", true);
                apellidos.setAttribute("readonly", true);
                apellidos.parentElement.classList.add("d-none")
                csunat.parentElement.classList.remove("d-none");
                esunat.parentElement.classList.remove("d-none");
                genero.parentElement.classList.add("d-none");
                fecha_nacimiento.parentElement.classList.add("d-none");
                boton_buscar.disabled = false
            } else if (tp_doc.value == 4 || tp_doc.value == 7 || tp_doc.value == 0) {
                nombres.closest("div").querySelector("label").innerHTML = `NOMBRES`
                nombres.parentElement.classList.remove("col-lg-12")
                nombres.parentElement.classList.add("col-lg-6")
                numDocInput.setAttribute("minlength", 12);
                numDocInput.setAttribute("maxlength", 12);
                nombres.removeAttribute("readonly");
                apellidos.removeAttribute("readonly");
                apellidos.parentElement.classList.remove("d-none")
                csunat.parentElement.classList.add("d-none");
                esunat.parentElement.classList.add("d-none");
                genero.parentElement.classList.remove("d-none");
                fecha_nacimiento.parentElement.classList.remove("d-none");
                boton_buscar.disabled = true
            } else {
                nombres.closest("div").querySelector("label").innerHTML = `NOMBRES`
                nombres.parentElement.classList.remove("col-lg-12")
                nombres.parentElement.classList.add("col-lg-6")
                nombres.setAttribute("readonly", true);
                apellidos.setAttribute("readonly", true);
                apellidos.parentElement.classList.remove("d-none")
                numDocInput.setAttribute("minlength", 8);
                numDocInput.setAttribute("maxlength", 8);
                csunat.parentElement.classList.add("d-none");
                esunat.parentElement.classList.add("d-none");
                genero.parentElement.classList.remove("d-none");
                fecha_nacimiento.parentElement.classList.remove("d-none");
                boton_buscar.disabled = false
            }
        });
    });

    //Click en el boton back_btn
    const back_btn = document.getElementById('back_btn');
    back_btn.addEventListener('click', function () {
        setTimeout(() => {
            btn_buses.click(); // Simula el clic en la pestaña "Datos"
        }, 1000);
    });

    function validarCorreoElectronico(correo) {
        const expresionRegularCorreo = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;
        return expresionRegularCorreo.test(correo);
    }

    // PROCESO DE VALIDACION Y CONTINUACION DE PROCES DE RECOPILACION DE DATOS

    // Función para validar y enviar formularios
    function validarYEnviarFormularios() {
        // Verificar términos y condiciones
        const checkbox = document.getElementById('miCheckbox');
        if (!checkbox || !checkbox.checked) {
            mostrarAlerta('Error', 'Debe aceptar los términos y condiciones', 'error');
            return;
        }

        // Verificar que todos los formularios estén completos
        const formularios = document.querySelectorAll('.formularioUser');
        let formulariosValidos = true;

        formularios.forEach(form => {
            // Verificar campos requeridos
            const camposRequeridos = form.querySelectorAll('[required]');
            camposRequeridos.forEach(campo => {
                if (!campo.value.trim()) {
                    formulariosValidos = false;
                    campo.classList.add('campo-error');
                } else {
                    campo.classList.remove('campo-error');
                }
            });
        });

        if (!formulariosValidos) {
            mostrarAlerta('Error', 'Complete todos los campos requeridos', 'error');
            return;
        }

        // Recolectar datos de todos los formularios
        const datosPasajeros = [];
        formularios.forEach(form => {
            const formData = new FormData(form);
            const pasajero = {};
            for (let [key, value] of formData.entries()) {
                pasajero[key] = value;
            }
            datosPasajeros.push(pasajero);
        });

        btn_pago.removeAttribute("disabled"); // Habilitar el botón

        // Guardar datos completos en localStorage
        const dataLocalStorage = JSON.parse(localStorage.getItem('venta')) || {};
        dataLocalStorage.pasajeros = datosPasajeros;
        localStorage.setItem('venta', JSON.stringify(dataLocalStorage));

        setTimeout(() => {
            btn_pago.click(); // Simula el clic en la pestaña "Pago"
        }, 1000);
    }

    // Función para mostrar alertas
    function mostrarAlerta(titulo, mensaje, icono) {
        Swal.fire({
            title: titulo,
            text: mensaje,
            icon: icono,
            confirmButtonText: 'Aceptar'
        });
    }
});
