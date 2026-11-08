<?php

namespace App\DataTables;

use App\Models\Sds_query_book;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class My_sds_query_bookDataTable extends DataTable
{
    /**
     * Build DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     * @return \Yajra\DataTables\EloquentDataTable
     */
  public function dataTable(QueryBuilder $query): EloquentDataTable
{
    return (new EloquentDataTable($query))
        ->addColumn('action', 'my_sds_query_book.action')

        ->editColumn('qb_mobile', function ($user) {

            $maskNumber = function ($number) {
                if (!$number) return '';

              
                $number = preg_replace('/\D/', '', $number);

                if (strlen($number) >= 11) {
                    return substr($number, 0, 5) . '***' . substr($number, -3);
                }

                return $number;
            };

            $mobile  = $maskNumber($user->qb_mobile);
            $mobile1 = $maskNumber($user->qb_mobile1);
            $mobile2 = $maskNumber($user->qb_mobile2);

            return "<a href=\"/client_feedback/{$user->qb_id}\" target=\"_blank\">
                        {$mobile} <br>
                        {$mobile1} <br>
                        {$mobile2}
                    </a>";
        })

        ->filterColumn('qb_mobile', function ($query, $keyword) {
            $query->where('qb_mobile', 'like', "%{$keyword}%")
                ->orWhere('qb_mobile1', 'like', "%{$keyword}%")
                ->orWhere('qb_mobile2', 'like', "%{$keyword}%");
        })

        ->editColumn('qb_result', function ($user) {
            $count = '';
            for ($i = 1; $i <= $user->qb_result; ++$i) {
                $count .= '<b style="color: green; font-size: 20px;">*</b>';
            }
            return $count;
        })

        ->rawColumns(['qb_mobile', 'qb_result'])
        ->setRowId('id');
}
    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\my_sds_query_book $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Sds_query_book $model): QueryBuilder
    {
        $com_id = Auth::user()->au_company_id;
        $user_id = Auth::user()->au_id;

        return $model->newQuery()
            ->Where('qb_company_id', $com_id)
            ->where('qb_entry_by',$user_id)
            // ->orWhere('qb_staff_id', $user_id)
            ->with(['reas_info'])
             ->orderBy('qb_id','asc');;
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('my_sds_query_book-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    //->dom('Bfrtip')
                    ->orderBy(1)
                    ->selectStyleSingle();
    }

    /**
     * Get the dataTable columns definition.
     *
     * @return array
     */
    public function getColumns(): array
    {
        return [
            ['title' => 'ID', 'data' => 'qb_id'],
            ['title' => 'Mobile', 'data' => 'qb_mobile'],
            ['title' => 'Name', 'data' => 'qb_name'],
            ['title' => 'Company Name', 'data' => 'qb_company_name'],
            ['title' => 'Address', 'data' => 'qb_address'],
            ['title' => 'Reason', 'data' => 'reas_info.sr_reason'],
            ['title' => 'Result', 'data' => 'qb_result',]
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename(): string
    {
        return 'My_sds_query_book_' . date('YmdHis');
    }
}
