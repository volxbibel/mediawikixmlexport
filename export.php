<?php
/*
 * volxbibelwiki
 * Simon Brüchner, 05.02.2007
 */
/**
 * Config einbinden
 *
 */
require_once dirname(__FILE__).'/config/config.php';

/**
 * Export der Volxbibel in XML für Oldmedia/R. Brockhaus Verlag
 *
 * Speichert ein XML
 *
 * @package volxbibelwiki
 * @author Simon Brüchner, 2007
 * @version	0.3
 * @see		http://wiki.volxbibel.com
 */
class VolxbibelExport {
    /**
     * Buch welches ausgegeben werden soll
     */
    var $buch;

    /**
     * Array mit Logeinträgen bei Fehlern und Meldungen für Benutzer
     *
     * Format: Array[level] -> Meldung
     */
    var $log = NULL;

    /**
     * Schwerer Fehler
     */
    var $error = NULL;

    /**
     * Der Volxbibel-MediaWiki XML Export einer Seite
     *
     * Wird über folgende URI für z.B.: 1. Korinther 13 aufgerufen: http://wiki.
     * volxbibel. com/index. php/Spezial:Export/1.Korinther_13
     */
    var $XML;

    /**
     * Seitentitel unter welchen ein Kapitel oder eine Buchübersichtsseite im
     * Volxbibel-MediaWiki liegt. Z.B.: "1.Korinther 13"
     */
    var $pageTitle;

    /**
     * Das Kapitel im MediaWiki-Syntax formatiert
     *
     * Nur der Relevante Teil aus dem XML-Export. Als Array.
     */
    var $kapitelContent;

    /**
     * Das Kapitel als Array mit Überschriften, Versnummern und Verstext
     *
     *
     */
    var $kapitelArray;

//    /**
//     * Buch Titel, z.B.: Matthäus
//     */
//    var $buchTitel;

    /**
     * Buch Untertitel
     *
     * Erklärende Zusätze am Anfang eines Buches
     */
    var $buchUntertitel;

    /**
     * Die Buchübersichtsseite im MediaWiki-Syntax formatiert
     *
     * Nur der Relevante Teil aus dem XML-Export. Als Array.
     */
    var $buchKapitel;

