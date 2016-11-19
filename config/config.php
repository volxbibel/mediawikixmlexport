<?php
/**
 * This is the Central Configuration File.
 */

/* START CUSTOM */

/* [Debug] */

/**
 * Debug
 *
 * Schaltet den Debugger an und aus.
 *
 * @default     false
 */
define('DEBUG', false);


/* [Volxbibel] */

/**
 * Interne Absolute Pfad zum MediaWiki
 *
 * @default             '/volxbibelwiki/wiki/'
 */
define('MEDIAWIKI_PATH', 'c:/www/volxbibelwiki/wiki');

/**
 * Relativer Pfad zum MediaWiki vom export-Ordner aus gesehen
 *
 * @default             '/volxbibelwiki/wiki/'
 */
define('MEDIAWIKI_PATH_REL', '../wiki/');

/**
 * URL zum  MediaWiki
 *
 * @default             'http://localhost/volxbibelwiki/wiki'
 */
define('MEDIAWIKI_URL', 'http://print.volxbibel.com');

/**
 * Index Datei auf dem Mediawiki Server
 *
 * @default             'index.php'
 */
define('MEDIAWIKI_INDEX', 'index.php');

/**
 * Relativer Pfad zum MediaWiki Export
 *
 * @default             'index.php/Spezial:Exportieren'
 */
define('MEDIAWIKI_EXPORTPATH', 'index.php/Spezial:Exportieren');

/**
 * Wiki Namespace
 *
 * implizit Versionen der Volxbibel
 *
 * '' 			// aktuelle Arbeitsversion
 * 'V1:' 		// aktuelle Druckversion
 * 'Testing:'	// zuk�nfigte Druckversion
 *
 * @default             ''
 * @default             'V1:'
 * @default             'Testing:'
 */
define('MEDIAWIKI_NAMESPACE', '');

/**
 * Format für Datumsausgaben
 *
 * Formatierung siehe http://php.net/date
 *
 * @default     'd.m.Y (H:i)'
 */
define('DATE_FORMAT', 'd.m.Y (H:i)');


/* [Wartung] */

/**
 * Wartungsmeldung
 *
 * @default     ''
 */
define('WARTUNGSMELDUNG', 'Temporarily Down');

/* [PHP Settings] */

/**
 * PHP-Debug-Meldungen
 *
 * @default     E_ALL & ~E_NOTICE
 */
define('ERROR_REPORTING', E_ALL &~ E_NOTICE);

/* END CUSTOM */


/**
 * Alle Einstellungen oberhalb können angepasst werden.
 * Ab hier sollten Sie nichts mehr ändern, außer Sie sind
 * sich absolut sicher was Sie tun.
 */

///* PEAR */
//ini_set('include_path', PEAR_PATH . ':' . ini_get('include_path'));

/* PHP */
/* register_globals */
ini_set('register_globals', 'off');

/* Fehler anzeigen */
ini_set('display_errors', 1);

/* Fehlerlevel */
error_reporting(ERROR_REPORTING);

/* DataObject Overload Kompatibilit�t */
define('DB_DATAOBJECT_NO_OVERLOAD', true);

// DB Config
#require_once 'config.dbDataObject.php';
?>