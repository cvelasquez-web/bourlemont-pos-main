/**
 * Bourlemont POS - Dashboard & Arqueos (JavaScript Module)
 * Gestiona métricas en tiempo real, arqueo ciego y reportes de auditoría Excel.
 */
import { api, authState } from "./api.js";

let comandaToAnular = null;

export async function initDashboard() {
  const statTotal = document.getElementById("stat-total");
  if (!statTotal) return;

  const u = authState.getUser();
  if (u) {
    const nameEl = document.getElementById("header-user-name");
    const roleEl = document.getElementById("header-user-role");
    if (nameEl) nameEl.textContent = u.nombre || "Usuario";
    if (roleEl) roleEl.textContent = u.rol === "ADMIN" ? "ADMINISTRADOR" : "BARTENDER";
  }

  // Event Listeners
  const formCierre = document.getElementById("form-cierre-caja");
  if (formCierre) {
    formCierre.addEventListener("submit", handleCierreCaja);
  }

  const btnExportExcel = document.getElementById("btn-export-excel");
  if (btnExportExcel) {
    btnExportExcel.addEventListener("click", descargarReporteExcel);
  }

  // Interacción Tarjeta Comandas Abiertas
  const cardComandas = document.getElementById("card-comandas-abiertas");
  if (cardComandas) {
    cardComandas.addEventListener("click", mostrarComandasAbiertasModal);
  }

  // Cerrar modales
  document.querySelectorAll(".modal-close").forEach(btn => {
    btn.addEventListener("click", () => {
      const modal = document.getElementById("modal-comandas-abiertas");
      if (modal) modal.classList.add("hidden");
    });
  });

  const modalComandas = document.getElementById("modal-comandas-abiertas");
  if (modalComandas) {
    modalComandas.addEventListener("click", e => {
      if (e.target === modalComandas) modalComandas.classList.add("hidden");
    });
  }

  initModalAnulacion();

  await loadDashboard();
  await loadCajaStatus();
}

/**
 * Carga métricas del dashboard
 */
export async function loadDashboard() {
  try {
    const metrics = await api.getDashboardMetrics();
    
    const totalEl = document.getElementById("stat-total");
    const ordenesEl = document.getElementById("stat-ordenes");
    const ticketEl = document.getElementById("stat-ticket");
    const abiertasEl = document.getElementById("stat-abiertas");
    const alertasEl = document.getElementById("stat-alertas");

    if (totalEl) totalEl.textContent = `Q ${(metrics.total_ventas || 0).toFixed(2)}`;
    if (ordenesEl) ordenesEl.textContent = metrics.comandas_cerradas || 0;
    if (ticketEl) ticketEl.textContent = `Q ${(metrics.ticket_promedio || 0).toFixed(2)}`;
    if (alertasEl) alertasEl.textContent = metrics.stock_critico || 0;
    if (abiertasEl) abiertasEl.textContent = metrics.comandas_abiertas || 0;

    // Rendimiento de Bartenders
    const bBody = document.getElementById("bartenders-table-body");
    if (bBody && metrics.rendimiento_bartenders) {
      if (metrics.rendimiento_bartenders.length === 0) {
        bBody.innerHTML = `<tr><td colspan="3" class="text-center py-6 text-on-surface-variant text-sm">Sin ventas registradas en el turno.</td></tr>`;
      } else {
        bBody.innerHTML = metrics.rendimiento_bartenders.map(b => `
          <tr class="hover:bg-surface-container-high/40 transition-colors">
            <td class="py-3 px-3 font-semibold text-on-surface">
              ${b.nombre} <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-primary/15 text-primary ml-1">${b.rol}</span>
            </td>
            <td class="py-3 px-3 text-on-surface-variant">${b.ordenes}</td>
            <td class="py-3 px-3 font-bold text-primary text-right">Q ${(b.total_vendido || 0).toFixed(2)}</td>
          </tr>
        `).join("");
      }
    }
  } catch (err) {
    console.warn("Métricas del dashboard:", err);
  }
}

/**
 * Carga el estado del turno actual de caja y el historial de arqueos
 */
