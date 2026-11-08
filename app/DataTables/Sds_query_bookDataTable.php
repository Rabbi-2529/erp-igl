<?php

namespace App\DataTables;

use App\Models\Sds_query_book;
use App\Models\Admin_user;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class Sds_query_bookDataTable extends DataTable
{
    /**
     * Build DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     * @return EloquentDataTable
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
{
    $count = ($this->request->input('start', 0) / $this->request->input('length', 10)) * $this->request->input('length', 10);

    return (new EloquentDataTable($query))
        ->addColumn('action', 'sds_query_book.action')
        ->addColumn('serial', '')

        ->editColumn('qb_mobile', function ($user) {

            // 🔒 Mask function
            $mask = function ($num) {
                if (!$num) return '';

                $num = preg_replace('/\D/', '', $num); // পরিষ্কার করা

                if (strlen($num) >= 11) {
                    return substr($num, 0, 5) . '***' . substr($num, -3);
                }

                return $num;
            };

            $mobiles = "<a href=\"/client_feedback/{$user->qb_id}\" target=\"_blank\">";

            // main number
            $mobiles .= $mask($user->qb_mobile);

            // extra numbers
            if (!empty($user->qb_mobile1)) {
                $mobiles .= "<br>" . $mask($user->qb_mobile1);
            }

            if (!empty($user->qb_mobile2)) {
                $mobiles .= "<br>" . $mask($user->qb_mobile2);
            }

            $mobiles .= "</a>";

            return $mobiles;
        })

        ->editColumn('serial', function () use (&$count) {
            $count++;
            return $count;
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
     * @param Sds_query_book $model
     * @return QueryBuilder
     */
    public function query(Sds_query_book $model): QueryBuilder
    {
        return $model->newQuery()
            ->with(['reas_info', 'find_info'])
            ->join('admin_user', 'admin_user.au_id', '=', 'sds_query_book.qb_entry_by')
            ->select('sds_query_book.*', 'admin_user.au_name')
            ->orderBy('qb_id', 'desc');
    }

    /**
     * Get the HTML builder object.
     *
     * @return HtmlBuilder
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('sds_query_book-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(0, 'desc')
            ->selectStyleSingle();
    }

    /**
     * Get the columns being used by the dataTable.
     *
     * @return array
     */
     public function getColumns(): array
        {
            return [
                ['title' => 'SL', 'data' => 'serial', 'searchable' => false, 'orderable' => false, 'width' => '20px'],
                ['title' => 'Customer Name', 'data' => 'qb_name', 'searchable' => true, 'width' => '120px'],
                ['title' => 'Mobile', 'data' => 'qb_mobile', 'searchable' => true, 'width' => '75px'],
                ['title' => 'Cus ID', 'data' => 'qb_id', 'searchable' => true, 'width' => '50px'],
                ['title' => 'Company Name', 'data' => 'qb_company_name', 'searchable' => true],
                ['title' => 'Address', 'data' => 'qb_address', 'searchable' => true],
                ['title' => 'Reason', 'data' => 'reas_info.sr_reason','searchable' => true, 'width' => '120px'],
                ['title' => 'Find Us', 'data' => 'find_info.sf_howto', 'searchable' => true, 'width' => '90px'],
                ['title' => 'Entry By', 'data' => 'au_name', 'searchable' => false],
                ['title' => 'Entry At', 'data' => 'qb_submit_date', 'searchable' => false, 'width' => '70px'],
                ['title' => 'Result', 'data' => 'qb_result', 'searchable' => true, 'width' => '50px']
            ];
        }

        /**
         * Get filename for export.
         *
         * @return string
         */
        protected function filename(): string
        {
            return 'Sds_query_book_' . date('YmdHis');
        }
}