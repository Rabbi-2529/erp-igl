<?php

namespace App\DataTables;

use App\Models\Client_feedback;
use App\Models\Sds_query_book;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class Followup_sds_query_bookDataTable extends DataTable
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

            // 🔒 Mask function
            $mask = function ($num) {
                if (!$num) return '';

                // remove non-digit (space, dash etc.)
                $num = preg_replace('/\D/', '', $num);

                if (strlen($num) >= 11) {
                    return substr($num, 0, 5) . '***' . substr($num, -3);
                }

                return $num;
            };

            $m1 = $mask($user->qb_mobile);
            $m2 = $mask($user->qb_mobile1);
            $m3 = $mask($user->qb_mobile2);

            return "<a href=\"/client_feedback/{$user->qb_id}\" target=\"_blank\">
                        {$m1} <br>
                        {$m2} <br>
                        {$m3}
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

        ->rawColumns(['qb_mobile', 'qb_result']) // ✅ fixed
        ->setRowId('id');
}

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\Followup_sds_query_book $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Sds_query_book $model): QueryBuilder
    {
        $com_id = Auth::user()->au_company_id;
        $user_id = Auth::user()->au_id;

        $all_cf_qb_id = Client_feedback::where('cf_company_id', $com_id)
            ->where('cf_entry_by', $user_id)
            ->groupBy('cf_qb_id')
            ->pluck('cf_qb_id');


        return $model->newQuery()
            ->Where('qb_company_id', $com_id)
            ->where('qb_entry_by',$user_id)
            ->whereIn('qb_id', $all_cf_qb_id)
            ->with(['reas_info','followinfo']);
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('followup_sds_query_book-table')
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
            ['title' => 'Result', 'data' => 'qb_result',],
            ['title' => 'Feedback', 'data' => 'followinfo.fm_msg',],
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename(): string
    {
        return 'Followup_sds_query_book_' . date('YmdHis');
    }
}