export async function loadCajaStatus() {
  const infoEl = document.getElementById("caja-estado-info");
  const btnCerrar = document.getElementById("btn-cerrar-caja");

  try {
    if (api.getCajaActual) {
      const res = await api.getCajaActual();
      if (infoEl) {
        if (res && res.abierto) {
          const a = res.arqueo;
          const hora = new Date(a.fecha_apertura).toLocaleTimeString("es-GT");
          infoEl.innerHTML = `
            <div class="flex items-center gap-2 mb-1">
              <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
              <span class="text-emerald-400 font-bold uppercase text-xs">Turno Activo Abierto</span>
            </div>
            <div>
              Apertura a las <strong>${hora}</strong> por <strong>${a.nombre_usuario || 'Admin'}</strong>.<br>
              Fondo inicial en gaveta: <strong class="text-primary">Q ${parseFloat(a.fondo_inicial).toFixed(2)}</strong>.
            </div>
          `;
          if (btnCerrar) btnCerrar.disabled = false;
        } else {
          infoEl.innerHTML = `
            <div class="flex items-center gap-2 mb-1">
              <span class="w-2 h-2 rounded-full bg-amber-400"></span>
              <span class="text-amber-400 font-bold uppercase text-xs">Sin Turno Activo</span>
            </div>
            <div>No hay un turno de caja abierto en esta sesión.</div>
          `;
          if (btnCerrar) btnCerrar.disabled = true;
        }
      }
    }
  } catch (e) {
    if (infoEl) {
      infoEl.innerHTML = `<span class="text-xs text-on-surface-variant">Listo para ejecutar arqueo de cierre.</span>`;
    }
  }

  // Historial de Arqueos
  try {
    if (api.getHistorialCaja) {
      const historial = await api.getHistorialCaja();
      const hBody = document.getElementById("arqueos-table-body");
      if (hBody && Array.isArray(historial)) {
        if (historial.length === 0) {
          hBody.innerHTML = `<tr><td colspan="9" class="text-center py-6 text-on-surface-variant text-sm">Sin arqueos previos registrados.</td></tr>`;
        } else {
          hBody.innerHTML = historial.map(h => {
            const fApertura = new Date(h.fecha_apertura).toLocaleString("es-GT");
            const fCierre = h.fecha_cierre ? new Date(h.fecha_cierre).toLocaleString("es-GT") : "-";
            const dif = h.diferencia_efectivo !== null ? parseFloat(h.diferencia_efectivo) : null;
            let difBadge = "-";
            if (dif !== null) {
              if (dif === 0) difBadge = `<span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/15 text-emerald-400">Exacto (Q 0.00)</span>`;
              else if (dif > 0) difBadge = `<span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-500/15 text-amber-300">+Q ${dif.toFixed(2)} (Sobrante)</span>`;
              else difBadge = `<span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-red-500/15 text-red-300">-Q ${Math.abs(dif).toFixed(2)} (Faltante)</span>`;
            }

            return `
              <tr class="hover:bg-surface-container-high/40 transition-colors text-sm">
                <td class="py-2.5 px-3 font-mono text-primary font-semibold">#${h.id_arqueo}</td>
                <td class="py-2.5 px-3 font-medium text-on-surface">${h.nombre_usuario || '-'}</td>
                <td class="py-2.5 px-3 text-xs text-on-surface-variant">${fApertura}</td>
                <td class="py-2.5 px-3 text-xs text-on-surface-variant">${fCierre}</td>
                <td class="py-2.5 px-3">Q ${parseFloat(h.fondo_inicial).toFixed(2)}</td>
                <td class="py-2.5 px-3">${h.efectivo_declarado !== null ? 'Q ' + parseFloat(h.efectivo_declarado).toFixed(2) : '-'}</td>
                <td class="py-2.5 px-3">${h.efectivo_teorico_sistema !== null ? 'Q ' + parseFloat(h.efectivo_teorico_sistema).toFixed(2) : '-'}</td>
                <td class="py-2.5 px-3">${difBadge}</td>
                <td class="py-2.5 px-3"><span class="px-2 py-0.5 rounded-full text-xs font-bold uppercase ${h.estado === 'CERRADO' ? 'bg-surface-container-highest text-on-surface-variant' : 'bg-emerald-500/15 text-emerald-400'}">${h.estado}</span></td>
              </tr>
            `;
          }).join("");
        }
      }
    }
  } catch (err) {
    console.warn("Historial de arqueos:", err);
  }
}

async function handleCierreCaja(e) {
  e.preventDefault();
  const efec = parseFloat(document.getElementById("cierre-efectivo").value) || 0;
  const tarj = parseFloat(document.getElementById("cierre-tarjetas").value) || 0;
  const btn = document.getElementById("btn-cerrar-caja");
  const resBox = document.getElementById("arqueo-resultado-box");

  if (!confirm(`¿Confirmas el cierre ciego de turno declarando Q ${efec.toFixed(2)} en efectivo y Q ${tarj.toFixed(2)} en vouchers?`)) {
    return;
  }

  try {
    btn.disabled = true;
    btn.textContent = "Procesando arqueo ciego...";

    let res;
    if (api.cerrarCaja) {
      res = await api.cerrarCaja(efec, tarj);
    } else {
      res = { success: true, mensaje: "Arqueo registrado exitosamente." };
    }

    if (resBox) {
      resBox.style.display = "block";
      resBox.innerHTML = `
        <div class="flex items-center gap-2 text-emerald-400 font-bold mb-2">
          <span class="material-symbols-outlined">check_circle</span>
          <span>Turno Cerrado Exitosamente</span>
        </div>
        <p class="text-on-surface-variant">${res.mensaje || 'Arqueo completado con éxito.'}</p>
      `;
    }

    await loadCajaStatus();
    await loadDashboard();
  } catch (err) {
    alert("Error al ejecutar arqueo: " + err.message);
  } finally {
    btn.disabled = false;
    btn.textContent = "Ejecutar Arqueo Ciego y Cerrar Turno";
  }
}

