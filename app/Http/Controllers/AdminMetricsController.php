<?php

namespace App\Http\Controllers;

use App\Models\CognitiveSkillScore;
use App\Models\OperationalAlert;
use App\Models\OperationalGroup;
use App\Models\OperationalMetricSnapshot;
use App\Models\SecurityUnit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class AdminMetricsController extends Controller
{
    private const CATEGORIES = [
        'atencion_sostenida' => 'Atención sostenida',
        'atencion_dividida' => 'Atención dividida',
        'tiempo_reaccion' => 'Tiempo de reacción',
        'memoria_trabajo' => 'Memoria de trabajo',
        'control_inhibitorio' => 'Control inhibitorio',
        'toma_decisiones' => 'Toma de decisiones',
        'flexibilidad_cognitiva' => 'Flexibilidad cognitiva',
        'carga_mental' => 'Tolerancia a carga mental',
    ];

    public function index(Request $request)
    {
        $title = 'Métricas operativas';
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
        $title = 'Métricas del elemento';
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

    public function comparative(Request $request)
    {
        $title = 'Análisis de brechas cognitivas';
        $users = User::query()
            ->with(['securityUnit', 'operationalGroup'])
            ->where('role', 'user')
            ->orderBy('name')
            ->get();

        $selectedUser = $request->filled('user_id')
            ? $users->firstWhere('id', (int) $request->input('user_id'))
            : $users->first();

        $comparison = $request->string('comparison')->trim()->value() ?: 'unit';

        $userMetrics = $selectedUser
            ? $this->metricsForUser($selectedUser)
            : collect();
        $baselineMetrics = $selectedUser
            ? $this->baselineMetrics($selectedUser, $comparison)
            : collect();

        $userScores = $this->categoryScores($userMetrics);
        $baselineScores = $comparison === 'optimal'
            ? array_fill_keys(array_keys(self::CATEGORIES), 85)
            : $this->categoryScores($baselineMetrics);

        $radarLabels = array_values(self::CATEGORIES);
        $radarUserData = array_map(fn ($key) => $userScores[$key] ?? 0, array_keys(self::CATEGORIES));
        $radarBaselineData = array_map(fn ($key) => $baselineScores[$key] ?? 0, array_keys(self::CATEGORIES));
        $trendData = $this->timelineScores($userMetrics);
        $insights = $this->tacticalInsights($userScores, $baselineScores, $selectedUser);

        return view('admin.metrics.comparative', compact(
            'title',
            'users',
            'selectedUser',
            'comparison',
            'radarLabels',
            'radarUserData',
            'radarBaselineData',
            'trendData',
            'insights'
        ))->with([
            'categories' => self::CATEGORIES,
            'comparisonLabel' => $this->comparisonLabel($selectedUser, $comparison),
        ]);
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
            ->with('success', 'Métrica registrada correctamente.');
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

    private function metricsForUser(User $user): Collection
    {
        $metrics = OperationalMetricSnapshot::query()
            ->with(['user.securityUnit', 'user.operationalGroup', 'unit', 'group'])
            ->where('user_id', $user->id)
            ->latest('measured_at')
            ->get();

        return $metrics->isNotEmpty()
            ? $metrics
            : $this->fallbackMetrics(['user_id' => $user->id]);
    }

    private function baselineMetrics(User $user, string $comparison): Collection
    {
        if ($comparison === 'optimal') {
            return collect();
        }

        $filters = match ($comparison) {
            'group' => ['operational_group_id' => $user->operational_group_id],
            'global' => [],
            default => ['security_unit_id' => $user->security_unit_id],
        };

        $metrics = $this->filteredMetrics(array_filter($filters))->get();

        return $metrics->isNotEmpty()
            ? $metrics
            : $this->fallbackMetrics(array_filter($filters));
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

    private function categoryScores(Collection $metrics): array
    {
        return $metrics
            ->groupBy('category')
            ->map(fn ($items) => round((float) $items->avg('score'), 2))
            ->all();
    }

    private function timelineScores(Collection $metrics): array
    {
        $months = collect(range(5, 0))
            ->map(fn ($offset) => now()->subMonths($offset)->format('Y-m'));

        $scores = $metrics
            ->filter(fn ($metric) => filled($metric->measured_at))
            ->groupBy(fn ($metric) => Carbon::parse($metric->measured_at)->format('Y-m'))
            ->map(fn ($items) => round((float) $items->avg('score'), 2));

        return [
            'labels' => $months->map(fn ($month) => Carbon::createFromFormat('Y-m', $month)->format('M Y'))->values()->all(),
            'scores' => $months->map(fn ($month) => $scores[$month] ?? null)->values()->all(),
        ];
    }

    private function comparisonLabel(?User $user, string $comparison): string
    {
        return match ($comparison) {
            'group' => 'Promedio del grupo '.($user?->operationalGroup?->name ?: 'operativo'),
            'global' => 'Promedio global de la agencia',
            'optimal' => 'Estándar óptimo requerido',
            default => 'Promedio de la unidad '.($user?->securityUnit?->name ?: 'operativa'),
        };
    }

    private function tacticalInsights(array $userScores, array $baselineScores, ?User $user): array
    {
        $diffs = collect(self::CATEGORIES)
            ->map(function ($label, $key) use ($userScores, $baselineScores) {
                return [
                    'key' => $key,
                    'label' => $label,
                    'diff' => round(($userScores[$key] ?? 0) - ($baselineScores[$key] ?? 0), 2),
                ];
            });

        $weakness = $diffs->sortBy('diff')->first();
        $strength = $diffs->sortByDesc('diff')->first();
        $riskLevel = ($weakness['diff'] ?? 0) <= -15 ? 'alto' : ((($weakness['diff'] ?? 0) <= -8) ? 'moderado' : 'controlado');

        return [
            'weakness' => $weakness,
            'strength' => $strength,
            'risk_level' => $riskLevel,
            'recommendation' => $riskLevel === 'alto'
                ? 'Programar refuerzo cognitivo antes de asignaciones de alto impacto.'
                : 'Mantener seguimiento periódico y entrenamiento focalizado en la categoría con mayor brecha.',
            'assignment' => $user?->assignment_area ?: 'No especificada',
        ];
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
            $score >= 85 => 'óptimo',
            $score >= 70 => 'adecuado',
            $score >= 60 => 'seguimiento',
            $score >= 45 => 'refuerzo',
            default => 'alerta',
        };
    }
}
