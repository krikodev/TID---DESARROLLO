class NoxAutoComplete {

    constructor(options) {

        if (!options)
            throw new Error("Debe enviar las opciones.");

        this.input = document.querySelector(options.input);

        if (!this.input)
            throw new Error("Input no encontrado.");

        this.hidden = options.hidden
            ? document.querySelector(options.hidden)
            : null;

        this.url = options.url || "";
        this.method = options.method || "POST";
        this.delay = options.delay || 300;
        this.minLength = options.minLength || 2;
        this.valueField = options.valueField || "id";
        this.textField = options.textField || "nombre";
        this.placeholder = options.placeholder || "";
        this.render = options.render || null;
        this.onSelect = options.onSelect || null;
        this.onSearch = options.onSearch || null;
        this.onClear = options.onClear || null;
        this.timeout = null;
        this.items = [];
        this.selectedItem = null;
        this.selectedIndex = -1;
        this.onNotFound = options.onNotFound || null;
        this.restoreOnBlur = options.restoreOnBlur !== undefined
            ? options.restoreOnBlur
            : true;

        // Controla qué pasa al hacer focus:
        // - true  -> borra el input (obliga a buscar de nuevo, como tenías antes)
        // - false -> solo selecciona el texto (default, no borra nada)
        this.clearOnFocus = options.clearOnFocus !== undefined
            ? options.clearOnFocus
            : true;
        this.previousValue = "";
        this.previousItem = null;
        this.selectionMade = false;
        this.isOpen = false;
        this.createElements();
        this.bindEvents();
        this.loading = false;
        this.abortController = null;
        this.cache = {};
        this.dropdownHeight = 0;
        this.containerRect = null;
        this.page = 1;
        this.hasMore = true;
        this.loadingMore = false;
        this.spinner = null;
        this.maxCache = 20;
        this.handleOutsideClick = this.handleOutsideClick.bind(this);
        this.bindOutsideClick();
    }

    createElements() {

        this.container = this.input.parentElement;

        this.input.classList.add("nox-autocomplete-input");

        if (getComputedStyle(this.container).position == "static")
            this.container.style.position = "relative";

        this.dropdown = document.createElement("div");

        this.dropdown.className = "nox-autocomplete";

        this.dropdown.style.display = "none";

        this.container.appendChild(this.dropdown);
        if (!this.scrollBound) {

            this.bindScroll();

            this.scrollBound = true;

        }

        if (this.placeholder)
            this.input.placeholder = this.placeholder;

    }

    bindEvents() {

        var self = this;

        this.input.addEventListener("focus", function () {

            // guardamos el estado "bueno" antes de tocar nada
            self.previousValue = self.input.value;
            self.previousItem = self.selectedItem;
            self.selectionMade = false;

            if (self.clearOnFocus) {
                self.input.value = "";
            } else {
                self.input.select();
            }

        });

        this.input.addEventListener("input", function () {

            clearTimeout(self.timeout);

            if (self.hidden)
                self.hidden.value = "";

            self.selectedItem = null;
            self.selectionMade = false;

            var texto = self.input.value.trim();

            if (texto.length < self.minLength) {

                self.items = [];
                self.dropdown.innerHTML = "";
                self.selectedIndex = -1;
                self.hide();

                return;
            }

            self.timeout = setTimeout(function () {
                self.search(texto);
            }, self.delay);

        });

        // 🔥 TECLADO
        this.input.addEventListener("keydown", function (e) {

            if (!self.isOpen)
                return;

            // ↓
            if (e.keyCode == 40) {

                e.preventDefault();

                self.moveDown();

            }

            // ↑
            else if (e.keyCode == 38) {

                e.preventDefault();

                self.moveUp();

            }

            // ENTER
            else if (e.keyCode == 13) {

                if (!self.items.length)
                    return;

                e.preventDefault();
                e.stopImmediatePropagation();   // 👈 agregar esta línea

                var idx = self.selectedIndex >= 0 ? self.selectedIndex : 0;

                self.select(idx);

            }

            // ESC
            else if (e.keyCode == 27) {

                self.hide();

            }

        });

        this.input.addEventListener("blur", function () {

            setTimeout(function () {

                if (!self.dropdown.contains(document.activeElement)) {

                    self.hide();

                    if (self.restoreOnBlur && !self.selectionMade) {

                        self.input.value = self.previousValue;
                        self.selectedItem = self.previousItem;

                        if (self.hidden) {
                            self.hidden.value = self.previousItem
                                ? self.previousItem[self.valueField]
                                : "";
                        }

                    }

                }

            }, 150);

        });

    }

    moveDown() {

        if (!this.items.length)
            return;

        this.selectedIndex++;

        if (this.selectedIndex >= this.items.length)
            this.selectedIndex = 0;

        this.highlight();

    }

    moveUp() {

        if (!this.items.length)
            return;

        this.selectedIndex--;

        if (this.selectedIndex < 0)
            this.selectedIndex = this.items.length - 1;

        this.highlight();

    }

    highlight() {

        var items = this.dropdown.querySelectorAll(".nox-autocomplete-item");

        for (var i = 0; i < items.length; i++) {
            items[i].classList.remove("active");
        }

        if (this.selectedIndex < 0)
            return;

        var el = items[this.selectedIndex];

        if (!el)
            return;

        el.classList.add("active");

        // 🔥 evitar scroll innecesario si ya está visible
        var parent = this.dropdown;

        var elTop = el.offsetTop;
        var elBottom = elTop + el.offsetHeight;

        var viewTop = parent.scrollTop;
        var viewBottom = viewTop + parent.clientHeight;

        if (elTop < viewTop || elBottom > viewBottom) {

            el.scrollIntoView({
                block: "nearest"
            });

        }

    }

    show() {

        this.dropdown.style.display = "block";

        this.isOpen = true;

        // calcular posición
        this.positionDropdown();

    }

    positionDropdown() {

        var rect = this.input.getBoundingClientRect();

        var dropdownHeight = this.dropdown.offsetHeight || 250;
        var spaceBelow = window.innerHeight - rect.bottom;
        var spaceAbove = rect.top;

        this.dropdown.style.left = "0px";
        this.dropdown.style.right = "auto";

        // reset
        this.dropdown.style.top = "100%";
        this.dropdown.style.bottom = "auto";

        // 🔥 MEJOR DECISIÓN
        if (spaceBelow < dropdownHeight && spaceAbove > dropdownHeight) {

            this.dropdown.style.top = "auto";
            this.dropdown.style.bottom = "100%";

        }

        // ajuste horizontal si se sale de pantalla
        var rightOverflow = rect.left + this.dropdown.offsetWidth > window.innerWidth;

        if (rightOverflow) {
            this.dropdown.style.left = "auto";
            this.dropdown.style.right = "0px";
        }

    }

    bindOutsideClick() {

        document.addEventListener("mousedown", this.handleOutsideClick);

    }

    handleOutsideClick(e) {

        if (!this.container.contains(e.target)) {
            this.hide();
        }

    }

    hide() {

        this.dropdown.style.display = "none";

        this.isOpen = false;
        this.selectedIndex = -1;

        this.dropdown.scrollTop = 0;

    }

    clear() {

        this.input.value = "";

        if (this.hidden)
            this.hidden.value = "";

        this.items = [];
        this.selectedItem = null;

        this.hide();
    }

    destroy() {

        document.removeEventListener("mousedown", this.handleOutsideClick);

        if (this.abortController)
            this.abortController.abort();

        clearTimeout(this.timeout);

        if (this.dropdown)
            this.dropdown.remove();

        this.input = null;
        this.hidden = null;
        this.cache = {};
        this.items = [];
        this.abortController = null;
        this.timeout = null;

    }


    getValue() {

        return {
            text: this.input.value,
            id: this.hidden ? this.hidden.value : null
        };

    }

    getSelectedItem() {
        return this.selectedItem;
    }

    setValue(text, id) {

        this.input.value = text;

        if (this.hidden)
            this.hidden.value = id;

    }

    setItem(item) {

        if (!item)
            return;

        this.selectedItem = item;
        this.selectionMade = true;

        this.input.value = item[this.textField];

        if (this.hidden)
            this.hidden.value = item[this.valueField];

        if (this.onSelect)
            this.onSelect(item);
    }

    async getOrCreate() {
        if (this.selectedItem)
            return this.selectedItem;

        const texto = this.input.value.trim();
        if (texto == "") return null;
        if (!this.onNotFound) return null;

        const nuevo = await this.onNotFound(texto);
        if (!nuevo) return null;

        this.setItem(nuevo);

        return nuevo;
    }

    search(texto) {

        if (this.onSearch)
            this.onSearch(texto);

        this.page = 1;
        this.hasMore = true;
        this.items = [];
        this.dropdown.scrollTop = 0;
        var cacheKey = texto.trim().toLowerCase() + "_p" + this.page;

        if (this.cache[cacheKey]) {

            this.items = this.cache[cacheKey];

            this.renderItems();

            return;

        }

        this.request(texto, true);

    }

    clearCache(texto) {
        if (texto) {
            var prefix = texto.trim().toLowerCase();
            for (var key in this.cache) {
                if (key.indexOf(prefix) === 0) delete this.cache[key];
            }
        } else {
            this.cache = {};
        }
    }

    async request(texto, reset = false) {

        // Cancela cualquier petición anterior en vuelo (search o loadMore) y
        // deja que esta, la más nueva, sea la que gane. Ya NO cortamos aquí
        // con "if (this.loading) return" porque eso hacía que una búsqueda
        // nueva se ignorara en silencio si un loadMore seguía en curso.
        if (this.abortController)
            this.abortController.abort();

        var controller = new AbortController();
        this.abortController = controller;

        this.loading = true;

        var self = this;
        try {

            if (reset) {

                this.showLoading("Buscando...");

                this.show();

            }

            var formData = new FormData();
            formData.append("texto", texto);
            formData.append("page", this.page);

            var response = await fetch(this.url, {

                method: this.method,
                body: formData,
                signal: controller.signal

            });
            var result = await response.json();
            var data = result.data || result;

            if (!this.shouldRender(data)) {
                this.dropdown.innerHTML =
                    '<div class="nox-autocomplete-empty">Respuesta inválida del servidor.</div>';
                return;
            }

            this.hasMore = result.hasMore !== false;

            for (var i = 0; i < data.length; i++) {
                this.items.push(data[i]);
            }

            var key = texto.trim().toLowerCase() + "_p" + this.page;
            this.cache[key] = data;
            this.cleanCache();

            this.renderItems();

        }
        catch (error) {

            if (error.name == "AbortError")
                return;

            console.error(error);

            this.dropdown.innerHTML =
                '<div class="nox-autocomplete-empty">Error al consultar.</div>';

        }
        finally {

            // Solo la petición vigente (la más reciente) puede liberar el
            // flag. Si ya fue reemplazada por otra más nueva, no tocamos nada,
            // para evitar que dos peticiones se pisen entre sí.
            if (this.abortController === controller) {
                this.loading = false;
            }

        }
    }

    shouldRender(data) {

        // Evita renderizar si la respuesta no tiene el formato esperado
        // (por ejemplo, si el backend devolvió un error o un formato inválido)
        return Array.isArray(data);

    }

    bindScroll() {

        var self = this;

        this.dropdown.addEventListener("scroll", function () {

            if (!self.isOpen || self.loadingMore || self.loading || !self.hasMore)
                return;
            var scrollTop = self.dropdown.scrollTop;
            var scrollHeight = self.dropdown.scrollHeight;
            var clientHeight = self.dropdown.clientHeight;

            if (scrollTop + clientHeight >= scrollHeight - 20) {

                self.loadMore();

            }

        });

    }

    loadMore() {

        if (this.loadingMore || !this.hasMore)
            return;

        this.loadingMore = true;

        this.page++;
        var key = this.input.value.trim().toLowerCase() + "_p" + this.page;

        if (this.cache[key]) {
            this.items = this.items.concat(this.cache[key]);
            this.renderItems();
            this.loadingMore = false;
            return;
        }

        var loading = document.createElement("div");
        loading.className = "nox-autocomplete-loading";
        loading.innerHTML = "Cargando más...";

        this.dropdown.appendChild(loading);

        var self = this;

        this.request(this.input.value.trim(), false).then(function () {

            self.loadingMore = false;

            loading.remove();

        });

    }

    searchPublic(texto) {

        this.input.value = texto;

        if (texto.length < this.minLength)
            return;

        this.search(texto);

    }

    highlightText(text, search) {

        if (!search)
            return text;

        var escaped = search.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        var reg = new RegExp("(" + escaped + ")", "gi");

        return text.replace(reg, "<strong>$1</strong>");

    }

    renderItems() {

        var self = this;

        // Si estamos en la página 1 es una búsqueda nueva -> se resetea el
        // resaltado. Si page > 1 es un loadMore (scroll) -> se conserva el
        // ítem resaltado que el usuario ya tenía con el teclado.
        var isFreshSearch = this.page <= 1;

        this.dropdown.innerHTML = "";

        if (isFreshSearch)
            this.selectedIndex = -1;

        if (!this.items.length) {

            this.dropdown.innerHTML =
                '<div class="nox-autocomplete-empty">No se encontraron resultados.</div>';

            this.show();

            return;

        }

        this.items.forEach(function (item, index) {

            var div = document.createElement("div");

            div.className = "nox-autocomplete-item";

            if (self.render) {

                div.innerHTML = self.render(item, self.input.value);

            } else {

                var value = item[self.textField];

                div.innerHTML = self.highlightText(value, self.input.value);

            }

            div.dataset.index = index;

            div.addEventListener("click", function () {

                self.select(index);

            });

            self.dropdown.appendChild(div);

            div.addEventListener("mouseenter", function () {

                self.selectedIndex = index;

                self.highlight();

            });

        });

        // Si veníamos de un loadMore y había un ítem resaltado con teclado,
        // el DOM se reconstruyó y perdió la clase "active" -> la reponemos.
        if (!isFreshSearch && this.selectedIndex >= 0)
            this.highlight();

        this.show();

    }

    select(index) {

        var item = this.items[index];

        if (!item)
            return;
        this.selectedItem = item;
        this.selectionMade = true;

        this.input.value = item[this.textField];

        if (this.hidden)
            this.hidden.value = item[this.valueField];

        this.selectedIndex = -1;

        this.hide();

        if (this.onSelect)
            this.onSelect(item);

    }

    showLoading(texto) {

        this.dropdown.innerHTML = `
        <div class="nox-autocomplete-loading">
            <span class="spinner"></span> ${texto || "Buscando..."}
        </div>
    `;

        this.show();

    }

    cleanCache() {

        var keys = Object.keys(this.cache);

        if (keys.length <= this.maxCache)
            return;

        while (Object.keys(this.cache).length > this.maxCache) {
            delete this.cache[Object.keys(this.cache)[0]];
        }

    }
}

export { NoxAutoComplete };