<?php
/*
 * volxbibelwiki
 * Simon Brüchner, 06.02.2007
 */
/**
 * Einfacher Download eines Zips
 */
if ($_REQUEST['file']) {
    $file = dirname(__FILE__).'/'.basename($_REQUEST['file']);
    
    // Download senden
    require_once 'HTTP/Download.php';
    $Dl =& new HTTP_Download();
    $Dl->setFile($file);
    $Dl->setContentDisposition(HTTP_DOWNLOAD_ATTACHMENT, basename($_REQUEST['file']));
    $Dl->guessContentType();
    $Dl->send();
    
    unlink($file);
}
?>