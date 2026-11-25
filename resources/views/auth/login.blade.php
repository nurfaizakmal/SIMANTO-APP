<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Masuk · Bengkel Motor</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;700&family=IBM+Plex+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/panel.css') }}">
<style>
body.masuk{margin:0;min-height:100vh;display:grid;place-items:center;padding:20px;font-family:var(--body);color:var(--teks);
  background:radial-gradient(60% 70% at 85% 100%,rgba(242,194,27,.18),transparent 62%),var(--aspal)}
.kotak{width:100%;max-width:420px}
.kotak .merek{justify-content:center;padding:0 0 18px;color:#fff}
.kotak .kartu-masuk{background:#fff;border-radius:16px;padding:26px 24px 22px;box-shadow:0 20px 50px rgba(0,0,0,.35)}
.kartu-masuk h1{font:700 2rem/1.05 var(--head);margin:0 0 4px}
.kartu-masuk p.sapa{margin:0 0 6px;color:var(--muted)}
.sandi{position:relative}
.sandi button{position:absolute;right:6px;top:50%;transform:translateY(-50%);border:0;background:none;color:var(--muted);padding:6px 8px;border-radius:8px;cursor:pointer;font:600 .8rem var(--body)}
.sandi input{padding-right:70px}
.info-masuk{background:#eaf6ef;border-left:4px solid #2e9e6b;color:#14532d;padding:9px 12px;border-radius:8px;margin-top:14px;font-size:.88rem}
.lupa{display:inline-block;margin-top:9px;font-size:.85rem;color:var(--muted)}
.galat-masuk{background:#fdecea;border-left:4px solid #c8372d;color:#8a1f17;padding:9px 12px;border-radius:8px;margin-top:14px;font-size:.88rem}
.ingat{display:flex;align-items:center;gap:8px;margin:14px 0 0;font-weight:400}
.kartu-masuk .btn{width:100%;justify-content:center;margin-top:16px;padding:12px}
.kembali{display:block;text-align:center;margin-top:16px;color:#b9c4cc;text-decoration:none;font-size:.88rem}
.kembali:hover{color:#fff}
</style>
</head>
<body class="masuk">
<div class="kotak">
  <a class="merek" href="{{ route('home') }}" style="text-decoration:none">
    <span class="merek-logo">{!! \App\Support\Ikon::svg('wrench', 22) !!}</span>
    <span><b>Bengkel Motor</b><small>Panel pengelola</small></span>
  </a>

  <div class="kartu-masuk">
    <h1>Masuk</h1>
    <p class="sapa">Khusus owner, admin, dan teknisi bengkel.</p>
    @if(session('ok'))<div class="info-masuk" role="status">{{ session('ok') }}</div>@endif

    <form method="POST" action="{{ route('login') }}">
      @csrf
      <label for="email">Email</label>
      <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">

      <label for="password">Password</label>
      <div class="sandi">
        <input id="password" type="password" name="password" required autocomplete="current-password">
        <button type="button" id="lihatSandi" aria-label="Tampilkan password">Lihat</button>
      </div>
      <a class="lupa" href="{{ route('sandi.lupa') }}">Lupa password?</a>

      @error('email')<div class="galat-masuk" role="alert">{{ $message }}</div>@enderror

      <label class="ingat"><input type="checkbox" name="remember"> Ingat saya di perangkat ini</label>
      <button class="btn" type="submit">Masuk</button>
    </form>
  </div>

  <a class="kembali" href="{{ route('home') }}">&larr; Kembali ke halaman cek status</a>
</div>
<script>
  document.getElementById('lihatSandi').addEventListener('click', function () {
    var i = document.getElementById('password'), t = i.type === 'password';
    i.type = t ? 'text' : 'password';
    this.textContent = t ? 'Sembunyi' : 'Lihat';
    this.setAttribute('aria-label', t ? 'Sembunyikan password' : 'Tampilkan password');
  });
</script>
</body>
</html>
