PHPDbMigrate is a module that emulates the rails ActiveMigration in that you can now
make your database changes and revert them as you need to. No more will you have
to do a database imports only find that you can't roll them back.

Dependancies:
None

Use:

To use as a class, include a config array when instanciating the PHPDbMigrate object.
After which call the run method with the environment to execute in. So:

$migrate = new PHPDbMigrate($config_array);
$migrate->run([$version], [$evnironment]);

The $version argument is optional, NULL by default, and if given will leave the
schema update at that version after running. If $version is NULL then PHPDbMigrate will
run the migrations to update the database to the last migration file.

The $environment argument is also optional and by default is development. You can specify
any string that you like, i.e. qa, staging, production, etc, however a key also needs to
exist in the config array that you supply to the PHPDbMigrate constructor.

Migrations:
By default PHPDbMigrate looks for migrations in the migrations folder. All migration
files must begin with a sequental number. 001_ or 2009020104301601_ are fine as well.

When running migrations up, the migration file must include an [up] section.

When running migrations down, the migration file must include a [down] section.

Besides running sql commands, you can also run native PHP code. Just
use a code: statement and place the script code on the next line.

Migrating:
When you run PHPDbMigrate it will look for a database table called schema_info. If
no table is found then one will be created. From there PHPDbMigrate will process
all the migrations in your migration folder, from 1 to whatever.

Calling PHPDbMigrate with a version number will run all the migrations up to and
including that number as long as the version specified is greater than what was
found as the version in the schema_info table. The databases changes will be read
from the [up] section of the migration file.

If the version in the schema_info table is greater than the version passed to
PHPDbMigrate then the [down] section of the migration will be run. Up to but not
including the version given.

Thanks:
This is certainly a release candidate module and I still have a lot to do. As I
am more familiar with MySql db's, that's the first supported database. Certianly
more db's will be supported through the inclusion of new adaptars. The are relatively
easy to build, so if you come up with one just let me know. ;)

I also need to create an API area for this library. Please bear with me.

You'll find more information here: http://www.techraving.com/phpdbmigrate-activemigration-for-php/

Enjoy