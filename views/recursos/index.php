<?php
$this->layout = 'panel';
// $app->view->jsObject['recourse'] = [
//     'NotificationController'
// ];
// $app->view->bodyProperties['ng-app'] = "recourse";
// $app->view->bodyProperties['ng-controller'] = "ItemController";
// $app->view->jsObject['angularAppDependencies'][] = 'entity.module.opportunity';
// $app->view->part('recourse/index', ['app' => $app]);


// dump($this->jsObject);
?>
<style>
    .table {
        width: 100%;
        max-width: 100%;
        margin-bottom: 20px;
    }

    .table td,
    .table-bordered th,
    .table-bordered td {
        border: 1px solid #ddd !important;
    }

    .table>thead>tr>th,
    .table>tbody>tr>th,
    .table>tfoot>tr>th,
    .table>thead>tr>td,
    .table>tbody>tr>td,
    .table>tfoot>tr>td {
        padding: 8px;
        line-height: 1.42857143;
        vertical-align: top;
        border-top: 1px solid #ddd;
    }

    .table>thead>tr>th {
        vertical-align: bottom;
        border-bottom: 2px solid #ddd;
    }

    .table>caption+thead>tr:first-child>th,
    .table>colgroup+thead>tr:first-child>th,
    .table>thead:first-child>tr:first-child>th,
    .table>caption+thead>tr:first-child>td,
    .table>colgroup+thead>tr:first-child>td,
    .table>thead:first-child>tr:first-child>td {
        border-top: 0;
    }

    .table>tbody+tbody {
        border-top: 2px solid #ddd;
    }

    .table .table {
        background-color: #fff;
    }

    .table-condensed>thead>tr>th,
    .table-condensed>tbody>tr>th,
    .table-condensed>tfoot>tr>th,
    .table-condensed>thead>tr>td,
    .table-condensed>tbody>tr>td,
    .table-condensed>tfoot>tr>td {
        padding: 5px;
    }

    .table-bordered {
        border: 1px solid #ddd;
    }

    .table-bordered>thead>tr>th,
    .table-bordered>tbody>tr>th,
    .table-bordered>tfoot>tr>th,
    .table-bordered>thead>tr>td,
    .table-bordered>tbody>tr>td,
    .table-bordered>tfoot>tr>td {
        border: 1px solid #ddd;
    }

    .table-bordered>thead>tr>th,
    .table-bordered>thead>tr>td {
        border-bottom-width: 2px;
    }

    .table-striped>tbody>tr:nth-of-type(odd) {
        background-color: #f9f9f9;
    }

    .table-hover>tbody>tr:hover {
        background-color: #f5f5f5;
    }

    .table>thead>tr.active>th {
        background-color: #f5f5f5;
    }

    .table>thead>tr>td.active,
    .table>tbody>tr>td.active,
    .table>tfoot>tr>td.active,
    .table>thead>tr>th.active,
    .table>tbody>tr>th.active,
    .table>tfoot>tr>th.active,
    .table>thead>tr.active>td,
    .table>tbody>tr.active>td,
    .table>tfoot>tr.active>td,
    .table>thead>tr.active>th,
    .table>tbody>tr.active>th,
    .table>tfoot>tr.active>th {
        background-color: #f5f5f5;
    }

    .table-hover>tbody>tr>td.active:hover,
    .table-hover>tbody>tr>th.active:hover,
    .table-hover>tbody>tr.active:hover>td,
    .table-hover>tbody>tr:hover>.active,
    .table-hover>tbody>tr.active:hover>th {
        background-color: #e8e8e8;
    }

    .tr-active {
        background-color: #f5f5f5;
    }
</style>
<div ng-app="ng.recourse" class="panel-list panel-main-content">
    <div class="panel-header clearfix">
        <h4>
            Recursos da Oportunidade: <?php echo $entity->name; ?>
        </h4>
    </div>
    <div ng-controller="ItemController">
        <ul>
            <li ng-repeat="item in data.items">{{item.title}} - <a>remover</a></li>
        </ul>
        <table class="table table-bordered table-hover">
            <thead>
                <tr class="tr-active">
                    <!-- <th>Publicar</th> -->
                    <th>Inscrição</th>
                    <th>Agente</th>
                    <th>Enviado em </th>
                    <th>Responsável</th>
                    <th>Status</th>
                    <th>Responder</th>
                    <th>Respondido em </th>
                </tr>
            </thead>
        </table>
    </div>
</div>

</div>