    /**
     * Konstruktor
     *
     * Exportiert eine Buch
     *
     * @param 	String 	Buchname
     * @param	int		Ordungsnummer in der Bibel
     * @param 	bool	XML in Browser ausgeben
     * @todo	$html auslagern bzw. Klasse soll nur das Log Array ausgeben
     * @access  public
     */
    function VolxbibelExport($buch, $nummer = NULL, $showXML = TRUE) {
        // 1. Buchübersichtsseite parsen
        // 2. jedes Buch (Kapitel?) parsen
        // 3. XML bauen

        $this->buch = $buch;
        $this->l('Starte Export von Buch <a href="'.MEDIAWIKI_URL.'/'.MEDIAWIKI_INDEX.'/'.$buch.'">'.$buch.'</a>', 'debug');

        $this->pageTitle = $this->buch;
        $this->getWikiExport();
        $this->setBuchUebersichtContent();

        foreach ($this->buchKapitel as $kapitel) {
            $this->l('Starte Export von Kapitel <a href="'.MEDIAWIKI_URL.'/'.MEDIAWIKI_INDEX.'/'.$kapitel.'">'.$kapitel.'</a>', 'debug');
            $this->pageTitle = $kapitel;
            $this->getWikiExport(TRUE);
            $this->setKapitelContent();
            $this->getHeadlinesAndContent();
        }
        $output = $this->formatAsXML();

        // Log ggfls. ausgeben
        if ($this->error OR DEBUG) {
            $html  = '';
            $html .= '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';
            $html .= '<html><head>
                        <title>Ein Fehler beim Volxbibel-MediaWiki Export ist aufgetreten</title>
                        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
                        <style type="text/css">.log {width:600px; padding:10px; text-align:left; border:1px solid black;} #debug {background-color:#BFF5A3;}  #info {background-color:#FFFBDF;} #error {background-color:#FFAA99;} #pre {display:inline; font-family:monospace;} .senderror {display:blocl; position:relative; top:-20px; height:0px; width:780px; text-align:right;}</style>
                        <link rel="stylesheet" type="text/css" href="reset-fonts-grids.css" />
                        </head><body>';
            $i = NULL;
            foreach ($this->log as $value) {
                $html .= '<div class="log" id="'.$value['level'].'">'.$value['msg'].'</div>';
                if ($value['level'] === 'error') {
                    $i++;
                    $html .= '<div class="senderror"><a target="_blank" href="fehlerFeedback.php?error='.urlencode($value['msg']).'">Fehler Feedback senden<a/></div>';
                }
            }
            echo $html;
            echo '<br /><div class="log">Gelb = Informationen zum Export<br />Rot = Fehlermeldungen, Export wurde fehlerhaft ausgeführt<br/>Grün = Debug Meldungen<br /><br />';
            if ($i) {
                echo $i.' schwere Fehler festgestellt</div>';
            } else {
                echo 'Keine schweren Fehler festgestellt</div>';
            }
        } else {
            // XML ausgeben / exportieren
            // @todo soltle nich thier geschene, sondern ausserhalb der klasse, klasse sollnur xml ausgeben...
            //if (true) {
            if ($showXML) {
                #header("Content-type: application/xhtml+xml");
                echo '<pre>'.htmlentities($output);
            }

            // Pfad zu Exportdatei
            if ($nummer) {
                $nummer = str_pad($nummer, 2, '0', STR_PAD_LEFT).'_';
            }
            $path = dirname(__FILE__).'/xml/'.$nummer.str_replace(array('ö', 'ä', 'ü'), array('oe', 'ae', 'ue'), $this->buch).'.xml';

            // Export speichern
            file_put_contents($path, $output);
        }
    }

    /**
     * Holt das XML des Volxbibel-MediaWiki
     *
     * @param	bool 	Kapitel oder Übersichtsseite einlesen
     * @todo Doku
     */
    function getWikiExport($kapitelToParse = FALSE) {
        if ($kapitelToParse) {
            $uri = MEDIAWIKI_URL.'/'.MEDIAWIKI_EXPORTPATH.'/'.MEDIAWIKI_NAMESPACE.str_replace(' ', '_', $this->pageTitle);
        } else {
            $uri = MEDIAWIKI_URL.'/'.MEDIAWIKI_EXPORTPATH.'/'.str_replace(' ', '_', $this->pageTitle);
        }
        // Log
        $this->l('<a href="'.$uri.'" target="_blank">'.$uri.'</a> aufgerufen zum parsen', 'debug');

        // Get content after login
        $xml = $this->wikiLogin($uri);

        $XML = simplexml_load_string($xml);
        $this->XML = $XML;
    }

    /**
     * Loginto MediaWiki with user which can read
     *
     * @param   string  URI to wiki export page
     * @return  string  Wiki page with wiki syntax
     */
    function wikiLogin($uri) {
        require_once 'HTTP/Client.php';

        $loginUrl = MEDIAWIKI_URL.'/index.php?title=Spezial:Anmelden&action=submitlogin&type=login';
        $param['wpName']            = 'Powtac';
        $param['wpPassword']        = 'Simon123';
        $param['wpLoginattempt']    = 'Anmelden';

        // login
        $Request = new HTTP_Client();
        $Request->post($loginUrl, $param);

        // get content of export page
        $Request->get($uri);
        $response = $Request->currentResponse();

        return $response['body'];
    }

