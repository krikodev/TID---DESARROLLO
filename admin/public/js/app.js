'use strict';

/* ===== Enable Bootstrap Popover (on element  ====== */

var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-toggle="popover"]'))
var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
	return new bootstrap.Popover(popoverTriggerEl)
})

/* ==== Enable Bootstrap Alert ====== */
var alertList = document.querySelectorAll('.alert')
alertList.forEach(function (alert) {
	new bootstrap.Alert(alert)
});

/* ===== Responsive Sidepanel ====== */
const sidePanelToggler = document.getElementById('sidepanel-toggler');
const sidePanel = document.getElementById('app-sidepanel');
const sidePanelDrop = document.getElementById('sidepanel-drop');
const sidePanelClose = document.getElementById('sidepanel-close');

function updateLayout() {
	if (sidePanel.classList.contains('sidepanel-visible')) {
		document.body.classList.add('sidebar-open');
		document.body.classList.remove('sidebar-closed');
	} else {
		document.body.classList.add('sidebar-closed');
		document.body.classList.remove('sidebar-open');
	}
}

window.addEventListener('load', function () {
	responsiveSidePanel();
});

window.addEventListener('resize', function () {
	responsiveSidePanel();
});

function responsiveSidePanel() {
	let w = window.innerWidth;
	if (w >= 1200) {
		sidePanel.classList.remove('sidepanel-hidden');
		sidePanel.classList.add('sidepanel-visible');
	} else {
		sidePanel.classList.remove('sidepanel-visible');
		sidePanel.classList.add('sidepanel-hidden');
	}
	updateLayout();
}

sidePanelToggler.addEventListener('click', () => {
	if (sidePanel.classList.contains('sidepanel-visible')) {
		sidePanel.classList.remove('sidepanel-visible');
		sidePanel.classList.add('sidepanel-hidden');
	} else {
		sidePanel.classList.remove('sidepanel-hidden');
		sidePanel.classList.add('sidepanel-visible');
	}
	updateLayout();
});

sidePanelClose.addEventListener('click', (e) => {
	e.preventDefault();
	sidePanelToggler.click();
});

sidePanelDrop.addEventListener('click', (e) => {
	sidePanelToggler.click();
});


function limitarFechaDesdeHoy(idInput) {
	const hoy = new Date().toISOString().split("T")[0];
	document.getElementById(idInput).setAttribute("min", hoy);
}

function limitarFechaHastaHoy(idInput) {
	const hoy = new Date().toISOString().split("T")[0];
	document.getElementById(idInput).setAttribute("max", hoy);
}

/*limitarRangoFecha("fecha", "2025-01-01", "2025-12-31"); */
function limitarRangoFecha(idInput, minFecha, maxFecha) {
	const input = document.getElementById(idInput);
	input.setAttribute("min", minFecha);
	input.setAttribute("max", maxFecha);
}



// Activar transiciones después de la carga
window.addEventListener('load', function () {
	setTimeout(() => {
		document.body.classList.add('loaded');
	}, 100);
});

// Control de z-index en los modulos cuando se usan mas de dos
$(document).on('show.bs.modal', '.modal', function () {
	const zIndex = 1055 + (10 * $('.modal:visible').length);
	$(this).css('z-index', zIndex);
	setTimeout(() => $('.modal-backdrop').not('.modal-sidebar').last().css('z-index', zIndex - 1));
});

if ('serviceWorker' in navigator) {
	window.addEventListener('load', () => {
		navigator.serviceWorker.register('/sw.js')
			.then((reg) => console.log('Service Worker registrado:', reg.scope))
			.catch((err) => console.error('Error registrando Service Worker:', err));
	});
}