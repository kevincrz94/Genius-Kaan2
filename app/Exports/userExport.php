<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;

class userExport implements FromCollection, WithHeadings
{
    /**
     * Return data for Excel
     */
    public function collection()
    {
        return new Collection([]); // no data, only headers
    }

    /**
     * Return Excel headers
     */
    public function headings(): array
    {
        return [
            'Nombre',
            'Correo',
            'Contrasena',
            'Edad',
            'Genero',
            'Placa',
            'Rango',
            'Unidad',
            'Grupo Operativo',
            'Area Asignada',
        ];
    }
}
