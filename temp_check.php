<?php
require __DIR__ . '/db.php';
$stmt = $pdo->query('DESCRIBE pos_hidden_items');
var_dump($stmt->fetchAll(PDO::FETCH_ASSOC));
