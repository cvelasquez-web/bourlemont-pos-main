import { api, authState } from "./api.js";

// 🛑 Solo ejecutar si estamos en la página de usuarios
if (document.getElementById("usuarios-table-body")) {
    if (!authState.isAuthenticated()) {
        window.location.href = "/";
    } else if (!authState.isAdmin()) {
        alert("Acceso denegado: Este módulo requiere credenciales de Administrador.");
        window.location.href = "/pos";
    }

    // ── Utilidades ───────────────────────────────────────────────────────────
    function showToast(mensaje, esError = false) {
        const toast = document.getElementById("toast-msg");
        toast.textContent = mensaje;
        toast.style.borderColor = esError ? "rgba(239, 68, 68, 0.6)" : "rgba(242, 202, 80, 0.6)";
        toast.style.color = esError ? "#ffb4ab" : "#f2ca50";
        toast.style.display = "block";
        setTimeout(() => { toast.style.display = "none"; }, 3500);
    }

    function setAvatarPreview(containerId, url) {
        const el = document.getElementById(containerId);
        if (!el) return;
        if (url) {
            el.innerHTML = `<img src="${url}" class="w-full h-full object-cover" onerror="this.onerror=null; this.parentElement.innerHTML='<span class=\\'material-symbols-outlined text-2xl\\'>person</span>'"/>`;
        } else {
            el.innerHTML = `<span class="material-symbols-outlined text-2xl">person</span>`;
        }
    }

    // ── Cargar Tabla ─────────────────────────────────────────────────────────
    async function cargarUsuarios() {
        const tbody = document.getElementById("usuarios-table-body");
        try {
            const usuarios = await api.getUsuarios();
            if (!usuarios || usuarios.length === 0) {
                tbody.innerHTML = `<tr><td colspan="5" class="text-center text-on-surface-variant py-8">No hay colaboradores registrados.</td></tr>`;
                return;
            }

            tbody.innerHTML = usuarios.map(u => {
                const rolBadge = u.rol === "ADMIN"
                    ? `<span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-primary/15 text-primary border border-primary/25">Administrador</span>`
                    : `<span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-surface-container-high text-on-surface-variant">Bartender</span>`;

                const estadoBadge = u.activo
                    ? `<span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/15 text-emerald-400 border border-emerald-500/25"><span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>Activo</span>`
                    : `<span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xs font-semibold bg-red-500/15 text-red-400 border border-red-500/25"><span class="w-1.5 h-1.5 rounded-full bg-red-400"></span>Inactivo</span>`;

                const avatarContent = u.foto_url 
                    ? `<img src="${u.foto_url}" alt="${u.nombre}" class="w-full h-full object-cover" onerror="this.onerror=null; this.parentElement.innerHTML='<span class=\\'material-symbols-outlined text-[18px]\\'>person</span>'"/>` 
                    : `<span class="material-symbols-outlined text-[18px]">person</span>`;

                return `
                    <tr class="hover:bg-surface-container-high/40 transition-colors">
                        <td class="py-3 px-3 font-mono text-primary font-semibold">#${u.id_usuario}</td>
                        <td class="py-3 px-3">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full overflow-hidden bg-primary/20 border border-primary/30 flex items-center justify-center flex-shrink-0 text-primary">
                                    ${avatarContent}
                                </div>
                                <span class="font-semibold text-on-surface">${u.nombre}</span>
                            </div>
                        </td>
                        <td class="py-3 px-3">${rolBadge}</td>
                        <td class="py-3 px-3">${estadoBadge}</td>
                        <td class="py-3 px-3 text-right">
                            <div class="inline-flex gap-1.5 justify-end">
                                <button class="btn btn-subtle-gold btn-edit px-2.5 py-1 text-xs font-semibold" data-user='${JSON.stringify(u)}'>
                                    ✏️ Editar
                                </button>
                                ${u.activo ? `
                                    <button class="btn btn-danger btn-deactivate px-2.5 py-1 text-xs font-semibold" data-id="${u.id_usuario}" data-nombre="${u.nombre}">
                                        Desactivar
                                    </button>
                                ` : ''}
                            </div>
                        </td>
                    </tr>
                `;
            }).join("");

            document.querySelectorAll(".btn-edit").forEach(btn => {
                btn.addEventListener("click", () => {
                    const u = JSON.parse(btn.getAttribute("data-user"));
                    abrirModalEditar(u);
                });
            });

            document.querySelectorAll(".btn-deactivate").forEach(btn => {
                btn.addEventListener("click", () => {
                    abrirModalDesactivar(btn.getAttribute("data-id"), btn.getAttribute("data-nombre"));
                });
            });

        } catch (err) {
            tbody.innerHTML = `<tr><td colspan="5" class="text-center text-error py-8">Error al cargar usuarios: ${err.message}</td></tr>`;
        }
    }

    // ── Modal Desactivar ─────────────────────────────────────────────────────
    const modalDesactivar = document.getElementById("modal-confirm-desactivar");
    const cerrarDesactivar = () => modalDesactivar.classList.add("hidden");
    document.getElementById("btn-cancelar-desactivar").addEventListener("click", cerrarDesactivar);

    function abrirModalDesactivar(id, nombre) {
        document.getElementById("deactivate-user-id").value = id;
        document.getElementById("deactivate-user-prompt").textContent = `¿Confirmas la desactivación del colaborador '${nombre}'? No podrá iniciar turno ni operar la terminal.`;
        modalDesactivar.classList.remove("hidden");
    }

    document.getElementById("btn-confirmar-desactivar").addEventListener("click", async () => {
        const id = document.getElementById("deactivate-user-id").value;
        try {
            await api.deleteUsuario(id);
            cerrarDesactivar();
            showToast("Colaborador desactivado exitosamente.");
            cargarUsuarios();
        } catch (err) {
            showToast(err.message || "Error al desactivar usuario", true);
        }
    });

    // ── Modal Nuevo Usuario ──────────────────────────────────────────────────
    const modalNuevo = document.getElementById("modal-nuevo-usuario");
    const nuevoRolSelect = document.getElementById("nuevo-rol");
    const grupoNuevoPass = document.getElementById("grupo-nuevo-pass");
    const nuevoFotoUrlInput = document.getElementById("nuevo-foto-url");

    document.getElementById("btn-nuevo-usuario").addEventListener("click", () => {
        document.getElementById("form-nuevo-usuario").reset();
        nuevoRolSelect.value = "BARTENDER";
        grupoNuevoPass.style.display = "none";
        document.getElementById("nuevo-pass").required = false;
        document.getElementById("nuevo-pin").required = true;
        nuevoFotoUrlInput.value = "";
        setAvatarPreview("nuevo-avatar-preview", null);
        modalNuevo.classList.remove("hidden");
    });

    const cerrarNuevo = () => modalNuevo.classList.add("hidden");
    document.getElementById("btn-cancelar-nuevo").addEventListener("click", cerrarNuevo);
    document.getElementById("btn-cerrar-modal-nuevo")?.addEventListener("click", cerrarNuevo);

    nuevoRolSelect.addEventListener("change", (e) => {
        if (e.target.value === "ADMIN") {
            grupoNuevoPass.style.display = "flex";
            document.getElementById("nuevo-pass").required = true;
        } else {
            grupoNuevoPass.style.display = "none";
            document.getElementById("nuevo-pass").required = false;
        }
    });

    // Subida de Avatar Nuevo
    document.getElementById("btn-upload-nuevo-avatar")?.addEventListener("click", () => document.getElementById("nuevo-avatar-file").click());
    document.getElementById("nuevo-avatar-file")?.addEventListener("change", async (e) => {
        if (!e.target.files[0]) return;
        const btn = document.getElementById("btn-upload-nuevo-avatar");
        try {
            btn.disabled = true;
            btn.innerHTML = `<span class="material-symbols-outlined text-[15px] animate-spin">refresh</span> Subiendo...`;
            const res = await api.uploadAvatar(e.target.files[0]);
            nuevoFotoUrlInput.value = res.url;
            setAvatarPreview("nuevo-avatar-preview", res.url);
            showToast("Avatar subido exitosamente.");
        } catch (err) {
            showToast(err.message || "Error al subir avatar", true);
        } finally {
            btn.disabled = false;
            btn.innerHTML = `<span class="material-symbols-outlined text-[15px]">upload</span> Subir Foto`;
        }
    });

    document.querySelectorAll(".btn-preset-avatar-nuevo").forEach(btn => {
        btn.addEventListener("click", () => {
            const url = btn.getAttribute("data-url");
            nuevoFotoUrlInput.value = url;
            setAvatarPreview("nuevo-avatar-preview", url);
        });
    });

    document.getElementById("btn-clear-nuevo-avatar")?.addEventListener("click", () => {
        nuevoFotoUrlInput.value = "";
        setAvatarPreview("nuevo-avatar-preview", null);
    });

    document.getElementById("form-nuevo-usuario").addEventListener("submit", async (e) => {
        e.preventDefault();
        const payload = {
            nombre: document.getElementById("nuevo-nombre").value.trim(),
            rol: nuevoRolSelect.value,
            pin: document.getElementById("nuevo-pin").value.trim() || null,
            password: nuevoRolSelect.value === "ADMIN" ? document.getElementById("nuevo-pass").value.trim() : null,
            foto_url: nuevoFotoUrlInput.value.trim() || null,
            activo: true
        };

        if (!payload.nombre) return;

        try {
            await api.crearUsuario(payload);
            modalNuevo.classList.add("hidden");
            showToast(`Colaborador '${payload.nombre}' registrado exitosamente.`);
            cargarUsuarios();
        } catch (err) {
            showToast(err.message || "Error al crear usuario", true);
        }
    });

    // ── Modal Editar Usuario ─────────────────────────────────────────────────
    const modalEdit = document.getElementById("modal-editar-usuario");
    const editRolSelect = document.getElementById("edit-rol");
    const grupoEditPass = document.getElementById("grupo-edit-pass");
    const editFotoUrlInput = document.getElementById("edit-foto-url");

    const cerrarEdit = () => modalEdit.classList.add("hidden");
    document.getElementById("btn-cancelar-edit").addEventListener("click", cerrarEdit);
    document.getElementById("btn-cerrar-modal-edit")?.addEventListener("click", cerrarEdit);

    editRolSelect.addEventListener("change", (e) => {
        grupoEditPass.style.display = e.target.value === "ADMIN" ? "flex" : "none";
    });

    function abrirModalEditar(u) {
        document.getElementById("edit-id").value = u.id_usuario;
        document.getElementById("edit-nombre").value = u.nombre;
        editRolSelect.value = u.rol;
        document.getElementById("edit-activo").value = u.activo ? "true" : "false";
        document.getElementById("edit-pin").value = "";
        document.getElementById("edit-pass").value = "";
        editFotoUrlInput.value = u.foto_url || "";
        setAvatarPreview("edit-avatar-preview", u.foto_url || null);
        grupoEditPass.style.display = u.rol === "ADMIN" ? "flex" : "none";
        modalEdit.classList.remove("hidden");
    }

    // Subida de Avatar Edición
    document.getElementById("btn-upload-edit-avatar")?.addEventListener("click", () => document.getElementById("edit-avatar-file").click());
    document.getElementById("edit-avatar-file")?.addEventListener("change", async (e) => {
        if (!e.target.files[0]) return;
        const btn = document.getElementById("btn-upload-edit-avatar");
        try {
            btn.disabled = true;
            btn.innerHTML = `<span class="material-symbols-outlined text-[15px] animate-spin">refresh</span> Subiendo...`;
            const res = await api.uploadAvatar(e.target.files[0]);
            editFotoUrlInput.value = res.url;
            setAvatarPreview("edit-avatar-preview", res.url);
            showToast("Avatar subido exitosamente.");
        } catch (err) {
            showToast(err.message || "Error al subir avatar", true);
        } finally {
            btn.disabled = false;
            btn.innerHTML = `<span class="material-symbols-outlined text-[15px]">upload</span> Subir Foto`;
        }
    });

    document.querySelectorAll(".btn-preset-avatar-edit").forEach(btn => {
        btn.addEventListener("click", () => {
            const url = btn.getAttribute("data-url");
            editFotoUrlInput.value = url;
            setAvatarPreview("edit-avatar-preview", url);
        });
    });

    document.getElementById("btn-clear-edit-avatar")?.addEventListener("click", () => {
        editFotoUrlInput.value = "";
        setAvatarPreview("edit-avatar-preview", null);
    });

    document.getElementById("form-editar-usuario").addEventListener("submit", async (e) => {
        e.preventDefault();
        const payload = {
            nombre: document.getElementById("edit-nombre").value.trim(),
            rol: editRolSelect.value,
            activo: document.getElementById("edit-activo").value === "true",
            foto_url: editFotoUrlInput.value.trim() || null
        };
        const pin = document.getElementById("edit-pin").value.trim();
        const password = document.getElementById("edit-pass").value.trim();

        if (pin) payload.pin = pin;
        if (password) payload.password = password;

        try {
            await api.updateUsuario(document.getElementById("edit-id").value, payload);
            modalEdit.classList.add("hidden");
            showToast("Colaborador actualizado exitosamente.");
            cargarUsuarios();
        } catch (err) {
            showToast(err.message || "Error al actualizar usuario", true);
        }
    });

    // Cerrar modales al hacer clic fuera
    [modalNuevo, modalEdit, modalDesactivar].forEach(modal => {
        modal.addEventListener("click", (e) => {
            if (e.target === modal) modal.classList.add("hidden");
        });
    });

    document.getElementById("top-logout-btn")?.addEventListener("click", () => {
        authState.clear();
        window.location.href = "/";
    });

    // ── Init ─────────────────────────────────────────────────────────────────
    cargarUsuarios();
}