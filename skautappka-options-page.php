<div class="wrap options-page">
	<h2><?php echo esc_html( $title ); ?></h2>

<div class="nav">
	<a class="nav-link" href="https://www.skautappka.cz/">SkautAppka Web</a>
</div>

<div id="notifications">
<?php if ( isset($_GET['message']) && isset($messages[$_GET['message']]) ) { ?>
<div id="message" class="updated fade"><p><?php echo $messages[$_GET['message']]; ?></p></div>
<?php } ?>
<?php if ( isset($_GET['error']) && isset($errors[$_GET['error']]) ) { ?>
<div id="message" class="error fade"><p><?php echo $errors[$_GET['error']]; ?></p></div>
<?php } ?>
</div><!-- /notifications -->

	<h2>Jak použít SkautAppka plugin</h2>
	<p>
		Tento plugin je určený na zobrazení informací ze SkautAppky na Tvoje webu.
	</p>
	<p>
		Plugin obsahuje Widget and taky tzv. shortcode, pomocí kterého ho můžeš vložit na kteroukoliv
		stránku, příspěvek, atd.
	</p>
	<p>
		Následující kód můžeš tedy vložit na kteroukoliv stránku:
	</p>
	<p style="font-family: 'Courier New', Courier, monospace; background-color: lightgray; padding: 10px;">
		[skautappka zobraz="vypravy" evidencni-cislo="xxx.xx.xx"]
	</p>

	<h3>Parametry:</h3>
	<ol>
		<li><span style="font-family: 'Courier New', Courier, monospace; background-color: lightgray; padding: 10px;">zobraz</span> - vyplň hodnotou "vypravy". V budoucnu rozšíříme i o jiný typ dat.</li>
		<li><span style="font-family: 'Courier New', Courier, monospace; background-color: lightgray; padding: 10px;">evidencni-cislo</span> - zadej evidenční číslo oddílu či družiny.</li>
		<li><span style="font-family: 'Courier New', Courier, monospace; background-color: lightgray; padding: 10px;">minule-vypravy</span> - zadej 'ne', pokud nechceš zobrazit výpravy, co již proběhly</li>
		<li><span style="font-family: 'Courier New', Courier, monospace; background-color: lightgray; padding: 10px;">budouci-vypravy</span> - zadej 'ne', pokud nechceš zobrazit výpravy, co se chystají</li>
	</ol>

	<h3>Příklady</h3>
	<p>
		Následující kód zobrazí pouze nejbližší výpravu:
	</p>
	<p style="font-family: 'Courier New', Courier, monospace; background-color: lightgray; padding: 10px;">
		[skautappka zobraz="vypravy" evidencni-cislo="xxx.xx.xx" minule-vypravy="ne" budouci-vypravy="ne"]
	</p>

	<p>
		Následující kód zobrazí nejbližší a budoucí výpravy:
	</p>
	<p style="font-family: 'Courier New', Courier, monospace; background-color: lightgray; padding: 10px;">
		[skautappka zobraz="vypravy" evidencni-cislo="xxx.xx.xx" minule-vypravy="ne"]
	</p>

<div id="debug-info">
	<?php if(WP_DEBUG){ ?>
	<h3>Debug information</h3>
	<p>You are seeing this because your WP_DEBUG variable is set to true.</p>
	<pre><?php print_r($current) ?></pre>
	<?php } ?>
</div>
<!-- /debug-info -->

</div> <!-- /wrap options-page -->
