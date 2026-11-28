<div id="buriti-painel-modulos-deploy" hidden
     data-buriti-modulos-deploy-painel
     data-url="{{ $urlJson }}"
     style="position:fixed;inset:0;z-index:90;display:none;align-items:flex-end;justify-content:center;padding:12px;background:rgba(15,30,40,.35)">
    <div role="dialog" aria-modal="true" aria-labelledby="buriti-modulos-deploy-titulo"
         style="width:min(760px,100%);max-height:min(78vh,640px);overflow:auto;background:#fff;border-radius:16px;border:1px solid #cfd8df;box-shadow:0 18px 40px rgba(15,30,40,.22);padding:14px 16px 16px">
        <header style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:10px">
            <div>
                <strong id="buriti-modulos-deploy-titulo" style="display:block;font-size:1rem">Módulos no servidor</strong>
                <span id="buriti-modulos-deploy-sub" style="color:#5b6b76;font-size:.8rem">Versão · origem do registro (CI confirmado vs inventário)</span>
            </div>
            <div style="display:flex;gap:6px;align-items:center">
                <button type="button" id="buriti-modulos-deploy-atualizar" title="Atualizar lista"
                        style="border:0;background:#f3f6f8;border-radius:8px;width:32px;height:32px;cursor:pointer;font-size:.95rem;line-height:1">
                    <i class="fa fa-refresh" aria-hidden="true"></i>
                </button>
                <button type="button" id="buriti-modulos-deploy-fechar" aria-label="Fechar"
                        style="border:0;background:#f3f6f8;border-radius:8px;width:32px;height:32px;cursor:pointer;font-size:1.1rem;line-height:1">&times;</button>
            </div>
        </header>
        <div id="buriti-modulos-deploy-legenda" style="display:flex;flex-wrap:wrap;gap:8px;margin:0 0 12px"></div>
        <div id="buriti-modulos-deploy-corpo" style="display:grid;gap:8px">
            <p style="margin:0;color:#5b6b76;font-size:.85rem">Carregando…</p>
        </div>
    </div>
