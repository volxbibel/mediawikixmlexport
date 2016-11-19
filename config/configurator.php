<?php
/**
 * Configurator
 *
 * Passt eine standisierte Configdatei an.
 *
 * @author Simon Brüchner
 * 
 * @package onlineshop
 * @todo    Config-Datei auf Dateiformate:
 *             * Windows (\r\n)
 *             * Unix (\n)
 *             * Mac (\r)
 *          umstellen.
 * @todo    @default extra ausgeben
 * @todo    Die Kommentare besser (z. B. ohne title-Attribut ausgeben) bzw. mit Pop-Up anzeigen.
 * @todo    Nur Konstanten ändern die geändert wurden (mit einer 
 *          array_callback()          -Methode überprüfen)
 *
 */
 
/**
 * Zeilenumbruch in String ersetzten
 * 
 * Damit die Werte der Konstanten in einer Zeile stehen können.
 * 
 * @see     lbBackReplace
 * @access  private
 * @param   String
 * @return  String
 */
function lbReplace($string) 
{
    $string = str_replace("\r\n", '<br />', $string); // Windows
    $string = str_replace("\n", '<br />', $string); // Unix
    $string = str_replace("\r", '<br />', $string); // Mac
    return $string;
}

/**
 * Zeilenumbruch in String zurück ersetzten
 * 
 * Damit die Werte der Konstanten in einer Zeile stehen können.
 * 
 * @see     lbReplace
 * @access  private
 * @param   String
 * @return  String
 */
function lbBackReplace($string) 
{
    return str_replace('<br />', "\r\n", $string);
}



// Config-File
$configFile = dirname(__FILE__).'/config.php';
require_once $configFile;

require_once 'HTML/QuickForm.php';

// echo chmod($configFile, 0755);

/* Alternative, allerdings nur für PHP > 4.3.0 o.ä.
 *
 * +if (!$content = file_get_contents($configFile)) {
 * -if ($handle = fopen($configFile, 'r', 0)) {
 * -    $content = NULL;
 * -    while (false !== ($char = fgetc($handle))) {
 * -        $content .= $char;
 * -    }
 * -    fclose($handle);
 * -} else {
 */
if ($handle = fopen($configFile, 'r', 0)) {
    $content = NULL;
    while (false !== ($char = fgetc($handle))) {
        $content .= $char;
    }
    fclose($handle);
} else {
    echo 'Configdatei: '. $configFile . ' konnte nicht geöffnet werden.';
    exit;
}

// Start und Ende finden und parsen
preg_match('=/\* START CUSTOM \*/(.*)/\* END CUSTOM \*/=s', $content, $matches);
$content = ($matches[1]); // Kein trim wg. entsprechendem reg exp. unten

$content = explode("\n", $content);

$Form =& new HTML_QuickForm ('setup', 'POST', '');

$num = count($content);

