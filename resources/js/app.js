import './api.js';
import './pinpad.js';
import './dashboard.js';
import './pos.js';
import './inventario.js';
import './auditoria.js';
import './recetas.js';
import './usuarios.js';

import { authState } from "./api.js";

document.addEventListener("DOMContentLoaded", () => {
    // ==============================================================
    // LÓGICA GLOBAL DE CIERRE DE SESIÓN (Funciona en todas las vistas)
    // ==============================================================
    const logoutBtns = document.querySelectorAll("#logout-btn, #top-logout-btn");
    
    logoutBtns.forEach(btn => {
        btn.addEventListener("click", (e) => {
            e.preventDefault();
            authState.clear(); // Borra el token del sessionStorage
            window.location.href = "/"; // Redirige al login
        });
    });

    // ==============================================================
    // BOTÓN "+ NUEVA ORDEN" GLOBAL
    // ==============================================================
    const btnNuevaOrden = document.getElementById("btn-nueva-orden");
    if (btnNuevaOrden) {
        btnNuevaOrden.addEventListener("click", (e) => {
            // Si estamos en el POS, pos.js se encarga. 
            // Si estamos en otra página, lo mandamos al POS.
            if (window.location.pathname !== "/pos") {
                window.location.href = "/pos";
            }
        });
    }
});