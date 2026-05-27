<?php

namespace App\Http\Controllers;

use App\Models\CognitiveSkillScore;
use App\Models\OperationalAlert;
use App\Models\OperationalGroup;
use App\Models\OperationalMetricSnapshot;
use App\Models\SecurityUnit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class AdminMetricsController extends Controller
{
    private const CATEGORIES = [
        'atencion_sostenida' => 'Atencion sostenida',
        'atencion_dividida' => 'Atencion dividida',
        'tiempo_reaccion' => 'Tiempo de reaccion',
        'memoria_trabajo' => 'Memoria de trabajo',
        'control_inhibitorio' => 'Control inhibitorio',
        'toma_decisiones' => 'Toma de decisiones',
        'flexibilidad_cognitiva' => 'Flexibilidad cognitiva',
        'carga_mental' => 'Tolerancia a carga mental',
    ];

    public function index(Request $request)
    {
        $title = 'Metricas operativas';
        $filters = $request->only(['security_unit_id', 'operational_group_id', 'category', 'user_id']);

        $units = SecurityUnit::query()->orderBy('name')->get();
        $groups = OperationalGroup::query()->with('unit')->orderBy('name')->get();
        $users = User::query()->with(['securityUnit', 'operationalGroup'])->orderBy('name')->get();

        $metrics = $this->filteredMetrics($filters)->get();

        if ($metrics->isEmpty()) {
            $metrics = $this->fallbackMetrics($filters);
        }

        $activeAlerts = $this->filteredAlerts($filters)
            ->whereNull('resolved_at')
            ->latest('detected_at')
            ->limit(12)
            ->get();

        $summary = $this->summary($users, $metrics, $activeAlerts, $filters);
        $categoryAverages = $this->categoryAverages($metrics);
        $unitAverages = $this->unitAverages($metrics);
        $groupAverages = $this->groupAverages($metrics);
        $elementRanking = $this->elementRanking($metrics);
        $riskElements = $elementRanking->filter(fn ($row) => $row['score'] < 60)->values();

        return view('admin.metrics.index', compact(
            'title',
            'filters',
            'units',
            'groups',
            'users',
            'summary',
            'categoryAverages',
            'unitAverages',
            'groupAverages',
            'elementRanking',
            'riskElements',
            'activeAlerts'
        ))->with('categories', self::CATEGORIES);
    }

    public function user(User $user)
    {
        $title = 'Metricas del elemento';
        $user->load(['securityUnit', 'operationalGroup']);

        $metrics = OperationalMetricSnapshot::query()
            ->where('user_id', $user->id)
            ->orderByDesc('measured_at')
            ->get();

        if ($metrics->isEmpty()) {
            $metrics = $this->fallbackMetrics(['user_id' => $user->id]);
        }

        $categoryAverages = $this->categoryAverages($metrics);
        $operationalIndex = round((float) $metrics->avg('score'), 2);
        $alerts = OperationalAlert::query()
            ->where('user_id', $user->id)
            ->latest('detected_at')
            ->get();

        return view('admin.metrics.user', compact(
            'title',
            'user',
            'metrics',
            'categoryAverages',
            'operationalIndex',
            'alerts'
        ))->with('categories', self::CATEGORIES);
    }

    public function storeUserMetric(Request $request, User $user)
    {
        $validated = $request->validate([
            'category' => ['required', 'string', 'max:120'],
            'metric_name' => ['nullable', 'string', 'max:160'],
            'score' => ['required', 'numeric', 'min:0', 'max:100'],
            'trend' => ['nullable', 'string', 'max:40'],
            'measured_at' => ['nullable', 'date'],
        ]);

        OperationalMetricSnapshot::create([
            'user_id' => $user->id,
            'security_unit_id' => $user->security_unit_id,
            'operational_group_id' => $user->operational_group_id,
            'category' => $validated['category'],
            'metric_name' => $validated['metric_name'] ?: (self::CATEGORIES[$validated['category']] ?? $validated['category']),
            'score' => $validated['score'],
            'level' => $this->levelFor((float) $validated['score']),
            'trend' => $validated['trend'] ?? 'stable',
            'source' => 'manual',
            'measured_at' => $validated['measured_at'] ?? now(),
        ]);

        return redirect()
            ->route('admin.metrics.user', $user)
            ->with('success', 'Metrica registrada correctamente.');
    }

    private function filteredMetrics(array $filters)
    {
        return OperationalMetricSnapshot::query()
            ->with(['user.securityUnit', 'user.operationalGroup', 'unit', 'group'])
            ->when($filters['security_unit_id'] ?? null, fn ($query, $id) => $query->where('security_unit_id', $id))
            ->when($filters['operational_group_id'] ?? null, fn ($query, $id) => $query->where('operational_group_id', $id))
            ->when($filters['category'] ?? null, fn ($query, $category) => $query->where('category', $category))
            ->when($filters['user_id'] ?? null, fn ($query, $id) => $query->where('user_id', $id))
            ->latest('measured_at');
    }

    private function filteredAlerts(array $filters)
    {
        return OperationalAlert::query()
            ->with(['user', 'unit', 'group'])
            ->when($filters['security_unit_id'] ?? null, fn ($query, $id) => $query->where('security_unit_id', $id))
            ->when($filters['operational_group_id'] ?? null, fn ($query, $id) => $query->where('operational_group_id', $id))
            ->when($filters['category'] ?? null, fn ($query, $category) => $query->where('category', $category))
            ->when($filters['user_id'] ?? null, fn ($query, $id) => $query->where('user_id', $id));
    }

    private function fallbackMetrics(array $filters): Collection
    {
        $scores = CognitiveSkillScore::query()
            ->with('user.securityUnit', 'user.operationalGroup')
            ->when($filters['user_id'] ?? null, fn ($query, $id) => $query->where('user_id', $id))
            ->when($filters['category'] ?? null, fn ($query, $category) => $query->where('name', self::CATEGORIES[$category] ?? $category))
            ->latest('measured_at')
            ->get();

        return $scores
            ->filter(function (CognitiveSkillScore $score) use ($filters) {
                if (($filters['security_unit_id'] ?? null) && (int) $score->user?->security_unit_id !== (int) $filters['security_unit_id']) {
                    return false;
                }

                if (($filters['operational_group_id'] ?? null) && (int) $score->user?->operational_group_id !== (int) $filters['operational_group_id']) {
                    return false;
                }

                return true;
            })
            ->map(function (CognitiveSkillScore $score) {
                $category = $this->categoryKey($score->name);

                return (object) [
                    'user_id' => $score->user_id,
                    'security_unit_id' => $score->user?->security_unit_id,
                    'operational_group_id' => $score->user?->operational_group_id,
                    'category' => $category,
                    'metric_name' => self::CATEGORIES[$category] ?? $score->name,
                    'score' => (float) $score->score,
                    'level' => $this->levelFor((float) $score->score),
                    'trend' => $score->trend,
                    'source' => 'skill_score',
                    'measured_at' => $score->measured_at,
                    'user' => $score->user,
                    'unit' => $score->user?->securityUnit,
                    'group' => $score->user?->operationalGroup,
                ];
            });
    }

    private function summary(Collection $users, Collection $metrics, Collection $alerts, array $filters): array
    {
        $filteredUsers = $users
            ->when($filters['security_unit_id'] ?? null, fn ($items, $id) => $items->where('security_unit_id', (int) $id))
            ->when($filters['operational_group_id'] ?? null, fn ($items, $id) => $items->where('operational_group_id', (int) $id));

        return [
            'elements' => $filteredUsers->count(),
            'evaluated' => $metrics->pluck('user_id')->unique()->count(),
            'operational_index' => round((float) $metrics->avg('score'), 2),
            'active_alerts' => $alerts->count(),
            'reinforcement_required' => $metrics->where('score', '<', 60)->pluck('user_id')->unique()->count(),
        ];
    }

    private function categoryAverages(Collection $metrics): Collection
    {
        return $metrics
            ->groupBy('category')
            ->map(fn ($items, $category) => [
                'category' => self::CATEGORIES[$category] ?? $category,
                'score' => round((float) $items->avg('score'), 2),
                'level' => $this->levelFor((float) $items->avg('score')),
                'count' => $items->count(),
            ])
            ->sortBy('score')
            ->values();
    }

    private function unitAverages(Collection $metrics): Collection
    {
        return $metrics
            ->groupBy(fn ($metric) => $metric->unit?->name ?? $metric->user?->securityUnit?->name ?? 'Sin unidad')
            ->map(fn ($items, $name) => [
                'name' => $name,
                'score' => round((float) $items->avg('score'), 2),
                'elements' => $items->pluck('user_id')->unique()->count(),
            ])
            ->sortByDesc('score')
            ->values();
    }

    private function groupAverages(Collection $metrics): Collection
    {
        return $metrics
            ->groupBy(fn ($metric) => $metric->group?->name ?? $metric->user?->operationalGroup?->name ?? 'Sin grupo')
            ->map(fn ($items, $name) => [
                'name' => $name,
                'score' => round((float) $items->avg('score'), 2),
                'elements' => $items->pluck('user_id')->unique()->count(),
            ])
            ->sortByDesc('score')
            ->values();
    }

    private function elementRanking(Collection $metrics): Collection
    {
        return $metrics
            ->groupBy('user_id')
            ->map(function ($items) {
                $user = $items->first()->user;

                return [
                    'id' => $user?->id,
                    'name' => $user?->name ?? 'Elemento sin nombre',
                    'badge_number' => $user?->badge_number,
                    'unit' => $user?->securityUnit?->name ?? 'Sin unidad',
                    'group' => $user?->operationalGroup?->name ?? 'Sin grupo',
                    'score' => round((float) $items->avg('score'), 2),
                    'level' => $this->levelFor((float) $items->avg('score')),
                ];
            })
            ->sortByDesc('score')
            ->values();
    }

    private function categoryKey(string $name): string
    {
        $normalized = str($name)->lower()->ascii()->replace([' ', '-'], '_')->toString();

        return match (true) {
            str_contains($normalized, 'memoria') => 'memoria_trabajo',
            str_contains($normalized, 'reaccion'), str_contains($normalized, 'velocidad') => 'tiempo_reaccion',
            str_contains($normalized, 'decision') => 'toma_decisiones',
            str_contains($normalized, 'flexibilidad') => 'flexibilidad_cognitiva',
            str_contains($normalized, 'inhibitorio'), str_contains($normalized, 'control') => 'control_inhibitorio',
            str_contains($normalized, 'dividida') => 'atencion_dividida',
            str_contains($normalized, 'carga'), str_contains($normalized, 'estres') => 'carga_mental',
            default => 'atencion_sostenida',
        };
    }

    private function levelFor(float $score): string
    {
        return match (true) {
            $score >= 85 => 'optimo',
            $score >= 70 => 'adecuado',
            $score >= 60 => 'seguimiento',
            $score >= 45 => 'refuerzo',
            default => 'alerta',
        };
    }
}
