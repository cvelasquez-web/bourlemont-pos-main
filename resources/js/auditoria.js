/**
 * Bourlemont POS - Auditoría de Pagos & Bitácora (JavaScript Module)
 */
import { api, authState } from "./api.js";

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
  let icon = "payments";
  let label = metodo;
  if (m.includes("VISA")) {
    icon = "credit_card";
    label = "Tarjeta Visa";
  } else if (m.includes("MASTERCARD") || m.includes("TARJETA")) {
    icon = "credit_card";
    label = "Tarjeta (POS)";
  } else if (m.includes("TRANSFERENCIA")) {
    icon = "account_balance";
    label = "Transferencia";
  } else if (m === "EFECTIVO") {
    icon = "payments";
    label = "Efectivo";
  }
  return `
    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-surface-container-lowest text-secondary-fixed text-xs font-semibold border border-white/5 shadow-sm">
      <span class="material-symbols-outlined text-sm text-primary">${icon}</span>
      <span>${label}</span>
    </span>
  `;
}

function renderPagos() {
  const tbody = document.getElementById("pagos-table-body");
  if (!tbody) return;

  if (!pagosList || pagosList.length === 0) {
    tbody.innerHTML = `<tr><td colspan="8" class="text-center py-8 text-on-surface-variant">No se encontraron pagos registrados en este turno.</td></tr>`;
    return;
  }

  const q = searchQuery.toLowerCase().trim();
  const filtered = pagosList.filter(p => {
    if (!q) return true;
    const idPago = String(p.id_pago);
    const idOrden = String(p.id_orden);
    const mesa = (p.numero_mesa || "").toLowerCase();
    const user = (p.nombre_bartender || "").toLowerCase();
    const met = (p.metodo_pago || "").toLowerCase();
    return idPago.includes(q) || idOrden.includes(q) || mesa.includes(q) || user.includes(q) || met.includes(q);
  });

  if (filtered.length === 0) {
    tbody.innerHTML = `<tr><td colspan="8" class="text-center py-8 text-on-surface-variant">Sin pagos coincidentes con el filtro de búsqueda.</td></tr>`;
    return;
  }

  tbody.innerHTML = filtered.map(p => {
    const fecha = new Date(p.fecha_transaccion).toLocaleString("es-GT");
    const iniciales = (p.nombre_bartender || 'U').slice(0, 2).toUpperCase();
    return `
      <tr class="hover:bg-surface-container-high/40 transition-colors">
        <td class="py-3 px-3">
          <div class="flex items-center gap-1.5 font-mono text-primary font-bold">
            <span class="w-1.5 h-1.5 rounded-full bg-primary animate-pulse"></span>
            <span>#PAG-${p.id_pago}</span>
          </div>
        </td>
        <td class="py-3 px-3 font-semibold text-on-surface">#ORD-${p.id_orden}</td>
        <td class="py-3 px-3">${p.numero_mesa}</td>
        <td class="py-3 px-3">
          <div class="flex items-center gap-1.5">
            <div class="w-6 h-6 rounded-full bg-primary/20 text-primary flex items-center justify-center font-bold text-xs">${iniciales}</div>
            <span class="text-on-surface">${p.nombre_bartender}</span>
          </div>
        </td>
        <td class="py-3 px-3 text-xs text-on-surface-variant">${fecha}</td>
        <td class="py-3 px-3">${getMetodoBadge(p.metodo_pago)}</td>
        <td class="py-3 px-3 font-bold text-primary text-base">Q ${parseFloat(p.monto_pagado).toFixed(2)}</td>
        <td class="py-3 px-3 text-right">
          <button class="px-3 py-1.5 rounded-full bg-primary-container text-on-primary hover:bg-primary transition-all flex items-center gap-1 text-xs font-semibold shadow-md btn-corr ml-auto cursor-pointer" data-id="${p.id_pago}">
            <span class="material-symbols-outlined text-xs">edit_note</span>
            <span>Editar Pago</span>
          </button>
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
  if (!tbody) return;

  if (!logsList || logsList.length === 0) {
    tbody.innerHTML = `<tr><td colspan="5" class="text-center py-8 text-on-surface-variant">No hay registros en la bitácora aún.</td></tr>`;
    return;
  }

  const q = searchQuery.toLowerCase().trim();
  const filtered = logsList.filter(l => {
    if (!q) return true;
    const idLog = String(l.id_log);
    const user = (l.nombre_usuario || "").toLowerCase();
    const accion = (l.accion || "").toLowerCase();
    const desc = (l.descripcion || "").toLowerCase();
    return idLog.includes(q) || user.includes(q) || accion.includes(q) || desc.includes(q);
  });

  if (filtered.length === 0) {
    tbody.innerHTML = `<tr><td colspan="5" class="text-center py-8 text-on-surface-variant">Sin registros coincidentes con el filtro.</td></tr>`;
    return;
  }

  tbody.innerHTML = filtered.map(l => {
    const fecha = new Date(l.fecha).toLocaleString("es-GT");
    return `
      <tr class="hover:bg-surface-container-high/40 transition-colors">
        <td class="py-3 px-3 font-mono text-primary font-semibold">#LOG-${l.id_log}</td>
        <td class="py-3 px-3 text-xs text-on-surface-variant">${fecha}</td>
        <td class="py-3 px-3 font-medium text-on-surface">${l.nombre_usuario}</td>
        <td class="py-3 px-3"><span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-primary/10 text-primary uppercase">${l.accion}</span></td>
        <td class="py-3 px-3 text-sm text-on-surface-variant">${l.descripcion}</td>
      </tr>
    `;
  }).join("");
}

function openCorrModal(pago) {
  const modal = document.getElementById("modal-corregir-pago");
  if (!modal) return;

  document.getElementById("corr-id-pago").value = pago.id_pago;
  document.getElementById("corr-info-pago").textContent = `#PAG-${pago.id_pago} (Orden #${pago.id_orden} - ${pago.numero_mesa})`;
  document.getElementById("corr-info-monto").textContent = `Q ${parseFloat(pago.monto_pagado).toFixed(2)}`;
  document.getElementById("corr-info-actual").textContent = pago.metodo_pago;
  document.getElementById("corr-nuevo-metodo").value = pago.metodo_pago;
  document.getElementById("corr-motivo").value = "";

  modal.classList.remove("hidden");
}

export async function initAuditoria() {
  const tbodyPagos = document.getElementById("pagos-table-body");
  if (!tbodyPagos) return;

  const u = authState.getUser();
  if (u) {
    const elName = document.getElementById("header-user-name");
    const elRole = document.getElementById("header-user-role");
    if (elName) elName.textContent = u.nombre || "Administrador";
    if (elRole) elRole.textContent = u.rol === 'ADMIN' ? 'ADMINISTRADOR' : 'BARTENDER';
  }

  // Pestañas
  const tabPagos = document.getElementById("tab-pagos");
  const tabLogs = document.getElementById("tab-logs");
  const viewPagos = document.getElementById("view-pagos");
  const viewLogs = document.getElementById("view-logs");

  tabPagos?.addEventListener("click", () => {
    tabPagos.classList.add("active");
    tabLogs?.classList.remove("active");
    if (viewPagos) viewPagos.style.display = "block";
    if (viewLogs) viewLogs.style.display = "none";
    loadPagos();
  });

  tabLogs?.addEventListener("click", () => {
    tabLogs.classList.add("active");
    tabPagos?.classList.remove("active");
    if (viewLogs) viewLogs.style.display = "block";
    if (viewPagos) viewPagos.style.display = "none";
    loadLogs();
  });

  // Búsqueda reactiva
  const searchInput = document.getElementById("audit-search-input");
  searchInput?.addEventListener("input", (e) => {
    searchQuery = e.target.value;
    if (viewPagos && viewPagos.style.display !== "none") {
      renderPagos();
    } else {
      renderLogs();
    }
  });

  // Exportar Excel
  const btnExport = document.getElementById("btn-export-excel-audit");
  btnExport?.addEventListener("click", async () => {
    const origText = btnExport.innerHTML;
    btnExport.disabled = true;
    btnExport.innerHTML = `<span class="material-symbols-outlined text-sm animate-spin text-on-primary">sync</span><span>Exportando...</span>`;
    try {
      await api.descargarExcel();
    } catch (err) {
      alert("Error exportando Excel: " + err.message);
    } finally {
      btnExport.disabled = false;
      btnExport.innerHTML = origText;
    }
  });

  // Modal corrección
  const modalCorr = document.getElementById("modal-corregir-pago");
  const btnCancelarCorr = document.getElementById("btn-cancelar-corr");
  const btnCerrarModalCorr = document.getElementById("btn-cerrar-modal-corr");
  const formCorr = document.getElementById("form-corregir-pago");

  btnCancelarCorr?.addEventListener("click", () => modalCorr?.classList.add("hidden"));
  btnCerrarModalCorr?.addEventListener("click", () => modalCorr?.classList.add("hidden"));
  modalCorr?.addEventListener("click", (e) => {
    if (e.target === modalCorr) modalCorr.classList.add("hidden");
  });

  formCorr?.addEventListener("submit", async (e) => {
    e.preventDefault();
    const idPago = parseInt(document.getElementById("corr-id-pago").value);
    const nuevoMetodo = document.getElementById("corr-nuevo-metodo").value;
    const motivo = document.getElementById("corr-motivo").value.trim();

    try {
      await api.cambiarMetodoPago(idPago, nuevoMetodo, motivo);
      modalCorr?.classList.add("hidden");
      alert("Método de pago corregido y sellado en bitácora.");
      await loadPagos();
    } catch (err) {
      alert("Error: " + err.message);
    }
  });

  async function loadPagos() {
    try {
      pagosList = await api.getPagosAuditoria();
      updateKPIs(pagosList);
      renderPagos();
    } catch (err) {
      console.warn("Error al cargar pagos:", err);
    }
  }

  async function loadLogs() {
    try {
      logsList = await api.getLogsAuditoria();
      renderLogs();
    } catch (err) {
      console.warn("Error al cargar logs:", err);
    }
  }

  await loadPagos();
}

// Inicialización automática
if (typeof document !== "undefined") {
  document.addEventListener("DOMContentLoaded", initAuditoria);
}
