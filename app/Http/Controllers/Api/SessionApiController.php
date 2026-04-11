<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CognitiveSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SessionApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'status' => ['nullable', 'string'],
        ]);

        $sessions = CognitiveSession::query()
            ->when($validated['user_id'] ?? null, fn ($query, $userId) => $query->where('user_id', $userId))
            ->when($validated['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->latest('scheduled_for')
            ->latest()
            ->get()
            ->map(fn (CognitiveSession $session) => $this->payload($session))
            ->values();

        return response()->json($sessions);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'area' => ['required', 'string', 'max:80'],
            'game_key' => ['nullable', 'string', 'max:120'],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:240'],
            'scheduled_for' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'metadata' => ['nullable', 'array'],
        ]);

        $session = CognitiveSession::query()->create([
            ...$validated,
            'status' => 'pending',
        ]);

        return response()->json($this->payload($session), 201);
    }

    public function show(CognitiveSession $session): JsonResponse
    {
        return response()->json($this->payload($session));
    }

    public function update(Request $request, CognitiveSession $session): JsonResponse
    {
        $validated = $request->validate([
            'area' => ['sometimes', 'string', 'max:80'],
            'game_key' => ['sometimes', 'nullable', 'string', 'max:120'],
            'duration_minutes' => ['sometimes', 'integer', 'min:1', 'max:240'],
            'status' => ['sometimes', Rule::in(['pending', 'in_progress', 'completed', 'cancelled'])],
            'score' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:100'],
            'scheduled_for' => ['sometimes', 'nullable', 'date'],
            'started_at' => ['sometimes', 'nullable', 'date'],
            'completed_at' => ['sometimes', 'nullable', 'date'],
            'notes' => ['sometimes', 'nullable', 'string'],
            'metadata' => ['sometimes', 'nullable', 'array'],
        ]);

        $session->update($validated);

        return response()->json($this->payload($session->refresh()));
    }

    private function payload(CognitiveSession $session): array
    {
        return [
            'id' => (string) $session->id,
            'user_id' => (string) $session->user_id,
            'area' => $session->area,
            'game_key' => $session->game_key,
            'duration_minutes' => $session->duration_minutes,
            'status' => $session->status,
            'score' => $session->score === null ? null : (float) $session->score,
            'scheduled_for' => optional($session->scheduled_for)->toISOString(),
            'started_at' => optional($session->started_at)->toISOString(),
            'completed_at' => optional($session->completed_at)->toISOString(),
            'notes' => $session->notes,
            'metadata' => $session->metadata,
            'created_at' => optional($session->created_at)->toISOString(),
            'updated_at' => optional($session->updated_at)->toISOString(),
        ];
    }
}
