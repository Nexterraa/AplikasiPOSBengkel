<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KasirAccountController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->input('search');

        $kasirs = User::where('role', 'kasir')
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.kasir.index', compact('kasirs', 'search'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ], [
            'name.required' => 'Nama kasir wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email tersebut telah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'kasir',
            'is_active' => true,
        ]);

        return back()->with('success', 'Akun kasir berhasil dibuat.');
    }

    public function update(Request $request, User $kasir): RedirectResponse
    {
        // Perlindungan agar tidak dapat mengedit/mengubah role Admin melalui endpoint ini
        if ($kasir->isAdmin()) {
            return back()->with('error', 'Akun Administrator tidak dapat dikelola melalui menu Kasir.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($kasir->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'is_active' => ['required', 'boolean'],
        ], [
            'name.required' => 'Nama kasir wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email tersebut telah terdaftar.',
            'password.min' => 'Password minimal 8 karakter.',
        ]);

        $kasir->name = $validated['name'];
        $kasir->email = $validated['email'];
        $kasir->is_active = $validated['is_active'];

        if (! empty($validated['password'])) {
            $kasir->password = Hash::make($validated['password']);
        }

        $kasir->save();

        return back()->with('success', 'Data akun kasir berhasil diperbarui.');
    }

    public function toggleStatus(User $kasir): RedirectResponse
    {
        if ($kasir->isAdmin()) {
            return back()->with('error', 'Akun Administrator tidak dapat dinonaktifkan.');
        }

        if ($kasir->id === Auth::id()) {
            return back()->with('error', 'Anda tidak dapat me-nonaktifkan akun Anda sendiri.');
        }

        $kasir->is_active = ! $kasir->is_active;
        $kasir->save();

        $statusText = $kasir->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Akun kasir berhasil {$statusText}.");
    }
}

