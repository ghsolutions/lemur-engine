<?php

namespace App\DataTables;

use App\Models\Map;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\EloquentDataTable;

class MapDataTable extends DataTable
{

    //to help with data testing and form settings
    public $link;
    public $htmlTag;
    public $title;
    public $resourceFolder;

    /**
     * receive the value from the controller to parameterise the display of the table
     * @param $array
     */
    public function setDrawParams($array)
    {

        $this->link = $array['link'];
        $this->htmlTag = $array['htmlTag'];
        $this->title = $array['title'];
        $this->resourceFolder = $array['resourceFolder'];
    }

    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        $dataTable = new EloquentDataTable($query);

        return $dataTable->addColumn('action', function ($row) {

            if (isset($row['slug'])) {
                $id = $row['slug'];
            } else {
                $id = $row['id'];
            }

            return view(
                $this->resourceFolder.'.datatables_actions',
                ['id'=>$id, 'title'=>$this->title, 'htmlTag'=>$this->htmlTag,
                'link'=>$this->link,
                'user_id'=>$row['user_id'],
                'name'=>$row['name']]
            );
        });
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\GitDetail $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Map $model)
    {
        return $model->dataTableQuery();
    }


    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {

        return $this->builder()
            ->columns($this->getColumns())
            ->minifiedAjax()
             ->addAction(['width' => '120px', 'printable' => false,'searchable'=>false, 'exportable'=>false])
            ->parameters([
                'drawCallback' => 'function(settings, json) {
                    addRowFeatures(settings, json, "'.$this->link.'","edit")
                }',
                'initComplete' => 'function(settings, json) {
                                        
                    var maxColumn = 5
                    var dateFields = [maxColumn-1]
                    var exactSearchFields = []
                    var noSearchFields = [maxColumn]                    
                
                    runAutoSearch(settings, json)
                    addFooterSearch(settings, json, dateFields ,exactSearchFields,noSearchFields)
                }',
                'dom'       => 'Bfrtip',
                'pageLength' => 25,
                'stateSave' => true,
                'order'     => [[0, 'desc']],
                'buttons'   => [
                    ['extend' => 'create', 'className' => 'btn btn-default btn-sm no-corner create-item',],
                    ['extend' => 'export', 'className' => 'btn btn-default btn-sm no-corner export-items',],
                    ['extend' => 'print', 'className' => 'btn btn-default btn-sm no-corner print-items',],
                    ['extend' => 'reset', 'className' => 'btn btn-default btn-sm no-corner reset-table',],
                    ['extend' => 'reload', 'className' => 'btn btn-default btn-sm no-corner reload-table',],
                ],
            ]);
    }


    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        return [
            'name',
            'description' => ['name'=>'description','data'=>'description','title'=>'Desc','searchable'=>true,
                'printable'=>true, 'exportable'=>true,'defaultContent'=>'', 'render' =>
                function () {
                    return 'function(data, type, full, meta)
                { 
                    return getFormattedItem(data, \'description\'); // 
                 }
                 ';
                }],
            'is_master' => ['name'=>'is_master','data'=>'is_master','title'=>'Master Data?','searchable'=>true,
                'printable'=>true, 'exportable'=>true,'defaultContent'=>'false', 'render' =>
                function () {
                    return 'function(data, type, full, meta)
                { 
                    return getFormattedItem(data, \'is_master\'); // 
                 }
                 ';
                }],
            'email'=> ['name'=>'users.email','data'=>'email','title'=>'Owner', 'exportable'=>false],
            'updated_at'=> ['name'=>'updated_at','data'=>'updated_at', 'title'=>'Updated',
                'defaultContent'=>'', 'exportable'=>false, 'render' =>
                function () {
                    return 'function(data, type, full, meta)
                { 
                    return moment(data).format("lll"); // "02 Nov 16 12:00AM"        
                 }
                 ';
                }],
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'maps_datatable_' . time();
    }
}
