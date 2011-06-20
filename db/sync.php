#!/usr/local/bin/php -q
<?php
/**
 * Синхронизатор версий базы данных
 * 
 * @see readme.txt  
 * 
 * @copyright 2006-2011 LanMediaService, Ltd.
 * @license    http://www.lanmediaservice.com/license/1_0.txt
 * @author Ilya Spesivtsev <macondos@gmail.com>
 * @version $Id: sync.php 700 2011-06-10 08:40:53Z macondos $
 */

require_once dirname(__FILE__) . '/config.php';

// @codingStandardsIgnoreStart
function writeToLog($str)
{
    echo $str;
    file_put_contents(
        dirname(__FILE__) . '/logs/' . date('Y-m-d') . '.log', $str, FILE_APPEND
    );
}
// @codingStandardsIgnoreEnd

$mysqlLink = mysql_connect(
    $config['mysql']['host'],
    $config['mysql']['username'],
    $config['mysql']['password']
);
if (!$mysqlLink
    || !mysql_select_db($config['mysql']['db'])
) {
    throw new Exception(mysql_error(), mysql_errno());
}

//check version
$res = mysql_query("SHOW TABLE STATUS LIKE '_version'");
$row = mysql_fetch_assoc($res);
$revisionDbNumber = $row['Comment'];


chdir(dirname(__FILE__));
if ($config['svn']['autoupdate']) {
    exec('svn update');
}
//scan pathes
$files = scandir(dirname(__FILE__));
$newPathes = array();
$lastPatchRevision = 0;
foreach ($files as $file) {
    if (preg_match('{update-(\d+).sql}', $file, $matches)) {
        $revisionPatchNumber = (int)$matches[1];
        if ($revisionPatchNumber > $revisionDbNumber) {
            $newPathes[$revisionPatchNumber] = $file;
        }
    }
}

if ($config['mysql']['password']) {
    $optPassword = " -p" . $config['mysql']['password'];
} else {
    $optPassword = '';
}

//apply patches
if (count($newPathes)) {
    ksort($newPathes);
    foreach ($newPathes as $revisionPatchNumber => $patchFile) {
        $cmd = "mysql -u " . $config['mysql']['username']
             . $optPassword
             . " " . $config['mysql']['db']
             . " -v -v -v < $patchFile";
        
        writeToLog("\n\nApply patch $patchFile ...\n");
        $mysqlDumpLines = array();
        exec($cmd, $mysqlDumpLines);
        foreach ($mysqlDumpLines as $line) {
            writeToLog("\n$line");
        }
        mysql_query("ALTER TABLE `_version` COMMENT = '$revisionPatchNumber'");
    }
}


$cmd = "mysqldump -u " . $config['mysql']['username']
     . $optPassword
     . " " . $config['mysql']['db']
     . " --no-data --skip-add-drop-table --no-set-names "
     . " --compact --skip-set-charset";

echo "\nDumping DB schema...";
exec($cmd, $mysqlDumpLines);
$dump = '';
foreach ($mysqlDumpLines as $line) {
    echo "\n$line"; //progress;
    $dump .= "\n";
    if (strpos($line, 'SET')!==0 && strpos($line, '/*!')!==0) {
        $line = str_replace("`{$config['mysql']['prefix']}", '`', $line);
        $line = preg_replace('/ ENGINE=(\w+)/i', '', $line);
        $line = preg_replace('/ AUTO_INCREMENT=\d+/i', '', $line);
        $dump .= $line;
    }
}
file_put_contents(dirname(__FILE__) . '/schema.sql', $dump);