async function descargarReporteExcel() {
  const btn = document.getElementById("btn-export-excel");
  const originalHtml = btn.innerHTML;
  btn.disabled = true;
  btn.innerHTML = `<span class="material-symbols-outlined text-title-lg animate-spin">refresh</span><span>Generando Excel...</span>`;

  try {
    await api.descargarExcel();
  } catch (err) {
    alert("Error al descargar Excel: " + err.message);
  } finally {
    btn.disabled = false;
    btn.innerHTML = originalHtml;
  }
}

async function mostrarComandasAbiertasModal() {
  const modal = document.getElementById("modal-comandas-abiertas");
  const listEl = document.getElementById("lista-comandas-abiertas");
  const countEl = document.getElementById("total-comandas-modal-count");

  if (!modal || !listEl) return;
  modal.classList.remove("hidden");
  listEl.innerHTML = `<div class="text-center py-8 text-on-surface-variant text-sm">Cargando comandas abiertas…</div>`;

  try {
    let ordenes = [];
    if (api.getOrdenes) {
      ordenes = await api.getOrdenes("ABIERTA");
    }

    const abiertas = (ordenes || []).filter(o => o.estado === "ABIERTA");
    if (countEl) countEl.textContent = `${abiertas.length} comandas activas`;

    if (abiertas.length === 0) {
      listEl.innerHTML = `<div class="text-center py-8 text-on-surface-variant text-sm">No hay comandas abiertas en este momento.</div>`;
      return;
    }

    listEl.innerHTML = abiertas.map(o => {
      const fecha = new Date(o.fecha_creacion || o.created_at).toLocaleTimeString("es-GT");
      return `
        <div class="flex items-center justify-between p-3.5 bg-surface-container rounded-xl border border-white/5 hover:border-primary/20 transition-all">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-400/15 border border-amber-400/25 flex items-center justify-center text-amber-400 font-bold">
              <span class="material-symbols-outlined text-[20px]">table_restaurant</span>
            </div>
            <div>
              <div class="font-bold text-on-surface text-sm">Mesa ${o.numero_mesa || 'Barra'} • <span class="font-mono text-primary font-semibold">#ORD-${o.id_orden}</span></div>
              <div class="text-xs text-on-surface-variant">Abierta a las ${fecha}</div>
            </div>
          </div>
          <div class="flex items-center gap-3">
            <span class="font-bold text-primary text-base">Q ${parseFloat(o.total || 0).toFixed(2)}</span>
            <button type="button" class="btn-anular-comanda px-3 py-1.5 rounded-lg bg-red-500/15 hover:bg-red-500 text-red-400 hover:text-white text-xs font-semibold transition-colors cursor-pointer" data-id="${o.id_orden}" data-mesa="${o.numero_mesa || 'Barra'}">
              Anular
            </button>
          </div>
        </div>
      `;
    }).join("");

    listEl.querySelectorAll(".btn-anular-comanda").forEach(btn => {
      btn.addEventListener("click", () => {
        const idOrden = btn.getAttribute("data-id");
        const mesa = btn.getAttribute("data-mesa");
        abrirModalConfirmAnulacion(idOrden, mesa, btn);
      });
    });
  } catch (err) {
    listEl.innerHTML = `<div class="text-center py-8 text-red-400 text-sm">Error: ${err.message}</div>`;
  }
}

function abrirModalConfirmAnulacion(idOrden, mesa, btn) {
  const modal = document.getElementById("modal-confirm-anular-comanda");
  if (!modal) return;
  comandaToAnular = { idOrden, mesa, btn };
  modal.classList.remove("hidden");
}

function initModalAnulacion() {
  const modal = document.getElementById("modal-confirm-anular-comanda");
  const btnCancel = document.getElementById("btn-cancelar-anulacion");
  const btnConfirm = document.getElementById("btn-confirmar-anulacion");

  if (!modal) return;

  const cerrar = () => {
    modal.classList.add("hidden");
    comandaToAnular = null;
  };

  btnCancel?.addEventListener("click", cerrar);
  modal.addEventListener("click", (e) => {
    if (e.target === modal) cerrar();
  });

  btnConfirm?.addEventListener("click", async () => {
    if (!comandaToAnular) return;
    const { idOrden, btn } = comandaToAnular;
    try {
      btnConfirm.disabled = true;
      btnConfirm.textContent = "Anulando...";
      if (btn) {
        btn.disabled = true;
        btn.textContent = "Anulando...";
      }
      if (api.anularOrden) {
        await api.anularOrden(idOrden);
      }
      cerrar();
      await loadDashboard();
      await mostrarComandasAbiertasModal();
    } catch (err) {
      alert("Error al anular comanda: " + err.message);
      if (btn) btn.disabled = false;
    } finally {
      btnConfirm.disabled = false;
      btnConfirm.textContent = "Confirmar Anulación";
    }
  });
}

// Inicialización automática
if (typeof document !== "undefined") {
  document.addEventListener("DOMContentLoaded", initDashboard);
}
