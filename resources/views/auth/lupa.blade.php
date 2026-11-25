@extends('auth.kerangka')
@section('title', 'Lupa Password')
@section('isi')
  <h1>Lupa password?</h1>
  <p class="sapa">Isi email akun dan password baru yang Anda inginkan.</p>

  <form method="POST" action="{{ route('sandi.kirim') }}">
    @csrf
    <label for="email">Email</label>
    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
    @error('email')<div class="galat-masuk" role="alert">{{ $message }}</div>@enderror

    <label for="password">Password baru</label>
    <input id="password" type="password" name="password" autocomplete="new-password" required>
    <label for="password_confirmation">Ulangi password baru</label>
    <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" required>
    @include('partials.syarat-password', ['target' => 'password'])
    @error('password')<div class="galat-masuk" role="alert">{{ $message }}</div>@enderror

    <details class="khusus-owner" @if($errors->has('kunci') || old('kunci')) open @endif>
      <summary>Saya owner</summary>
      <label for="kunci">Kunci pemulihan owner</label>
      <input id="kunci" type="password" name="kunci" autocomplete="off">
      <small class="bantu">Owner langsung mengganti password tanpa persetujuan, asalkan kunci pemulihan benar. Kunci ada di file <code>.env</code> (OWNER_RECOVERY_KEY).</small>
    </details>

    <button class="btn" type="submit">Kirim permintaan</button>
  </form>

  <p class="catatan-lupa"><b>Admin dan teknisi:</b> password baru baru aktif setelah owner menyetujui permintaan ini. Anda tetap memakai password lama sampai saat itu.</p>
@endsection
