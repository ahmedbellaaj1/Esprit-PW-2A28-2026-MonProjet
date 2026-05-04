<?php
include "../config/db.php";
include "../model/UserModel.php";
$data = [
"age" => $_POST['age'],
"weight" => $_POST['weight'],
"calories" => $_POST['calories']
];
$user_id = UserModel::createUser($pdo, $data);
echo json_encode(["user_id" => $user_id]);
?>