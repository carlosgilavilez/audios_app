#!/usr/bin/env node
// Simple, cross‑platform git push helper to trigger CI deploys.
// Usage:
//   npm run deploy -- "mensaje de commit opcional"

import { spawnSync } from 'node:child_process';

function run(cmd, args = [], opts = {}) {
  const res = spawnSync(cmd, args, { stdio: 'inherit', shell: false, ...opts });
  if (res.status !== 0) {
    const pretty = `${cmd} ${args.join(' ')}`.trim();
    throw new Error(`Comando falló: ${pretty}`);
  }
}

function runCapture(cmd, args = [], opts = {}) {
  const res = spawnSync(cmd, args, { encoding: 'utf8', shell: false, ...opts });
  if (res.status !== 0) {
    const pretty = `${cmd} ${args.join(' ')}`.trim();
    throw new Error(`Comando falló: ${pretty}`);
  }
  return res.stdout.trim();
}

(async () => {
  try {
    // Verificación rápida de git
    run('git', ['--version']);

    // Rama actual
    const branch = runCapture('git', ['rev-parse', '--abbrev-ref', 'HEAD']);
    const isMain = branch === 'main';

    // Asegura remoto y upstream
    let upstream = '';
    try {
      upstream = runCapture('git', ['rev-parse', '--abbrev-ref', '--symbolic-full-name', '@{u}']);
    } catch {
      // Sin upstream configurado; lo configuraremos al hacer push
    }

    // Sincroniza antes de commitear para reducir rechazos
    run('git', ['fetch', '--all', '--prune']);
    if (upstream) {
      // Rebase contra upstream si existe
      run('git', ['pull', '--rebase']);
    }

    // Preparar commit
    run('git', ['add', '-A']);
    const changes = runCapture('git', ['status', '--porcelain']);

    if (changes) {
      // Mensaje de commit
      const msgFromArg = process.argv.slice(2).join(' ').trim();
      const iso = new Date().toISOString().replace('T', ' ').slice(0, 19);
      const message = msgFromArg || `chore: sync local -> origin (${iso})`;
      run('git', ['commit', '-m', message]);
    } else {
      console.log('No hay cambios para commitear. Intentaré hacer push por si hay commits pendientes.');
    }

    // Push (configura upstream si falta)
    if (upstream) {
      run('git', ['push']);
    } else {
      run('git', ['push', '-u', 'origin', branch]);
    }

    if (!isMain) {
      console.warn(`
⚠️ Estás en la rama "${branch}". El deploy a producción se dispara en GitHub Actions solo con pushes a "main".
   - Sube un PR a main o cambia a main y vuelve a ejecutar este comando.
`);
    } else {
      console.log('\n✅ Push a main completado. GitHub Actions iniciará el despliegue a producción.');
      console.log('   Revisa la pestaña Actions del repo para ver el progreso.');
    }
  } catch (err) {
    console.error(`\n❌ Error: ${err.message}`);
    process.exit(1);
  }
})();