    /**
     * Gibt den Buchuntertitel und die Kapitelnamne einer Buchseite aus
     *
     * Buchuntertitel = Sprechende Beschreibung eines Buches z.B.: "Die
     * Geschichte von den Aposteln" sowie ggf. zusätzliche Informationen
     *
     * Kapitelnamen = Namen der Seiten der Kapitel im Volxbibel- MediaWiki z.B.:
     * "Apostelgeschichte 1"
     *
     * @param 	Array	KapitelTitel->KapitelNamen
     * @access  private
     * @todo	Einheitliche Formatierung für Buchuntertitel fehlt Siehe Bug#48
     */
    function setBuchUebersichtContent() {
        $buchUebersichtContent = (string)$this->XML->page->revision->text[0];

        // Buchuntertitel
        $pattern = '/(.*)<div class="kapiteluebersicht">/s';
        preg_match_all($pattern, $buchUebersichtContent, $result3);

        // Buchuntertitel Überschriften formatieren
        // siehe @todo
        $result3 = str_replace('== Die Kapitel ==', '', (string)trim($result3[1][0]));

        // Kapitelnamen
        $pattern = '/\[\[([^]]+)\]\]/';
        preg_match_all($pattern, $buchUebersichtContent, $result1);
        $kapitel = $result1[1]; // Array, kein trim() oder (string)!

        // Wenn fehlerhaft Kapitel- statt Buchübersicht aufgerufen wurde
        if (/*strlen($buchTitel) <= 0 OR*/ count($kapitel) <= 1) {
            $this->l('Keinen (Buchtitel und keine/nur ein) Kapitel gefunden, Kapitel statt Buchübersichtsseite aufgerufen?', 'info');
        }

        if (count($kapitel) <= 0) {
            $this->l('Kein Kapitel gefunden', 'error');
        }

        // Bei Kapitelnamen wie z.B.: Johannes1|Joh1 "|" entfernen
        foreach ($kapitel as $data) {
            #if ($data === 'Matthäus 16') {
                if (!(substr($data, 0, strlen('Bild:')) === 'Bild:')) {
                    if (strpos($data, '|')) {
                        $kapitel2[] = substr($data, 0, strpos($data, '|'));
                    } else {
                        $kapitel2[] = $data;
                    }
                }
            #}
        }

        $this->buchUntertitel = trim($result3);

        $this->buchKapitel = $kapitel2;
    }

    /**
     * Der für das Kapitel relevante Teil aus dem XML-Export
     *
     * Nicht mehr notwendig: "Formatvorlage im Volxbibel-MediaWiki muss
     * unbedingt beachtet werden! Z. B.: "__TOC__" und "__NOTOC__" müssen
     * vorkommen!"
     *
     *
     *
     * @todo	Mehrfache Fundstellen abfangen als Fehler
     * @access  private
     */
    function setKapitelContent() {
        $kapitelContent = (string)$this->XML->page->revision->text[0];

//        if (!preg_match('/.*__TOC__.+$/s', $kapitelContent)) {
//            $this->l('Im Kapitelinhalt Wiki-Formatierung "__TOC__" nicht gefunden', 'error');
//        }
//
//        $pattern = '/__TOC__(.*)$/s';
//
//        preg_match_all($pattern, $kapitelContent, $result);
//
//        // Mehrfache bzw. keinerlei Fundstellen abfangen
//        // siehe @todo
//
//
//        $string = trim($result[1][0]);

        // @todo:
        // __TOC__ herausparsen....


        $string = trim($kapitelContent);

        if(strlen($string) <= 0) {
            $this->l('Kein Kapitelinhalt gefunden in "<div id="pre">'.$kapitelContent.'</div>" evtl. "__TOC__" und "__NOTOC__" nicht vorhanden <a href="'.MEDIAWIKI_URL.'/'.MEDIAWIKI_INDEX.'/'.$this->pageTitle.'" target="_blank">Zeige Kapitel '.$this->pageTitle.'</a>', 'error');
        }

        $this->kapitelContent = $string;
    }

