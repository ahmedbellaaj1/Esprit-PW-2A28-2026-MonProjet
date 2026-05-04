<?php
$user_id = 1;
$data = file_get_contents("http://localhost/project/api/get_products.php?
user_id=$user_id");
$products = json_decode($data, true);
?>
<h2>Produits recommandés</h2>
<?php foreach($products as $p): ?>
<div>
5
<h3><?= $p['name'] ?></h3>
<p>Calories: <?= $p['calories'] ?></p>
<p>Score AI: <?= $p['score'] ?></p>
</div>
<?php endforeach; ?>
