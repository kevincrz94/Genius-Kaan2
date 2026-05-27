<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class UserImport implements ToCollection
{
    public $data = [];

    /**
     * Handle the collection.
     */
    public function collection(Collection $collection)
    {
        $rows = $this->hasHeaderRow($collection)
            ? $collection->slice(1)->values()
            : $collection->values();

        foreach ($rows as $row) {

            $record = [
                'name' => $this->cell($row, 0),
                'email' => $this->cell($row, 1),
                'password' => $this->cell($row, 2),
                'age' => $this->cell($row, 3),
                'gender' => $this->normalizeGender($this->cell($row, 4)),
                'badge_number' => $this->cell($row, 5),
                'rank' => $this->cell($row, 6),
                'security_unit' => $this->cell($row, 7),
                'operational_group' => $this->cell($row, 8),
                'assignment_area' => $this->cell($row, 9),
            ];

            if (! $this->hasContent($record)) {
                continue;
            }

            $this->data[] = $record;
        }
    }

    public function getData()
    {
        return $this->data;
    }

    private function normalizeGender($value): ?string
    {
        $normalized = $this->normalizeText($value);

        return match ($normalized) {
            'm', 'masc', 'masculino', 'hombre', 'male' => 'male',
            'f', 'fem', 'femenino', 'mujer', 'female' => 'female',
            'o', 'otro', 'otra', 'other' => 'other',
            default => $normalized ?: null,
        };
    }

    private function cell($row, int $index): ?string
    {
        $value = $row[$index] ?? null;

        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function hasContent(array $record): bool
    {
        foreach ($record as $value) {
            if ($value !== null && trim((string) $value) !== '') {
                return true;
            }
        }

        return false;
    }

    private function hasHeaderRow(Collection $collection): bool
    {
        $first = $collection->first();

        if (! $first) {
            return false;
        }

        $labels = collect($first)
            ->take(10)
            ->map(fn ($value) => $this->normalizeText($value))
            ->filter()
            ->all();

        $knownLabels = [
            'nombre',
            'name',
            'correo',
            'email',
            'contrasena',
            'password',
            'edad',
            'age',
            'genero',
            'gender',
            'placa',
            'rango',
            'unidad',
            'grupo operativo',
            'area asignada',
        ];

        return count(array_intersect($labels, $knownLabels)) >= 2;
    }

    private function normalizeText($value): string
    {
        $text = strtolower(trim((string) $value));

        return strtr($text, [
            'á' => 'a',
            'é' => 'e',
            'í' => 'i',
            'ó' => 'o',
            'ú' => 'u',
            'ñ' => 'n',
            'Á' => 'a',
            'É' => 'e',
            'Í' => 'i',
            'Ó' => 'o',
            'Ú' => 'u',
            'Ñ' => 'n',
        ]);
    }
}
