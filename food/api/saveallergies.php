<?php
include "../config/db.php";
include "../model/UserModel.php";
$user_id = $_POST['user_id'];
$allergies = $_POST['allergies']; // array
foreach($allergies as $a) {
UserModel::addAllergy($pdo, $user_id, $a);
}
echo json_encode(["status" => "ok"]);
?>