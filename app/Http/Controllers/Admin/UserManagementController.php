<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\Registered;

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
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make(Str::random(20)),
            'role'     => 'editor',
        ]);

        // Si el modelo User implementa MustVerifyEmail, esto enviará verificación
        event(new Registered($user));

        // Enviar enlace de restablecimiento (para que establezca su contraseña)
        try {
            $status = Password::broker()->sendResetLink([
                'email' => $user->email,
            ]);

            if ($status !== Password::RESET_LINK_SENT) {
                \Log::error('Password reset link NOT sent', [
                    'status' => $status,
                    'email'  => $user->email,
                ]);
                return redirect()->route('admin.users.index')
                    ->with('error', 'No se pudo enviar el email de restablecimiento (' . $status . '). Revisa la configuración SMTP o inténtalo nuevamente.');
            }
        } catch (\Throwable $e) {
            \Log::error('Error enviando email de restablecimiento', [
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);
            return redirect()->route('admin.users.index')
                ->with('error', 'Error al enviar el email: ' . $e->getMessage());
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario editor creado. Se ha enviado un correo para establecer su contraseña.');
    }

    public function destroy(User $user)
    {
        // Prevent self-deletion
        if (auth()->id() === $user->id) {
            return back()->with('error', 'No puedes eliminar tu propia cuenta.');
        }

        // Prevent admin from deleting another admin
        if ($user->role === 'admin') {
            return back()->with('error', 'No puedes eliminar una cuenta de administrador.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Usuario eliminado exitosamente.');
    }
}

