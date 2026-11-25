<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title', 'Bengkel Motor')</title>
<style>
:root{--p:#d9480f;--bg:#f6f4f1;--t:#222;--m:#666;--b:#ddd}
*{box-sizing:border-box}body{margin:0;font-family:system-ui,sans-serif;background:var(--bg);color:var(--t)}
nav{background:#1c1c1c;color:#fff;padding:.8rem 1.2rem;display:flex;gap:1rem;align-items:center}
nav a{color:#fff;text-decoration:none}nav .sp{flex:1}nav form{margin:0}
main{max-width:960px;margin:1.5rem auto;padding:0 1rem}
.card{background:#fff;border:1px solid var(--b);border-radius:10px;padding:1.2rem;margin-bottom:1rem}
label{display:block;margin:.7rem 0 .25rem;font-weight:600}
input,select,textarea{width:100%;padding:.6rem;border:1px solid var(--b);border-radius:6px;font:inherit}
.btn{background:var(--p);color:#fff;border:0;padding:.6rem 1.1rem;border-radius:6px;cursor:pointer;text-decoration:none;display:inline-block;font:inherit}
.btn.sec{background:#555}.btn.red{background:#b42318}.btn.sm{padding:.3rem .7rem;font-size:.85rem}
table{width:100%;border-collapse:collapse}th,td{padding:.55rem;border-bottom:1px solid var(--b);text-align:left;font-size:.92rem}
.err{color:#b42318;margin:.4rem 0}.ok{background:#e6f4ea;border:1px solid #a8d5b5;padding:.7rem;border-radius:6px;margin-bottom:1rem}
.badge{padding:.2rem .6rem;border-radius:99px;font-size:.8rem;color:#fff;background:#888}
.s-menunggu{background:#888}.s-dikerjakan{background:#1971c2}.s-menunggu_sparepart{background:#e8890c}.s-selesai{background:#2f9e44}.s-diambil{background:#495057}
.row{display:grid;grid-template-columns:1fr 1fr;gap:1rem}@media(max-width:600px){.row{grid-template-columns:1fr}}
.steps{display:flex;gap:.4rem;margin:1rem 0}.steps div{flex:1;text-align:center;padding:.5rem .2rem;border-radius:6px;background:#eee;font-size:.78rem;color:var(--m)}
.steps .on{background:var(--p);color:#fff;font-weight:600}
</style>
@stack('styles')
</head>
<body>
<nav>
  <a href="{{ route('home') }}"><strong>🔧 Bengkel Motor</strong></a>
  <span class="sp"></span>
  @auth
    <a href="{{ route('admin.servis.index') }}">Panel</a>
    @can('lihat-dashboard')<a href="{{ route('admin.dashboard') }}">Dashboard</a>@endcan
    @can('kelola-pengguna')<a href="{{ route('admin.pengguna.index') }}">Pengguna</a>@endcan
    @can('lihat-audit')<a href="{{ route('admin.audit.index') }}">Audit Log</a>@endcan
    <span style="font-size:.85rem;color:#b9c4cc">{{ auth()->user()->name }} <span class="badge" style="background:#d9480f">{{ auth()->user()->role }}</span></span>
    <form method="POST" action="{{ route('logout') }}">@csrf<button class="btn sec sm">Keluar</button></form>
  @else
    <a href="{{ route('login') }}">Login Admin</a>
  @endauth
</nav>
<main>
  @if(session('ok'))<div class="ok">{{ session('ok') }}</div>@endif
  @yield('content')
</main>
</body>
</html>
