// storageManager.js
export class StorageManager {
    constructor(clave, valorInicial = {}) {
        this.clave = clave;
        this.valor = this.obtener() || valorInicial;
    }

    obtener() {
        try {
            return JSON.parse(localStorage.getItem(this.clave)) || null;
        } catch (e) {
            console.warn(`Error leyendo localStorage "${this.clave}"`, e);
            return null;
        }
    }

    guardar() {
        try {
            localStorage.setItem(this.clave, JSON.stringify(this.valor));
        } catch (e) {
            console.error(`Error guardando en localStorage "${this.clave}"`, e);
        }
    }

    actualizar(data = {}) {
        this.valor = { ...this.valor, ...data };
        this.guardar();
    }

    limpiar() {
        localStorage.removeItem(this.clave);
        this.valor = {};
    }
}
