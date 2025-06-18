<?php
// Archivo: api/get_adjacent_regions.php

header('Content-Type: application/json');

// Asegúrate de tener conexión a la base de datos
include_once '../config/db.php'; // Ajusta el path si es necesario

$regionId = isset($_GET['region_id']) ? intval($_GET['region_id']) : 0;

if (!$regionId) {
    echo json_encode([]);
    exit;
}

// Consulta regiones vecinas (debes adaptar esta parte si usas una tabla distinta)
$query = "
    SELECT r2.id, r2.nombre AS name
    FROM regiones_conexiones rc
    JOIN regiones r2 ON r2.id = rc.region_vecina_id
    WHERE rc.region_id = ?
";

$stmt = $pdo->prepare($query);
$stmt->execute([$regionId]);
$regiones = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($regiones);
?>
