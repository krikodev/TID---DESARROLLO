export class Modal {
    constructor() { }

    static change_name(modals = []) {
        modals.forEach(mdl => {
            let modal = document.querySelector(mdl)
            modal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget
                var title = button.getAttribute('data-bs-whatever')
                var modalTitle = modal.querySelector('.modal-title')
                modalTitle.textContent = title
            })
        });
    }
}