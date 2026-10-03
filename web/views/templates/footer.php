<footer class="footer-container">
  <!-- Sección superior con logo y contenido principal -->
  <div class="footer-main">
    <div class="footer-logo-section">
      <img src="<?= URL_IMAGEN_ADMIN ?>logo_blanconegro.png" alt="Eurosac" class="footer-logo">
      <p>Huancayo - El Tambo entre Jr la Libertad - Jr. Ayacucho  (Oficina Principal)</p>
      <div class="reclamos-section">
        <h5 class="reclamos-heading">LIBRO DE RECLAMACIONES</h5>
        <a href="<?= URL_WEB ?>home/reclamo" class="reclamos-link" target="_blank" rel="noopener noreferrer">
          <img src="<?= URL_IMAGEN_WEB ?>libro_reclamaciones.png" alt="Libro de Reclamaciones" class="reclamos-img">
        </a>
      </div>
    </div>

    <div class="footer-info-container">
      <div class="footer-contact">
        <h4 class="footer-heading">CONTACTO RÁPIDO</h4>
        <ul class="footer-contact-list">
          <li><span class="contact-label">Teléfono:</span> +51 984551160</li>
          <li><span class="contact-label">Teléfono:</span> +51 957516751</li>
          <li><span class="contact-label">Email:</span>tidhuancayo@gmail.com</li>
          <li>
            <a href="https://www.facebook.com/sistemasTID" target="_blank" style="margin-right: 10px;">
              <img src="<?= URL_IMAGEN_WEB ?>facebook.png" alt="Facebook" style="width: 50px; height: 50px;">
            </a>

            <a href="https://wa.me/51957516751" target="_blank" style="margin-right: 10px;">
              <img src="<?= URL_IMAGEN_WEB ?>whatsapp.png" alt="WhatsApp" style="width: 50px; height: 50px;">
            </a>
          </li>
        </ul>
        <h4 class="footer-heading">TERMINOS Y POLÍTICAS</h4>
        <ul class="footer-contact-list">
          <li><a href="<?= URL_WEB ?>pdf/ClausulasGeneralesContratacionServicioEncomiendas.pdf" style="color: #c0c0c0; text-decoration: none;" target="_blank" rel="noopener noreferrer">Términos y Condiciones de Encomiendas y Carga</a></li>
          <li><a href="<?= URL_WEB ?>pdf/ClausulasGeneralesContratacionServicioViaje.pdf" style="color: #c0c0c0; text-decoration: none;" target="_blank" rel="noopener noreferrer">Términos y Condiciones de Viaje</a></li>
          <li><a href="<?= URL_WEB ?>pdf/Politicas_de_DevolucionEurosac.pdf" style="color: #c0c0c0; text-decoration: none;" target="_blank" rel="noopener noreferrer">Políticas de cambio o devoluciones</a></li>

        </ul>
      </div>

      <div class="footer-agencies">
        <h4 class="footer-heading">NUESTRAS AGENCIAS</h4>
        <div class="agencies-grid">
          <div class="agency-column">
            <div class="agency-item">Av. 28 de Julio 1598 Cruce Jr. Andahuaylas 400 - La Victoria</div>
            <div class="agency-item">Av. 28 de Julio 1581 (Ter. la Merced Stand: 01) - La Victoria</div>
            <div class="agency-item">Av. Alfredo Mendiola 3889 (Ter. Via Buss) - Los Olivos</div>

          </div>
          <div class="agency-column">
            <div class="agency-item">Jr. Arica N°. Int: 26 (Terminal Tobias Ruiz) - Juanjui</div>
            <div class="agency-item">Costado del Mercado Santa Anita - Carr. Mayopanga C-01. N° 153 - Tarapoto</div>
            <div class="agency-item">Av. Marginal 107 Cerca del Puente Puerto Pizana</div>

          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="footer-bottom">
    <p>© 2026 - TID DEMOSTRACIÓN | Todos los derechos reservados.</p>
  </div>
</footer>


<a href="https://wa.me/51984551160" class="whatsaap btn  btn-success btn-lg-square back-to-top-whatsappp" target="blank">
  <i class="fab fa-whatsapp fa-3x" style="color: green;"></i>
</a>


