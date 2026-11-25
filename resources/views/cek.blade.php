<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Cek Status Perbaikan Motor</title>
<script>document.documentElement.classList.add('js')</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;700&family=IBM+Plex+Sans:wght@400;600&display=swap" rel="stylesheet">
<style>
:root{
  --aspal:#17242f; --aspal-2:#223442; --kuning:#f2c21b; --baja:#eef1f3;
  --abu:#8795a1; --garis:#cfd6db; --teks:#1b2630; --hijau:#2e9e6b;
  --head:'Barlow Condensed','Arial Narrow',sans-serif; --body:'IBM Plex Sans',system-ui,sans-serif;
}
*{box-sizing:border-box}
body{margin:0;font-family:var(--body);color:var(--teks);background:var(--baja);line-height:1.55}
a{color:inherit}
:focus-visible{outline:3px solid var(--kuning);outline-offset:2px}

/* ---- header + hero ---- */
.top{color:#fff;background:radial-gradient(70% 90% at 78% 100%,rgba(242,194,27,.17),transparent 62%),var(--aspal)}
.bar{max-width:1040px;margin:0 auto;padding:1rem 1.25rem;display:flex;justify-content:space-between;align-items:center}
.brand{font-family:var(--head);font-weight:700;font-size:1.5rem;letter-spacing:.02em;text-decoration:none}
.hero{max-width:1040px;margin:0 auto;padding:1.6rem 1.25rem 0}
.grid{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:1rem;align-items:end}
.teks{padding-bottom:2.4rem}
.hero h1{font-family:var(--head);font-weight:700;font-size:clamp(2.5rem,6.4vw,4.4rem);line-height:1;margin:0 0 .9rem;max-width:13ch}
.hero h1 span{color:var(--kuning)}
.hero p{margin:0 0 1.8rem;color:#b9c4cc;max-width:44ch}
form.cek{display:grid;grid-template-columns:1fr auto;gap:.8rem;align-items:end;max-width:520px}
.cek label{display:block;font-size:.85rem;color:#b9c4cc;margin-bottom:.3rem}
.cek input{width:100%;padding:.8rem .9rem;font:600 1.05rem var(--body);border:2px solid transparent;border-radius:4px;background:#fff;color:var(--teks);text-transform:uppercase;letter-spacing:.04em}
.cek input::placeholder{color:#9aa6b0;font-weight:400;text-transform:none;letter-spacing:0}
.cek button{padding:.85rem 1.5rem;font:700 1.05rem var(--body);background:var(--kuning);color:var(--aspal);border:0;border-radius:4px;cursor:pointer;transition:transform .15s,background .15s}
.cek button:hover{background:#ffd23f;transform:translateY(-1px)}
.galat{max-width:520px;margin:1rem 0 0;padding:.7rem .9rem;background:#3a2326;border-left:4px solid #ff6b6b;border-radius:4px;color:#ffd9d9}
.galat small{display:block;color:#e7b4b4}

/* adegan: motor melaju di atas jalan */
.adegan{margin-bottom:-2px}
.adegan svg{display:block;width:100%;height:auto;overflow:visible}
.roda{transform-box:fill-box;transform-origin:center;animation:putar .55s linear infinite}
.badan{animation:getar 1.1s ease-in-out infinite alternate}
.sinar{animation:sinar 2.4s ease-in-out infinite alternate}
.lampu{animation:kedip 1.6s ease-in-out infinite}
.kecepatan line{animation:garis .9s linear infinite}
.kecepatan line:nth-child(2){animation-delay:.3s}.kecepatan line:nth-child(3){animation-delay:.6s}
.jalan{position:relative;height:48px;background:#0e171e;border-top:3px solid #2d4152;overflow:hidden}
.jalan::before{content:"";position:absolute;left:0;right:0;top:calc(50% - 2px);height:4px;background:repeating-linear-gradient(90deg,var(--kuning) 0 40px,transparent 40px 96px);background-size:96px 4px;animation:aspal .8s linear infinite}
@keyframes putar{to{transform:rotate(-360deg)}}
@keyframes getar{from{transform:translateY(-1.5px)}to{transform:translateY(1.5px)}}
@keyframes sinar{from{opacity:.65}to{opacity:1}}
@keyframes kedip{0%,100%{opacity:1}50%{opacity:.35}}
@keyframes garis{from{transform:translateX(20px);opacity:0}30%{opacity:.7}to{transform:translateX(-16px);opacity:0}}
@keyframes aspal{to{background-position:-96px 0}}

/* animasi masuk (sekali saat halaman dibuka) */
.masuk{animation:naik .7s cubic-bezier(.2,.7,.2,1) both;animation-delay:calc(var(--d,0) * 90ms)}
.geser{animation-name:geser}
@keyframes naik{from{opacity:0;transform:translateY(18px)}}
@keyframes geser{from{opacity:0;transform:translateX(70px)}}

@media(max-width:820px){
  .grid{grid-template-columns:1fr}
  .teks{padding-bottom:1rem}
  .adegan{max-width:420px;width:100%;margin:0 auto -2px}
}
@media(max-width:560px){form.cek{grid-template-columns:1fr}.cek button{width:100%}}

/* ---- angka hidup ---- */
.angka-wrap{background:#fff;border-bottom:1px solid var(--garis)}
.hasil + .angka-wrap{margin-top:2rem}
.angka{max-width:1040px;margin:0 auto;padding:1.4rem 1.25rem;display:grid;grid-template-columns:repeat(3,1fr)}
.angka div{padding:0 1.2rem;border-left:3px dashed var(--garis)}
.angka div:first-child{border-left:0;padding-left:0}
.angka b{display:block;font-family:var(--head);font-weight:700;font-size:clamp(2.2rem,6vw,3.4rem);line-height:1;color:var(--aspal)}
.angka span{font-size:.88rem;color:#4a5a68}
.live{display:inline-block;width:9px;height:9px;border-radius:50%;background:var(--hijau);margin-right:.45rem;box-shadow:0 0 0 0 rgba(46,158,107,.6);animation:denyut 1.8s ease-out infinite}
@keyframes denyut{70%{box-shadow:0 0 0 9px rgba(46,158,107,0)}100%{box-shadow:0 0 0 0 rgba(46,158,107,0)}}
@media(max-width:560px){.angka{grid-template-columns:1fr;gap:1rem}.angka div{border-left:0;padding:0;display:flex;align-items:baseline;gap:.8rem}.angka b{min-width:3.2rem}}

/* ---- bagian informasi ---- */
.bagian{max-width:1040px;margin:0 auto;padding:3rem 1.25rem 0}
.bagian h2{font-family:var(--head);font-weight:700;font-size:clamp(1.8rem,4vw,2.4rem);line-height:1.05;margin:0 0 1.4rem}
.langkah{list-style:none;margin:0;padding:0;display:grid;grid-template-columns:repeat(3,1fr);gap:1.6rem}
.langkah li{position:relative;padding-top:3.6rem}
.langkah em{position:absolute;top:0;left:0;font:700 4rem/1 var(--head);font-style:normal;color:transparent;-webkit-text-stroke:2px var(--aspal-2)}
.langkah li:not(:last-child)::after{content:"";position:absolute;top:1.9rem;left:5rem;right:0;border-top:3px dashed var(--garis)}
.langkah h3{margin:0 0 .3rem;font-size:1.1rem}
.langkah p{margin:0;color:#4a5a68}
.arti{list-style:none;margin:0;padding:0;display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:1rem}
.arti li{background:#fff;border:1px solid var(--garis);border-left:6px solid var(--s);border-radius:4px;padding:.8rem 1rem}
.arti strong{display:block;color:var(--s)}
.arti span{color:#4a5a68;font-size:.92rem}
.aman{margin-top:3.4rem;background:var(--kuning);color:var(--aspal)}
.aman-isi{max-width:1040px;margin:0 auto;padding:2.2rem 1.25rem;display:grid;grid-template-columns:1fr 1.3fr;gap:1.4rem;align-items:center}
.aman h2{font-family:var(--head);font-weight:700;font-size:clamp(2rem,5vw,3rem);line-height:1;margin:0}
.aman p{margin:0 0 .9rem}
.aman ul{list-style:none;margin:0;padding:0;display:flex;gap:.5rem;flex-wrap:wrap}
.aman li{border:2px solid var(--aspal);border-radius:99px;padding:.15rem .8rem;font-weight:600;font-size:.88rem}
@media(max-width:760px){.langkah{grid-template-columns:1fr}.langkah li:not(:last-child)::after{display:none}.aman-isi{grid-template-columns:1fr}}

/* muncul saat di-scroll */
.js .reveal{opacity:0;transform:translateY(18px);transition:opacity .6s ease,transform .6s cubic-bezier(.2,.7,.2,1)}
.js .reveal.tampil{opacity:1;transform:none}

/* ---- hubungi via WhatsApp ---- */
.btn-wa{display:inline-flex;align-items:center;gap:.5rem;padding:.65rem 1.1rem;border-radius:99px;background:#25d366;color:#0b2e17;font-weight:700;text-decoration:none;transition:filter .15s,transform .15s}
.btn-wa:hover{filter:brightness(1.07);transform:translateY(-1px)}
.btn-wa-kecil{padding:.45rem .9rem;font-size:.9rem}
@media(max-width:420px){.btn-wa-kecil span{display:none}.btn-wa-kecil{padding:.5rem}}
.tanya{display:flex;align-items:center;justify-content:space-between;gap:.8rem 1.2rem;flex-wrap:wrap;margin-top:1.4rem;padding:1rem 1.1rem;border-radius:10px;background:#f1f4f6;border:1px solid var(--garis)}
.tanya p{margin:0;font-weight:600}
.fab-wa{position:fixed;right:18px;bottom:18px;width:56px;height:56px;border-radius:50%;background:#25d366;color:#fff;display:grid;place-items:center;box-shadow:0 6px 18px rgba(0,0,0,.28);z-index:50;transition:transform .15s}
.fab-wa:hover{transform:scale(1.07)}
@media(max-width:560px){.fab-wa{right:14px;bottom:14px;width:52px;height:52px}}

/* ---- hasil ---- */
.hasil{max-width:1040px;margin:2rem auto 0;padding:0 1.25rem}
.panel{background:#fff;border:1px solid var(--garis);border-left:6px solid var(--s,var(--abu));border-radius:4px;padding:1.6rem 1.5rem 1.8rem}
.kepala{display:flex;justify-content:space-between;align-items:flex-start;gap:1rem;flex-wrap:wrap}
.motor{font-family:var(--head);font-weight:700;font-size:1.9rem;line-height:1.1;margin:0}
.plat{font:600 1.15rem/1 var(--body);letter-spacing:.14em;padding:.45rem .8rem;background:#fff;border:2px solid var(--teks);border-radius:5px;box-shadow:inset 0 0 0 2px #fff,inset 0 0 0 3px var(--teks)}
.status{margin:1.3rem 0 .2rem;font-family:var(--head);font-weight:700;font-size:clamp(2rem,6vw,3rem);line-height:1;color:var(--s)}
.pesan{margin:0;color:#4a5a68;max-width:56ch}

/* jalur perbaikan */
.track{position:relative;padding-top:2.6rem;margin:2rem 0 1.8rem}
.road,.fill{position:absolute;top:calc(2.6rem + 9px);height:4px;left:10%}
.road{right:10%;background:repeating-linear-gradient(90deg,var(--garis) 0 10px,transparent 10px 18px)}
.fill{width:calc(var(--i) * 20%);background:var(--s);border-radius:2px;animation:isi 1.1s ease-out}
.marker{position:absolute;top:0;left:calc(10% + var(--i) * 20%);transform:translateX(-50%) scaleX(-1);font-size:1.7rem;line-height:1;animation:jalan 1.1s ease-out}
.stops{display:grid;grid-template-columns:repeat(5,1fr);margin:0;padding:0;list-style:none;position:relative}
.stops li{text-align:center;font-size:.8rem;color:var(--abu);padding:0 .15rem}
.stops li::before{content:"";display:block;width:22px;height:22px;margin:0 auto .45rem;border-radius:50%;background:#fff;border:3px solid var(--garis)}
.stops li.lewat{color:var(--teks)}
.stops li.lewat::before{background:var(--s);border-color:var(--s)}
.stops li.kini{color:var(--teks);font-weight:600}
.stops li.kini::before{background:#fff;border-color:var(--s);border-width:6px}
@keyframes jalan{from{left:10%}}
@keyframes isi{from{width:0}}

/* riwayat */
.riwayat{margin:0 0 1.6rem}
.riwayat h3{font-family:var(--head);font-size:1.3rem;margin:0 0 .7rem}
.riwayat ol{list-style:none;margin:0;padding:0 0 0 1rem;border-left:3px solid var(--garis)}
.riwayat li{position:relative;padding:0 0 .8rem 1rem;display:flex;justify-content:space-between;gap:1rem;flex-wrap:wrap}
.riwayat li::before{content:"";position:absolute;left:-1.42rem;top:.45rem;width:10px;height:10px;border-radius:50%;background:var(--s)}
.riwayat li span{color:var(--abu);font-size:.85rem}

/* rincian */
.rincian{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1.1rem 1.6rem;margin:0;padding-top:1.3rem;border-top:1px solid var(--garis)}
.rincian div{min-width:0}
.rincian dt{font-size:.82rem;color:var(--abu);margin-bottom:.15rem}
.rincian dd{margin:0;font-weight:600;overflow-wrap:anywhere}
.rincian .lebar{grid-column:1/-1}
.catatan{margin-top:1.4rem;padding:.9rem 1rem;background:#fff8dc;border-left:4px solid var(--kuning);border-radius:3px}
.catatan strong{display:block;margin-bottom:.15rem}

/* galeri motor di bengkel */
.catatan-galeri{margin:-.8rem 0 1.2rem;color:#4a5a68}
.galeri{list-style:none;margin:0;padding:0;display:grid;grid-template-columns:repeat(auto-fill,minmax(230px,1fr));gap:1rem}
.galeri li{background:#fff;border:1px solid var(--garis);border-bottom:5px solid var(--s);border-radius:6px;overflow:hidden;transition:transform .2s,box-shadow .2s}
.galeri li:hover{transform:translateY(-3px);box-shadow:0 8px 20px rgba(23,36,47,.12)}
.galeri img{display:block;width:100%;height:auto;aspect-ratio:4/3;object-fit:cover;background:#dfe5e9}
.isi-kartu{padding:.8rem .9rem 1rem}
.isi-kartu h3{font-family:var(--head);font-size:1.45rem;line-height:1.1;margin:.6rem 0 .5rem}
.plat.kecil{display:inline-block;font-size:.95rem;padding:.3rem .6rem}
.status-kecil{display:inline-block;padding:.18rem .7rem;border-radius:99px;background:var(--s);color:#fff;font-size:.8rem;font-weight:600}

/* total biaya */
.biaya-box{display:flex;justify-content:space-between;align-items:baseline;gap:1rem;flex-wrap:wrap;margin:0 0 1.6rem;padding:.9rem 1.1rem;background:#fff8dc;border-left:4px solid var(--kuning);border-radius:4px}
.biaya-box span{color:#6d5600;font-weight:600}
.biaya-box b{font:700 1.8rem/1.1 var(--head);color:var(--aspal)}

/* tutup hasil pengecekan */
.kepala-kanan{display:flex;align-items:center;gap:.7rem}
.tutup-hasil{width:38px;height:38px;border-radius:50%;border:1.5px solid var(--garis);background:#fff;color:var(--teks);font-size:1.5rem;line-height:1;cursor:pointer;padding:0;transition:background .15s,border-color .15s}
.tutup-hasil:hover{background:#f1f4f6;border-color:#b8c3cb}
.tutup-bawah{display:block;width:100%;margin-top:1.4rem;padding:.8rem;border:2px solid var(--garis);border-radius:6px;background:#fff;color:var(--teks);font:600 1rem var(--body);cursor:pointer;transition:background .15s,border-color .15s}
.tutup-bawah:hover{background:#f1f4f6;border-color:#b8c3cb}

footer{background:var(--aspal);color:#b9c4cc;font-size:.88rem}
footer div{max-width:1040px;margin:0 auto;padding:1.3rem 1.25rem;display:flex;justify-content:space-between;gap:1rem;flex-wrap:wrap}
footer a{text-decoration:none}footer a:hover{color:#fff}

/* hormati pengguna yang mematikan animasi */
@media(prefers-reduced-motion:reduce){
  .masuk,.roda,.badan,.sinar,.lampu,.kecepatan line,.jalan::before,.live,.fill,.marker{animation:none!important}
  .js .reveal{opacity:1;transform:none;transition:none}
  .cek button{transition:none}
  .galeri li{transition:none}
}
</style>
</head>
<body>

@php
  $waNomor = preg_replace('/\D/', '', (string) config('bengkel.whatsapp'));
  $waUrl = 'https://wa.me/' . $waNomor . '?text=' . rawurlencode(config('bengkel.whatsapp_pesan'));
@endphp
<header class="top">
  <div class="bar">
    <a class="brand" href="{{ route('home') }}">🔧 Bengkel Motor</a>
    <a class="btn-wa btn-wa-kecil" href="{{ $waUrl }}" target="_blank" rel="noopener" aria-label="Hubungi kami via WhatsApp">@include('partials.ikon-wa', ['u' => 18])<span>WhatsApp</span></a>
  </div>

  <div class="hero">
    <div class="grid">
      <div class="teks">
        <h1 class="masuk" style="--d:0">Motor Anda <span>sudah sampai tahap mana?</span></h1>
        <p class="masuk" style="--d:1">Masukkan kode servis yang diberikan bengkel. Status perbaikan langsung tampil di sini.</p>

        <form class="cek masuk" style="--d:2" method="POST" action="{{ route('cek') }}#hasil">
          @csrf
          <div>
            <label for="kode_servis">Kode servis</label>
            <input id="kode_servis" name="kode_servis" placeholder="SRV-A1B2C3" value="{{ old('kode_servis') }}" autocomplete="off" required>
          </div>
          <button type="submit">Cek status</button>
        </form>

        @if($errors->any())
          <div class="galat" role="alert">
            {{ $errors->first() }}
            <small>Kode servis diberikan bengkel saat motor masuk, contoh SRV-A1B2C3.</small>
          </div>
        @endif
      </div>

      <div class="adegan masuk geser" style="--d:3">
        <svg viewBox="0 0 440 211" role="img" aria-label="Ilustrasi sepeda motor yang sedang melaju">
          <defs>
            <linearGradient id="sinarGrad" x1="0" x2="1" y1="0" y2="0">
              <stop offset="0" stop-color="#ffe58a" stop-opacity=".6"/>
              <stop offset="1" stop-color="#ffe58a" stop-opacity="0"/>
            </linearGradient>
          </defs>

          <ellipse cx="220" cy="209" rx="200" ry="5" fill="#000" opacity=".35"/>

          <g class="kecepatan" stroke="#8795a1" stroke-width="3" stroke-linecap="round" opacity=".8">
            <line x1="6" y1="118" x2="44" y2="118"/>
            <line x1="14" y1="150" x2="50" y2="150"/>
            <line x1="4" y1="182" x2="38" y2="182"/>
          </g>

          <polygon class="sinar" points="326,98 440,60 440,148 326,110" fill="url(#sinarGrad)"/>

          <!-- roda belakang -->
          <g class="roda">
            <circle cx="100" cy="166" r="40" fill="none" stroke="#4a5a68" stroke-width="9"/>
            <circle cx="100" cy="166" r="31" fill="none" stroke="#cfd6db" stroke-width="2"/>
            <g stroke="#cfd6db" stroke-width="2">
              <line x1="69" y1="166" x2="131" y2="166"/>
              <line x1="84.5" y1="139.15" x2="115.5" y2="192.85"/>
              <line x1="115.5" y1="139.15" x2="84.5" y2="192.85"/>
            </g>
            <circle cx="100" cy="127" r="3.5" fill="#f2c21b"/>
          </g>
          <!-- roda depan -->
          <g class="roda">
            <circle cx="340" cy="166" r="40" fill="none" stroke="#4a5a68" stroke-width="9"/>
            <circle cx="340" cy="166" r="31" fill="none" stroke="#cfd6db" stroke-width="2"/>
            <g stroke="#cfd6db" stroke-width="2">
              <line x1="309" y1="166" x2="371" y2="166"/>
              <line x1="324.5" y1="139.15" x2="355.5" y2="192.85"/>
              <line x1="355.5" y1="139.15" x2="324.5" y2="192.85"/>
            </g>
            <circle cx="340" cy="127" r="3.5" fill="#f2c21b"/>
          </g>

          <g class="badan">
            <!-- lengan ayun + shock -->
            <line x1="214" y1="162" x2="100" y2="166" stroke="#cfd6db" stroke-width="6" stroke-linecap="round"/>
            <line x1="152" y1="116" x2="130" y2="160" stroke="#f2c21b" stroke-width="4" stroke-linecap="round"/>
            <!-- knalpot -->
            <path d="M236 174 Q196 188 150 178" fill="none" stroke="#8795a1" stroke-width="11" stroke-linecap="round"/>
            <!-- mesin -->
            <path d="M170 126 L250 126 L262 150 L250 176 L176 176 L164 150 Z" fill="#2b3d4c" stroke="#8795a1" stroke-width="2"/>
            <rect x="205" y="106" width="30" height="22" fill="#3a4f61" stroke="#8795a1" stroke-width="2"/>
            <g stroke="#8795a1" stroke-width="2"><line x1="208" y1="112" x2="232" y2="112"/><line x1="208" y1="118" x2="232" y2="118"/></g>
            <circle cx="214" cy="152" r="14" fill="#1b2a36" stroke="#8795a1" stroke-width="2"/>
            <!-- garpu + rangka -->
            <line x1="296" y1="90" x2="340" y2="166" stroke="#cfd6db" stroke-width="6" stroke-linecap="round"/>
            <line x1="292" y1="98" x2="254" y2="142" stroke="#cfd6db" stroke-width="5" stroke-linecap="round"/>
            <!-- stang -->
            <line x1="294" y1="86" x2="276" y2="76" stroke="#cfd6db" stroke-width="5" stroke-linecap="round"/>
            <circle cx="274" cy="75" r="4" fill="#0e171e"/>
            <!-- tangki + jok -->
            <path d="M196 92 C215 76 262 76 292 92 L288 110 C255 104 220 106 192 114 Z" fill="#f2c21b"/>
            <path d="M118 112 C140 100 170 98 196 100 L192 116 C165 116 135 118 112 122 Z" fill="#2b3d4c" stroke="#8795a1" stroke-width="1.5"/>
            <circle class="lampu" cx="107" cy="116" r="4" fill="#ff5a4d"/>
            <!-- lampu depan -->
            <ellipse cx="316" cy="100" rx="10" ry="12" fill="#fff6d1" stroke="#cfd6db" stroke-width="2"/>
            <!-- spakbor depan -->
            <path d="M306 138 Q340 112 374 140" fill="none" stroke="#f2c21b" stroke-width="5" stroke-linecap="round"/>
            <circle cx="340" cy="166" r="5" fill="#f2c21b"/>
            <circle cx="100" cy="166" r="5" fill="#f2c21b"/>
          </g>
        </svg>
      </div>
    </div>
  </div>
  <div class="jalan" aria-hidden="true"></div>
</header>

@isset($servis)
@php
  $urut = ['menunggu', 'dikerjakan', 'menunggu_sparepart', 'selesai', 'diambil'];
  $label = ['Antrian', 'Dikerjakan', 'Sparepart', 'Selesai', 'Diambil'];
  $warna = ['menunggu' => '#6b7a87', 'dikerjakan' => '#1f6fb2', 'menunggu_sparepart' => '#c97a0a', 'selesai' => '#2e9e6b', 'diambil' => '#4a5a68'];
  $pesan = [
    'menunggu' => 'Motor Anda sudah tercatat dan menunggu giliran dikerjakan.',
    'dikerjakan' => 'Mekanik sedang mengerjakan motor Anda.',
    'menunggu_sparepart' => 'Pengerjaan berhenti sementara sampai sparepart tiba.',
    'selesai' => 'Perbaikan selesai. Motor siap Anda ambil di bengkel.',
    'diambil' => 'Motor sudah Anda ambil. Terima kasih sudah servis di sini.',
  ];
  $idx = array_search($servis->status, $urut);
@endphp
<section class="hasil" id="hasil">
  <div class="panel" style="--s: {{ $warna[$servis->status] }}; --i: {{ $idx }}">
    <div class="kepala">
      <h2 class="motor">{{ $servis->merk_motor }}</h2>
      <div class="kepala-kanan">
        <span class="plat" title="Plat nomor disamarkan demi keamanan">{{ $servis->platSamar() }}</span>
        <button type="button" class="tutup-hasil" data-tutup aria-label="Tutup hasil pengecekan" title="Tutup (Esc)">&times;</button>
      </div>
    </div>

    <p class="status">{{ $servis->labelStatus() }}</p>
    <p class="pesan">{{ $pesan[$servis->status] }}</p>

    <div class="track" aria-hidden="true">
      <div class="road"></div>
      <div class="fill"></div>
      <div class="marker">🏍️</div>
      <ol class="stops">
        @foreach($urut as $n => $s)
          <li class="{{ $n < $idx ? 'lewat' : ($n === $idx ? 'kini' : '') }}">{{ $label[$n] }}</li>
        @endforeach
      </ol>
    </div>

    <div class="biaya-box">
      <span>Total biaya perbaikan</span>
      <b>{{ is_null($servis->biaya) ? 'Belum ditentukan' : \App\Support\Format::rupiah($servis->biaya) }}</b>
    </div>

    @if($servis->riwayat->isNotEmpty())
      <div class="riwayat">
        <h3>Riwayat perbaikan</h3>
        <ol>
          @foreach($servis->riwayat->reverse() as $r)
            <li><strong>{{ $r->labelKe() }}</strong><span>{{ $r->created_at->locale('id')->translatedFormat('d M Y, H:i') }}</span></li>
          @endforeach
        </ol>
      </div>
    @endif

    <dl class="rincian">
      <div><dt>Kode servis</dt><dd>{{ $servis->kode_servis }}</dd></div>
      <div><dt>Tanggal masuk</dt><dd>{{ $servis->tanggal_masuk->locale('id')->translatedFormat('d F Y') }}</dd></div>
      @if($servis->tanggal_selesai)
        <div><dt>Tanggal selesai</dt><dd>{{ $servis->tanggal_selesai->locale('id')->translatedFormat('d F Y') }}</dd></div>
      @endif
      <div class="lebar"><dt>Keluhan</dt><dd>{{ $servis->keluhan }}</dd></div>
    </dl>

    @if($servis->catatan)
      <div class="catatan"><strong>Catatan mekanik</strong>{{ $servis->catatan }}</div>
    @endif

    @php $waKode = 'https://wa.me/' . $waNomor . '?text=' . rawurlencode('Halo, saya mau bertanya tentang servis motor saya dengan kode ' . $servis->kode_servis . '.'); @endphp
    <div class="tanya">
      <p>Ada yang mau ditanyakan tentang motor ini?</p>
      <a class="btn-wa" href="{{ $waKode }}" target="_blank" rel="noopener">@include('partials.ikon-wa', ['u' => 18])Tanya via WhatsApp</a>
    </div>

    <button type="button" class="tutup-bawah" data-tutup>Tutup dan cek motor lain</button>
  </div>
</section>
@endisset

@isset($stat)
<div class="angka-wrap">
  <section class="angka reveal">
    <div><b data-n="{{ $stat['di_bengkel'] }}">{{ $stat['di_bengkel'] }}</b><span><i class="live"></i>motor sedang di bengkel</span></div>
    <div><b data-n="{{ $stat['siap_ambil'] }}">{{ $stat['siap_ambil'] }}</b><span>selesai, siap diambil</span></div>
    <div><b data-n="{{ $stat['selesai_bulan'] }}">{{ $stat['selesai_bulan'] }}</b><span>selesai bulan ini</span></div>
  </section>
</div>
@endisset

@isset($galeri)
@if($galeri->isNotEmpty())
@php
  $warnaGaleri = ['menunggu' => '#6b7a87', 'dikerjakan' => '#1f6fb2', 'menunggu_sparepart' => '#c97a0a', 'selesai' => '#2e9e6b', 'diambil' => '#4a5a68'];
@endphp
<section class="bagian reveal" id="motor-di-bengkel">
  <h2>Motor di bengkel</h2>
  <p class="catatan-galeri">Motor yang sedang ditangani saat ini. Plat nomor hanya ditampilkan sebagian demi keamanan.</p>
  <ul class="galeri">
    @foreach($galeri as $m)
      <li style="--s: {{ $warnaGaleri[$m->status] ?? '#6b7a87' }}">
        <img src="{{ $m->fotoUrl() }}" alt="Foto {{ $m->merk_motor }}" loading="lazy" width="440" height="330">
        <div class="isi-kartu">
          <span class="plat kecil">{{ $m->platSamar() }}</span>
          <h3>{{ $m->merk_motor }}</h3>
          <span class="status-kecil">{{ $m->labelStatus() }}</span>
        </div>
      </li>
    @endforeach
  </ul>
</section>
@endif
@endisset

<section class="bagian reveal">
  <h2>Cara kerjanya</h2>
  <ol class="langkah">
    <li><em>01</em><h3>Motor masuk bengkel</h3><p>Admin mencatat motor Anda dan memberikan kode servis.</p></li>
    <li><em>02</em><h3>Pantau dari mana saja</h3><p>Masukkan kode di atas. Status dan riwayat perbaikan tampil seketika.</p></li>
    <li><em>03</em><h3>Ambil saat selesai</h3><p>Status berubah menjadi "Selesai, siap diambil". Tinggal datang ke bengkel.</p></li>
  </ol>
</section>

<section class="bagian reveal">
  <h2>Arti setiap status</h2>
  <ul class="arti">
    <li style="--s:#6b7a87"><strong>Menunggu antrian</strong><span>Motor sudah tercatat dan menunggu giliran dikerjakan.</span></li>
    <li style="--s:#1f6fb2"><strong>Sedang dikerjakan</strong><span>Mekanik sedang mengerjakan motor Anda.</span></li>
    <li style="--s:#c97a0a"><strong>Menunggu sparepart</strong><span>Pengerjaan berhenti sementara sampai sparepart tiba.</span></li>
    <li style="--s:#2e9e6b"><strong>Selesai, siap diambil</strong><span>Perbaikan selesai. Motor siap diambil di bengkel.</span></li>
    <li style="--s:#4a5a68"><strong>Sudah diambil</strong><span>Motor sudah diambil pemiliknya.</span></li>
  </ul>
</section>

<section class="aman reveal">
  <div class="aman-isi">
    <h2>Data Anda dijaga.</h2>
    <div>
      <p>Plat nomor motor disimpan terenkripsi dan hanya tampil sebagian di halaman publik. Akses petugas dibatasi sesuai peran, dan setiap perubahan status tercatat.</p>
      <ul><li>AES-256</li><li>Bcrypt</li><li>Audit log</li></ul>
    </div>
  </div>
</section>

<footer>
  <div>
    <span>© {{ now()->year }} Bengkel Motor</span>
    <a href="{{ route('login') }}">Login petugas</a>
  </div>
</footer>

<script>
(function () {
  var kurang = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var elemen = document.querySelectorAll('.reveal');

  function hitung(el) {
    var akhir = parseInt(el.getAttribute('data-n'), 10) || 0;
    if (kurang || akhir === 0) { el.textContent = akhir; return; }
    var mulai = null, durasi = 900;
    function langkah(t) {
      if (mulai === null) mulai = t;
      var p = Math.min((t - mulai) / durasi, 1);
      el.textContent = Math.round(akhir * (1 - Math.pow(1 - p, 3)));
      if (p < 1) requestAnimationFrame(langkah);
    }
    el.textContent = '0';
    requestAnimationFrame(langkah);
  }

  function tampilkan(el) {
    el.classList.add('tampil');
    el.querySelectorAll('[data-n]').forEach(hitung);
  }

  if (kurang || !('IntersectionObserver' in window)) {
    elemen.forEach(function (el) { el.classList.add('tampil'); });
  } else {
    var io = new IntersectionObserver(function (daftar) {
      daftar.forEach(function (d) {
        if (d.isIntersecting) { tampilkan(d.target); io.unobserve(d.target); }
      });
    }, { threshold: 0.15 });
    elemen.forEach(function (el) { io.observe(el); });
  }
})();
</script>
@isset($servis)
<script>document.getElementById('hasil')?.scrollIntoView({block:'start'});</script>
<script>
  // Tutup hasil pengecekan: panel hilang, alamat kembali ke halaman awal, dan kolom kode siap dipakai lagi.
  (function () {
    var h = document.getElementById('hasil');
    if (!h) return;
    function tutup() {
      if (!document.body.contains(h)) return;
      h.remove();
      history.replaceState(null, '', @json(route('home')));   // refresh tidak lagi mengirim ulang form
      var f = document.getElementById('kode_servis');
      if (f) {
        f.value = '';
        var pelan = window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth';
        f.scrollIntoView({ block: 'center', behavior: pelan });
        f.focus({ preventScroll: true });
      }
    }
    h.querySelectorAll('[data-tutup]').forEach(function (b) { b.addEventListener('click', tutup); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') tutup(); });
  })();
</script>
@endisset
<a class="fab-wa" href="{{ $waUrl }}" target="_blank" rel="noopener" aria-label="Hubungi kami via WhatsApp" title="Hubungi kami via WhatsApp">@include('partials.ikon-wa', ['u' => 28])</a>
</body>
</html>
