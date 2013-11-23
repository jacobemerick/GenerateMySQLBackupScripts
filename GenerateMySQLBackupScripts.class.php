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
        try {
            $this->dbh = new PDO("mysql:host=$host", $rootUsername, $rootPassword);
        } catch (PDOException $e) {
            exit("Connection failed - {$e->getMessage()} - aborting.");
        }
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
        $return .= "USE {$database};\n\n";
        
        $table_list_statement = $this->getTableListStatement($database);
        while ($table_row = $table_list_statement->fetch(PDO::FETCH_NUM)) {
            $table = $table_row[0];
            
            $return .= "DROP TABLE IF EXISTS {$table};\n\n";
            
            $table_create_statement = $this->getTableCreateStatement($database, $table);
            while ($table_create_row = $table_create_statement->fetch(PDO::FETCH_ASSOC)) {
                $return .= $table_create_row['Create Table'] . ";\n\n";
            }
            
            $row_statement = $this->getRowStatement($database, $table);
            while ($row = $row_statement->fetch(PDO::FETCH_NUM)) {
                $insert_row = "INSERT INTO {$table} VALUES (";
                for ($i = 0; $i < count($row); $i++) {
                    $insert_row .= $this->dbh->quote($row[$i]);
                    if ($i < (count($row) - 1)) {
                        $insert_row .= ',';
                    }
                }
                $insert_row .= ");\n";
                $return .= $insert_row;
            }
            
            $return .= "\n\n\n";
        }
        
        return $return;
    }

    /**
     * Write backup script directly to a file (to save on memory)
     *
     * @param $database string      The name of the database you'd like to backup
     * @param $file_name string     Name of the file that you want to write to
     * @return bool                 Whether or not the write was successful
     */
    public function writeBackupScriptToFile($database, $file_name)
    {
        $file_handle = $this->getFileHandle($file_name);
        
        fwrite($file_handle, "USE {$database};\n\n");
        
        $table_list_statement = $this->getTableListStatement($database);
        while ($table_row = $table_list_statement->fetch(PDO::FETCH_NUM)) {
            $table = $table_row[0];
            
            fwrite($file_handle, "DROP TABLE IF EXISTS {$table};\n\n");
            
            $table_create_statement = $this->getTableCreateStatement($database, $table);
            while ($table_create_row = $table_create_statement->fetch(PDO::FETCH_ASSOC)) {
                fwrite($file_handle, $table_create_row['Create Table'] . ";\n\n");
            }
            
            $row_statement = $this->getRowStatement($database, $table);
            while ($row = $row_statement->fetch(PDO::FETCH_NUM)) {
                $insert_row = "INSERT INTO {$table} VALUES (";
                for ($i = 0; $i < count($row); $i++) {
                    $insert_row .= $this->dbh->quote($row[$i]);
                    if ($i < (count($row) - 1)) {
                        $insert_row .= ',';
                    }
                }
                $insert_row .= ");\n";
                fwrite($file_handle, $insert_row);
            }
            
            fwrite($file_handle, "\n\n\n");
        }
        
        return fclose($file_handle);
    }

    protected function getFileHandle($file_name)
    {
        $file_handle = fopen($file_name, 'w');
        if ($file_handle === false) {
            exit("Could not write to {$file_name} - aborting.");
        }
        
        return $file_handle;
    }

    protected function getTableListStatement($database)
    {
        $query = "SHOW TABLES FROM `{$database}`";
        return $this->getStatement($query);
    }

    protected function getTableCreateStatement($database, $table)
    {
        $query = "SHOW CREATE TABLE `{$database}`.`{$table}`";
        return $this->getStatement($query);
    }

    protected function getRowStatement($database, $table)
    {
        $query = "SELECT * FROM `{$database}`.`{$table}`";
        return $this->getStatement($query);
    }

    protected function getStatement($query)
    {
        $statement = $this->dbh->prepare($query);
        if ($statement->execute() === false) {
            exit("Was not able to execute query - {$query} - aborting.");
        }
        
        return $statement;
    }

}