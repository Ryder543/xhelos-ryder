<?php
require_once '../includes/database.inc'; // o el archivo correcto que maneje la conexión

session_start();


function sanitize_int($value) {
    return filter_var($value, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]) ?: 0;
}


$region_id = sanitize_int($_GET['region_id'] ?? 0);
$team_id = sanitize_int($_SESSION['team_id'] ?? 0); // Asegúrate de que esto esté bien seteado en login

if ($region_id <= 0 || $team_id <= 0) {
    echo json_encode(["error" => "Datos inválidos"]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id, name, level FROM game_army_units WHERE region_id = ? AND team_id = ?");
    $stmt->execute([$region_id, $team_id]);
    $units = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($units);
} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>
