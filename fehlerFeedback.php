<?php
/*
 * volxbibelwiki
 * Simon Brüchner, 06.02.2007
 */
if ($_REQUEST['error']) {
    $msg = urldecode(trim(stripslashes($_REQUEST['error'])));
    if (mail('mail@examle.com', 'Fehler beim Export '.date('c'), strip_tags($msg, '<a>'))) {
        echo 'Danke!';
    } else {
        echo 'Fehler beim Feedback';
    }
}
?>