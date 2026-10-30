import { api, authState } from "./api.js";

// 🛑 Solo ejecutar si estamos en la página de inventario
if (document.getElementById("inventory-table-body")) {
    
    // ── Auth Guard por Rol ──────────────────────────────────────────────────
    if (!authState.isAuthenticated()) {
        window.location.href = "/";
    }

    const user = authState.getUser();
    if (user) {
        const uName = document.getElementById("header-user-name");
        const uRole = document.getElementById("header-user-role");
        if (uName) uName.textContent = user.nombre;
        if (uRole) uRole.textContent = user.rol === "ADMIN" ? "ADMINISTRADOR" : "BARTENDER";
    }

    // Ocultar cosas de admin si es bartender
    if (!authState.isAdmin()) {
        document.querySelectorAll(".admin-only").forEach(el => (el.style.display = "none"));
        const btnNuevo = document.getElementById("btn-nuevo-producto");
        if (btnNuevo) btnNuevo.style.display = "none";
        const btnAddIng = document.getElementById("btn-add-ingrediente");
        if (btnAddIng) btnAddIng.style.display = "none";
        const escEditor = document.getElementById("escandallo-editor");
        if (escEditor) escEditor.style.display = "none";
    }

    // ── Estado ──────────────────────────────────────────────────────────────
    let allProducts = [];
    let allInsumos = [];
    let currentFilter = "";
    let currentSearch = "";

    // ── Carga inicial ───────────────────────────────────────────────────────
    async function loadData() {
        try {
            allProducts = await api.getProductos();
            updateBentoMetrics();
            renderTable();
            allInsumos = allProducts.filter(p => p.tipo === "INSUMO");
            populateEscandalloSelectors();
        } catch (err) {
            showToast("Error al cargar inventario: " + err.message, "danger");
        }
    }

    function updateBentoMetrics() {
        const totalValor = allProducts.reduce((acc, p) => acc + (parseFloat(p.precio_venta || 0) * (p.stock_ml > 0 ? (p.stock_ml / 750) : 0)), 0);
        const metricValor = document.getElementById("metric-valor-total");
        if (metricValor) metricValor.textContent = `Q ${totalValor.toLocaleString("es-GT", { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

        const metricSkus = document.getElementById("metric-total-skus");
        if (metricSkus) metricSkus.textContent = allProducts.length;

        const criticos = allProducts.filter(p => p.stock_ml <= p.stock_minimo_ml).length;
        const metricCriticos = document.getElementById("metric-alertas-count");
        if (metricCriticos) metricCriticos.textContent = criticos;

        const desglose = document.getElementById("metric-sku-desglose");
        if (desglose) {
            const champ = allProducts.filter(p => p.categoria === "CHAMPAGNE").length;
            const lic = allProducts.filter(p => p.categoria === "LICORES").length;
            const coct = allProducts.filter(p => p.categoria === "COCTELES_AUTOR").length;
            desglose.textContent = `${champ} Champagnes • ${lic} Licores • ${coct} Cócteles`;
        }
    }

    // ── Tabla de Inventario ─────────────────────────────────────────────────
    function renderTable() {
        const tbody = document.getElementById("inventory-table-body");
        const isAdmin = authState.isAdmin();
        const thAcciones = document.getElementById("th-acciones");
        if (thAcciones) thAcciones.style.display = isAdmin ? "" : "none";

        let filtered = allProducts.filter(p => {
            const matchCat = !currentFilter || p.categoria === currentFilter;
            const matchQ = !currentSearch || p.nombre.toLowerCase().includes(currentSearch.toLowerCase());
            return matchCat && matchQ;
        });

        if (filtered.length === 0) {
            tbody.innerHTML = `<tr><td colspan="${isAdmin ? 10 : 9}" class="text-center text-on-surface-variant py-8">Sin productos coincidentes en esta categoría.</td></tr>`;
            return;
        }

        tbody.innerHTML = filtered.map(p => {
            const isNeg = p.stock_ml < 0;
            const isLow = p.stock_ml > 0 && p.stock_ml <= p.stock_minimo_ml;
            let badge = `<span class="badge-status optimo">● Óptimo</span>`;
            if (isNeg) badge = `<span class="badge-status negativo">⚠ Negativo</span>`;
            else if (isLow) badge = `<span class="badge-status reordenar">⚠ Reordenar</span>`;
            else if (p.stock_ml === 0 && p.tipo === "COMPUESTO")
                badge = `<span class="badge-status escandallo">🧪 Escandallo</span>`;

            const imgEl = `<img src="${p.imagen_url}" onerror="this.src='/assets/img/cocktail-fallback.svg'" class="w-10 h-10 object-contain rounded bg-stone-900 border border-stone-800">`;

            let actionsTd = "";
            if (isAdmin) {
                actionsTd = `
                <td class="py-3 px-3 text-right">
                    <div class="inline-flex gap-1.5">
                        <button class="btn btn-subtle-gold btn-edit px-2.5 py-1 text-xs font-semibold" data-id="${p.id_producto}">Editar</button>
                        <button class="btn btn-danger btn-del px-2.5 py-1 text-xs" data-id="${p.id_producto}" data-name="${escH(p.nombre)}">Eliminar</button>
                    </div>
                </td>`;
            }

            return `
            <tr class="hover:bg-surface-container-high/40 transition-colors">
                <td class="py-3 px-3">${imgEl}</td>
                <td class="py-3 px-3 font-semibold text-on-surface max-w-[200px]">${escH(p.nombre)}</td>
                <td class="py-3 px-3"><span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-primary/10 text-primary uppercase">${p.categoria}</span></td>
                <td class="py-3 px-3 text-on-surface-variant text-xs">${p.tipo}</td>
                <td class="py-3 px-3 font-bold text-primary">Q ${parseFloat(p.precio_venta).toFixed(2)}</td>
                <td class="py-3 px-3 font-semibold">${p.stock_ml.toLocaleString()} ml</td>
                <td class="py-3 px-3 text-on-surface-variant">${p.stock_ml > 0 ? (p.stock_ml/750).toFixed(2)+' bot.' : '—'}</td>
                <td class="py-3 px-3 text-on-surface-variant">${p.stock_minimo_ml.toLocaleString()} ml</td>
                <td class="py-3 px-3">${badge}</td>
                ${actionsTd}
            </tr>`;
        }).join("");

        // Listeners de la tabla
        if (authState.isAdmin()) {
            tbody.querySelectorAll(".btn-edit").forEach(btn => {
                btn.addEventListener("click", () => openEditModal(parseInt(btn.getAttribute("data-id"), 10)));
            });
            tbody.querySelectorAll(".btn-del").forEach(btn => {
                btn.addEventListener("click", () => {
                    document.getElementById("delete-prod-id").value = btn.getAttribute("data-id");
                    document.getElementById("delete-prod-name").textContent = btn.getAttribute("data-name");
                    document.getElementById("delete-modal").classList.remove("hidden");
                });
            });
        }
    }

    // ── Modal Crear / Editar ────────────────────────────────────────────────
    const getModalProducto = () => document.getElementById("modal-nuevo-producto") || document.getElementById("producto-modal");

    // ── Drag & Drop / File Input para imagen ──
    const dropZone = document.getElementById("drop-zone-imagen");
    const fileInput = document.getElementById("input-imagen-archivo");
    const prodImgInput = document.getElementById("prod-imagen");
    const previewContainer = document.getElementById("preview-imagen-container");
    const previewImg = document.getElementById("preview-imagen-img");
    const previewNombre = document.getElementById("preview-nombre-archivo");

    if (dropZone && fileInput) {
        dropZone.addEventListener("click", () => fileInput.click());

        ["dragenter", "dragover"].forEach(eventName => {
            dropZone.addEventListener(eventName, e => {
                e.preventDefault();
                e.stopPropagation();
                dropZone.classList.add("border-primary", "bg-primary/5");
            });
        });

        ["dragleave", "drop"].forEach(eventName => {
            dropZone.addEventListener(eventName, e => {
                e.preventDefault();
                e.stopPropagation();
                dropZone.classList.remove("border-primary", "bg-primary/5");
            });
        });

        dropZone.addEventListener("drop", e => {
            const dt = e.dataTransfer;
            if (dt && dt.files && dt.files.length) {
                handleFileUpload(dt.files[0]);
            }
        });

        fileInput.addEventListener("change", () => {
            if (fileInput.files && fileInput.files.length) {
                handleFileUpload(fileInput.files[0]);
            }
        });
    }

    async function handleFileUpload(file) {
        if (!file.type.startsWith("image/")) {
            showToast("El archivo seleccionado debe ser una imagen válida.", "warning");
            return;
        }

        const reader = new FileReader();
        reader.onload = e => {
            if (previewImg) previewImg.src = e.target.result;
            if (previewContainer) previewContainer.classList.remove("hidden");
        };
        reader.readAsDataURL(file);

        if (previewNombre) previewNombre.textContent = `Subiendo: ${file.name}…`;

        try {
            const res = await api.uploadImagen(file);
            if (prodImgInput) prodImgInput.value = res.url;
            if (previewNombre) previewNombre.textContent = `✓ Archivo cargado: ${res.filename}`;
            showToast("✓ Imagen subida y vinculada al producto.", "success");
        } catch (err) {
            if (previewNombre) previewNombre.textContent = "Error al subir imagen.";
            showToast("Error al subir imagen: " + err.message, "danger");
        }
    }

    function openCreateModal() {
        document.getElementById("prod-id").value = "";
        document.getElementById("producto-form").reset();
        if (fileInput) fileInput.value = "";
        if (previewContainer) previewContainer.classList.add("hidden");
        if (previewImg) previewImg.src = "";
        if (previewNombre) previewNombre.textContent = "Formatos: PNG, JPG, WEBP, SVG";
        document.getElementById("producto-modal-title").innerHTML = `<span class="material-symbols-outlined text-primary">add_box</span><span>Nuevo Insumo / Licor</span>`;
        document.getElementById("prod-submit-btn").textContent = "Crear Insumo / Licor";
        getModalProducto().classList.remove("hidden");
    }

    function openEditModal(id) {
        const p = allProducts.find(x => x.id_producto === id);
        if (!p) return;
        document.getElementById("prod-id").value = p.id_producto;
        document.getElementById("prod-nombre").value = p.nombre;
        document.getElementById("prod-categoria").value = p.categoria;
        document.getElementById("prod-tipo").value = p.tipo;
        document.getElementById("prod-precio").value = parseFloat(p.precio_venta).toFixed(2);
        document.getElementById("prod-stock").value = p.stock_ml;
        document.getElementById("prod-min").value = p.stock_minimo_ml;
        document.getElementById("prod-unidad").value = p.unidad_medida;
        document.getElementById("prod-imagen").value = p.imagen_url || "";
        
        if (fileInput) fileInput.value = "";
        if (p.imagen_url) {
            if (previewImg) {
                previewImg.src = p.imagen_url.startsWith("http") || p.imagen_url.startsWith("/") ? p.imagen_url : `/assets/img/${p.imagen_url}`;
            }
            if (previewContainer) previewContainer.classList.remove("hidden");
            if (previewNombre) previewNombre.textContent = `Imagen actual: ${p.imagen_url}`;
        } else {
            if (previewContainer) previewContainer.classList.add("hidden");
            if (previewImg) previewImg.src = "";
            if (previewNombre) previewNombre.textContent = "Formatos: PNG, JPG, WEBP, SVG";
        }

        document.getElementById("producto-modal-title").innerHTML = `<span class="material-symbols-outlined text-primary">edit</span><span>Editar: ${p.nombre}</span>`;
        document.getElementById("prod-submit-btn").textContent = "Guardar Cambios";
        getModalProducto().classList.remove("hidden");
    }

    const formProducto = document.getElementById("producto-form");
    if (formProducto) {
        formProducto.addEventListener("submit", async e => {
            e.preventDefault();
            const btn = document.getElementById("prod-submit-btn");
            btn.disabled = true;
            btn.textContent = "Guardando…";

            const id = document.getElementById("prod-id").value;
            const payload = {
                nombre: document.getElementById("prod-nombre").value.trim(),
                categoria: document.getElementById("prod-categoria").value,
                tipo: document.getElementById("prod-tipo").value,
                precio_venta: parseFloat(document.getElementById("prod-precio").value),
                stock_ml: parseInt(document.getElementById("prod-stock").value, 10) || 0,
                stock_minimo_ml: parseInt(document.getElementById("prod-min").value, 10) || 750,
                unidad_medida: document.getElementById("prod-unidad").value,
                imagen_url: document.getElementById("prod-imagen").value.trim() || null
            };

            try {
                if (id) {
                    await api.updateProducto(parseInt(id, 10), payload);
                    showToast(`✓ Producto actualizado exitosamente.`, "success");
                } else {
                    await api.createProducto(payload);
                    showToast(`✓ Producto creado en inventario.`, "success");
                }
                getModalProducto().classList.add("hidden");
                await loadData();
            } catch (err) {
                showToast("Error: " + err.message, "danger");
            } finally {
                btn.disabled = false;
                btn.textContent = "Guardar";
            }
        });
    }

    // ── Eliminar Producto ───────────────────────────────────────────────────
    const confirmDeleteBtn = document.getElementById("confirm-delete-btn");
    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener("click", async () => {
            const id = parseInt(document.getElementById("delete-prod-id").value, 10);
            try {
                await api.deleteProducto(id);
                document.getElementById("delete-modal").classList.add("hidden");
                showToast("✓ Producto eliminado del inventario.", "success");
                await loadData();
            } catch (err) {
                showToast("Error: " + err.message, "danger");
            }
        });
    }

    // ── Escandallos ─────────────────────────────────────────────────────────
    function populateEscandalloSelectors() {
        const prodSel = document.getElementById("escandallo-producto-sel");
        const insumoSel = document.getElementById("new-insumo-sel");
        if (!prodSel || !insumoSel) return;

        const compuestos = allProducts.filter(p => p.tipo === "COMPUESTO" || p.tipo === "FINAL");
        prodSel.innerHTML = `<option value="">— Seleccionar producto —</option>` +
            compuestos.map(p => `<option value="${p.id_producto}">${escH(p.nombre)}</option>`).join("");

        insumoSel.innerHTML = `<option value="">— Seleccionar insumo —</option>` +
            allInsumos.map(p => `<option value="${p.id_producto}">${escH(p.nombre)} (${p.stock_ml} ml)</option>`).join("");
    }

    const escandaloSelect = document.getElementById("escandallo-producto-sel");
    if (escandaloSelect) {
        escandaloSelect.addEventListener("change", function() {
            const id = parseInt(this.value, 10);
            const prod = allProducts.find(p => p.id_producto === id);
            const panel = document.getElementById("escandallo-current");
            const editor = document.getElementById("escandallo-editor");
            if (!prod) {
                panel.style.display = "none";
                editor.style.display = "none";
                return;
            }
            renderIngredientsList(prod);
            panel.style.display = "block";
            editor.style.display = "none";
        });
    }

    function renderIngredientsList(prod) {
        const list = document.getElementById("escandallo-ingredients-list");
        if (!prod.recetas_como_final || prod.recetas_como_final.length === 0) {
            list.innerHTML = `<p class="text-on-surface-variant text-sm">Sin ingredientes definidos.</p>`;
            return;
        }
        list.innerHTML = prod.recetas_como_final.map(r => `
            <div class="flex justify-between items-center bg-surface-container p-2.5 rounded-xl border border-white/5 text-sm">
                <span class="text-on-surface font-semibold">${escH(r.nombre_insumo)}</span>
                <div class="flex items-center gap-3">
                    <span class="text-primary font-bold">−${r.cantidad_descontar_ml} ml</span>
                    ${authState.isAdmin() ? `<button class="btn btn-danger btn-del-ing px-2 py-0.5 text-xs" data-id="${r.id_receta}">✕</button>` : ""}
                </div>
            </div>`).join("");

        list.querySelectorAll(".btn-del-ing").forEach(btn => {
            btn.addEventListener("click", async () => {
                const idR = parseInt(btn.getAttribute("data-id"), 10);
                try {
                    await api.deleteReceta(idR);
                    showToast("Ingrediente eliminado.", "success");
                    await loadData();
                    const id = parseInt(document.getElementById("escandallo-producto-sel").value, 10);
                    const updatedProd = allProducts.find(p => p.id_producto === id);
                    if (updatedProd) renderIngredientsList(updatedProd);
                } catch (err) {
                    showToast("Error: " + err.message, "danger");
                }
            });
        });
    }

    const btnAddIng = document.getElementById("btn-add-ingrediente");
    if (btnAddIng) {
        btnAddIng.addEventListener("click", () => {
            document.getElementById("escandallo-editor").style.display = "block";
        });
    }

    const btnConfirmIng = document.getElementById("btn-confirm-ingrediente");
    if (btnConfirmIng) {
        btnConfirmIng.addEventListener("click", async () => {
            const prodId = parseInt(document.getElementById("escandallo-producto-sel").value, 10);
            const insumoId = parseInt(document.getElementById("new-insumo-sel").value, 10);
            const ml = parseInt(document.getElementById("new-insumo-ml").value, 10);

            if (!prodId || !insumoId || !ml || ml <= 0) {
                showToast("Completa todos los campos del ingrediente.", "warning");
                return;
            }

            const prod = allProducts.find(p => p.id_producto === prodId);
            if (!prod) return;

            const existentes = (prod.recetas_como_final || []).map(r => ({
                id_insumo_base: r.id_insumo_base,
                cantidad_descontar_ml: r.cantidad_descontar_ml
            }));
            existentes.push({ id_insumo_base: insumoId, cantidad_descontar_ml: ml });

            try {
                await api.asignarReceta({ id_producto_final: prodId, ingredientes: existentes });
                showToast("✓ Ingrediente agregado al escandallo.", "success");
                document.getElementById("escandallo-editor").style.display = "none";
                document.getElementById("new-insumo-ml").value = "";
                await loadData();
                const updatedProd = allProducts.find(p => p.id_producto === prodId);
                if (updatedProd) renderIngredientsList(updatedProd);
            } catch (err) {
                showToast("Error: " + err.message, "danger");
            }
        });
    }

    // ── Filtros ─────────────────────────────────────────────────────────────
    document.querySelectorAll(".filter-btn").forEach(btn => {
        btn.addEventListener("click", () => {
            document.querySelectorAll(".filter-btn").forEach(b => b.classList.remove("active"));
            btn.classList.add("active");
            currentFilter = btn.getAttribute("data-cat");
            renderTable();
        });
    });

    const invSearch = document.getElementById("inv-search");
    if (invSearch) {
        invSearch.addEventListener("input", e => {
            currentSearch = e.target.value;
            renderTable();
        });
    }

    // ── Tabs de sección ─────────────────────────────────────────────────────
    document.querySelectorAll(".section-tab").forEach(tab => {
        tab.addEventListener("click", () => {
            document.querySelectorAll(".section-tab").forEach(t => t.classList.remove("active"));
            document.querySelectorAll(".section-view").forEach(v => v.classList.remove("active"));
            tab.classList.add("active");
            const viewId = `view-${tab.getAttribute("data-section")}`;
            const viewEl = document.getElementById(viewId);
            if (viewEl) viewEl.classList.add("active");
        });
    });

    // ── Nuevo producto ───────────────────────────────────────────────────────
    const btnNuevo = document.getElementById("btn-nuevo-producto");
    if (btnNuevo) {
        btnNuevo.addEventListener("click", openCreateModal);
    }

    // ── Logout Superior ──────────────────────────────────────────────────────
    const topLogoutBtn = document.getElementById("top-logout-btn");
    if (topLogoutBtn) {
        topLogoutBtn.addEventListener("click", () => {
            authState.clear();
            window.location.href = "/";
        });
    }

    // ── Cerrar modales ───────────────────────────────────────────────────────
    document.querySelectorAll(".modal-close, .modal-cancel").forEach(btn => {
        btn.addEventListener("click", () => {
            document.querySelectorAll("#modal-nuevo-producto, #delete-modal").forEach(m => m.classList.add("hidden"));
        });
    });
    document.querySelectorAll("#modal-nuevo-producto, #delete-modal").forEach(overlay => {
        overlay.addEventListener("click", e => {
            if (e.target === overlay) overlay.classList.add("hidden");
        });
    });

    // ── Toast ────────────────────────────────────────────────────────────────
    function showToast(msg, type = "success") {
        const container = document.getElementById("toast-container");
        if (!container) return;
        const t = document.createElement("div");
        t.className = `p-3 rounded-xl shadow-2xl text-sm font-semibold border ${
            type === "danger"
                ? "bg-red-950/90 text-red-200 border-red-700/50"
                : type === "warning"
                ? "bg-amber-950/90 text-amber-200 border-amber-700/50"
                : "bg-surface-container-high/90 text-primary border-primary/40"
        }`;
        t.textContent = msg;
        container.appendChild(t);
        setTimeout(() => {
            t.style.transition = "opacity .3s";
            t.style.opacity = "0";
            setTimeout(() => t.remove(), 300);
        }, 4000);
    }

    function escH(s) {
        return String(s ?? "").replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;");
    }

    // ── Init ─────────────────────────────────────────────────────────────────
    loadData();
}