for ($i = 0; $i < $num; $i++, $data = next($content)) {
    if (isset($data) AND $data !== NULL) {
        // echo '<hr>';
        // var_dump($data);
        // echo '<hr>';

        // Kategorie
        if (preg_match("=/\*\s*\[(.*)\]\s*\*/=i", $data, $matches)) {

            // Kategorie hinzufügen
            $Form->addElement('html', "<tr><td>\n<br />\n<br /></td></tr>");
            $Form->addElement('header', null, $matches[1]);

        // Kommentar Start: / * *
        } elseif (preg_match("=/\*\*=i", $data)) { // Keine Leerzeichen am Anfang entfernen!

            $string = NULL;

            // Kommentar: Ende * /
            while (!preg_match("=\*/=", $data, $matches)) {
                $string .= $data."\n";
                $data = next($content);
            }

            // Erste Zeile im Kommtenar und Rest trennen.
            preg_match_all("=\s*/\*\*\s*\n\s*\*\s*(.*)\s*\n\s*\*\s*\n=", $string,  $matches);

            // Die Bezeichung einer Konstante steht in der ersten Zeile eines
            // Kommentars
            $ersteZeileKommentar = $matches[1][0];
            $ersteZeileKommentar = wordwrap($ersteZeileKommentar, 40, "<br />"/*'<br />'*/);

            // Der Rest sind Hinweise zum Wert der Konstante sowie zum Default-Wert
            // Rest des Kommentars kann auch leer sein.
            $restKommentar = @explode($matches[1][0], $string);
            $restKommentar = format($restKommentar[1]);

        // Define
        } elseif (preg_match("=define\('([A-Z0-9_-]*)',\s*(.*)\)=", $data, $matches)) {

            $schluessel = $matches[1];
            $wert       = $matches[2];

            $defaultValues[$schluessel] = lbBackReplace($wert);
            $Form->setDefaults($defaultValues);
            
            // HTML input Type "text" oder "textarea"?
            if (strlen($wert) < 60) {
                $inputType      = 'text';
                $inputFormat    = 'size=70';
            } else {
                $inputType      = 'textarea';
                $inputFormat    = array('rows=8', 'cols=60');
            }
            if ($restKommentar) { 
                $Form->addElement($inputType, $schluessel, $ersteZeileKommentar./*' <span style="color:blue;font-size:130%; cursor:help;" onMouseMove="setHelpPos()" onMouseOut="closeHelp()" OnMouseOver="setHelpText(\''.$restKommentar.'\')"><b>?</b></span>'*/'<a href="#" class="tooltip" title="'.$ersteZeileKommentar.'<hr />'.$restKommentar.'">?</a>', $inputFormat);
            } else {
                $Form->addElement($inputType, $schluessel, $ersteZeileKommentar./*' <span style="color:blue;font-size:130%; cursor:help;" onMouseMove="setHelpPos()" onMouseOut="closeHelp()" OnMouseOver="setHelpText(\'Keine zusätzlichen Informationen verfügbar.\')"><b>?</b></span>'*/'<a href="#" class="tooltip" title="'.$ersteZeileKommentar.'<hr />'.$restKommentar.'">?</a>', $inputFormat);
            }
        }
    }
}

// Cache Lite
//$Form->addElement('checkbox', 'empty_cache', 'Cache leeren'.' <span style="color:blue;font-size:130%; cursor:help;" title="@default markiert Alle Cache Dateien löschen. Hat keine Auswirkungen auf die Funktionalität des Shops. Sollte immer bei einer Änderung an der Konfiguration ausgeführt werden."><b>?</b></span>');
//$Form->setDefaults(array('empty_cache' => 'checked'));

$buttons[] = $Form->createElement('submit', null, 'Speichern');
$buttons[] = $Form->createElement('reset', null, 'Zurücksetzen');
$Form->addGroup($buttons);


// var_dump($Form);

// Funktion, um Kommentare zu formatieren und * zu entfernen.
function format($string)
{
    $string = trim(str_replace('*', '', $string));
    $string = htmlspecialchars($string, ENT_QUOTES);
    #$string = htmlentities($string);
    $string = preg_replace('=^(\s*)=m', '', $string);
    // TODO
    // statt str_replace() str_ireplace() verwenden, allerdings nur PHP 5!!!
    $string = str_replace("\n", '<br />', $string); // Siehe http://www.tiptom.ch/homepage/faq.html?q=zeilenumbruch
    $string = str_replace("\r", '', $string);
    $string = str_replace("&", '', $string);
    return $string;
}

// HTTP-Ausgabe
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// HTML-Augabe
$returnString = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />';
$returnString .= '<title>Setup</title>';
$returnString .= '<META http-equiv=Content-Type content="text/html; charset=iso-8859-1">';
$returnString .= '<style type="text/css">
<!--
body { font-family:Arial,Helvetica,San-Serif; }
#HelpLayer
{
    visibility:hidden;  
    background-color:#F0F8FF; /*#F8F8FF*/
    border: blue solid 1px;
    position:absolute;
    width:200px;
    height:100px;
    padding:5px;
}
//-->
</style>
<script language="JScript" type="text/jscript">
<!--
function setHelpText(text)
{
    document.all.HelpLayer.innerHTML = \'<div style="border-bottom: blue solid 1px; font-weight:bold;">Hinweis:</div>\'+text;
    document.all.HelpLayer.style.visibility = "visible";
}
function setHelpPos()
{
    document.all.HelpLayer.style.left = window.event.x;
    document.all.HelpLayer.style.top = window.event.y;
}
function closeHelp()
{
    window.document.all.HelpLayer.style.visibility = "hidden";
}
//-->
</script>';
$returnString .= '<script type="text/javascript">