    /**
     * Parst das Kapitel nach Überschriften, Versnummern und Verstext
     *
     * @return 	Array	<div id="pre">
     * 					Kapitel [level]
     * 					Kapitel	[verse]
     * 					Kapitel	[verse][int Versnummer] -> Verstext
     * 					</div>
     * @access  private
     * @todo	deutsche Umlaute/Satzzeichen bei Anchor für Link direkt zum
     * Abschnitt ersetzen, hier gibt es ein Encoding/Unicode/HTML Problem
     * fussnote erweiterung testen
     */
    function getHeadlinesAndContent() {
        $array = explode("\n", $this->kapitelContent);

        $h = NULL;
        // Jede Zeile
        foreach ($array as $key => $value) {
            $value = trim($value);
            // Wenn Text enthält
            if (strlen($value) > 0 AND $value !== '----' AND $value !== '__NOTOC__' AND substr($value, 0,2) !== '{{') {
                // Abschnittnamen
                if (preg_match('/^(={1,5})([^=]*)(={1,5})$/', $value, $kapitelResult)) {
                    // Überschriften Syntax Ok?
                    if (strlen($kapitelResult[1]) !== strlen($kapitelResult[3])) {
                        $this->l('Zeile "<div id="pre">'.$value.'</div>" fehlerhafte Überschriften Formatierung, ungleiche Anzahl von "="', 'error');
                    } else {
                        $ueberschrift = trim($kapitelResult[2]);
                        if (strlen($ueberschrift) <= 0) {
                            $this->l('Fehlerhafter Abschnittname bei "<div id="pre">'.$value.'</div>"');
                        }
                        $kapitel[$ueberschrift]['level'] = strlen($kapitelResult[1]);
                    }
                // Vers
                } else if (preg_match('/^([\d{1,3}|\d{1,3}\/\d{1,3}|\d{1,3}-\d{1,3}]+) (.*)/s', $value, $versResult)) {
                    if ($ueberschrift === NULL) {
                        $this->l('Zeile "<div id="pre">'.$value.'</div>" keinem Kapitel zugeordnet <a href="'.MEDIAWIKI_URL.'/'.MEDIAWIKI_INDEX.'/'.$this->pageTitle.'">Zeige Kapitel</a>', 'info');
                        $ueberschrift = '&nbsp;';
                    }
                    $vers = trim($versResult[2]);
                    if (strlen($vers) <= 0) {
                        $this->l('Fehlerhafter Vers bei "<div id="pre">'.$value.'</div>"');
                    }

                    // kursive Verse
                    if (substr($vers, 0, 2) === '\'\'' AND substr($vers, strlen($vers) - 2) === '\'\'') {
                        $vers = '<Kursiv>'.str_replace('\'\'', '', $vers).'</Kursiv>';
                    }

                    // alternative Verse (Versnummer ist schon belegt)
                    if (isset($kapitel[$ueberschrift]['verse'][$versResult[1]])) {
                        $original = $kapitel[$ueberschrift]['verse'][$versResult[1]];
                        $kapitel[$ueberschrift]['verse'][$versResult[1]] = null;
                        $kapitel[$ueberschrift]['verse'][$versResult[1]]['original']    = $original;
                        $kapitel[$ueberschrift]['verse'][$versResult[1]]['alternativ']  = $vers;
                    } else {
                        $kapitel[$ueberschrift]['verse'][$versResult[1]] = $vers;
                    }


                // Fußnote z.B.:*
                } else if (preg_match('/^\((\*+)\) (.*)/', $value, $fussnoteResult)) {

                    // z.B.: [[Menschensohn|Siehe Erklärung in Matthäus 8]]
                    if (preg_match('/\[\[.*\|(.*)\]\](.*)/', $fussnoteResult[2], $fussnoteResult2)) {
                        $kapitel[$ueberschrift]['fussnote'][$fussnoteResult[1]] = $fussnoteResult2[1].$fussnoteResult2[2];
                    } else {
                        $kapitel[$ueberschrift]['fussnote'][$fussnoteResult[1]] = $fussnoteResult[2];
                    }

                    // Kein Fehler mehr, sondern Ausnahme (s.u.)
                    // $this->l('Fussnote gefunden in "<a href="'.MEDIAWIKI_URL.'/'.MEDIAWIKI_INDEX.'/'.$this->pageTitle.'">'.$this->pageTitle.'</a>" in Zeile "'.$key.'": "<div id="pre">'.$value.'</div>"', 'info');

                // Fußnote z.B.:*4*
                } else if (preg_match('/^\((\*\d+\*)\) (.*)/', $value, $fussnoteResult)) {

                    // z.B.: [[Menschensohn|Siehe Erklärung in Matthäus 8]]
                    if (preg_match('/\[\[.*\|(.*)\]\](.*)/', $fussnoteResult[2], $fussnoteResult2)) {
                        $kapitel[$ueberschrift]['fussnote'][$fussnoteResult[1]] = $fussnoteResult2[1].$fussnoteResult2[2];
                    } else {
                        $kapitel[$ueberschrift]['fussnote'][$fussnoteResult[1]] = $fussnoteResult[2];
                    }

                } else {
                    // Ausnahme z.B. Dein Johannes
                    $kapitel[$ueberschrift]['ausnahme'][] = $value;

                    // bisherige Fehlermeldung
                    $anchor = str_replace(array(' '), array('_'), $ueberschrift);
                    $this->l($this->formatWarning($value, null, null, 'Weder als Überschrift noch als Vers erkannt, wird als Ausnahme formatiert!', MEDIAWIKI_URL.'/'.MEDIAWIKI_INDEX.'/'.$this->pageTitle.'#'.$anchor), 'info');
                }
            }
        }
        $this->kapitelArray[] = $kapitel;
    }

