$(document).ready(function () {
  const regionOrigenId = window.CURRENT_REGION_ID;
  const teamId = window.CURRENT_TEAM_ID;

  function cargarTropas() {
    $.getJSON("api/get_team_units.php", { region_id: regionOrigenId }, function (data) {
      const container = $("#lista-tropas");
      container.empty();
      data.forEach(unit => {
        container.append(\`
          <label>
            <input type="checkbox" class="unidad" value="\${unit.id}" />
            \${unit.name} (Nivel \${unit.level})
          </label><br>
        \`);
      });
    });
  }

  function cargarRegionesVecinas() {
    $.getJSON("api/get_adjacent_regions.php", { region_id: regionOrigenId }, function (data) {
      const select = $("#region_destino");
      select.empty();
      data.forEach(region => {
        select.append(\`<option value="\${region.id}">\${region.name}</option>\`);
      });
    });
  }

  function enviarTropasSeleccionadas() {
    const regionDestinoId = $("#region_destino").val();
    const tropasSeleccionadas = $(".unidad:checked").map(function () {
      return $(this).val();
    }).get();

    if (tropasSeleccionadas.length === 0) {
      alert("Selecciona al menos una tropa.");
      return;
    }

    $.post("workentry.php", {
      work: "movearmytoregionwork",
      origin_region_id: regionOrigenId,
      target_region_id: regionDestinoId,
      army_ids: tropasSeleccionadas
    }, function (response) {
      if (response.success) {
        alert("Tropas movidas exitosamente.");
        cargarTropas();
      } else {
        alert("Error: " + (response.message || "No se pudo mover las tropas"));
      }
    }, "json");
  }

  $("#mover-btn").click(enviarTropasSeleccionadas);
  cargarTropas();
  cargarRegionesVecinas();
});