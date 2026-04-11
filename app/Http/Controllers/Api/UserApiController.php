<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class UserApiController extends Controller
{
    public function index(): JsonResponse
    {
        $users = User::query()
            ->orderBy('name')
            ->get()
            ->map(fn (User $user) => $this->payload($user))
            ->values();

        return response()->json($users);
    }

    public function show(User $user): JsonResponse
    {
        return response()->json($this->payload($user));
    }

    private function payload(User $user): array
    {
        return [
            'id' => (string) $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'age' => $user->age,
            'gender' => $user->gender,
            'image' => $user->image,
            'status' => $user->status,
            'cognifit_registered' => filled($user->cognifit_user_token),
            'cognifit_locale' => $user->cognifit_locale,
            'cognifit_registered_at' => optional($user->cognifit_registered_at)->toISOString(),
            'created_at' => optional($user->created_at)->toISOString(),
        ];
    }
}
