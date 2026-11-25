<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Nota {{ $servis->kode_servis }}</title>
<style>
@page{size:A5;margin:10mm}
*{box-sizing:border-box}
body{margin:0;font-family:system-ui,'Segoe UI',sans-serif;background:#e9ecef;color:#111;line-height:1.45}
.alat{max-width:148mm;margin:1rem auto 0;display:flex;gap:.6rem;flex-wrap:wrap}
.alat button,.alat a{padding:.55rem 1rem;border:0;border-radius:6px;font:inherit;cursor:pointer;text-decoration:none;background:#d9480f;color:#fff}
.alat a{background:#555}
.nota{width:148mm;max-width:100%;margin:1rem auto;background:#fff;padding:10mm;border:1px solid #cfd6db}
.kop{display:flex;justify-content:space-between;align-items:flex-start;border-bottom:3px solid #111;padding-bottom:.6rem;margin-bottom:1rem}
.kop h1{margin:0;font-size:1.4rem}.kop small{color:#555}
.kode{text-align:right}.kode b{display:block;font-size:1.35rem;letter-spacing:.06em}
table{width:100%;border-collapse:collapse;font-size:.95rem}
td{padding:.35rem 0;vertical-align:top;border-bottom:1px dotted #bbb}
td:first-child{width:34%;color:#555}
.cek{display:flex;gap:1rem;align-items:center;margin-top:1.2rem;padding:.8rem;border:2px dashed #111;border-radius:6px}
#qr{flex:none;width:130px;height:130px}
.cek p{margin:0 0 .3rem;font-size:.9rem}.cek code{font-size:.78rem;word-break:break-all}
.kaki{margin-top:1.2rem;font-size:.8rem;color:#555;text-align:center}
@media print{body{background:#fff}.alat{display:none}.nota{margin:0;border:0;width:auto;padding:0}}
</style>
</head>
<body>
<div class="alat">
  <button onclick="window.print()">Cetak / Simpan PDF</button>
  <a href="{{ route('admin.servis.show', $servis) }}">Kembali</a>
</div>

<div class="nota">
  <div class="kop">
    <div><h1>🔧 Bengkel Motor</h1><small>Nota Servis</small></div>
    <div class="kode"><small>Kode servis</small><b>{{ $servis->kode_servis }}</b></div>
  </div>

  <table>
    <tr><td>Tanggal masuk</td><td>{{ $servis->tanggal_masuk->locale('id')->translatedFormat('d F Y') }}</td></tr>
    <tr><td>Pelanggan</td><td>{{ $servis->nama_pelanggan }}@if($servis->no_hp) ({{ $servis->no_hp }})@endif</td></tr>
    <tr><td>Motor</td><td>{{ $servis->merk_motor }}</td></tr>
    <tr><td>Plat nomor</td><td>{{ $servis->plat_nomor }}</td></tr>
    <tr><td>Keluhan</td><td>{{ $servis->keluhan }}</td></tr>
    <tr><td>Status saat dicetak</td><td>{{ $servis->labelStatus() }}</td></tr>
  </table>

  <div class="cek">
    <div id="qr"></div>
    <div>
      <p><strong>Pantau perbaikan motor Anda.</strong> Scan QR ini atau buka alamat berikut.</p>
      <code>{{ $url }}</code>
    </div>
  </div>

  <div class="kaki">Simpan nota ini. Tunjukkan saat mengambil motor.</div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
  try {
    new QRCode(document.getElementById('qr'), {
      text: @json($url), width: 130, height: 130, correctLevel: QRCode.CorrectLevel.M
    });
  } catch (e) {
    document.getElementById('qr').textContent = 'QR tidak dapat dimuat (periksa koneksi internet).';
  }
</script>
</body>
</html>
