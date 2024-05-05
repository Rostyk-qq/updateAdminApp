<?php

require_once('../../sqlMain.php');

header('Access-Control-Allow-Origin: http://localhost:63342/*');
header('Access-Control-Allow-Method: POST');
header('Access-Control-Allow-Headers: Content-Type');
header("Content-Type: application/json");




$sqlMain = new MainSql(null, null);

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $request = file_get_contents('php://input');
    $request = json_decode($request, true);

    $username = htmlspecialchars($request['username_login']);
    $password = htmlspecialchars($request['password_login']);

    $errors = [];

    if(empty($username) ){
        $errors[] = ['code' => 1, 'message' => 'Username field was empty!'];
    }
    if($username !== '' && !$sqlMain->checkPassword($password, $username)){
        $errors[] = ['code' => 2, 'message' => 'Password is not correct!'];
    }
    if(!empty($errors)){
        echo json_encode(['status' => false, 'errors' => $errors]);
        die();
    }
    else {
        new MainSql($username, $password);

        $counterFile = '../../counter.dat';

        if (!file_exists($counterFile)) {
            touch($counterFile);
        }

        $res_array = [];

        $currentCounter = file_get_contents($counterFile);
        $data_users = preg_split("/\n\s*/", $currentCounter);

        foreach ($data_users as $users) {
            if (!empty($users)) {
                list($user_name, $count) = explode(": ", $users, 2);

                if ($user_name === $username) {
                    $count = (int)$count + 1;

                    $found = false;
                    foreach ($res_array as &$res) {
                        if ($res['username'] === $user_name) {
                            $res['count'] = $count;
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {
                        $res_array[] = ['username' => $user_name, 'count' => $count];
                    }
                } else {
                    $res_array[] = ['username' => $user_name, 'count' => $count];
                }
            }
        }

        $checkAll = false;
        foreach ($res_array as $r) {
            if ($r['username'] === $username) {
                $checkAll = true;
            }
        }
        if (!$checkAll) {
            $res_array[] = ['username' => $username, 'count' => 1];
            file_put_contents($counterFile, $username . ': ' . 1 . "\n", FILE_APPEND);
        } else {
            file_put_contents($counterFile, '');
            foreach ($res_array as $user) {
                file_put_contents($counterFile, $user['username'] . ': ' . $user['count'] . "\n", FILE_APPEND);
            }
        }

        echo json_encode(['status' => true, 'url' => "http://localhost:63342/laba4/view/index.php?username=$username"]);
        die();
    }
}
