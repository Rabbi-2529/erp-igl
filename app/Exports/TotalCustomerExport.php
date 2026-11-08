<?php

namespace App\Exports;

use App\Models\Sds_query_book;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TotalCustomerExport implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function headings(): array
    {
        return [
            'Mobile',
        ];
    }

    public function collection(): Collection
    {
        $data = Sds_query_book::pluck('qb_mobile');

        $collection = new Collection();
        foreach ($data as $value) {
            $collection->push([$value]);
        }

        return $collection;
    }
}
