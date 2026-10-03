class Datatable {

    constructor() { }

    static buttons(obj, label) {
        new $.fn.dataTable.Buttons(this.table, {
            buttons: [
                obj,
            ]
        }).container().appendTo($(label));
    }

    /**
     * Búsqueda manual en DataTables
     * @param {object} table 
     * @param {string} input_search 
     * @param {string|null} btn_search 
     */
    static inputSearch(table, input_search, btn_search = null, mode = 'auto') {
        if (mode === 'auto') {
            $(input_search).on('keyup', () => {
                table.search($(input_search).val()).draw();
            });
        } else if (mode === 'manual') {
            $(input_search).on('keyup', function (e) {
                if (e.key === 'Enter') {
                    table.search($(this).val()).draw();
                }
            });

            if (btn_search) {
                $(btn_search).on('click', function () {
                    table.search($(input_search).val()).draw();
                });
            }
        }
    }

    static reloadTable(table) {
        table.ajax.reload();
    }
}
export { Datatable };