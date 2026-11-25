<ul class="syarat" id="syaratSandi" aria-live="polite">
  <li data-r="len">Minimal 8 karakter</li>
  <li data-r="huruf">Mengandung huruf</li>
  <li data-r="angka">Mengandung angka</li>
  <li data-r="simbol">Mengandung simbol khusus (! @ # $ % &amp; *)</li>
</ul>
<script>
  (function () {
    var i = document.getElementById(@json($target)), daftar = document.querySelectorAll('#syaratSandi li');
    if (!i) return;
    var aturan = {
      len:    function (v) { return v.length >= 8; },
      huruf:  function (v) { return /\p{L}/u.test(v); },
      angka:  function (v) { return /\p{N}/u.test(v); },
      simbol: function (v) { return /[^\p{L}\p{N}\s]/u.test(v); }
    };
    function perbarui() {
      daftar.forEach(function (li) { li.classList.toggle('ok', aturan[li.dataset.r](i.value)); });
    }
    i.addEventListener('input', perbarui); perbarui();
  })();
</script>
