<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $q = AuditLog::with('user')->latest('created_at')->latest('id');

        if ($aksi = $request->query('aksi')) {
            $q->where('aksi', $aksi);
        }

        return view('admin.audit', ['daftar' => $q->paginate(25)->withQueryString()]);
    }
}
