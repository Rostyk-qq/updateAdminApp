<?php
require_once('../sqlMain.php');

header('Access-Control-Allow-Origin: http://localhost:63342/*');
header('Access-Control-Allow-Method: POST');
header('Access-Control-Allow-Headers: Content-Type');
header("Content-Type: application-json");

$sqlMain = new MainSql(null, null);

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $request = file_get_contents('php://input');
    $request = json_decode($request, true);


    if ($request['action'] === 'search'){
        $res_of_search = $sqlMain->search($request['value']);
        echo json_encode(['status' => true, 'searched' => $res_of_search]);
        die();
    }

    if($request['action'] === 'delete'){
        if($res = $sqlMain->deleteEntry($request)){
            echo json_encode(['status' => true, 'entry_id' => $res]);
        }
        die();
    }

    if(!empty($request['permission']) && $request['permission'] === 'check'){
        if($request['action']){
            $res = $sqlMain->checkPermission($request['action']);
            if($res){
                echo json_encode(['status' => true]);
            }
            else{
                echo json_encode(['status' => false]);
            }
        }
        die();
    }

    $errors = [];

    if(empty($request['entrance_number'])){
        $errors[] = ['code' => 1, 'message' => 'You must fill entry number field!'];
    }
    if(!preg_match("/[0-9]/", $request['entrance_number'])){
        $errors[] = ['code' => 2, 'message' => 'Entry number field must by of type number!'];
    }


    if(empty($request['flat_number'])){
        $errors[] = ['code' => 3, 'message' => 'You must fill flat number field!'];
    }
    if(!preg_match("/[0-9]/", $request['flat_number'])){
        $errors[] = ['code' => 4, 'message' => 'Flat number field must by of type number!'];
    }
    if(!$sqlMain->checkEntryFlat($request)){
        $errors[] = ['code' => 5, 'message' => 'This flat with owner was exists!'];
    }


    if(empty($request['owner_firstname'])){
        $errors[] = ['code' => 6, 'message' => 'You must fill owner firstname field!'];
    }
    if(empty($request['owner_secondname'])){
        $errors[] = ['code' => 7, 'message' => 'You must fill owner secondname field!'];
    }


    if(empty($request['phone_number_owner'])){
        $errors[] = ['code' => 8, 'message' => 'You must fill phone number owner!'];
    }
    if(!$sqlMain->checkPhoneNumber($request)){
        $errors[] = ['code' => 9, 'message' => 'Phone number was exists!'];
    }
    if(!preg_match("/[0-9]/", $request['phone_number_owner'])){
        $errors[] = ['code' => 10, 'message' => 'Phone number must have only numbers!'];
    }
    $clear_number = preg_replace("/\D/", '', $request['phone_number_owner']);
    if(!preg_match('/^\d{10,11}$/', $clear_number)){
        $errors[] = ['code' => 11, 'message' => 'Phone number is not active!'];
    }


    if(empty($request['services_price'])){
        $errors[] = ['code' => 12, 'message' => 'You must fill services price field!'];
    }
    if(!preg_match("/[0-9]/", $request['services_price'])){
        $errors[] = ['code' => 13, 'message' => 'Service price field must by of type number!'];
    }


    if(empty($request['tenants'])){
        $errors[] = ['code' => 14, 'message' => 'You must fill tenants count!'];
    }
    if(!preg_match("/[0-9]/", $request['tenants'])){
        $errors[] = ['code' => 15, 'message' => 'Tenants field must by of type number!'];
    }


    if(!empty($errors)){
        echo json_encode(['status' => false, 'errors' => $errors]);
    }
    else{
        if($request['action'] === 'add'){
            $res = $sqlMain->addEntry($request);
            if($sqlMain->getNewEntry($res)){
                echo json_encode(['status' => true, 'newEntry' => $sqlMain->getNewEntry($res)]);
            }
        }
        else{
            $res = $sqlMain->updateEntry($request);
            if($sqlMain->getNewEntry($res)){
                echo json_encode(['status' => true, 'newEntry' => $sqlMain->getNewEntry($res)]);
            }
        }
        die();
    }
}