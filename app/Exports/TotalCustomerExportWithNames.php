<?php

namespace App\Exports;

use App\Models\Sds_query_book;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TotalCustomerExportWithNames implements FromCollection, WithHeadings
{
    public function headings(): array
    {
        return [
            'Name',
            'Mobile'
        ];
    }

    public function collection(): Collection
    {
        $data = Sds_query_book::all(['qb_name', 'qb_mobile']);

        return $data;
    }
}