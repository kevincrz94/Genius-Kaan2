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
        // Skip header row
        $rows = $collection->slice(1)->values();

        foreach ($rows as $row) {

            $record = [
                'name' => $row[0] ?? null,
                'email' => $row[1] ?? null,
                'password' => $row[2] ?? null,
                'age' => $row[3] ?? null,
                'gender' => $row[4] ?? null,
            ];

            if (
                empty($record['name']) ||
                empty($record['email']) ||
                empty($record['password']) ||
                is_null($record['age']) ||
                empty($record['gender'])
            ) {
                continue;
            }

            $this->data[] = $record;
        }
    }

    public function getData()
    {
        return $this->data;
    }
}
