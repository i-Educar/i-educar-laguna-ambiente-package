{{-- Links CSS do tema Buriti (só quando ativo) --}}
@if(!empty($temaBuritiAtivo))
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
    @foreach(($temaBuritiAssets ?? []) as $href)
        <link rel="stylesheet" type="text/css" href="{{ $href }}?v=3">
    @endforeach
@endif
