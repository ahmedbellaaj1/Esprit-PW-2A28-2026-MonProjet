<?php
session_start();
session_destroy();
header("Location: /PROJET%202A28/index.php?page=home");
exit;
