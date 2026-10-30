import { api, authState } from "./api.js";

// 🔒 Solo ejecutar si estamos en la pantalla de Login (donde existe pin-display)
if (document.getElementById("pin-display")) {
    console.log("🔥 EL PINPAD SE HA CARGADO EN LA PANTALLA CORRECTA");

    // Si ya hay una sesión válida, ir al POS
    if (authState.isAuthenticated()) {
        window.location.href = "/pos";
    } else {
        
        const pinDots = document.querySelectorAll(".pin-dot");
        const errorMsg = document.getElementById("login-error");
        const modeTabs = document.querySelectorAll(".auth-mode-tab");
        const pinSection = document.getElementById("pin-section");
        const adminSection = document.getElementById("admin-section");
        const adminForm = document.getElementById("admin-form");

        let currentPin = "";

        function updatePinDots() {
            pinDots.forEach((dot, index) => {
                if (index < currentPin.length) {
                    dot.classList.add("filled");
                } else {
                    dot.classList.remove("filled");
                }
            });
        }

        async function handlePinSubmit() {
            if (currentPin.length !== 4) return;
            
            errorMsg.textContent = "";
            errorMsg.style.display = "none";

            try {
                await api.loginPin(currentPin);
                console.log("✅ Login exitoso, redirigiendo al POS...");
                window.location.href = "/pos"; 
            } catch (err) {
                currentPin = "";
                updatePinDots();
                errorMsg.textContent = err.message || "PIN inválido";
                errorMsg.style.display = "block";
                pinDisplay.classList.add("shake");
                setTimeout(() => pinDisplay.classList.remove("shake"), 400);
            }
        }

        // Teclado táctil
        const keyButtons = document.querySelectorAll(".pin-key");
        keyButtons.forEach(btn => {
            btn.addEventListener("click", () => {
                const val = btn.getAttribute("data-val");

                if (val === "clear") {
                    currentPin = "";
                    updatePinDots();
                    errorMsg.style.display = "none";
                    return;
                }
                if (val === "back") {
                    currentPin = currentPin.slice(0, -1);
                    updatePinDots();
                    return;
                }
                if (currentPin.length < 4 && /^\d$/.test(val)) {
                    currentPin += val;
                    updatePinDots();
                    if (currentPin.length === 4) {
                        setTimeout(handlePinSubmit, 150);
                    }
                }
            });
        });

        // Teclado físico
        window.addEventListener("keydown", (e) => {
            if (adminSection.style.display !== "none") return;

            if (/^[0-9]$/.test(e.key)) {
                if (currentPin.length < 4) {
                    currentPin += e.key;
                    updatePinDots();
                    if (currentPin.length === 4) {
                        setTimeout(handlePinSubmit, 150);
                    }
                }
            } else if (e.key === "Backspace") {
                currentPin = currentPin.slice(0, -1);
                updatePinDots();
            } else if (e.key === "Escape" || e.key.toLowerCase() === "c") {
                currentPin = "";
                updatePinDots();
                errorMsg.style.display = "none";
            }
        });

        // Cambiar entre PIN y Admin
        modeTabs.forEach(tab => {
            tab.addEventListener("click", () => {
                modeTabs.forEach(t => t.classList.remove("active"));
                tab.classList.add("active");
                const mode = tab.getAttribute("data-mode");

                errorMsg.style.display = "none";
                if (mode === "admin") {
                    pinSection.style.display = "none";
                    adminSection.style.display = "block";
                } else {
                    adminSection.style.display = "none";
                    pinSection.style.display = "block";
                    currentPin = "";
                    updatePinDots();
                }
            });
        });

        const adminUserInput = document.getElementById("admin-user");
        const adminPassInput = document.getElementById("admin-pass");
        const btnAdminLogin = document.getElementById("btn-admin-login");
        if (btnAdminLogin) btnAdminLogin.disabled = false;

        if (adminForm) {
            adminForm.addEventListener("submit", async (e) => {
                e.preventDefault();
                const nombre = adminUserInput ? adminUserInput.value.trim() : "";
                const password = adminPassInput ? adminPassInput.value : "";

                if (!nombre || !password) {
                    errorMsg.textContent = "Debe ingresar usuario y contraseña de administrador.";
                    errorMsg.style.display = "block";
                    return;
                }

                errorMsg.style.display = "none";
                try {
                    await api.loginAdmin(nombre, password);
                    console.log("✅ Login Admin exitoso, redirigiendo...");
                    window.location.href = "/pos";
                } catch (err) {
                    errorMsg.textContent = err.message || "Credenciales de administrador incorrectas";
                    errorMsg.style.display = "block";
                }
            });
        }
    }
}