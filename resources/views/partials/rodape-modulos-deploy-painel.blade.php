<div id="buriti-painel-modulos-deploy" hidden
     data-buriti-modulos-deploy-painel
     data-url="{{ $urlJson }}"
     style="position:fixed;inset:0;z-index:80;display:none;align-items:flex-end;justify-content:center;padding:12px;background:rgba(15,30,40,.35)">
    <div role="dialog" aria-modal="true" aria-labelledby="buriti-modulos-deploy-titulo"
         style="width:min(720px,100%);max-height:min(78vh,640px);overflow:auto;background:#fff;border-radius:16px;border:1px solid #cfd8df;box-shadow:0 18px 40px rgba(15,30,40,.22);padding:14px 16px 16px">
        <header style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:10px">
            <div>
                <strong id="buriti-modulos-deploy-titulo" style="display:block;font-size:1rem">Módulos no servidor</strong>
                <span id="buriti-modulos-deploy-sub" style="color:#5b6b76;font-size:.8rem">Versão do pacote · data do último deploy CI</span>
            </div>
            <button type="button" id="buriti-modulos-deploy-fechar" aria-label="Fechar"
                    style="border:0;background:#f3f6f8;border-radius:8px;width:32px;height:32px;cursor:pointer;font-size:1.1rem;line-height:1">&times;</button>
        </header>
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
  var sub = document.getElementById('buriti-modulos-deploy-sub');
  var fechar = document.getElementById('buriti-modulos-deploy-fechar');
  if (!btn || !painel || !corpo) return;
  var url = painel.getAttribute('data-url') || '';
  var carregado = false;

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

  function carregar() {
    corpo.innerHTML = '<p style="margin:0;color:#5b6b76;font-size:.85rem">Carregando…</p>';
    fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
      .then(function (r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
      .then(function (data) {
        carregado = true;
        var mods = Array.isArray(data.modulos) ? data.modulos : [];
        sub.textContent = (data.ativos || 0) + ' ativos · i-Educar ' + (data.app_version || '2.12');
        if (!mods.length) {
          corpo.innerHTML = '<p style="margin:0;color:#5b6b76">Nenhum módulo encontrado.</p>';
          return;
        }
        corpo.innerHTML = mods.map(function (m) {
          var tom = m.ativo ? '#17663f' : '#b86e00';
          var fundo = m.ativo ? '#e5f6ec' : '#fff4e0';
          var st = m.ativo ? 'Ativo' : (m.instalado ? 'Instalado (off)' : 'Ausente');
          var deploy = m.deployed_at_br || 'Deploy ainda não registrado pelo CI';
          var sha = m.sha ? ' · ' + m.sha : '';
          return (
            '<article style="border:1px solid #cfd8df;border-radius:12px;padding:10px 12px;display:grid;gap:4px">' +
              '<div style="display:flex;justify-content:space-between;gap:8px;align-items:center">' +
                '<strong style="font-size:.92rem"><i class="fa ' + esc(m.icone || 'fa-cube') + '" style="color:' + esc(m.cor || '#0f4c5c') + '"></i> ' + esc(m.nome) + '</strong>' +
                '<span style="font-size:.72rem;font-weight:700;border-radius:999px;padding:3px 8px;background:' + fundo + ';color:' + tom + '">' + esc(st) + '</span>' +
              '</div>' +
              '<div style="font-size:.8rem;color:#1a242b"><code style="font-size:.78rem">v' + esc(m.versao || '—') + '</code>' +
                (m.versao_composer && m.versao_composer !== m.versao ? ' <span style="color:#5b6b76">(composer ' + esc(m.versao_composer) + ')</span>' : '') +
              '</div>' +
              '<div style="font-size:.75rem;color:#5b6b76"><i class="fa fa-cloud-upload"></i> ' + esc(deploy) + esc(sha) + '</div>' +
            '</article>'
          );
        }).join('');
      })
      .catch(function (e) {
        corpo.innerHTML = '<p style="margin:0;color:#9b2c2c;font-size:.85rem">Falha ao carregar: ' + esc(e.message || e) + '</p>';
      });
  }

  btn.addEventListener('click', function () {
    var fechado = painel.hidden || painel.style.display === 'none';
    abrir(fechado);
  });
  if (fechar) fechar.addEventListener('click', function () { abrir(false); });
  painel.addEventListener('click', function (ev) { if (ev.target === painel) abrir(false); });
  document.addEventListener('keydown', function (ev) { if (ev.key === 'Escape') abrir(false); });
})();
</script>
