<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Servis;
use App\Support\FotoMotor;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ServisController extends Controller
{
    /** $servis diisi saat mengubah data, agar plat milik servis itu sendiri tidak dianggap kembar. */
    private function rules(?Servis $servis = null): array
    {
        return [
            'nama_pelanggan' => 'required|string|max:100',
            'no_hp'          => 'nullable|string|max:20',
            'plat_nomor'     => ['required', 'string', 'max:15', function (string $attribute, mixed $value, \Closure $fail) use ($servis) {
                // Dibandingkan lewat hash karena plat disimpan terenkripsi. "h 1234 ab" dan "H1234AB" dianggap sama.
                // Plat hanya dianggap kembar bila motor itu MASIH AKTIF di bengkel (belum "Sudah diambil").
                // Motor langganan yang sudah diambil boleh masuk lagi dengan servis baru.
                $ada = Servis::where('plat_nomor_hash', Servis::hashPlat((string) $value))
                    ->where('status', '!=', 'diambil')
                    ->when($servis, fn ($q) => $q->where('id', '!=', $servis->id))
                    ->first();

                if ($ada) {
                    $fail($this->pesanKembar($ada));
                }
            }],
            'merk_motor'     => 'required|string|max:100',
            'keluhan'        => 'required|string',
            'status'         => ['required', Rule::in(array_keys(Servis::STATUS))],
            'catatan'        => 'nullable|string',
            'biaya'          => 'nullable|integer|min:0|max:999999999',   // total biaya perbaikan (Rp)
            'tanggal_masuk'  => 'required|date',
            'foto'           => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',   // maks 5 MB
            'hapus_foto'     => 'nullable|boolean',
        ];
    }

    private function pesanKembar(Servis $ada): string
    {
        return 'Motor dengan plat nomor ini masih ada di bengkel (servis ' . $ada->kode_servis . ', status: ' . $ada->labelStatus() . '). Selesaikan dan ubah ke "Sudah diambil" dulu sebelum mencatat servis baru.';
    }

    /** Dipanggil saat mengetik plat di form: memberi tahu langsung bila plat sudah dipakai motor lain. */
    public function cekPlat(Request $request)
    {
        $plat = trim((string) $request->query('plat', ''));
        if ($plat === '' || mb_strlen($plat) > 15) {
            return response()->json(['ada' => false, 'pesan' => '']);
        }

        $ada = Servis::where('plat_nomor_hash', Servis::hashPlat($plat))
            ->where('status', '!=', 'diambil')
            ->when($request->query('abaikan'), fn ($q, $id) => $q->where('id', '!=', (int) $id))
            ->first();

        return response()->json(['ada' => (bool) $ada, 'pesan' => $ada ? $this->pesanKembar($ada) : '']);
    }

    /** Simpan/ganti/hapus foto, lalu kembalikan data yang aman untuk disimpan ke model. */
    private function urusFoto(Request $request, array $data, ?Servis $servis = null): array
    {
        unset($data['foto'], $data['hapus_foto']);

        if ($request->hasFile('foto')) {
            $data['foto'] = FotoMotor::simpan($request->file('foto'));
            FotoMotor::hapus($servis?->foto);               // buang foto lama yang diganti
        } elseif ($servis && $request->boolean('hapus_foto')) {
            FotoMotor::hapus($servis->foto);
            $data['foto'] = null;
        }

        return $data;
    }

    public function index(Request $request)
    {
        $q = Servis::query()->latest();

        if ($status = $request->query('status')) {
            $q->where('status', $status);
        }
        if ($cari = trim((string) $request->query('cari'))) {
            $q->where(fn ($w) => $w->where('kode_servis', 'like', "%$cari%")
                ->orWhere('nama_pelanggan', 'like', "%$cari%")
                ->orWhere('plat_nomor_hash', Servis::hashPlat($cari)));
        }

        $hitung = Servis::select('status', DB::raw('count(*) as n'))->groupBy('status')->pluck('n', 'status');

        return view('admin.index', ['daftar' => $q->paginate(15)->withQueryString(), 'hitung' => $hitung]);
    }

    public function show(Servis $servis)
    {
        $servis->load('riwayat.user');
        // Servis lain untuk plat yang sama (kunjungan sebelumnya/sesudahnya)
        $kunjungan = Servis::where('plat_nomor_hash', $servis->plat_nomor_hash)
            ->where('id', '!=', $servis->id)->orderByDesc('tanggal_masuk')->orderByDesc('id')->get();
        return view('admin.show', compact('servis', 'kunjungan'));
    }


    public function create()
    {
        return view('admin.form', ['servis' => new Servis(['tanggal_masuk' => now(), 'status' => 'menunggu'])]);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());
        $servis = Servis::create($this->urusFoto($request, $data));
        return redirect()->route('admin.servis.index')
            ->with('ok', "Motor masuk dicatat. Kode servis: {$servis->kode_servis}");
    }

    public function edit(Servis $servis)
    {
        return view('admin.form', compact('servis'));
    }

    public function update(Request $request, Servis $servis)
    {
        $rules = $this->rules($servis);

        // Admin hanya boleh memperbarui status & catatan.
        // Owner boleh mengubah seluruh data.
        if ($request->user()->role !== 'owner') {
            $rules = Arr::only($rules, ['status', 'catatan', 'biaya', 'foto', 'hapus_foto']);
        }

        // validate() hanya mengembalikan field yang ada di $rules,
        // jadi field lain yang dikirim paksa akan diabaikan.
        $data = $request->validate($rules);
        $servis->update($this->urusFoto($request, $data, $servis));

        return redirect()->route('admin.servis.index')->with('ok', 'Data servis diperbarui.');
    }

    public function destroy(Servis $servis)
    {
        $servis->delete();
        return redirect()->route('admin.servis.index')->with('ok', 'Data servis dihapus.');
    }
}