// ||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
// 
// Coded by Travis Beckham
// http://www.squidfingers.com | http://www.podlob.com
// If want to use this code, feel free to do so, but please leave this message intact.
//
// ||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
// --- version date: 06/13/04 ---------------------------------------------------------

tooltip = {
    name : "tooltipDiv",
    offsetX : 40,
    offsetY : -10,
    tip : null
};
tooltip.init = function () {
    if (!document.getElementById) return;
    
    // It would be nice to be able to generate the tooltip div, 
    // but when using document.createElement Explorer5/MacOS9, 
    // the tooltip div becomes 100% of the window height.
    // Therefore, we have to use document.getElementById to access
    // a div that is already in the body.
    
    // this.tip = document.createElement ("div");
    // this.tip.setAttribute ("id", this.name);
    // document.body.appendChild (this.tip);
    
    this.tip = document.getElementById (this.name);
    if (this.tip) document.onmousemove = function (evt) {tooltip.move (evt)};
    
    var a;
    var anchors = document.getElementsByTagName ("a");
    for (var i = 0; i < anchors.length; i ++) {
        a = anchors[i];
        if (a.className == "tooltip") {
            a.onmouseover = function () {tooltip.show (this.title)};
            a.onmouseout = function () {tooltip.hide ()};
        }
    }
};
tooltip.move = function (evt) {
    var x=0, y=0;
    if (document.all) {// Explorer
    
        // Explorer5 contains the documentElement object but it\'s empty, 
        // so we must check if the scrollLeft property is available.
        
        // If Explorer6 is in Quirks mode, the documentElement properties 
        // will still be defined, but they will contain the number 0.
        
        // If Explorer6 is in Standards compliant mode, the document.body 
        // properties will still be defined, but they will contain the number 0.
        
        x = (document.documentElement && document.documentElement.scrollLeft) ? document.documentElement.scrollLeft : document.body.scrollLeft;
        y = (document.documentElement && document.documentElement.scrollTop) ? document.documentElement.scrollTop : document.body.scrollTop;
        x += window.event.clientX;
        y += window.event.clientY;
        
    } else {// Mozilla
        x = evt.pageX;
        y = evt.pageY;
    }
    // If the style property value is not a string containing the unit measurement,
    // browsers in standard compliant mode will not set the property.
    this.tip.style.left = (x + this.offsetX) + "px";
    this.tip.style.top = (y + this.offsetY) + "px";
};
tooltip.show = function (text) {
    if (!this.tip) return;
    this.tip.innerHTML = text;
    // Without the next line, Explorer5/Mac has a redraw problem.
    this.tip.style.visibility = "visible";
    this.tip.style.display = "block";
};
tooltip.hide = function () {
    if (!this.tip) return;
    // Without the next line, Explorer5/Mac has a redraw problem.
    this.tip.style.visibility = "hidden";
    this.tip.style.display = "none";
    this.tip.innerHTML = "";
};

window.onload = function () {
    tooltip.init ();
}

</script>';
$returnString .= '<style type="text/css">

body {
    background-color: #fff;
    padding: 0;
    margin: 20px;
}
p {
    color: #3c2819;
    font-family: verdana, sans-serif;
    font-size: 11px;
    padding: 0;
    margin: 0 0 10px 0;
}
a {
    color: #be5028;
}
a:hover {
    color: #539dbc;
}

