Auto-Magical MySQL Database Backup Script Generator
==================

A simple PHP Class that can generate .SQL exports (just like the exports from phpMyAdmin).

## Usage (See cron.php for a full example)
First, instantiate the class with your MySQL Username, Password, and (optional) host.

 - $database = new backupScriptGetDatabases('Username', 'PaSsWoRd!');
 - $database = new backupScriptGetDatabases('Username', 'PaSsWoRd!','localhost');
 - $database = new backupScriptGetDatabases('Username', 'PaSsWoRd!','mysql.remote.net');

Once connected you can call:

- $database->getDatabaseNames()
  - Returns an array of the names of the databases

And

-$database->generateBackupScript('the_db_you_want_to_backup')
  - Which returns a string of the backup.