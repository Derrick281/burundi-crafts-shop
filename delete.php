<?php
$db = new SQLite3('shop.db');
$id = $_GET['id'];
$db->exec("DELETE FROM products WHERE id = $id");
header("Location: admin.php");
