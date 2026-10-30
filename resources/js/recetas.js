import { api, authState } from "./api.js";

// 🛑 Solo ejecutar si estamos en la página de recetas
if (document.getElementById("cocktails-grid")) {
    if (!authState.isAuthenticated()) {
        window.location.href = "/";
    }
    
    // ── Estado ──────────────────────────────────────────────────────────────
    let allProducts = [];
    let allInsumos = [];
    let allRecipes = [];

    // ── Carga de Datos ──────────────────────────────────────────────────────
    async function loadData() {
        try {
            allProducts = await api.getProductos();
            allInsumos = allProducts.filter(p => p.tipo === "INSUMO");
            allRecipes = await api.getRecetas();
            renderGrid();
            populateCoctelSelector();
        } catch (err) {
            showToast("Error al cargar escandallos: " + err.message, "danger");
        }
    }

    // ── Renderizado de Tarjetas de Cócteles ──────────────────────────────────
    function renderGrid() {
        const container = document.getElementById("cocktails-grid");
        if (allRecipes.length === 0) {
            container.innerHTML = `<div class="col-span-full text-center text-on-surface-variant py-10">
                No hay cócteles con escandallos registrados. Haz clic en '+ Asignar / Editar Escandallo' para crear uno.
            </div>`;
            return;
        }

        container.innerHTML = allRecipes.map(c => {
            const totalMl = (c.recetas_como_final || []).reduce((acc, r) => acc + r.cantidad_descontar_ml, 0);

            return `
                <div class="cocktail-card" data-id="${c.id_producto}">
                    <div>
                        <div class="flex justify-between items-start mb-3 pb-3 border-b border-white/5">
                            <div>
                                <h3 class="text-title-lg font-bold text-on-surface leading-tight">${escHtml(c.nombre)}</h3>
                                <span class="inline-block mt-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-primary/10 text-primary uppercase">${c.categoria}</span>
                            </div>
                            <div class="text-right">
                                <div class="text-title-lg font-bold text-primary">Q ${parseFloat(c.precio_venta).toFixed(2)}</div>
                                <div class="text-xs text-on-surface-variant mt-0.5">${totalMl} ml total / porción</div>
                            </div>
                        </div>

                        <div class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant font-semibold mb-2">
                            Deducción Algorítmica por Porción:
                        </div>

                        <div class="flex flex-col gap-2 mb-4">
                            ${(c.recetas_como_final && c.recetas_como_final.length > 0)
                                ? c.recetas_como_final.map(r => `
                                    <div class="ingredient-row">
                                        <span class="text-on-surface font-semibold">${escHtml(r.nombre_insumo)}</span>
                                        <div class="flex items-center gap-2">
                                            <span class="text-primary font-bold">−${r.cantidad_descontar_ml} ml</span>
                                            ${authState.isAdmin() ? `
                                                <button class="btn btn-subtle-gold btn-edit-ml px-2 py-0.5 text-xs font-semibold" data-receta-id="${r.id_receta}" data-nombre="${escHtml(r.nombre_insumo)}" data-ml="${r.cantidad_descontar_ml}" title="Modificar deducción en ml">✏️ ml</button>
                                                <button class="ingredient-del-btn" data-receta-id="${r.id_receta}" title="Remover ingrediente de la receta">✕</button>
                                            ` : ''}
                                        </div>
                                    </div>
                                `).join("")
                                : `<div class="text-on-surface-variant text-xs italic">Sin ingredientes asignados aún.</div>`
                            }
                        </div>
                    </div>

                    <div class="border-t border-white/5 pt-3.5 flex justify-between items-center gap-2 flex-wrap">
                        <span class="text-xs text-emerald-400 font-semibold flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                            <span>Escandallo Activo</span>
                        </span>
                        <div class="flex gap-2">
                            ${authState.isAdmin() ? `
                                <button class="btn btn-subtle-gold btn-add-insumo-trigger text-xs px-3 py-1.5 font-semibold" data-id="${c.id_producto}" data-nombre="${escHtml(c.nombre)}">
                                    + Insumo
                                </button>
                                <button class="btn btn-gold btn-edit-escandallo text-xs px-3 py-1.5 font-bold" data-id="${c.id_producto}">
                                    Editar Receta
                                </button>
                            ` : `<span class="text-xs text-on-surface-variant font-medium py-1">Solo lectura (Bartender)</span>`}
                        </div>
                    </div>
                </div>
            `;
        }).join("");

        // Listeners de la tabla
        container.querySelectorAll(".btn-edit-ml").forEach(btn => {
            btn.addEventListener("click", (e) => {
                e.stopPropagation();
                document.getElementById("edit-ml-receta-id").value = btn.getAttribute("data-receta-id");
                document.getElementById("edit-ml-subtitle").textContent = `Insumo: ${btn.getAttribute("data-nombre")}`;
                document.getElementById("edit-ml-input").value = btn.getAttribute("data-ml");
                document.getElementById("modal-edit-ml").classList.remove("hidden");
            });
        });

        container.querySelectorAll(".btn-add-insumo-trigger").forEach(btn => {
            btn.addEventListener("click", (e) => {
                e.stopPropagation();
                document.getElementById("add-insumo-coctel-id").value = btn.getAttribute("data-id");
                document.getElementById("add-insumo-subtitle").textContent = `Asignar insumo a: ${btn.getAttribute("data-nombre")}`;
                document.getElementById("modal-input-insumo-ml").value = "";

                const sel = document.getElementById("modal-select-insumo-base");
                sel.innerHTML = `<option value="">— Seleccionar ingrediente base —</option>` +
                    allInsumos.map(i => `<option value="${i.id_producto}">${escHtml(i.nombre)} (${i.stock_ml} ml)</option>`).join("");

                document.getElementById("modal-add-insumo-card").classList.remove("hidden");
            });
        });

        container.querySelectorAll(".ingredient-del-btn").forEach(btn => {
            btn.addEventListener("click", async (e) => {
                e.stopPropagation();
                if (!confirm("¿Deseas remover este ingrediente de la receta?")) return;
                try {
                    await api.deleteReceta(parseInt(btn.getAttribute("data-receta-id"), 10));
                    showToast("✓ Ingrediente removido de la receta.", "success");
                    await loadData();
                } catch (err) {
                    showToast("Error al remover ingrediente: " + err.message, "danger");
                }
            });
        });

        container.querySelectorAll(".btn-edit-escandallo").forEach(btn => {
            btn.addEventListener("click", () => {
                openRecipeModal(parseInt(btn.getAttribute("data-id"), 10));
            });
        });
    }

    // ── Modales ─────────────────────────────────────────────────────────────
    document.getElementById("btn-save-edit-ml")?.addEventListener("click", async () => {
        const idReceta = document.getElementById("edit-ml-receta-id").value;
        const ml = parseInt(document.getElementById("edit-ml-input").value, 10);
        if (!ml || ml <= 0) return alert("Por favor ingresa una cantidad válida de ml (mayor a 0).");
        
        try {
            await api.updateReceta(idReceta, ml);
            document.getElementById("modal-edit-ml").classList.add("hidden");
            showToast(`✓ Deducción actualizada a ${ml} ml.`, "success");
            await loadData();
        } catch (err) {
            showToast("Error al actualizar: " + err.message, "danger");
        }
    });

    document.getElementById("btn-save-add-insumo")?.addEventListener("click", async () => {
        const idProd = document.getElementById("add-insumo-coctel-id").value;
        const idInsumo = document.getElementById("modal-select-insumo-base").value;
        const ml = parseInt(document.getElementById("modal-input-insumo-ml").value, 10);
        
        if (!idInsumo) return alert("Selecciona un ingrediente base.");
        if (!ml || ml <= 0) return alert("Ingresa una cantidad en ml válida (mayor a 0).");
        
        try {
            await api.agregarIngredienteReceta(idProd, idInsumo, ml);
            document.getElementById("modal-add-insumo-card").classList.add("hidden");
            showToast("✓ Insumo agregado a la receta exitosamente.", "success");
            await loadData();
        } catch (err) {
            showToast("Error al agregar insumo: " + err.message, "danger");
        }
    });

    function populateCoctelSelector() {
        const sel = document.getElementById("receta-coctel-sel");
        const cocteles = allProducts.filter(p => p.tipo === "COMPUESTO" || p.tipo === "FINAL");
        sel.innerHTML = `<option value="">— Seleccionar cóctel —</option>` +
            cocteles.map(p => `<option value="${p.id_producto}">${escHtml(p.nombre)} (Q ${parseFloat(p.precio_venta).toFixed(2)})</option>`).join("");
    }

    function openRecipeModal(productId = null) {
        const modal = document.getElementById("receta-modal");
        const coctelSel = document.getElementById("receta-coctel-sel");
        const rowsContainer = document.getElementById("builder-rows-container");

        rowsContainer.innerHTML = "";

        if (productId) {
            coctelSel.value = productId;
            coctelSel.disabled = true;
            const recipe = allRecipes.find(r => r.id_producto === productId);
            if (recipe && recipe.recetas_como_final && recipe.recetas_como_final.length > 0) {
                recipe.recetas_como_final.forEach(ing => addIngredientRow(ing.id_insumo_base, ing.cantidad_descontar_ml));
            } else {
                addIngredientRow();
            }
            document.getElementById("receta-modal-title").innerHTML = `<span class="material-symbols-outlined text-primary">edit</span><span>Editar Receta: ${recipe ? recipe.nombre : ""}</span>`;
        } else {
            coctelSel.value = "";
            coctelSel.disabled = false;
            document.getElementById("receta-modal-title").innerHTML = `<span class="material-symbols-outlined text-primary">local_bar</span><span>Nuevo Escandallo</span>`;
            addIngredientRow();
        }

        updateBuilderTotalMl();
        modal.classList.remove("hidden");
    }

    document.getElementById("receta-coctel-sel")?.addEventListener("change", function () {
        const prodId = parseInt(this.value, 10);
        const rowsContainer = document.getElementById("builder-rows-container");
        rowsContainer.innerHTML = "";

        if (!prodId) {
            addIngredientRow();
            updateBuilderTotalMl();
            return;
        }

        const recipe = allRecipes.find(r => r.id_producto === prodId);
        if (recipe && recipe.recetas_como_final && recipe.recetas_como_final.length > 0) {
            recipe.recetas_como_final.forEach(ing => addIngredientRow(ing.id_insumo_base, ing.cantidad_descontar_ml));
        } else {
            addIngredientRow();
        }
        updateBuilderTotalMl();
    });

    function addIngredientRow(selectedInsumoId = "", mlValue = "") {
        const container = document.getElementById("builder-rows-container");
        const rowId = "row_" + Math.random().toString(36).substring(2, 9);

        const optionsHtml = allInsumos.map(ins =>
            `<option value="${ins.id_producto}" ${ins.id_producto == selectedInsumoId ? "selected" : ""}>
                ${escHtml(ins.nombre)} (${ins.stock_ml} ml disponibles)
            </option>`
        ).join("");

        container.insertAdjacentHTML("beforeend", `
            <div class="builder-row" id="${rowId}">
                <select class="input-field row-insumo-sel" required>
                    <option value="">— Seleccionar insumo base —</option>
                    ${optionsHtml}
                </select>
                <input type="number" class="input-field row-ml-input" placeholder="ml" min="1" max="2000" value="${mlValue}" required>
                <button type="button" class="ingredient-del-btn row-del-btn" title="Eliminar fila">✕</button>
            </div>
        `);

        const rowEl = document.getElementById(rowId);
        rowEl.querySelector(".row-del-btn").addEventListener("click", () => {
            rowEl.remove();
            updateBuilderTotalMl();
        });
        rowEl.querySelector(".row-ml-input").addEventListener("input", updateBuilderTotalMl);
    }

    function updateBuilderTotalMl() {
        const inputs = document.querySelectorAll("#builder-rows-container .row-ml-input");
        let total = 0;
        inputs.forEach(inp => {
            const val = parseInt(inp.value, 10);
            if (!isNaN(val) && val > 0) total += val;
        });
        document.getElementById("builder-total-ml").textContent = `Total: ${total} ml / porción`;
    }

    document.getElementById("btn-builder-add-row")?.addEventListener("click", () => addIngredientRow());

    document.getElementById("btn-guardar-receta")?.addEventListener("click", async () => {
        const coctelId = parseInt(document.getElementById("receta-coctel-sel").value, 10);
        if (!coctelId) return showToast("Por favor selecciona un cóctel final.", "warning");

        const rows = document.querySelectorAll("#builder-rows-container .builder-row");
        const ingredientes = [];

        for (const row of rows) {
            const insumoId = parseInt(row.querySelector(".row-insumo-sel").value, 10);
            const ml = parseInt(row.querySelector(".row-ml-input").value, 10);
            if (insumoId && ml && ml > 0) {
                ingredientes.push({ id_insumo_base: insumoId, cantidad_descontar_ml: ml });
            }
        }

        if (ingredientes.length === 0) return showToast("Debes agregar al menos un insumo con mililitros a descontar.", "warning");

        const btn = document.getElementById("btn-guardar-receta");
        btn.disabled = true;
        btn.textContent = "Guardando…";

        try {
            await api.asignarReceta({ id_producto_final: coctelId, ingredientes: ingredientes });
            showToast("✓ Escandallo guardado y actualizado exitosamente.", "success");
            document.getElementById("receta-modal").classList.add("hidden");
            await loadData();
        } catch (err) {
            showToast("Error al guardar escandallo: " + err.message, "danger");
        } finally {
            btn.disabled = false;
            btn.textContent = "Guardar Escandallo";
        }
    });

    document.querySelectorAll(".modal-close, .modal-cancel").forEach(btn => {
        btn.addEventListener("click", () => btn.closest("[role='dialog']")?.classList.add("hidden"));
    });

    ["receta-modal", "modal-edit-ml", "modal-add-insumo-card"].forEach(id => {
        document.getElementById(id)?.addEventListener("click", (e) => {
            if (e.target === document.getElementById(id)) document.getElementById(id).classList.add("hidden");
        });
    });

    document.getElementById("btn-nueva-receta")?.addEventListener("click", () => openRecipeModal());
    document.getElementById("top-logout-btn")?.addEventListener("click", () => { authState.clear(); window.location.href = "/"; });

    function showToast(message, type = "success") {
        const container = document.getElementById("toast-container");
        const toast = document.createElement("div");
        toast.className = `p-3 rounded-xl shadow-2xl text-sm font-semibold border ${
            type === "danger" ? "bg-red-950/90 text-red-200 border-red-700/50" :
            type === "warning" ? "bg-amber-950/90 text-amber-200 border-amber-700/50" :
            "bg-surface-container-high/90 text-primary border-primary/40"
        }`;
        toast.textContent = message;
        container.appendChild(toast);
        setTimeout(() => toast.remove(), 4000);
    }

    function escHtml(str) {
        return String(str ?? "").replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
    }

    // ── Init ─────────────────────────────────────────────────────────────────
    loadData();
}