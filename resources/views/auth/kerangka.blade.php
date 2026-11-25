<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title') · Bengkel Motor</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;700&family=IBM+Plex+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/panel.css') }}">
<style>
body.masuk{margin:0;min-height:100vh;display:grid;place-items:center;padding:20px;font-family:var(--body);color:var(--teks);
  background:radial-gradient(60% 70% at 85% 100%,rgba(242,194,27,.18),transparent 62%),var(--aspal)}
.kotak{width:100%;max-width:420px}
.kotak .merek{justify-content:center;padding:0 0 18px;color:#fff;text-decoration:none}
.kartu-masuk{background:#fff;border-radius:16px;padding:26px 24px 22px;box-shadow:0 20px 50px rgba(0,0,0,.35)}
.kartu-masuk h1{font:700 2rem/1.05 var(--head);margin:0 0 4px}
.kartu-masuk p.sapa{margin:0 0 6px;color:var(--muted)}
.info-masuk{background:#eaf6ef;border-left:4px solid #2e9e6b;color:#14532d;padding:9px 12px;border-radius:8px;margin:12px 0 0;font-size:.88rem}
.galat-masuk{background:#fdecea;border-left:4px solid #c8372d;color:#8a1f17;padding:9px 12px;border-radius:8px;margin-top:12px;font-size:.88rem}
.kartu-masuk .btn{width:100%;justify-content:center;margin-top:16px;padding:12px}
.kembali{display:block;text-align:center;margin-top:16px;color:#b9c4cc;text-decoration:none;font-size:.88rem}
.kembali:hover{color:#fff}
.kode-input{font:700 1.8rem var(--body);letter-spacing:.5em;text-align:center;padding-left:.9em}
.khusus-owner{margin-top:14px;border:1.5px dashed var(--garis);border-radius:10px;padding:10px 12px}
.khusus-owner summary{cursor:pointer;font-weight:600;font-size:.88rem}
.khusus-owner code{background:#eef1f4;padding:1px 5px;border-radius:4px;font-size:.8rem}
.catatan-lupa{margin:14px 0 0;font-size:.82rem;color:var(--muted)}
.tautan-kecil{display:block;text-align:center;margin-top:12px;font-size:.88rem;color:var(--muted)}
</style>
</head>
<body class="masuk">
<div class="kotak">
  <a class="merek" href="{{ route('home') }}">
    <span class="merek-logo">{!! \App\Support\Ikon::svg('wrench', 22) !!}</span>
    <span><b>Bengkel Motor</b><small>Panel pengelola</small></span>
  </a>
  <div class="kartu-masuk">
    @yield('isi')
  </div>
  <a class="kembali" href="{{ route('login') }}">&larr; Kembali ke halaman masuk</a>
</div>
</body>
</html>
