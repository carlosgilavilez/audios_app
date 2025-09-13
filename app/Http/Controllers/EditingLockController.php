<?php

namespace App\Http\Controllers;

use App\Models\EditingLock;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class EditingLockController extends Controller
{
    private int $ttlSeconds = 90; // lock expires if no heartbeat in 90s

    private function ensureEditorOrAdmin()
    {
        if (!Auth::check() || !Gate::any(['admin','editor'])) {
            abort(403);
        }
    }

    public function acquire(Request $request)
    {
        $this->ensureEditorOrAdmin();
        $data = $request->validate([
            'type' => 'required|string',
            'id' => 'required|integer',
        ]);
        $user = Auth::user();

        $lock = EditingLock::where('lockable_type', $data['type'])
            ->where('lockable_id', $data['id'])
            ->first();

        // Clear expired locks
        if ($lock && $lock->isExpired()) {
            $lock->delete();
            $lock = null;
        }

        if ($lock && $lock->user_id !== $user->id) {
            $owner = User::find($lock->user_id);
            return response()->json([
                'locked' => true,
                'by' => $owner?->name,
                'user_id' => $owner?->id,
                'until' => $lock->expires_at?->toIso8601String(),
            ], 423);
        }

        // Create or renew lock for this user
        $expiresAt = Carbon::now()->addSeconds($this->ttlSeconds);
        $lock = EditingLock::updateOrCreate(
            [
                'lockable_type' => $data['type'],
                'lockable_id' => $data['id'],
            ],
            [
                'user_id' => $user->id,
                'expires_at' => $expiresAt,
            ]
        );

        return response()->json([
            'locked' => false,
            'owner_id' => $lock->user_id,
            'until' => $lock->expires_at->toIso8601String(),
        ]);
    }

    public function heartbeat(Request $request)
    {
        $this->ensureEditorOrAdmin();
        $data = $request->validate([
            'type' => 'required|string',
            'id' => 'required|integer',
        ]);
        $user = Auth::user();

        $lock = EditingLock::where('lockable_type', $data['type'])
            ->where('lockable_id', $data['id'])
            ->first();

        if (!$lock || $lock->user_id !== $user->id) {
            return response()->json(['ok' => false], 409);
        }

        $lock->expires_at = Carbon::now()->addSeconds($this->ttlSeconds);
        $lock->save();

        return response()->json(['ok' => true]);
    }

    public function release(Request $request)
    {
        $this->ensureEditorOrAdmin();
        $data = $request->validate([
            'type' => 'required|string',
            'id' => 'required|integer',
        ]);
        $user = Auth::user();

        $lock = EditingLock::where('lockable_type', $data['type'])
            ->where('lockable_id', $data['id'])
            ->first();

        if ($lock && $lock->user_id === $user->id) {
            $lock->delete();
        }

        return response()->json(['ok' => true]);
    }

    public function status(Request $request)
    {
        $this->ensureEditorOrAdmin();
        $data = $request->validate([
            'type' => 'required|string',
            'id' => 'required|integer',
        ]);

        $lock = EditingLock::where('lockable_type', $data['type'])
            ->where('lockable_id', $data['id'])
            ->first();

        if (!$lock || $lock->isExpired()) {
            return response()->json(['locked' => false]);
        }

        return response()->json([
            'locked' => true,
            'by' => $lock->user?->name,
            'user_id' => $lock->user_id,
            'until' => $lock->expires_at->toIso8601String(),
        ]);
    }
}

