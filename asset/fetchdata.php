<?php
$db = mysqli_connect('localhost', 'root', '', 'monitor');


$tableName = $_POST['moduleId'];
// récupération des dernières données
$result = mysqli_query($db, "SELECT Date, Puissance, température FROM $tableName ORDER BY Date DESC LIMIT 1");
$data = mysqli_fetch_assoc($result);

mysqli_close($db);

echo json_encode($data);
?>