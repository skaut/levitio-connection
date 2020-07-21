function skautAppkaUpdateVyprava(jq, parent, isCollapsed, vyprava, suffix)
{
    var vypravaHtml = '';

    if (isCollapsed)
        vypravaHtml += '<h3 class="skautappka-widget-collapsed"><span id="skautappka-widget-nazev-vypravy-' + suffix + '">Výlet do Bruntálu </span><span class="skautappka-widget-nadpis-doplnek" id="skautappka-widget-nadpis-doplnek-' + suffix + '"></span></h3>';
    else
        vypravaHtml += '<h2><span id="skautappka-widget-nazev-vypravy-' + suffix + '">Výlet do Bruntálu </span><span class="skautappka-widget-nadpis-doplnek" id="skautappka-widget-nadpis-doplnek-' + suffix + '"></span></h2>';

    vypravaHtml += '<div id="skautappka-widget-vyprava-info-' + suffix + ' class="skautappka-widget-vyprava-info" style="'+ (isCollapsed ? 'display: none;' : '') + '">';
    vypravaHtml += '<div>';
    vypravaHtml += '<span class="skautappka-widget-nazev-polozky">Sraz:</span> <span class="skautappka-widget-sraz-datum" id="skautappka-widget-sraz-datum-' + suffix + '"></span><span class="skautappka-widget-sraz-cas" id="skautappka-widget-sraz-cas-' + suffix + '"></span><span class="skautappka-widget-sraz-misto" id="skautappka-widget-sraz-misto-' + suffix + '"></span><span class="skautappka-widget-sraz-zpusob-dopravy" id="skautappka-widget-sraz-zpusob-dopravy-' + suffix + '"></span><br/>';
    vypravaHtml += '<span class="skautappka-widget-nazev-polozky">Návrat:</span> <span class="skautappka-widget-navrat-datum" id="skautappka-widget-navrat-datum-' + suffix + '"></span><span class="skautappka-widget-navrat-cas" id="skautappka-widget-navrat-cas-' + suffix + '"></span><span class="skautappka-widget-navrat-misto" id="skautappka-widget-navrat-misto-' + suffix + '"></span><span class="skautappka-widget-navrat-zpusob-dopravy" id="skautappka-widget-navrat-zpusob-dopravy-' + suffix + '"></span><br/>';
    vypravaHtml += '</div>';
    vypravaHtml += '<div class="skautappka-widget-sekce-cena" id="skautappka-widget-sekce-cena-' + suffix + '">';
    vypravaHtml += '<span class="skautappka-widget-nazev-polozky">Cena:</span> <span class="skautappka-widget-cena" id="skautappka-widget-cena-' + suffix + '"></span><span class="skautappka-widget-poznamka-k-cene" id="skautappka-widget-poznamka-k-cene-' + suffix + '"></span>';
    vypravaHtml += '</div>';
    vypravaHtml += '<div class="skautappka-widget-s-sebou" id="skautappka-widget-sekce-s-sebou-' + suffix + '">';
    vypravaHtml += '<span class="skautappka-widget-nazev-polozky">S sebou:</span>';
    vypravaHtml += '<div class="skautappka-widget-s-sebou-text" id="skautappka-widget-s-sebou-text-' + suffix + '"></div>';
    vypravaHtml += '</div>';
    vypravaHtml += '<div class="skautappka-widget-sekce-poznamky" id="skautappka-widget-sekce-poznamky-' + suffix + '">';
    vypravaHtml += '<span class="skautappka-widget-nazev-polozky">Další informace:</span>';
    vypravaHtml += '<div class="skautappka-widget-poznamky-text" id="skautappka-widget-poznamky-text-' + suffix + '"></div>';
    vypravaHtml += '</div>';
    vypravaHtml += '<div class="skautappka-widget-sekce-kontakt" id="skautappka-widget-sekce-kontakt-' + suffix + '">';
    vypravaHtml += '<span class="skautappka-widget-nazev-polozky">Kontakt:</span> <span class="skautappka-widget-kontakt" id="skautappka-widget-kontakt-' + suffix + '"></span>';
    vypravaHtml += '</div>';
    vypravaHtml += '</div>';
    vypravaHtml += '<div class="skautappka-widget-divider"></div>';

    parent.append(vypravaHtml);

    jq("#skautappka-widget-nazev-vypravy-" + suffix).text(vyprava.Nazev !== null && vyprava.Nazev !== undefined ? vyprava.Nazev : "Výprava");

    jq("#skautappka-widget-sraz-datum-" + suffix).text(new Date(vyprava.Zacatek).toLocaleDateString("cs-CS"));
    jq("#skautappka-widget-sraz-cas-" + suffix).text(new Date(vyprava.Zacatek).toLocaleTimeString("cs-CS"));

    jq("#skautappka-widget-navrat-datum-" + suffix).text(new Date(vyprava.Konec).toLocaleDateString("cs-CS"));
    jq("#skautappka-widget-navrat-cas-" + suffix).text(new Date(vyprava.Konec).toLocaleTimeString("cs-CS"));

    if (isCollapsed)
    {
        var sraz = jq("#skautappka-widget-sraz-datum-" + suffix).text();
        var navrat = jq("#skautappka-widget-navrat-datum-" + suffix).text();
        jq("#skautappka-widget-nadpis-doplnek-" + suffix).text(sraz !== navrat ? sraz + " - " + navrat : sraz);
    }
    else
    {
        if (vyprava.Stav === "Koncept")
            jq("#skautappka-widget-nadpis-doplnek-" + suffix).text("Výprava se připravuje");
    }

    if (vyprava.Info !== undefined)
    {
        jq("#skautappka-widget-sraz-misto-" + suffix).text(vyprava.Info.MistoSrazu);
        jq("#skautappka-widget-sraz-zpusob-dopravy-" + suffix).text(vyprava.Info.ZpusobDopravySrazu);

        jq("#skautappka-widget-navrat-misto-" + suffix).text(vyprava.Info.MistoNavratu);
        jq("#skautappka-widget-navrat-zpusob-dopravy-" + suffix).text(vyprava.Info.ZpusobDopravyNavratu);

        if ((vyprava.Info.Cena === undefined || vyprava.Info.Cena === null) && (vyprava.Info.PoznamkaKCene === undefined || vyprava.Info.PoznamkaKCene === null))
        {
            jq("#skautappka-widget-sekce-cena-" + suffix).hide();
        }
        else
        {

            if (vyprava.Info.Cena === undefined || vyprava.Info.Cena === null)
                jq("#skautappka-widget-cena-" + suffix).hide();
            else
                jq("#skautappka-widget-cena-" + suffix).text(vyprava.Info.Cena);

            if (vyprava.Info.PoznamkaKCene === undefined || vyprava.Info.PoznamkaKCene === null)
                jq("#skautappka-widget-poznamka-k-cene-" + suffix).hide();
            else
                jq("#skautappka-widget-poznamka-k-cene-" + suffix).text(vyprava.Info.PoznamkaKCene);

            jq("#skautappka-widget-sekce-cena-" + suffix).show();
        }

        if (vyprava.Info.VeciSSebou === undefined || vyprava.Info.VeciSSebou === null)
            jq("#skautappka-widget-sekce-s-sebou-" + suffix).hide();
        else
            jq("#skautappka-widget-s-sebou-text-" + suffix).html(vyprava.Info.VeciSSebou);

        if (vyprava.Info.Poznamky === undefined || vyprava.Info.Poznamky === null)
            jq("#skautappka-widget-sekce-poznamky-" + suffix).hide();
        else
            jq("#skautappka-widget-poznamky-text-" + suffix).html(vyprava.Info.Poznamky);

        if (vyprava.Info.Kontakt === undefined || vyprava.Info.Kontakt === null)
            jq("#skautappka-widget-sekce-kontakt-" + suffix).hide();
        else
            jq("#skautappka-widget-kontakt-" + suffix).html(vyprava.Info.Kontakt);
    }
    else
    {
        jq("#skautappka-widget-sekce-cena-" + suffix).hide();
        jq("#skautappka-widget-sekce-s-sebou-" + suffix).hide();
        jq("#skautappka-widget-sekce-poznamky-" + suffix).hide();
        jq("#skautappka-widget-sekce-kontakt-" + suffix).hide();
        jq("#skautappka-widget-sraz-cas-" + suffix).hide();
        jq("#skautappka-widget-navrat-cas-" + suffix).hide();
    }


    jq("#skautappka-widget-" + suffix).show();
    jq("#skautappka-widget-error-" + suffix).hide();
}