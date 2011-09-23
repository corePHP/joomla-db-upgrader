Joomla DB Upgrader for Joomla! 1.7
================================

What is it?
---------------------------------------
This is a very simple plugin to help development teams keep their databases in sync, by adding changes to the database to versioned files in the file system.

How does it work?
---------------------------------
It works by keeping track of a database version for the specified namespace, there is a default namespace called 'default'. This default namespace can be enabled on the plugin and will be run everytime a page loads.
Each time the page loads or when the db_check() method is called either manually or automatically, it will check the current DB version and will check the filesytem for any new database files. And will run these database queries.

How do I use it?
-----------------------------
Package the plugin, install and enable.

The plugin by default has a 'default' namespace, which starts at version 1000.

If the 'Do default' parameter is enabled on the plugin, it will automatically do this call:
```php
DBUpgrader::getInstance( 'default' )->db_check();
```
This uses the 'default' namespace.

When triggered it checks the current version of the database, and then checks the file system for any new files that have a higher version.

New files are added here, going from the root of your Joomla site:
    /plugins/system/dbupgrader/sql/NAMECSPACE_DBVERSION.sql
- NAMESPACE would be default (for this example)
- DBVERSION would be any number higher than 1000

Use case
-----------------------------
If you were developing an extension called com_xyz and you would have it on a version controll system

You would install this plugin and enable it, but don't enable the option to 'Do default'

On the administration file for the component located here:
    /administrator/components/com_xyz/xyz.php
You would add this line towards the beginning of the file:
```php
DBUpgrader::getInstance( 'com_xyz' )->db_check();
```
When developer Bob makes a change to his database, he would create this file, with the sql statements:
    /plugins/system/dbupgrader/sql/com_xyz_1001.sql
Bob would then commit his changes.

When developer Alice updates her repository and visits the backend of the xyz component, the DBUpgrader would find the new file with version 1001 and would execute those queries.

Now Alice's DB is in sync with Bob's!

Everyone is happy...

Roadmap
-----------------------------
- Add phing build script
- Add script to create symlinks

