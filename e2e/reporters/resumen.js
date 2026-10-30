import fs from 'node:fs';

const sinColor = (s) => s.replace(/\u001b\[[0-9;]*m/g, '');

export default class ResumenReporter {
  constructor() {
    this.resultados = [];
  }

  onTestEnd(test, result) {
    this.resultados.push({
      titulo: test.titlePath().slice(2).join(' › '),
      estado: result.status,
      ms: result.duration,
      reintento: result.retry,
      error: sinColor(result.error?.message ?? '').split('\n')[0],
    });
  }

  onEnd(resultado) {
    const total = this.resultados.length;
    const ok = this.resultados.filter((r) => r.estado === 'passed');
    const omitidos = this.resultados.filter((r) => r.estado === 'skipped');
    const fallidos = this.resultados.filter((r) => !['passed', 'skipped'].includes(r.estado));
    const lentos = [...this.resultados].sort((a, b) => b.ms - a.ms).slice(0, 5);
    const seg = (ms) => (ms / 1000).toFixed(1) + 's';

    const lineas = [];
    lineas.push('');
    lineas.push('========== RESUMEN DE LA CORRIDA ==========');
    lineas.push(`Estado general: ${resultado.status}`);
    lineas.push(`Total: ${total} | Pasaron: ${ok.length} | Fallaron: ${fallidos.length} | Omitidos: ${omitidos.length}`);
    lineas.push(`Duración: ${seg(resultado.duration)}`);

    if (fallidos.length) {
      lineas.push('');
      lineas.push('--- Fallidos ---');
      for (const f of fallidos) {
        lineas.push(`✘ ${f.titulo} (${seg(f.ms)})`);
        if (f.error) lineas.push(`    ${f.error}`);
      }
    }

    lineas.push('');
    lineas.push('--- 5 tests más lentos ---');
    for (const l of lentos) lineas.push(`${seg(l.ms).padStart(7)}  ${l.titulo}`);

    const reintentados = this.resultados.filter((r) => r.reintento > 0);
    if (reintentados.length) {
      lineas.push('');
      lineas.push('--- Tests que necesitaron reintento (posible inestabilidad) ---');
      for (const r of reintentados) lineas.push(`  ${r.titulo}`);
    }
    lineas.push('===========================================');

    const texto = lineas.join('\n');
    console.log(texto);

    fs.mkdirSync('reportes', { recursive: true });
    fs.writeFileSync('reportes/resumen.txt', texto);
  }
}