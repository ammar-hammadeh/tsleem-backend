<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;

class PendingUserExport implements FromCollection, WithHeadings, WithMapping
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function map($data): array
    {
        return [
            $data->id,
            $data->name,
            $data->Type ? $data->Type->name : '',
            $data->City ? $data->City->name : '',
            $data->Company ? $data->Company->name : '',
            $data->hardcopyid,
            $data->phone,
            $data->email,
            $data->Company ? $data->Company->license : '',
            $data->Company ? $data->Company->commercial : '',
            $data->employee,
            $data->Company ? $data->Company->files_counter : '',
            $data->created_at,
            $data->status,
        ];
    }

    public function headings(): array
    {
        $columns = [
            'ID',
            'Name',
            'Type',
            'City',
            'Company',
            'Hard copy id',
            'phone',
            'Email',
            'License',
            'Commercial',
            'Employee',
            'Files Counter',
            'Created at',
            'Status',
        ];

        return $columns;
    }

    public function collection()
    {
        return $this->data;
    }
}