    /**
     * Formatiert ein Buch nach XML Vorgaben von Oldmedia
     *
     * @return 	XML Formatiertes Buch in XML
     * @access	public
     * @todo 	pageTitel zu jedem Titel bereit halten, damit Kommentar
     * hinzugefügt werden kann
     */
    function formatAsXML() {
        $return = '';
        $return .= '<?xml version=\'1.0\' encoding=\'UTF-8\'?>'."\n";
        $return .= '<!--Export von '.MEDIAWIKI_URL.'/'.MEDIAWIKI_INDEX.'/'.$this->buch."\n".date('c')."\n".'Referrer http://'.$_SERVER["SERVER_NAME"].''.$_SERVER["PHP_SELF"]."\n".'Namespace '.MEDIAWIKI_NAMESPACE.'-->'."\n";
        $return .= '<Bibel>'."\n";
        $return .= '<Buch>'."\n";
        $return .= '<Titel>'.$this->buch.'</Titel>'."\n";
        if (!empty($this->buchUntertitel)) {
            $return .= '<Untertitel>'.$this->buchUntertitel.'</Untertitel>';
        }

        // KapitelArray aus getHeadlinesAndContent()
        if (is_array($this->kapitelArray)) {
            // Kapitel
            foreach ($this->kapitelArray as $key => $kapitel) {

                // siehe @todo
                // $return .= '<!-- Export von '.MEDIAWIKI_URL.'/'.MEDIAWIKI_INDEX.'/'.$this->pageTitle.' -->'."\n";

                $return .= '<Kapitel>'."\n";
                $return .= '<Kapitelziffer>'.($key + 1).'</Kapitelziffer>'."\n";
                if (is_array($kapitel)) {
                    // Abschnitte
                    foreach ($kapitel as $key => $value) {
                        $return .= '<Abschnitt>'."\n";
                        // Textüberschrift
                        if ($key !== '&nbsp;') {
                            $return .= '<Us1>'.$key.'</Us1>'."\n";
                        }
                        $return .= '<Grundtext>'."\n";
                        // Verse
                        foreach ($value['verse'] as $versnummer => $vers) {

                            if (!is_array($vers)) {
                                $return .= '<Vers>'."\n";
                                $return .= '<Versziffer>'.$versnummer.'</Versziffer>'."\n";
                                $return .= '<Verstext>'.$vers.'</Verstext>'."\n";
                                $return .= '</Vers>'."\n";
                            } else {

                                $versOri = $vers['original'];

                                $return .= '<Vers>'."\n";
                                $return .= '<Versziffer>'.$versnummer.'</Versziffer>'."\n";
                                $return .= '<Verstext>'.$versOri.'</Verstext>'."\n";
                                $return .= '</Vers>'."\n";

                                $versAlt = $vers['alternativ'];

                                $return .= '<Alternativ>'."\n";
                                $return .= '<Vers>'."\n";
                                $return .= '<Versziffer>'.$versnummer.'</Versziffer>'."\n";
                                $return .= '<Verstext>'.$versAlt.'</Verstext>'."\n";
                                $return .= '</Vers>'."\n";
                                $return .= '</Alternativ>'."\n";

                            }
                        }
                        $return .= '</Grundtext>'."\n";
                        // Fussnoten
                        if (is_array($value['fussnote'])) {
                            foreach ($value['fussnote'] as $fussnotenummer => $fussnotetext) {
                                $return .= '<Fussnote>'."\n";
                                $return .= '<Fussnotenummer>'.$fussnotenummer.'</Fussnotenummer>'."\n";
                                $return .= '<Fussnotetext>'.$fussnotetext.'</Fussnotetext>'."\n";
                                $return .= '</Fussnote>'."\n";
                            }
                        }
                        // Ausnahmen
                        // Fussnoten
                        if (is_array($value['ausnahme'])) {
                            foreach ($value['ausnahme'] as $ausnahmetext) {
                                $return .= '<Ausnahme>'."\n";
                                $return .= $ausnahmetext."\n";
                                $return .= '</Ausnahme>'."\n";
                            }
                        }

                        $return .= '</Abschnitt>'."\n";
                    }
                } else {
                    $this->l('$kapitel = "<div id="pre">'.$kapitel.'</div>" ist kein Array bei Kapitel "<div id="pre">'.($key + 1).'</div>", evl. ist Fehler im nächsten Kapitel', 'error');
                }
                $return .= '</Kapitel>'."\n";
            }
        } else {
            $this->l('$this->kapitelArray = "<div id="pre">'.$this->kapitelArray.'</div>" ist kein Array', 'error');
        }

        $return .= '</Buch>'."\n";
        $return .= '</Bibel>'."\n";
        return $return;
    }