#tooltipDiv {
    position: absolute;
    left: 0;
    top: 0;
    z-index: 1000;
    display: none;
    padding: 5px;
    border-style: solid;
    border-width: 5px;
    border-color: #fff;
    background-color: #cde9f2;
    color: #3278a0;
    font-family: verdana, sans-serif;
    font-size: 11px;
    white-space: nowrap;
}

</style>';
$returnString .= "\n</head>\n<body>\n";
$returnString .= '<div id="HelpLayer"></div><div id="tooltipDiv"></div>';
$returnString .= '<div class="content">';
$returnString .= "\n<h1>Setup</h1>\n";
$returnString .= "<p>\nDie Werte der Konstanten welche als String gespeichert werden sollen, müssen mit Hochkomma <b>\" \"</b> oder <b>' '</b> eingegeben werden.\n</p>\n";
$returnString .= "<p>Config Datei: \"$configFile\"</p>";

// Formular abspeichern
if ($Form->validate()) {

    // Formular ohne Inputs anzeigen
    // $Form->freeze();
    
    // Alle Define Werte ersetzen
    // Alte Version um unterschiede zwischen altem und neuen Config-File zu ersetzten
//    $array = $Form->toArray();
//    
//    // Wen dies stört, der soll es anders machen...
//    if (is_array($array)) {
//        foreach ($array['sections'] as $data) {
//            if (is_array($data)) {
//                foreach ($data as $data2) {
//                    if (is_array($data2)) {
//                        foreach ($data2 as $data3) {
//                            $arrayKonstanten[$data3['name']] = $data3['value'];
//                        }
//                    }
//                }
//            }
//        }
//    }
    // Alte Version ende
    
    // Neue Version
    $arrayKonstanten = array_diff($Form->getSubmitValues(), $defaultValues);
    
    $arrayKonstanten = array_map('lbReplace', $arrayKonstanten);
    
    if (!$zeilen = file($configFile)) {
        echo '<p>Configdatei: '. $configFile . ' konnte nicht geöffnet werden.</p>';
    }

    if (is_array($arrayKonstanten)) {
        foreach(array_keys($arrayKonstanten) as $arrayk) {
            // $muster = "/define\('[A-Z_]*',\s*'(.*)'\);[\s]*/";
            $muster = "/\s*define\('$arrayk',\s*(.*)\);[\s]*/";
            $zeilen = preg_replace($muster, "define('$arrayk', $arrayKonstanten[$arrayk]);\n", $zeilen);
            $ersetzungen[] = $arrayk; // Für Feedback s.u.
        }
        
        // Feedback was ersetzt wurde, nötig?
        // foreach ($ersetzungen as $data) echo $data.' wurde geändert<br />';

        // Datei öffnen
        if ($configFileHandler = fopen($configFile, 'w+')) {
            //Schreiben
            foreach($zeilen as $zeilen) fputs($configFileHandler, $zeilen);
            fclose($configFileHandler);
            $returnString .= "\n<h2>".basename($configFile)." wurde erfolgreich gespeichert!</h2>\n";            
            $returnString .= "\n<p>Den Configurator erneut <a href=\"".basename(__FILE__)."\">aufrufen</a>.</p>\n";
        } else {
            echo '<p>Configdatei: '. $configFile
                . ' konnte nicht zum Schreiben geöffnet werden.</p>';
            exit;
        }
        
//        // Cache Lite Start
//        // Alle Cache Dateien löschen
//        require_once 'Cache/Lite.php';
//        $optionsCL = array('cacheDir' => '../'.PATH_CACHE_LITE);
//        $CacheLite = new Cache_Lite($optionsCL);
//        if ($CacheLite->clean()) {
//            //echo '<p>Der Cache im Verzeichnis "'.$options['cacheDir'].'" wurde erfolgreich geleert!</p>';
//        } else {
//            // TODO
//            // Hier Logfile schreiben
//        }
//        // Cache Lite Ende 
               
    }
} else {
    $returnString .= $Form->toHtml();
}

$returnString .= "\n</body>\n<html>\n";

// Ausgeben
echo $returnString;
?>
