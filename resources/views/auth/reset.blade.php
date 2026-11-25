@extends('auth.kerangka')
@section('title', 'Atur Ulang Password')
@section('isi')
  <h1>Atur ulang password</h1>
  <p class="sapa">Kode dikirim ke <b>{{ $email }}</b>.</p>

  @if(session('info'))<div class="info-masuk" role="status">{{ session('info') }}</div>@endif

  <form method="POST" action="{{ route('sandi.proses') }}">
    @csrf
    <label for="kode">Kode verifikasi (6 angka)</label>
    <input id="kode" class="kode-input" name="kode" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code" placeholder="000000" required autofocus>
    @error('kode')<div class="galat-masuk" role="alert">{{ $message }}</div>@enderror

    <label for="password">Password baru</label>
    <input id="password" type="password" name="password" autocomplete="new-password" required>
    <label for="password_confirmation">Ulangi password baru</label>
    <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" required>
    <small class="bantu">Minimal 8 karakter, mengandung huruf dan angka.</small>
    @error('password')<div class="galat-masuk" role="alert">{{ $message }}</div>@enderror

    <button class="btn" type="submit">Simpan password baru</button>
  </form>
  <a class="tautan-kecil" href="{{ route('sandi.lupa') }}">Belum menerima kode? Kirim ulang</a>
@endsection
