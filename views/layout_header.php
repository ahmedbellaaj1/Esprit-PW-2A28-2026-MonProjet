<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>GreenBite — <?= $pageTitle ?? 'Dons' ?></title>
  <?php $baseUrl = '/' . basename(dirname(__DIR__)) . '/views'; ?>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="<?= $baseUrl ?>/assets/style.css"/>
  <?php if (!empty($extraCss)): ?>
    <link rel="stylesheet" href="<?= $extraCss ?>"/>
  <?php endif; ?>
</head>
<body>
