<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title', 'Panel') · Bengkel Motor</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;700&family=IBM+Plex+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/panel.css') }}">
@stack('styles')
</head>
<body class="panel">
@php
  $pengguna = auth()->user();
  // [label, nama rute, pola rute aktif, ikon]
  $menu = [
    ['Ringkasan', 'admin.dashboard', 'admin.dashboard', 'dashboard'],
    ['Servis', 'admin.servis.index', 'admin.servis.*', 'wrench'],
    ['Langganan', 'admin.langganan.index', 'admin.langganan.*', 'star'],
    ['Cadangan', 'admin.cadangan.index', 'admin.cadangan.*', 'database'],
  ];

  $labelPeran = 'Admin';
  $kata = preg_split('/\s+/', trim($pengguna->name)) ?: [];
  $inisial = mb_strtoupper(mb_substr($kata[0] ?? '', 0, 1) . mb_substr($kata[1] ?? '', 0, 1));
  $judulHalaman = trim($__env->yieldContent('judul')) ?: trim($__env->yieldContent('title'));
@endphp

<aside class="sisi">
  <a class="merek" href="{{ route('admin.dashboard') }}">
    <span class="merek-logo">{!! \App\Support\Ikon::svg('wrench', 22) !!}</span>
    <span><b>Bengkel Motor</b><small>Panel admin</small></span>
  </a>

  <nav class="menu" aria-label="Menu utama">
    @foreach($menu as [$label, $rute, $pola, $ikon])
      <a href="{{ route($rute) }}" class="{{ request()->routeIs($pola) ? 'aktif' : '' }}" @if(request()->routeIs($pola)) aria-current="page" @endif>
        {!! \App\Support\Ikon::svg($ikon, 19) !!}<span>{{ $label }}</span>
      </a>
    @endforeach
  </nav>

  <div class="sisi-bawah">
    <div class="kartu-user">
      <span class="avatar">{{ $inisial }}</span>
      <div><b>{{ $pengguna->name }}</b><small>{{ $labelPeran }}</small></div>
    </div>
    <a class="tautan-sisi" href="{{ route('home') }}" target="_blank" rel="noopener">{!! \App\Support\Ikon::svg('globe', 18) !!}<span>Lihat website</span></a>
    <form method="POST" action="{{ route('logout') }}">
      @csrf
      <button class="tautan-sisi" type="submit">{!! \App\Support\Ikon::svg('logout', 18) !!}<span>Keluar</span></button>
    </form>
  </div>
</aside>

<main class="isi">
  <header class="kepala">
    <div>
      <h1>{{ $judulHalaman }}</h1>
      @hasSection('sub')<p>@yield('sub')</p>@endif
    </div>
    <div class="kepala-aksi">
      <span class="chip-tgl">{{ now()->locale('id')->translatedFormat('l, d F Y') }}</span>
      @yield('aksi')
    </div>
  </header>

  @if(session('peringatan'))
    <p class="chip-info" role="alert" style="margin:0 0 16px;border-radius:12px">{{ session('peringatan') }}</p>
  @endif

  @yield('content')
</main>

@if(session('ok'))
  <div class="toast" data-toast role="status"><span>{{ session('ok') }}</span><button type="button" aria-label="Tutup">&times;</button></div>
@endif
<script>
  document.querySelectorAll('[data-toast]').forEach(function (t) {
    var tutup = function () { t.classList.add('hilang'); setTimeout(function () { t.remove(); }, 450); };
    setTimeout(tutup, 5000);
    var b = t.querySelector('button'); if (b) b.addEventListener('click', tutup);
  });
</script>
</body>
</html>
