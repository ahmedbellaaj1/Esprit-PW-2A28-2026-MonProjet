<?php
// view/front/logout.php
session_start();
session_destroy();
header('Location: listEvenements.php');
exit();
?>