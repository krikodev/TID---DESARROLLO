const connetor_plugin = (() => {
    // Clase que representa una operación específica
    class OperacionTicket {
        constructor(accion, datos) {
            this.accion = accion + '';
            this.datos = datos + '';
        }
    }

    // Clase que maneja el conector del plugin
    class ConectorPlugin {
        static URL_PLUGIN_POR_DEFECTO = 'http://localhost:4567';
        static AccionText = 'AccionText';
        static Accionqr = 'qr';
        static AccionFontsize = 'fontsize';
        static AccionTextaling = 'textaling';
        static AccionFeed = 'feed';
        static AccionBarcode_ean13 = 'barcode_ean13';
        static AccionBarcode_code39 = 'barcode_39';
        static AccionBarcode_code128 = 'barcode_128';
        static AccionImg_location = 'img_url';
        static AccionCut = 'cut';
        static AccionPDF = 'pdf'; // Nueva acción para PDFs

        constructor(ruta = ConectorPlugin.URL_PLUGIN_POR_DEFECTO) {
            this.ruta = ruta;
            this.operaciones = [];
        }

        static obtenerImpresoras(url) {
            if (url) ConectorPlugin.URL_PLUGIN_POR_DEFECTO = url;
            return fetch(ConectorPlugin.URL_PLUGIN_POR_DEFECTO + '/getprinters')
                .then(response => response.json());
        }

        addText(texto) {
            this.operaciones.push(new OperacionTicket(ConectorPlugin.AccionText, texto));
            return this;
        }

        addQR(qrCode) {
            this.operaciones.push(new OperacionTicket(ConectorPlugin.Accionqr, qrCode));
            return this;
        }

        setFontSize(size) {
            this.operaciones.push(new OperacionTicket(ConectorPlugin.AccionFontsize, size));
            return this;
        }

        // Otros métodos para agregar operaciones...

        // Nuevo método para imprimir PDFs
        async printPDF(base64PDF, printerName, apiKey) {
            const data = {
                operaciones: [
                    new OperacionTicket(ConectorPlugin.AccionPDF, base64PDF)
                ],
                nombre_impresora: printerName,
                api_key: apiKey
            };
            const response = await fetch(this.ruta + '/imprimir', {
                method: 'POST',
                body: JSON.stringify(data)
            });
            return await response.json();
        }
    }

    return ConectorPlugin;
})();

export default connetor_plugin;