    /**
     * Log
     *
     * @access 	public
     * @param 	String 	Log Meldung
     * @param	String	Log Level 'error', 'info' oder 'debug'
     */
    function l($msg, $level = 'debug') {
        if ($level === 'error') $this->error = TRUE;
        if (in_array($level, array('error', 'info', 'debug'))) {
            $this->log[(count($this->log) + 1)]['level']    = $level;
            $this->log[count($this->log)]['msg']            = $msg;
        } else {
            $this->l('VolxbibelExport::l() wurde mit einem unbekannten Loglevel aufgerufen', 'debug');
            $this->log[(count($this->log) + 1)]['level']    = 'debug';
            $this->log[count($this->log)]['msg']            = $msg;
        }
    }


    function formatWarning($line = null, $ueberschrift = null, $prevLine = null, $fehlerBeschr = null, $url = null) {
        $return  = '';
        if ($ueberschrift)  $return .= 'Bei <b><pre>'.$ueberschrift.'</pre></b><br />';
        if ($prevLine)      $return .= 'Vorherige Zeile: <pre>'.$prevLine.'</pre><br />';
        if ($line)          $return .= '<pre style="color:red; font-weight:bold;">'.$line.'</pre><br />';
        if ($fehlerBeschr)  $return .= $fehlerBeschr.'<br />';
        if ($url)           $return .= '<a href="'.$url.'" target="_blank">Zeige Abschnitt im Wiki</a>';
        return $return;
    }
}

// Debug
//$title = 'Offenbarung';
//$Export = new VolxbibelExport($title);
?>