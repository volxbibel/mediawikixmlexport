<?php
/*
 * volxbibelwiki2
 * Simon Bruechner, 04.07.2008
 */

/**
 * Liste mit allen biblischen Büchern (Neues Testament) aus dem Volxbibel-
 * MediaWiki
 *
 * exakt in der Schreibweise aus dem Wiki! Namensänderungen und Ergänzugen im
 * Wiki müssen hier auch gepflegt werden! Deutsche Umlaute sind erlaubt, genauso
 * ".", "," und " " sowie Ziffern
 *
 * @see     http://wiki.volxbibel.com
 */
$arrayNT = array(
    'Matthäus',
    'Markus',
    'Lukas',
    'Johannes',
    'Apostelgeschichte',
    'Römer',
    '1.Korinther',
    '2.Korinther',
    'Galater',
    'Epheser',
    'Philipper',
    'Kolosser',
    '1.Thessalonicher',
    '2.Thessalonicher',
    '1.Timotheus',
    '2.Timotheus',
    'Titus',
    'Philemon',
    'Hebräer',
    'Jakobus',
    '1.Petrus',
    '2.Petrus',
    '1.Johannes',
    '2.Johannes',
    '3.Johannes',
    'Judas',
    'Offenbarung',
);

$arrayAT = array(
    '1.Mose',
    'Psalmen',
);

require_once 'HTML/QuickForm.php';
$Form = new HTML_QuickForm('exportform');

// NT
foreach ($arrayNT as $buch) {
    $group[] =& HTML_QuickForm::createElement('checkbox', $buch, $buch, $buch/*, array('checked' => 'true')*/);
}

// AT
foreach ($arrayAT as $buch) {
    $group2[] =& HTML_QuickForm::createElement('checkbox', $buch, $buch, $buch/*, array('checked' => 'true')*/);
}

$Form->addGroup($group, 'nt', 'Neues Testament:', '<br />');
$Form->addGroup($group2, 'at', 'Altes Testament<br />(experimentell):', '<br />');

$Form->addElement('submit', null, 'Exportiere Ausgewählte Bücher');

if ($Form->validate()) {

    // XML Ordner leeren
    function remove_directory($dir) {
      if ($handle = opendir("$dir")) {
       while (false !== ($item = readdir($handle))) {
         if ($item != "." && $item != "..") {
           if (is_dir("$dir/$item")) {
             remove_directory("$dir/$item");
           } else {
             unlink("$dir/$item");
           }
         }
       }
       closedir($handle);
       rmdir($dir);
      }
    }
    remove_directory(dirname(__FILE__).'/xml/');
    mkdir(dirname(__FILE__).'/xml/');

    /**
     * Gibt die Reihenfolge der Bücher in der Bibel aus
     */
    function getKey($element) {
        global $arrayNT;
        foreach ($arrayNT as $key => $value) {
            if ($element === $value) {
                // Starte mit 1
                return ($key + 1);
            }
        }
        global $arrayAT;
        foreach ($arrayAT as $key => $value) {
            if ($element === $value) {
                // Starte mit 1
                return ($key + 1);
            }
        }
    }

    #var_dump($Form->exportValue('nt'));
    #var_dump($Form->exportValue('at'));
    require_once dirname(__FILE__).'/export.php';
    foreach ($Form->exportValue('nt') as $buch => $value) {
        echo 'Exportiere: '.$buch.' <br/>';
        new VolxbibelExport($buch, getKey($buch), FALSE);
    }

    if (TRUE) {
        require_once "File/Archive.php";
        $fileName = 'volxbibel-'.date('dmY').'.zip';

        File_Archive::extract(
            $src = array(dirname(__FILE__).'/xml/*.xml'),
            File_Archive::toArchive(dirname(__FILE__).'/'.$fileName, File_Archive::toFiles() )
        );

        if (is_file(dirname(__FILE__).'/'.$fileName)) {
            $download = TRUE;
        }
    }
}

$Form->display();
if (!empty($download)) {
    echo '<script language="javascript">
            setTimeout("document.location.href=\''.dirname($_SERVER['PHP_SELF']).'/download.php?file='.$fileName.'\';", 1000);
            </script>';
}
?>