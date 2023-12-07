<?php

// $app->view->jsObject['recourse'] = [
//     'NotificationController'
// ];

// $app->view->layout = 'panel';
// dump($this->jsObject);
?>

<div>
    <h1>Recourse</h1>
    <div class="main-content">
        <h1> Lista de itens </h1>
        <div ng-controller="ItemController">
            <ul>
                <li ng-repeat="item in data.items">{{item.title}} - <a>remover</a></li>
            </ul>
        </div>
    </div>
</div>