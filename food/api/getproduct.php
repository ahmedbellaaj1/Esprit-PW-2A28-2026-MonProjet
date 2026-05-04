<?php
include "../config/db.php";
$user_id = $_GET['user_id'];
$user = $pdo->query("SELECT * FROM users WHERE id=$user_id")->fetch();
$products = $pdo->query("SELECT * FROM products")->fetchAll();
$result = [];
foreach($products as $p) {
$score = 0;
if($p['calories'] <= $user['calories']) $score += 50;
if($score > 40) {
$p['score'] = $score;
$result[] = $p;
}
}
echo json_encode($result);
?>
