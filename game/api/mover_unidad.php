<?php
require_once '../includes/database.inc'; // o el archivo correcto que maneje la conexión

session_start();

function sanitize_int($value) {
    return filter_var($value, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]) ?: 0;
}

$unit_id = sanitize_int($_POST['unit_id'] ?? 0);
$target_region_id = sanitize_int($_POST['target_region_id'] ?? 0);
$team_id = sanitize_int($_SESSION['team_id'] ?? 0); // Asume que el usuario está autenticado

if ($unit_id <= 0 || $target_region_id <= 0 || $team_id <= 0) {
    echo json_encode(["success" => false, "message" => "Parámetros inválidos"]);
    exit;
}

// Paso 1: Obtener información de la unidad
$stmt = $pdo->prepare("SELECT region_id FROM game_army_units WHERE id = ? AND team_id = ?");
$stmt->execute([$unit_id, $team_id]);
$unidad = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$unidad) {
    echo json_encode(["success" => false, "message" => "Unidad no encontrada o no te pertenece"]);
    exit;
}

$region_actual = $unidad['region_id'];

// Paso 2: Validar si la región destino es vecina
$stmt = $pdo->prepare("SELECT 1 FROM regiones_conexiones WHERE region_id = ? AND region_vecina_id = ?");
$stmt->execute([$region_actual, $target_region_id]);

if (!$stmt->fetch()) {
    echo json_encode(["success" => false, "message" => "La región de destino no es vecina"]);
    exit;
}

// Paso 3: Mover la unidad
$stmt = $pdo->prepare("UPDATE game_army_units SET region_id = ? WHERE id = ?");
$stmt->execute([$target_region_id, $unit_id]);

echo json_encode(["success" => true, "message" => "Unidad movida con éxito"]);
?>
