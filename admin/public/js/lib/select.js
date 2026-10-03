
class Select {

    constructor() {
        this.url = null;
        this.select = null;
        this.type_select = null;
        this.selectTag = null;
        this.parentSelectTag = null;
        this.baseUrl = null;
    }

    language() {
        const obj = {
            noResults: function () {

                return "No se encontraron resultado";
            },
            searching: function () {

                return "Buscando..";
            }
        };
        return obj;
    }

    createSelect(selectTag = null, parentSelectTag = null, placeholder = null, allowClear = false) {
        this.selectTag = selectTag;
        this.parentSelectTag = parentSelectTag;
        if (this.parentSelectTag) {
            $(this.selectTag).select2({
                language: this.language(),
                width: "100%",
                dropdownParent: $(this.parentSelectTag),
                dropdownAutoWidth: true,
                placeholder: placeholder ? placeholder : 'Seleccione',
                allowClear: allowClear
            });
        } else {
            $(this.selectTag).select2({
                language: this.language(),
                width: "100%",
                dropdownAutoWidth: true,
                placeholder: placeholder ? placeholder : 'Seleccione',
                allowClear: allowClear
            });
        }
    }

    createSelectSinSearch(selectTag = null, parentSelectTag = null, placeholder = null, allowClear = false) {
        this.selectTag = selectTag;
        this.parentSelectTag = parentSelectTag;
        if (this.parentSelectTag) {
            $(this.selectTag).select2({
                language: this.language(),
                width: "100%",
                dropdownParent: $(this.parentSelectTag),
                dropdownAutoWidth: true,
                placeholder: placeholder ? placeholder : 'Seleccione',
                allowClear: allowClear,
                minimumResultsForSearch: Infinity // Deshabilita el campo de búsqueda
            });
        } else {
            $(this.selectTag).select2({
                language: this.language(),
                width: "100%",
                dropdownAutoWidth: true,
                placeholder: placeholder ? placeholder : 'Seleccione',
                allowClear: allowClear,
                minimumResultsForSearch: Infinity // Deshabilita el campo de búsqueda
            });
        }
    }    

    createSelectSinParent(selectTag = null, placeholder = null) {
        this.selectTag = selectTag;
        $(this.selectTag).select2({
            language: this.language(),
            dropdownAutoWidth: true,
            placeholder: placeholder
        });
    }
}

export { Select };