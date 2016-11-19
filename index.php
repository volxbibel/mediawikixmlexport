<?php
/*
 * volxbibelwiki
 * Simon Brüchner, 06.02.2007
 */

// HTML Header
$html = '';
            $html .= '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';
            $html .= '<html><head>
                        <title>Volxbibel Export</title>
                        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
                        <style type="text/css">.log {width:600px; padding:10px; text-align:left; border:1px solid black;} #debug {background-color:#BFF5A3;}  #info {background-color:#FFFBDF;} #error {background-color:#FFAA99;} #pre {display:inline; font-family:monospace;} .senderror {display:blocl; position:relative; top:-20px; height:0px; width:780px; text-align:right;}</style>
                        <link rel="stylesheet" type="text/css" href="reset-fonts-grids.css" />
                        </head><body>';
echo $html;
require_once './kapitelliste.php';
echo '</body></html>';
