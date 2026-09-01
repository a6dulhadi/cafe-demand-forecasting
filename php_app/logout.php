<?php
session_start();
session_unset();
session_destroy();

require_once "config/app.php";

header("Location: " . BASE_URL . "index.php");
exit();
?>