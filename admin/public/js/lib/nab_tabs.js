class NabTabs {
    constructor() {

    }

    // target_buttons ejemplo "#pillsTab_contrato li button"
    // target_content ejemplo "#tabs_contrato div"
    static resetSelected(target_buttons, target_content) {
        try {
            // Seleccionando el primer button del navtabs
            document.querySelectorAll(target_buttons).forEach((e) => {
                e.classList.remove("active")
            });
            document.querySelectorAll(target_buttons)[0].classList.add("active")

            // Seleccionando el primer button del content
            document.querySelectorAll(target_content).forEach((e) => {
                e.classList.remove("show")
                e.classList.remove("active")
            });
            document.querySelectorAll(target_content)[0].classList.add("show")
            document.querySelectorAll(target_content)[0].classList.add("active")

        } catch (Exception) {
            console.log("Se produjo un error...");
        }
    }
}

export { NabTabs };