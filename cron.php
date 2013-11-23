<?php
/**
 * Example Cron Script:
 *
 * Saves a .sql file for each database. The file is saved in /backup/YYYY-MM-DD/, and the filename looks like
 * (DB Name)-(unix timestamp).sql
 */

//report all errors so we know a backup failed
error_reporting(E_ALL);
ini_set('display_errors', '1');

//get the backup class
require_once 'GenerateMySQLBackupScripts.class.php';

//create a new database object with our root user and password
//optional to use root, but it will make backing up multiple tables easier
$database = new GenerateMySQLBackupScripts('root', 'Pa55word!');

//One level deeper than your public_html
//This is so the backups are not publicly accessible
$dir = __DIR__ . '/../backup/';
if (!is_dir($dir)) {
    mkdir($dir);
}
$dir .= date('Y-m-d') . '/';
if (!is_dir($dir)) {
    // if this doesn't exist, make it
    mkdir($dir);
}

//for each database
foreach ($database->getDatabaseNames() as $singleDB) {
    if ($singleDB == 'information_schema') {
        continue; #I don't want to backup the information_schema table.
    }
    //generate a backup script and write the file to the server
    file_put_contents($dir.$singleDB . '-' . time() . '.sql', $database->generateBackupScript($singleDB));
    
    //generate a backup script and save directly to a file handle
    //$database->writeBackupScriptToFile($singleDB, $dir . $singleDB . '-' . time() . '.sql');
}



