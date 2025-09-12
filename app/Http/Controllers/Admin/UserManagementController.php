<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Password;

class UserManagementController extends Controller
{
    public function index()
    {
        $users = User::orderBy('role')->orderBy('name')->get();
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make(Str::random(20)), // Create a secure temporary password
            'role' => 'editor',
        ]);

        event(new Registered($user)); // This will trigger the email verification notification

        // Send the password reset link
        Password::broker()->sendResetLink(
            $request->only('email')
        );

        return redirect()->route('admin.users.index')->with('success', 'Usuario editor creado. Se ha enviado un correo para verificar su email y establecer una contraseña.');
    }
}
