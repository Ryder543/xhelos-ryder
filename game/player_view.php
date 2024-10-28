<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Resumen del Jugador</title>
    <link rel="stylesheet" href="css/player_view.css">
</head>
<body>
    <div id="MainContent" style="margin: 20px;">
        <div id="PlayerViewPanel" class="ui-state-active panel">
            <h2 class="ui-widget-header">Resumen del Jugador</h2>
            <ul id="PlayerViewPanelContent">

                <li><strong>Ejércitos activos:</strong>
                    <span id="activeArmies">Calculando...</span>
                </li>

                <li><strong>Planetas controlados:</strong>
                    <span id="controlledPlanets">Calculando...</span>
                </li>

                <li><strong>Recursos:</strong>
                    <ul>
                        <?php for ($i = 1; $i <= 4; $i++): ?>
                            <?php if (isset($resources[$i])): ?>
                                <li>
                                    <img src='img/web/icon_res<?= $resources[$i]['id']; ?>.png' 
                                         alt='<?= htmlspecialchars($resources[$i]['name']); ?>' 
                                         title='<?= htmlspecialchars($resources[$i]['name']); ?>' 
                                         style='height: 24px; vertical-align: middle; margin-right: 4px;'> 
                                    <?= htmlspecialchars($resources[$i]['name']); ?>: <?= $resources[$i]['amount'] ?? 0; ?>
                                </li>
                            <?php else: ?>
                                <li>Recurso <?= $i; ?> no disponible</li>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </ul>
                </li>

                <li><strong>Unidades creadas:</strong>
                    <ul id="unitList">
                        <li>
                            <img src="https://4xhelos.lndo.site/game/img/unit/1.png" style="height: 48px; vertical-align: middle; margin-right: 6px;">
                            Fighter: <span class="unitCount" data-type="fighter">Calculando...</span>
                        </li>
                        <li>
                            <img src="https://4xhelos.lndo.site/game/img/unit/2.png" style="height: 48px; vertical-align: middle; margin-right: 6px;">
                            Archer: <span class="unitCount" data-type="archer">Calculando...</span>
                        </li>
                        <li>
                            <img src="https://4xhelos.lndo.site/game/img/unit/3.png" style="height: 48px; vertical-align: middle; margin-right: 6px;">
                            Artillery: <span class="unitCount" data-type="artillery">Calculando...</span>
                        </li>
                    </ul>
                </li>

            </ul>
        </div>
    </div>

    <script src="lib/js/player_view.js"></script>
</body>
</html>