<style>
  /* Estilos para el botón de WhatsApp */
  /* Mejoras al botón de WhatsApp existente */
  .back-to-top-whatsappp {
    position: fixed;
    bottom: 25px;
    right: 25px;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 65px;
    height: 65px;
    border-radius: 50%;
    background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
    color: white;
    font-size: 24px;
    text-decoration: none;
    z-index: 1000;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    transition: all 0.3s ease;
    border: 2px solid white;
  }

  .back-to-top-whatsappp:hover {
    transform: scale(1.1) translateY(-5px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
  }

  .back-to-top-whatsappp i {
    color: white;
    font-size: 32px;
  }

  .btn-lg-square {
    border-radius: 50% !important;
    width: 60px !important;
    height: 60px !important;
    padding: 0 !important;
  }


  /* ESTILOS PARA EL NUEVO FOOTER */
  .footer-container {
    background: linear-gradient(135deg, #0F5B99 0%, #0F5B99 100%);
    /* Azul más oscuro */
    color: #f0f0f0;
    font-family: 'Poppins', sans-serif;
    padding: 0;
    width: 100%;
    border-radius: 12px 12px 0 0;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    border: 1px solid rgba(255, 255, 255, 0.1);
  }

  .footer-main {
    display: flex;
    padding: 3rem 5%;
    flex-wrap: wrap;
  }

  .footer-logo-section {
    flex: 0 0 25%;
    padding-right: 3rem;
  }

  .footer-logo {
    max-width: 200px;
    height: auto;
    margin-bottom: 1.9rem;
    transition: filter 0.3s ease;
    display: block;
    margin-left: auto;
    margin-right: auto;
  }

  .footer-logo:hover {
    filter: brightness(1.2);
  }

  .footer-tagline {
    font-size: 0.9rem;
    line-height: 1.5;
    color: #a0a0a0;
    margin-bottom: 1.5rem;
  }

  /* Estilos para la sección de libro de reclamaciones */
  .reclamos-section {
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
  }

  .reclamos-heading {
    font-size: 0.9rem;
    font-weight: 600;
    color: #ffffff;
    margin-bottom: 0.8rem;
    text-align: center;
  }

  .reclamos-link {
    display: block;
    text-align: center;
    transition: transform 0.3s ease;
  }

  .reclamos-link:hover {
    transform: scale(1.05);
  }

  .reclamos-img {
    max-width: 120px;
    height: auto;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
  }

  /* Contenedor de información */
  .footer-info-container {
    flex: 0 0 75%;
    display: flex;
    flex-wrap: wrap;
  }

  /* Sección de contacto */
  .footer-contact {
    flex: 0 0 30%;
    padding-right: 1rem;
  }

  .footer-heading {
    position: relative;
    font-size: 1rem;
    font-weight: 600;
    color: #ffffff;
    margin-bottom: 1.5rem;
    padding-bottom: 0.7rem;
    letter-spacing: 0.5px;
  }

  .footer-heading::after {
    content: '';
    position: absolute;
    left: 0;
    bottom: 0;
    width: 50px;
    height: 5px;
    background-color: rgb(255, 0, 0);
    /* Color amarillo modificado */
  }

  .footer-contact-list {
    list-style: none;
    padding: 0;
    margin: 0;
  }

  .footer-contact-list li {
    margin-bottom: 1rem;
    font-size: 0.8rem;
    color: #c0c0c0;
    position: relative;
    transition: transform 0.2s ease;
  }

  .footer-contact-list li:hover {
    transform: translateX(5px);
    color: #ffffff;
  }

  .contact-label {
    font-weight: 500;
    color: #ffffff;
    margin-right: 5px;
  }

  /* Sección de agencias */
  .footer-agencies {
    flex: 0 0 70%;
  }

  .agencies-grid {
    display: flex;
    flex-wrap: wrap;
  }

  .agency-column {
    flex: 0 0 50%;
  }

  .agency-item {
    position: relative;
    padding: 0.5rem 0 0.5rem 0.5rem;
    margin-bottom: 0.5rem;
    font-size: 0.75rem;
    color: #b0b0b0;
    transition: all 0.2s ease;
    border-left: 2px solid transparent;
  }

  .agency-item:hover {
    color: #ffffff;
    border-left-color: rgb(255, 0, 0);
    /* Color amarillo modificado */
    padding-left: 1.2rem;
    background-color: rgba(255, 0, 0, 0.05);
  }

  .agency-item::before {
    content: '•';
    position: absolute;
    left: 0;
    color: rgb(238, 250, 20);
    /* Color amarillo modificado */
    transition: transform 0.2s;
  }

  .agency-item:hover::before {
    transform: scale(1.2);
  }

  /* Sección inferior del footer */
  .footer-bottom {
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    padding: 1.5rem 5%;
    text-align: center;
  }

  .footer-bottom p {
    margin: 0;
    font-size: 0.85rem;
    color: #ffffff;
  }

  /* Responsive */
  @media (max-width: 992px) {
    .footer-logo-section {
      flex: 0 0 100%;
      margin-bottom: 2rem;
      padding-right: 0;
    }

    .footer-info-container {
      flex: 0 0 100%;
    }

    .footer-contact {
      flex: 0 0 100%;
      margin-bottom: 2rem;
      padding-right: 0;
    }

    .footer-agencies {
      flex: 0 0 100%;
    }
  }

  @media (max-width: 768px) {
    .agency-column {
      flex: 0 0 100%;
    }

    .footer-main {
      padding: 2rem 5%;
    }
  }
</style>


<!-- 
<script src="../js/main.js"></script>
</body> -->

</html>

<!-- PHP -->
<?php if (isset($this->php)) : ?>
  <?php foreach ($this->php as $php) : ?>
    <?php require("web/views/" . $php) ?>
  <?php endforeach ?>
<?php endif ?>

<!-- SCRIPTS -->
<?php
// Cambia manualmente esta versión cuando actualices los scripts
$version = "1.0.7";
if (isset($this->js)) : ?>
  <?php foreach ($this->js as $js) : ?>
    <?php
    $arry = explode(".", $js);
    MINIFY_JS ? $arry[0] = $arry[0] . '.min.' : $arry[0] = $arry[0] . '.';
    $name_script = implode("", $arry);
    ?>
    <script src="<?php echo URL_WEB; ?>views/<?php echo $name_script ?>?v=<?php echo $version; ?>" type="module"></script>
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
</script>
<script src="<?php echo URL; ?>public/plugins/select2/select2.min.js"></script>

</body>

</html>