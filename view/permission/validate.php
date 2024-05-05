<?php

header('Access-Control-Allow-Origin: http://localhost:63342/*');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');


require_once('../../sqlMain.php');
$sqlMain = new MainSql(null, null);

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $request = file_get_contents('php://input');
    $request = json_decode($request, true);

    if($array = $sqlMain->updatePermissions($request)){
        echo json_encode(['status' => true, 'permissions' => $array]);
    }
    else{
        echo json_encode(['status' => false]);
    }
}
