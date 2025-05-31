$(document).ready(function () {
    const $panel = $("#PlayerViewPanel");
    const $content = $("#PlayerViewPanelContent");
    const $header = $("#PlayerViewPanel h2.ui-widget-header");

    $header.css("cursor", "pointer");
    $header.on("click", function () {
        $panel.toggleClass("open");
        $content.slideToggle("fast");
    });

    function getUnitCountsFromPortraits() {
        const counts = { fighter: 0, archer: 0, artillery: 0 };

        $(".portrait").each(function () {
            const imgSrc = $(this).find("img").attr("src") || "";
            const amount = parseInt($(this).find("span").text().trim()) || 0;

            if (imgSrc.includes("/1.png")) counts.fighter += amount;
            else if (imgSrc.includes("/2.png")) counts.archer += amount;
            else if (imgSrc.includes("/3.png")) counts.artillery += amount;
        });

        return counts;
    }

    function updateUnitDisplay() {
        const unitCounts = getUnitCountsFromPortraits();
        $(".unitCount").each(function () {
            const type = $(this).data("type");
            $(this).text(unitCounts[type] ?? 0);
        });
    }

    function updateArmiesAndPlanets() {
        const armyCount = $(".army_turn.myself").length;
        $("#activeArmies").text(armyCount + " ejércitos activos");

        const planetCount = $("img[src*='planet_icon']").length;
        $("#controlledPlanets").text(planetCount + " planetas");
    }

    function updatePanel() {
        updateUnitDisplay();
        updateArmiesAndPlanets();
    }

    // Actualiza cada segundo
    setInterval(updatePanel, 1000);
});