</div>
<script>
(function () {
  var btn = document.getElementById('buriti-btn-modulos-deploy');
  var painel = document.getElementById('buriti-painel-modulos-deploy');
  var corpo = document.getElementById('buriti-modulos-deploy-corpo');
  var legenda = document.getElementById('buriti-modulos-deploy-legenda');
  var sub = document.getElementById('buriti-modulos-deploy-sub');
  var fechar = document.getElementById('buriti-modulos-deploy-fechar');
  var atualizar = document.getElementById('buriti-modulos-deploy-atualizar');
  if (!btn || !painel || !corpo) return;
  var url = painel.getAttribute('data-url') || '';
  var carregado = false;

  var ORIGEM = {
    ci: {
      chave: 'ci',
      rotulo: 'CI',
      titulo: 'Deploy confirmado via GitHub Actions',
      tom: '#17663f',
      fundo: '#e5f6ec',
      borda: '#17663f',
      icone: 'fa-check-circle'
    },
    inventario: {
      chave: 'inventario',
      rotulo: 'Inventário',
      titulo: 'Só inventário no servidor — ainda sem deploy CI',
      tom: '#9a6700',
      fundo: '#fff8e6',
      borda: '#d4a017',
      icone: 'fa-database'
    },
    sem: {
      chave: 'sem',
      rotulo: 'Sem registro',
      titulo: 'Sem data de deploy nem inventário',
      tom: '#9b2c2c',
      fundo: '#fdeceb',
      borda: '#c53030',
      icone: 'fa-exclamation-circle'
    }
  };

  function abrir(v) {
    painel.hidden = !v;
    painel.style.display = v ? 'flex' : 'none';
    btn.setAttribute('aria-expanded', v ? 'true' : 'false');
    if (v && !carregado) carregar();
  }

  function esc(s) {
    return String(s == null ? '' : s)
      .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }

  function origemDe(m) {
    if (m.source === 'github-actions') return ORIGEM.ci;
    if (m.source === 'inventario-servidor') return ORIGEM.inventario;
    if (m.deployed_at_br) return ORIGEM.inventario;
    return ORIGEM.sem;
  }

  function pill(o, extra) {
    return '<span title="' + esc(o.titulo) + '" style="display:inline-flex;align-items:center;gap:5px;font-size:.7rem;font-weight:800;letter-spacing:.02em;border-radius:999px;padding:3px 9px;background:' + o.fundo + ';color:' + o.tom + ';border:1px solid color-mix(in srgb, ' + o.borda + ' 35%, transparent)">' +
      '<i class="fa ' + o.icone + '" aria-hidden="true"></i> ' + esc(o.rotulo) + (extra || '') +
    '</span>';
  }

  function carregar() {
    carregado = false;
    if (legenda) legenda.innerHTML = '';
    corpo.innerHTML = '<p style="margin:0;color:#5b6b76;font-size:.85rem">Carregando…</p>';
    fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
      .then(function (r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
      .then(function (data) {
        carregado = true;
        var mods = Array.isArray(data.modulos) ? data.modulos : [];
        var ativos = data.ativos || mods.filter(function (m) { return m.ativo; }).length;
        var nCi = 0, nInv = 0, nSem = 0;
        mods.forEach(function (m) {
          var o = origemDe(m);
          if (o.chave === 'ci') nCi++;
          else if (o.chave === 'inventario') nInv++;
          else nSem++;
        });
        sub.textContent = ativos + ' ativos · ' + mods.length + ' instalados · i-Educar ' + (data.app_version || '2.12');
        if (legenda) {
          legenda.innerHTML =
            pill(ORIGEM.ci, ' · ' + nCi) +
            pill(ORIGEM.inventario, ' · ' + nInv) +
            pill(ORIGEM.sem, ' · ' + nSem) +
            '<span style="font-size:.72rem;color:#5b6b76;align-self:center">Verde = confirmado no CI · Âmbar = só inventário · Vermelho = sem registro</span>';
        }
        if (!mods.length) {
          corpo.innerHTML = '<p style="margin:0;color:#5b6b76">Nenhum módulo instalado encontrado.</p>';
          return;
        }
        // CI primeiro, depois inventário, depois sem registro; dentro do grupo, nome
        mods = mods.slice().sort(function (a, b) {
          var oa = origemDe(a), ob = origemDe(b);
          var rank = { ci: 0, inventario: 1, sem: 2 };
          var d = (rank[oa.chave] || 0) - (rank[ob.chave] || 0);
          if (d !== 0) return d;
          return String(a.nome || '').localeCompare(String(b.nome || ''), 'pt');
        });
        corpo.innerHTML = mods.map(function (m) {
          var o = origemDe(m);
          var ativoTom = m.ativo ? '#17663f' : '#5b6b76';
          var ativoFundo = m.ativo ? '#e5f6ec' : '#f3f6f8';
          var st = m.ativo ? 'Ativo' : 'Instalado (off)';
          var quando = m.deployed_at_br ? esc(m.deployed_at_br) : '—';
          var sha = m.sha ? ' · <code style="font-size:.72rem">' + esc(m.sha) + '</code>' : '';
          return (
            '<article style="border:1px solid color-mix(in srgb, ' + o.borda + ' 28%, #cfd8df);border-left:4px solid ' + o.borda + ';border-radius:12px;padding:10px 12px;display:grid;gap:6px;background:linear-gradient(90deg, ' + o.fundo + ' 0%, #fff 42%)">' +
              '<div style="display:flex;justify-content:space-between;gap:8px;align-items:center;flex-wrap:wrap">' +
                '<strong style="font-size:.92rem"><i class="fa ' + esc(m.icone || 'fa-cube') + '" style="color:' + esc(m.cor || '#0f4c5c') + '"></i> ' + esc(m.nome) + '</strong>' +
                '<span style="display:inline-flex;flex-wrap:wrap;gap:6px;align-items:center">' +
                  pill(o, '') +
                  '<span style="font-size:.7rem;font-weight:700;border-radius:999px;padding:3px 8px;background:' + ativoFundo + ';color:' + ativoTom + '">' + esc(st) + '</span>' +
                '</span>' +
              '</div>' +
              '<div style="font-size:.8rem;color:#1a242b"><code style="font-size:.78rem">v' + esc(m.versao || '—') + '</code>' +
                (m.versao_composer && m.versao_composer !== m.versao ? ' <span style="color:#5b6b76">(composer ' + esc(m.versao_composer) + ')</span>' : '') +
              '</div>' +
              '<div style="font-size:.75rem;color:' + o.tom + ';font-weight:600">' +
                '<i class="fa fa-cloud-upload" aria-hidden="true"></i> ' +
                esc(o.rotulo) + ' · ' + quando + sha +
              '</div>' +
            '</article>'
          );
        }).join('');
      })
      .catch(function (e) {
        carregado = false;
        if (legenda) legenda.innerHTML = '';
        corpo.innerHTML = '<p style="margin:0;color:#9b2c2c;font-size:.85rem">Falha ao carregar: ' + esc(e.message || e) + '</p>';
      });
  }

  btn.addEventListener('click', function () {
    var fechado = painel.hidden || painel.style.display === 'none';
    abrir(fechado);
  });
  if (fechar) fechar.addEventListener('click', function () { abrir(false); });
  if (atualizar) atualizar.addEventListener('click', function () { carregar(); });
  painel.addEventListener('click', function (ev) { if (ev.target === painel) abrir(false); });
  document.addEventListener('keydown', function (ev) { if (ev.key === 'Escape') abrir(false); });
})();
</script>
