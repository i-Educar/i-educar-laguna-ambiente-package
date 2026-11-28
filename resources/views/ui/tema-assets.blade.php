{{-- Assets + bootstrap do modo claro/escuro (anti-FOUC) --}}
<script>
(function () {
    try {
        var m = localStorage.getItem('buriti.tema.modo');
        if (m !== 'claro' && m !== 'escuro') m = 'claro';
        document.documentElement.setAttribute('data-buriti-modo', m);
    } catch (e) {
        document.documentElement.setAttribute('data-buriti-modo', 'claro');
    }
})();
</script>
@if(!empty($temaBuritiAtivo))
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700&display=swap" rel="stylesheet">
    @foreach(($temaBuritiAssets ?? []) as $href)
        {{-- assetUrls() já inclui ?v=filemtime; não duplicar --}}
        <link rel="stylesheet" type="text/css" href="{{ $href }}">
    @endforeach
@else
    {{-- Tokens de marca + modo mesmo com skin do core desligado --}}
    @php
        $temaTokens = public_path('vendor/buriti-tema/tokens.css');
        $temaTokensV = is_file($temaTokens) ? (string) filemtime($temaTokens) : '11';
    @endphp
    <link rel="stylesheet" type="text/css" href="/vendor/buriti-tema/tokens.css?v={{ $temaTokensV }}">
@endif
<script>
(function () {
    if (window.__buritiModoCore) return;
    window.__buritiModoCore = true;
    var KEY = 'buriti.tema.modo';
    function atual() {
        return document.documentElement.getAttribute('data-buriti-modo') === 'escuro' ? 'escuro' : 'claro';
    }
    function aplicar(modo) {
        modo = modo === 'escuro' ? 'escuro' : 'claro';
        document.documentElement.setAttribute('data-buriti-modo', modo);
        if (document.body) document.body.setAttribute('data-buriti-modo', modo);
        try { localStorage.setItem(KEY, modo); } catch (e) {}
        document.querySelectorAll('[data-buriti-modo-toggle]').forEach(function (btn) {
            var ico = btn.querySelector('[data-buriti-modo-icone]') || btn.querySelector('i');
            var escuro = modo === 'escuro';
            btn.title = escuro ? 'Mudar para modo claro' : 'Mudar para modo escuro';
            btn.setAttribute('aria-label', btn.title);
            btn.setAttribute('aria-pressed', escuro ? 'true' : 'false');
            if (ico) ico.className = escuro ? 'fa fa-sun-o' : 'fa fa-moon-o';
        });
    }
    function boot() { aplicar(atual()); }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
    else boot();
    document.addEventListener('click', function (e) {
        var btn = e.target && e.target.closest && e.target.closest('[data-buriti-modo-toggle]');
        if (!btn) return;
        e.preventDefault();
        aplicar(atual() === 'escuro' ? 'claro' : 'escuro');
    });
})();
</script>
