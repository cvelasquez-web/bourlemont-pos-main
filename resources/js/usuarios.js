/**
 * Bourlemont POS - Gestión de Usuarios & Seguridad (JavaScript Module)
 */
import { api, authState } from "./api.js";

function showToast(mensaje, esError = false) {
  const t = document.getElementById("toast-msg");
  if (!t) return;
  t.textContent = mensaje;
  t.style.display = "block";
  t.style.borderColor = esError ? "rgba(239, 68, 68, 0.5)" : "rgba(212, 175, 55, 0.4)";
  t.style.color = esError ? "#f87171" : "#f2ca50";
  setTimeout(() => {
    t.style.display = "none";
  }, 3500);
}

function setAvatarPreview(idContenedor, url) {
  const el = document.getElementById(idContenedor);
  if (!el) return;
  if (url) {
    el.innerHTML = `<img src="${url}" class="w-full h-full object-cover">`;
  } else {
    el.innerHTML = `<span class="material-symbols-outlined text-2xl">person</span>`;
  }
}

export async function initUsuarios() {
  const tbody = document.getElementById("usuarios-table-body");
  if (!tbody) return;

  const u = authState.getUser();
  if (u) {
    const elName = document.getElementById("header-user-name");
    const elRole = document.getElementById("header-user-role");
    if (elName) elName.textContent = u.nombre || "Usuario";
    if (elRole) elRole.textContent = u.rol === "ADMIN" ? "ADMINISTRADOR" : "BARTENDER";
  }

  // Modales
  const modalNuevo = document.getElementById("modal-nuevo-usuario");
  const modalEdit = document.getElementById("modal-editar-usuario");
  const modalDesactivar = document.getElementById("modal-confirm-desactivar");

  const btnNuevo = document.getElementById("btn-nuevo-usuario");
  const btnCancelarNuevo = document.getElementById("btn-cancelar-nuevo");
  const btnCerrarModalNuevo = document.getElementById("btn-cerrar-modal-nuevo");
  const nuevoRolSelect = document.getElementById("nuevo-rol");
  const grupoNuevoPass = document.getElementById("grupo-nuevo-pass");
  const nuevoPassInput = document.getElementById("nuevo-pass");
  const nuevoPinInput = document.getElementById("nuevo-pin");
  const nuevoFotoUrlInput = document.getElementById("nuevo-foto-url");
  const btnUploadNuevoAvatar = document.getElementById("btn-upload-nuevo-avatar");
  const fileNuevoAvatar = document.getElementById("nuevo-avatar-file");

  if (btnUploadNuevoAvatar && fileNuevoAvatar) {
    btnUploadNuevoAvatar.addEventListener("click", () => fileNuevoAvatar.click());
    fileNuevoAvatar.addEventListener("change", async (e) => {
      const file = e.target.files[0];
      if (!file) return;
      try {
        btnUploadNuevoAvatar.disabled = true;
        btnUploadNuevoAvatar.innerHTML = `<span class="material-symbols-outlined text-[15px] animate-spin">refresh</span> Subiendo...`;
        if (api.uploadAvatar) {
          const res = await api.uploadAvatar(file);
          nuevoFotoUrlInput.value = res.url;
          setAvatarPreview("nuevo-avatar-preview", res.url);
          showToast("Avatar subido exitosamente.");
        } else {
          showToast("Carga de avatar local configurada.");
        }
      } catch (err) {
        showToast(err.message || "Error al subir avatar", true);
      } finally {
        btnUploadNuevoAvatar.disabled = false;
        btnUploadNuevoAvatar.innerHTML = `<span class="material-symbols-outlined text-[15px]">upload</span> Subir Foto`;
      }
    });
  }

  document.querySelectorAll(".btn-preset-avatar-nuevo").forEach(btn => {
    btn.addEventListener("click", () => {
      const url = btn.getAttribute("data-url");
      nuevoFotoUrlInput.value = url;
      setAvatarPreview("nuevo-avatar-preview", url);
    });
  });

  const btnClearNuevoAvatar = document.getElementById("btn-clear-nuevo-avatar");
  if (btnClearNuevoAvatar) {
    btnClearNuevoAvatar.addEventListener("click", () => {
      nuevoFotoUrlInput.value = "";
      setAvatarPreview("nuevo-avatar-preview", null);
    });
  }

  if (btnNuevo) {
    btnNuevo.addEventListener("click", () => {
      const form = document.getElementById("form-nuevo-usuario");
      if (form) form.reset();
      if (nuevoRolSelect) nuevoRolSelect.value = "BARTENDER";
      if (grupoNuevoPass) grupoNuevoPass.style.display = "none";
      if (nuevoPassInput) nuevoPassInput.required = false;
      if (nuevoPinInput) nuevoPinInput.required = true;
      if (nuevoFotoUrlInput) nuevoFotoUrlInput.value = "";
      setAvatarPreview("nuevo-avatar-preview", null);
      modalNuevo?.classList.remove("hidden");
    });
  }

  btnCancelarNuevo?.addEventListener("click", () => modalNuevo?.classList.add("hidden"));
  btnCerrarModalNuevo?.addEventListener("click", () => modalNuevo?.classList.add("hidden"));

  nuevoRolSelect?.addEventListener("change", (e) => {
    if (e.target.value === "ADMIN") {
      grupoNuevoPass.style.display = "flex";
      nuevoPassInput.required = true;
    } else {
      grupoNuevoPass.style.display = "none";
      nuevoPassInput.required = false;
    }
  });

  document.getElementById("form-nuevo-usuario")?.addEventListener("submit", async (e) => {
    e.preventDefault();
    const nombre = document.getElementById("nuevo-nombre").value.trim();
    const rol = nuevoRolSelect.value;
    const pin = nuevoPinInput.value.trim();
    const password = nuevoPassInput.value.trim();
    const foto_url = nuevoFotoUrlInput.value.trim() || null;

    try {
      await api.crearUsuario({
        nombre,
        rol,
        pin,
        password: password || undefined,
        foto_url
      });
      modalNuevo.classList.add("hidden");
      showToast("Colaborador registrado exitosamente.");
      cargarUsuarios();
    } catch (err) {
      showToast(err.message || "Error al crear usuario", true);
    }
  });

  // Modal Editar
  const btnCancelarEdit = document.getElementById("btn-cancelar-edit");
  const btnCerrarModalEdit = document.getElementById("btn-cerrar-modal-edit");
  const editRolSelect = document.getElementById("edit-rol");
  const grupoEditPass = document.getElementById("grupo-edit-pass");
  const editFotoUrlInput = document.getElementById("edit-foto-url");
  const btnUploadEditAvatar = document.getElementById("btn-upload-edit-avatar");
  const fileEditAvatar = document.getElementById("edit-avatar-file");

  if (btnUploadEditAvatar && fileEditAvatar) {
    btnUploadEditAvatar.addEventListener("click", () => fileEditAvatar.click());
    fileEditAvatar.addEventListener("change", async (e) => {
      const file = e.target.files[0];
      if (!file) return;
      try {
        btnUploadEditAvatar.disabled = true;
        btnUploadEditAvatar.innerHTML = `<span class="material-symbols-outlined text-[15px] animate-spin">refresh</span> Subiendo...`;
        if (api.uploadAvatar) {
          const res = await api.uploadAvatar(file);
          editFotoUrlInput.value = res.url;
          setAvatarPreview("edit-avatar-preview", res.url);
          showToast("Avatar subido exitosamente.");
        }
      } catch (err) {
        showToast(err.message || "Error al subir avatar", true);
      } finally {
        btnUploadEditAvatar.disabled = false;
        btnUploadEditAvatar.innerHTML = `<span class="material-symbols-outlined text-[15px]">upload</span> Subir Foto`;
      }
    });
  }

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

  btnCancelarEdit?.addEventListener("click", () => modalEdit?.classList.add("hidden"));
  btnCerrarModalEdit?.addEventListener("click", () => modalEdit?.classList.add("hidden"));

  function abrirModalEditar(u) {
    document.getElementById("edit-id").value = u.id_usuario;
    document.getElementById("edit-nombre").value = u.nombre;
    editRolSelect.value = u.rol;
    document.getElementById("edit-activo").value = u.activo ? "true" : "false";
    document.getElementById("edit-pin").value = "";
    document.getElementById("edit-pass").value = "";
    editFotoUrlInput.value = u.foto_url || "";
    setAvatarPreview("edit-avatar-preview", u.foto_url);

    if (u.rol === "ADMIN") {
      grupoEditPass.style.display = "flex";
    } else {
      grupoEditPass.style.display = "none";
    }

    modalEdit.classList.remove("hidden");
  }

  editRolSelect?.addEventListener("change", (e) => {
    if (e.target.value === "ADMIN") {
      grupoEditPass.style.display = "flex";
    } else {
      grupoEditPass.style.display = "none";
    }
  });

  document.getElementById("form-editar-usuario")?.addEventListener("submit", async (e) => {
    e.preventDefault();
    const id = document.getElementById("edit-id").value;
    const nombre = document.getElementById("edit-nombre").value.trim();
    const rol = editRolSelect.value;
    const activo = document.getElementById("edit-activo").value === "true";
    const pin = document.getElementById("edit-pin").value.trim();
    const password = document.getElementById("edit-pass").value.trim();
    const foto_url = editFotoUrlInput.value.trim() || null;

    const payload = {
      nombre,
      rol,
      foto_url,
      activo
    };

    if (pin) payload.pin = pin;
    if (password) payload.password = password;

    try {
      await api.updateUsuario(id, payload);
      modalEdit.classList.add("hidden");
      showToast("Colaborador actualizado exitosamente.");
      cargarUsuarios();
    } catch (err) {
      showToast(err.message || "Error al actualizar usuario", true);
    }
  });

  // Modal Desactivar
  const btnCancelarDesactivar = document.getElementById("btn-cancelar-desactivar");
  const btnConfirmarDesactivar = document.getElementById("btn-confirmar-desactivar");

  function abrirModalDesactivar(id, nombre) {
    document.getElementById("deactivate-user-id").value = id;
    const prompt = document.getElementById("deactivate-user-prompt");
    if (prompt) {
      prompt.textContent = `¿Confirmas la desactivación del colaborador '${nombre}'? No podrá iniciar turno ni operar la terminal.`;
    }
    modalDesactivar?.classList.remove("hidden");
  }

  btnCancelarDesactivar?.addEventListener("click", () => modalDesactivar?.classList.add("hidden"));

  btnConfirmarDesactivar?.addEventListener("click", async () => {
    const id = document.getElementById("deactivate-user-id").value;
    try {
      await api.deleteUsuario(id);
      modalDesactivar?.classList.add("hidden");
      showToast("Colaborador desactivado exitosamente.");
      cargarUsuarios();
    } catch (err) {
      showToast(err.message || "Error al desactivar usuario", true);
    }
  });

  [modalNuevo, modalEdit, modalDesactivar].forEach(modal => {
    modal?.addEventListener("click", (e) => {
      if (e.target === modal) modal.classList.add("hidden");
    });
  });

  async function cargarUsuarios() {
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
                <button class="btn btn-subtle-gold btn-edit px-2.5 py-1 text-xs font-semibold cursor-pointer" data-user='${JSON.stringify(u)}'>
                  Editar
                </button>
                ${u.activo ? `
                  <button class="btn px-2.5 py-1 text-xs font-semibold bg-red-500/15 hover:bg-red-500 text-red-400 hover:text-white rounded-lg transition-colors cursor-pointer btn-deactivate" data-id="${u.id_usuario}" data-nombre="${u.nombre}">
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
          const userObj = JSON.parse(btn.getAttribute("data-user"));
          abrirModalEditar(userObj);
        });
      });

      document.querySelectorAll(".btn-deactivate").forEach(btn => {
        btn.addEventListener("click", () => {
          const id = btn.getAttribute("data-id");
          const nombre = btn.getAttribute("data-nombre");
          abrirModalDesactivar(id, nombre);
        });
      });

    } catch (err) {
      tbody.innerHTML = `<tr><td colspan="5" class="text-center text-red-400 py-8">Error al cargar usuarios: ${err.message}</td></tr>`;
    }
  }

  await cargarUsuarios();
}

// Inicialización automática
if (typeof document !== "undefined") {
  document.addEventListener("DOMContentLoaded", initUsuarios);
}
