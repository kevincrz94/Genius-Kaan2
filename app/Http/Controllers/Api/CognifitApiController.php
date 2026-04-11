<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\CognifitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class CognifitApiController extends Controller
{
    public function __construct(private readonly CognifitService $cognifit)
    {
    }

    public function status(): JsonResponse
    {
        return response()->json([
            'configured' => $this->cognifit->configured(),
            'client_id_present' => filled($this->cognifit->clientId()),
        ]);
    }

    public function registerUser(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'locale' => ['nullable', 'string', 'max:8'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        try {
            $token = $this->cognifit->registerUser(
                user: $user,
                locale: $validated['locale'] ?? 'es',
                password: $validated['password'] ?? null,
            );
        } catch (RuntimeException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json([
            'user_id' => (string) $user->id,
            'cognifit_user_token' => $token,
            'locale' => $user->fresh()->cognifit_locale,
            'registered_at' => optional($user->fresh()->cognifit_registered_at)->toISOString(),
        ]);
    }

    public function updateLocale(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'locale' => ['required', 'string', 'max:8'],
        ]);

        try {
            $this->cognifit->updateLocale($user, $validated['locale']);
        } catch (RuntimeException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json([
            'user_id' => (string) $user->id,
            'locale' => $user->fresh()->cognifit_locale,
        ]);
    }

    public function launch(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'game_key' => ['required', 'string', 'max:120'],
            'goal' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $payload = $this->cognifit->launchPayload(
                user: $user,
                gameKey: $validated['game_key'],
                goal: $validated['goal'] ?? null,
            );
        } catch (RuntimeException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json($payload);
    }

    public function scores(User $user): JsonResponse
    {
        try {
            $scores = $this->cognifit->historicalScores($user);
        } catch (RuntimeException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json($scores);
    }

    public function playedGames(User $user): JsonResponse
    {
        try {
            $games = $this->cognifit->playedGames($user);
        } catch (RuntimeException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json($games);
    }
}
