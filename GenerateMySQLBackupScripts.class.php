<?php
/**
 * GenerateMySQLBackupScripts Class
 */

class GenerateMySQLBackupScripts
{

    private $dbh;

    /**
     * @param $rootUsername     Connection Username
     * @param $rootPassword     User's password
     * @param string $host optional, host of the database
     */
    public function __construct($rootUsername, $rootPassword, $host = 'localhost')
    {
        //connect to the database
        $this->dbh = new PDO("mysql:host=$host", $rootUsername, $rootPassword);
    }

    /**
     * Builds a list of databases.
     *
     * @return mixed    Array of table names
     */
    public function getDatabaseNames()
    {
        $tables = $this->dbh->query('SHOW DATABASES');
        $tables = $tables->fetchAll(PDO::FETCH_COLUMN, 0);
        return $tables;
    }

    /**
     * Generates the database backup script (just like phpMyAdmin would)
     *
     * @param $database string      The name of the database you'd like to backup
     * @return string               A string that is the backup script
     */
    public function generateBackupScript($database)
    {
        $return = '';
        $getTables = $this->dbh->prepare('SHOW TABLES FROM ' . $database);
        $getTables->execute();
        $getTables = $getTables->fetchAll();
        foreach ($getTables as $singleTable) {
            $return .= 'DROP TABLE ' . $singleTable[0] . ';';
            $sth = $this->dbh->prepare("SHOW CREATE TABLE `{$database}`.`{$singleTable[0]}`");
            $sth->execute();
            $create = $sth->fetch(PDO::FETCH_ASSOC);
            $return .= "\n\n" . $create['Create Table'] . ";\n\n";
            $getRows = $this->dbh->prepare("SELECT * FROM `{$database}`.`{$singleTable[0]}`");
            $getRows->execute();
            foreach ($getRows->fetchAll(PDO::FETCH_NUM) as $singleRow) {
                $return .= 'INSERT INTO ' . $singleTable[0] . ' VALUES(';
                for ($i = 0; $i < count($singleRow); $i++) {
                    $return .= "'" . addslashes($singleRow[$i]) . "'";
                    if ($i < count($singleRow) - 1) {
                        $return .= ','; //add a comma
                    }
                }
                $return .= ')' . "\n";
            }

            $return .= "\n\n\n";
        }
        return $return;
    }

}