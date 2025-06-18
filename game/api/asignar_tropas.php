<?php
require_once __DIR__ . '/../../config/db.php';
session_start();


function sanitize_int($value) {
    return filter_var($value, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]) ?: 0;
}


$equipo_id = sanitize_int($_GET['team_id'] ?? 0);
$region_id = sanitize_int($_GET['region_id'] ?? 0);

if ($equipo_id <= 0 || $region_id <= 0) {
    echo "Parámetros inválidos.";
    exit;
}
//prueba 2
try {
    $query = $pdo->prepare("SELECT id, name FROM game_army_units WHERE region_id = :region_id AND (team_id IS NULL OR team_id = 0)");
    $query->execute(['region_id' => $region_id]);
    $tropas = $query->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($tropas)) {
        $update = $pdo->prepare("UPDATE game_army_units SET team_id = :team_id WHERE region_id = :region_id AND (team_id IS NULL OR team_id = 0)");
        $update->execute(['team_id' => $equipo_id, 'region_id' => $region_id]);
        echo "Tropas asignadas al equipo $equipo_id:\n";
        foreach ($tropas as $t) echo "- {\$t['name']} (ID: {\$t['id']})\n";
    } else {
        echo "No hay tropas sin equipo en la región $region_id.";
    }
} catch (PDOException $e) {
    echo "Error de base de datos: " . $e->getMessage();
}
?>
