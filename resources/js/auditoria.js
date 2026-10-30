import { api, authState } from "./api.js";

if (document.getElementById("pagos-table-body")) {
    if (!authState.isAuthenticated()) window.location.href = "/";
    if (!authState.isAdmin()) {
        alert("Acceso denegado: Esta pantalla requiere rol de Administrador.");
        window.location.href = "/pos";
    }

    let pagosList = [];
    let logsList = [];
    let searchQuery = "";

    function updateKPIs(pagos) {
        const total = pagos.reduce((sum, p) => sum + parseFloat(p.monto_pagado || 0), 0);
        const count = pagos.length;
        const promedio = count > 0 ? total / count : 0;

        const totalEl = document.getElementById("kpi-total-facturado");
        const countEl = document.getElementById("kpi-total-tickets");
        const promEl = document.getElementById("kpi-ticket-promedio");

        if (totalEl) totalEl.textContent = `Q ${total.toLocaleString("es-GT", { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
        if (countEl) countEl.textContent = count;
        if (promEl) promEl.textContent = `Q ${promedio.toLocaleString("es-GT", { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    }

    function getMetodoBadge(metodo) {
        const m = (metodo || "").toUpperCase();
        let icon = "payments", label = metodo;
        if (m.includes("VISA")) { icon = "credit_card"; label = "Tarjeta Visa"; }
        else if (m.includes("MASTERCARD")) { icon = "credit_card"; label = "Mastercard"; }
        else if (m.includes("TRANSFERENCIA")) { icon = "account_balance"; label = "Transferencia"; }
        else if (m === "EFECTIVO") { icon = "payments"; label = "Efectivo"; }
        return `<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-surface-container-lowest text-secondary-fixed text-xs font-semibold border border-white/5 shadow-sm"><span class="material-symbols-outlined text-sm text-primary">${icon}</span><span>${label}</span></span>`;
    }

    async function loadPagos() {
        try {
            pagosList = await api.getPagosAuditoria();
            updateKPIs(pagosList);
            renderPagos();
        } catch (err) { alert("Error al cargar pagos: " + err.message); }
    }

    async function loadLogs() {
        try {
            logsList = await api.getLogsAuditoria();
            renderLogs();
        } catch (err) { alert("Error al cargar logs: " + err.message); }
    }

    function renderPagos() {
        const tbody = document.getElementById("pagos-table-body");
        if (!pagosList || pagosList.length === 0) {
            tbody.innerHTML = `<tr><td colspan="8" class="text-center py-8 text-on-surface-variant">No se encontraron pagos registrados en este turno.</td></tr>`;
            return;
        }
        const q = searchQuery.toLowerCase().trim();
        const filtered = pagosList.filter(p => !q || String(p.id_pago).includes(q) || String(p.id_orden).includes(q) || (p.numero_mesa||"").toLowerCase().includes(q) || (p.nombre_bartender||"").toLowerCase().includes(q) || (p.metodo_pago||"").toLowerCase().includes(q));

        if (filtered.length === 0) {
            tbody.innerHTML = `<tr><td colspan="8" class="text-center py-8 text-on-surface-variant">Sin pagos coincidentes con el filtro de búsqueda.</td></tr>`;
            return;
        }

        tbody.innerHTML = filtered.map(p => {
            const fecha = new Date(p.fecha_transaccion).toLocaleString("es-GT");
            const iniciales = (p.nombre_bartender || 'U').slice(0, 2).toUpperCase();
            return `
                <tr class="hover:bg-surface-container-high/40 transition-colors">
                    <td class="py-3 px-3"><div class="flex items-center gap-1.5 font-mono text-primary font-bold"><span class="w-1.5 h-1.5 rounded-full bg-primary animate-pulse"></span><span>#PAG-${p.id_pago}</span></div></td>
                    <td class="py-3 px-3 font-semibold text-on-surface">#ORD-${p.id_orden}</td>
                    <td class="py-3 px-3">${p.numero_mesa}</td>
                    <td class="py-3 px-3"><div class="flex items-center gap-1.5"><div class="w-6 h-6 rounded-full bg-primary/20 text-primary flex items-center justify-center font-bold text-xs">${iniciales}</div><span class="text-on-surface">${p.nombre_bartender}</span></div></td>
                    <td class="py-3 px-3 text-xs text-on-surface-variant">${fecha}</td>
                    <td class="py-3 px-3">${getMetodoBadge(p.metodo_pago)}</td>
                    <td class="py-3 px-3 font-bold text-primary text-base">Q ${parseFloat(p.monto_pagado).toFixed(2)}</td>
                    <td class="py-3 px-3 text-right">
                        <button class="px-3 py-1.5 rounded-full bg-primary-container text-on-primary hover:bg-primary transition-all flex items-center gap-1 text-xs font-semibold shadow-md btn-corr ml-auto" data-id="${p.id_pago}"><span class="material-symbols-outlined text-xs">edit_note</span><span>Editar Pago</span></button>
                    </td>
                </tr>
            `;
        }).join("");

        tbody.querySelectorAll(".btn-corr").forEach(btn => {
            btn.addEventListener("click", () => {
                const id = parseInt(btn.getAttribute("data-id"));
                const p = pagosList.find(item => item.id_pago === id);
                if (p) openCorrModal(p);
            });
        });
    }

    function renderLogs() {
        const tbody = document.getElementById("logs-table-body");
        if (!logsList || logsList.length === 0) {
            tbody.innerHTML = `<tr><td colspan="5" class="text-center py-8 text-on-surface-variant">No hay registros en la bitácora aún.</td></tr>`;
            return;
        }
        const q = searchQuery.toLowerCase().trim();
        const filtered = logsList.filter(l => !q || String(l.id_log).includes(q) || (l.nombre_usuario||"").toLowerCase().includes(q) || (l.accion||"").toLowerCase().includes(q) || (l.descripcion||"").toLowerCase().includes(q));

        if (filtered.length === 0) {
            tbody.innerHTML = `<tr><td colspan="5" class="text-center py-8 text-on-surface-variant">Sin registros coincidentes con el filtro.</td></tr>`;
            return;
        }

        tbody.innerHTML = filtered.map(l => {
            const fecha = new Date(l.fecha).toLocaleString("es-GT");
            return `<tr class="hover:bg-surface-container-high/40 transition-colors"><td class="py-3 px-3 font-mono text-primary font-semibold">#LOG-${l.id_log}</td><td class="py-3 px-3 text-xs text-on-surface-variant">${fecha}</td><td class="py-3 px-3 font-medium text-on-surface">${l.nombre_usuario}</td><td class="py-3 px-3"><span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-primary/10 text-primary uppercase">${l.accion}</span></td><td class="py-3 px-3 text-on-surface-variant">${l.descripcion}</td></tr>`;
        }).join("");
    }

    const modalCorr = document.getElementById("correction-modal");
    function openCorrModal(p) {
        document.getElementById("corr-pago-id").value = p.id_pago;
        document.getElementById("corr-info").value = `Pago #${p.id_pago} (Orden #${p.id_orden} · ${p.numero_mesa} · Q ${parseFloat(p.monto_pagado).toFixed(2)})`;
        document.getElementById("corr-nuevo-metodo").value = p.metodo_pago;
        document.getElementById("corr-motivo").value = "";
        modalCorr.classList.remove("hidden");
    }

    const cerrarCorrModal = () => modalCorr.classList.add("hidden");
    modalCorr.querySelectorAll(".modal-close, .modal-cancel").forEach(b => b.addEventListener("click", cerrarCorrModal));
    modalCorr.addEventListener("click", (e) => { if (e.target === modalCorr) cerrarCorrModal(); });

    document.getElementById("correction-form").addEventListener("submit", async (e) => {
        e.preventDefault();
        const idPago = parseInt(document.getElementById("corr-pago-id").value);
        const nuevoMetodo = document.getElementById("corr-nuevo-metodo").value;
        const motivo = document.getElementById("corr-motivo").value.trim();
        try {
            await api.cambiarMetodoPago(idPago, nuevoMetodo, motivo);
            cerrarCorrModal();
            alert("✓ Método de pago corregido y registrado en la bitácora inmutable.");
            await loadPagos();
            if (document.getElementById("tab-logs").classList.contains("active")) await loadLogs();
        } catch (err) { alert("Error al corregir: " + err.message); }
    });

    const searchInput = document.getElementById("audit-search-input");
    if (searchInput) {
        searchInput.addEventListener("input", (e) => {
            searchQuery = e.target.value;
            renderPagos();
            renderLogs();
        });
    }

    const btnExport = document.getElementById("btn-export-excel-audit");
    if (btnExport) {
        btnExport.addEventListener("click", async () => {
            const origText = btnExport.innerHTML;
            btnExport.disabled = true;
            btnExport.innerHTML = `<span class="material-symbols-outlined text-title-md animate-spin text-primary">sync</span><span>Exportando...</span>`;
            try { await api.descargarExcel(); } catch (err) { alert("Error exportando Excel: " + err.message); }
            finally { btnExport.disabled = false; btnExport.innerHTML = origText; }
        });
    }

    const tabPagos = document.getElementById("tab-pagos");
    const tabLogs = document.getElementById("tab-logs");
    const viewPagos = document.getElementById("view-pagos");
    const viewLogs = document.getElementById("view-logs");

    if (tabPagos && tabLogs) {
        tabPagos.addEventListener("click", () => {
            tabPagos.classList.add("active"); tabLogs.classList.remove("active");
            viewPagos.style.display = "block"; viewLogs.style.display = "none";
            loadPagos();
        });
        tabLogs.addEventListener("click", () => {
            tabLogs.classList.add("active"); tabPagos.classList.remove("active");
            viewLogs.style.display = "block"; viewPagos.style.display = "none";
            loadLogs();
        });
    }

    const topLogoutBtn = document.getElementById("top-logout-btn");
    if (topLogoutBtn) topLogoutBtn.addEventListener("click", () => { authState.clear(); window.location.href = "/"; });

    loadPagos();
}