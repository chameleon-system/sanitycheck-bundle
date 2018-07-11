Usage
=====

For an explanation of how checks work on a more low-level perspective, see the documentation of the SanityCheck library.

Defining Checks
---------------

A check is defined as a Symfony service. Let's look at an example:

.. code-block:: xml

    <service id="chameleon_system_core.check_php_version"  parent="chameleon_system_sanity_check.check.php_runtime_version">
        <argument>%chameleon_system_sanity_check.level.error%</argument>
        <argument>5.3.6</argument>
        <tag name="chameleon_system.sanity_check.check" translation_key="label.supportedphpversion" />
    </service>

Like any other Symfony service, a check needs a class or a parent service to perform the actual work. The class or parent
service describes the type of the check (what to check - if there is enough disk space, if some directory is writable and so on).
There are a few check classes and abstract check services defined in this bundle (see :ref:`predefined-checks`).
Note that it is mandatory that the check's service ID is prefixed with the bundle alias ("chameleon_system_core"
in this example). This allows for executing all checks for a specific bundle.

The first argument is always the error level of this check. This means, on which level should a message be raised if
the check actually detects an issue. There are some parameters defined for these levels:

.. code-block:: xml

    <parameters>
        <parameter key="chameleon_system_sanity_check.level.ok" type="constant">ChameleonSystem\SanityCheck\Outcome\CheckOutcome::OK</parameter>
        <parameter key="chameleon_system_sanity_check.level.notice" type="constant">ChameleonSystem\SanityCheck\Outcome\CheckOutcome::NOTICE</parameter>
        <parameter key="chameleon_system_sanity_check.level.warning" type="constant">ChameleonSystem\SanityCheck\Outcome\CheckOutcome::WARNING</parameter>
        <parameter key="chameleon_system_sanity_check.level.error" type="constant">ChameleonSystem\SanityCheck\Outcome\CheckOutcome::ERROR</parameter>
        <parameter key="chameleon_system_sanity_check.level.exception" type="constant">ChameleonSystem\SanityCheck\Outcome\CheckOutcome::EXCEPTION</parameter>
    </parameters>

Normally you will only need the levels NOTICE, WARNING and ERROR. Level OK is returned if everything was fine
(so you can see that the check was actually executed). Level EXCEPTION is returned if an exception
was thrown, preventing the check execution.

After the first argument, the check-specific arguments follow.

Each check also needs the tag "chameleon_system.sanity_check.check" in order to be recognized by the system.
There is an optional attribute "translation_key" which allows to provide localized names for the checks (so that
you may for example list all checks in a backend in a human-readable manner and execute single ones). If you provide
a translation_key, you also need to provide the corresponding texts. If you use the Chameleon system, these texts are
expected in the message domain "chameleon_system_sanitycheck".

Executing Checks
----------------

This bundle provides a console command for executing checks: "chameleon_system:sanitycheck:check".
For options see the help text given by the console help command.

Writing Custom Check Classes
----------------------------

Writing a custom check is very easy. You may either

a) Create a new Check class that implements `ChameleonSystem\\SanityCheck\\Check\\CheckInterface`.

or

b) Create a new Check class that extends `ChameleonSystem\\SanityCheck\\Check\\AbstractCheck` (recommended).

The `CheckInterface` interface requires you to implement two methods: `check()` and `getLevel()`. The `check` method does the actual
work and must return an array of `ChameleonSystem\\SanityCheck\\Outcome\\CheckOutcome` objects (the array may contain
one or more outcomes). The `getLevel()` method simply returns the configured level of the check.

The `AbstractCheck` class contains only a few lines of boilerplate code to initialize and return the check level.

The new check should be registered as a service as described above.

Writing Custom Check Execution
------------------------------

Alternatively you can of course implement your own execution to allow the administrator of your application to
perform checks (or automate this task using e.g. a cronjob). To do this, follow these steps:

Get CheckHandler
................

Inject `chameleon_system_sanity_check.check_handler` into one of your services/controllers.

Execute Checks
..............

Call one of the methods defined in the `CheckHandlerInterface` to execute either all checks or only some specific ones.
All of these methods return a list of check outcomes.

Get Output Object
.................

To print the check outcomes, it is best to use the defined check outputs. Inject the service
`chameleon_system_sanity_check.output_resolver` into your service/controller and call the `get` method providing the
desired output alias.

The predefined outputs are:

* default (echo to the browser or console)
* log (write to a logger - needs configuration)

Output Outcome List
...................

The output consists of two steps:

* gather all output (loop over the outcome list and call the `gather` method on the output, providing the outcome as an argument)
* commit the output (call the `commit` method once on the output)

This procedure allows for different kinds of outputs to work efficiently by storing the output in an internal buffer and
flushing this buffer at the end. For example you don't want to send a notification e-mail for every single
outcome, but a single one that contains all the collected lines).

When you implement your own output, buffering is optional. It is perfectly fine to output data in the `gather` method.

Complete example (container injection is not recommended, but only displayed here for demonstration purposes):

.. code-block:: php
    :linenos:

        $checkHandler = $container->get('chameleon_system_sanity_check.check_handler');
        $checkOutcomeList = $checkHandler->checkAll();

        $outputResolver = $container->get('chameleon_system_sanity_check.output_resolver');
        $output = $outputResolver->get('default');

        foreach($checkOutcomeList as $outcome) {
            $output->gather($outcome);
        }
        $output->commit();

Output Formatters
.................

An output formatter adds bells and whistles to outcome messages. This might be some HTML code or console formatting.

Normally you won't need to deal with these formatters - the default outputs will use the appropriate formatter for HTML
or console output (which are the predefined formatters).
If you want to set a custom formatter, add a compiler pass that replaces the respective argument in the output service.

Check Suites
------------

What was described in the previous sections completely suffices to write your own check execution code. But there is
also an easier way to bundle some checks and outputs, so that only a single line of code is needed to execute and
output checks. Such a bundle is called a check suite.

.. _predefined-checks:

Predefined Checks
-----------------

All predefined checks are implemented in the SanityCheck library. In this bundle, there are service definitions for each
of these checks which are described in this section.

DiskSpaceCheck
..............

Checks if a certain amount of disk space is available.

Configuration:

- check level
- directory (the disk on which this directory is located will be checked)
- thresholds

The thresholds parameter is an array of single threshold parameters. Each of these parameters consists of:

- value: the amount of space that needs to be available
- key: the check level to raise if the available disk space is below the given value

The value parameter needs to be in one of these formats:

- a numeric value of bytes
- a numeric value followed by one of ('B', 'KiB', 'MiB', 'GiB', 'TiB')
- a percentage value

Examples:

To raise a warning if below 1GiB and an error if below 100MiB use something like this:

.. code-block:: xml

    <service id="chameleon_system_core.check_disk_space" parent="chameleon_system_sanity_check.check.disk_space">
        <argument>%chameleon_system_sanity_check.level.error%</argument>
        <argument>%kernel.root_dir%</argument>
        <argument type="collection">
            <argument key="%chameleon_system_sanity_check.level.warning%">1GiB</argument>
            <argument key="%chameleon_system_sanity_check.level.error%">100MiB</argument>
        </argument>
        <tag name="chameleon_system.sanity_check.check" translation_key="label.diskspace" />
    </service>

To raise a warning if below 5% use something like this:

.. code-block:: xml

    <service id="chameleon_system_core.check_disk_space" parent="chameleon_system_sanity_check.check.disk_space">
        <argument>%chameleon_system_sanity_check.level.error%</argument>
        <argument>%kernel.root_dir%</argument>
        <argument type="collection">
            <argument key="%chameleon_system_sanity_check.level.warning%">5%</argument>
        </argument>
        <tag name="chameleon_system.sanity_check.check" translation_key="label.diskspace" />
    </service>

ExpressionCheck
...............

Checks if a given expression returns true. There are two caveats when using this check:

- it uses the PHP `eval` function without further checks, so be careful which expressions you use.
- a quite cryptic message is given if the check fails - a non-technical user will most likely find it difficult to understand.

Configuration:

- check level
- an array of expression strings

Examples:


FileExistsCheck
...............

Checks if a file or directory exists.

Configuration:

- check level
- an array of files or directories to check for
- base directory (optional) - if provided, all files/directories from the array parameter will be expected relative to this directory.

Examples:

To check if %kernel.root_dir%/cache and %kernel.root_dir%/logs exist use something like this:

.. code-block:: xml

    <service id="chameleon_system_core.check_files_exist"  parent="chameleon_system_sanity_check.check.file_exists">
        <argument>%chameleon_system_sanity_check.level.error%</argument>
        <argument type="collection">
            <argument>cache</argument>
            <argument>logs</argument>
        </argument>
        <argument>%kernel.root_dir%</argument>
        <tag name="chameleon_system.sanity_check.check" translation_key="label.filesexist" />
    </service>

FilePermissionCheck
...................

Checks if given permissions are granted on the given files. This check only makes sense if used on file systems that support permissions.

Configuration:

- check level
- an array of files or directories to check for
- an array of permissions to check - one or more of ['READ', 'WRITE', 'EXECUTE']
- base directory (optional) - if provided, all files/directories from the file array parameter will be expected relative to this directory.

Examples:

To raise an error if the default Symfony cache or log directory is not readable or not writable use something like this:

.. code-block:: xml

    <service id="chameleon_system_core.check_cms_dir_writable"  parent="chameleon_system_sanity_check.check.file_permission">
        <argument>%chameleon_system_sanity_check.level.error%</argument>
        <argument type="collection">
            <argument>cache</argument>
            <argument>logs</argument>
        </argument>
        <argument type="collection">
            <argument>READ</argument>
            <argument>WRITE</argument>
        </argument>
        <argument>%kernel.root_dir%</argument>
        <tag name="chameleon_system.sanity_check.check" translation_key="label.directorieswritable" />
    </service>


PhpModuleLoadedCheck
....................

Checks if certain PHP modules are loaded.

Configuration:

- check level
- an array of PHP modules; the names need to be provided in the same format which is output by `php -m`

Examples:

.. code-block:: xml

    <service id="chameleon_system_core.check_php_modules_loaded"  parent="chameleon_system_sanity_check.check.php_module_loaded">
        <argument>%chameleon_system_sanity_check.level.error%</argument>
        <argument type="collection">
            <argument>gd</argument>
            <argument>pdo_mysql</argument>
            <argument>xml</argument>
        </argument>
        <tag name="chameleon_system.sanity_check.check" translation_key="label.phpmodulesloaded" />
    </service>


PhpRuntimeVersionCheck
......................

Checks if a valid PHP version is used.

Configuration:

* check level
* allowed PHP version or versions

The allowed PHP version can be configured in several ways:

* a single version string to allow all PHP versions from this version and up
* an array of version constraints. A version constraint is either a string as described directly above, or an array
  consisting of a version information and an operator to apply (">", ">=", "==", "!=", "<=" or "<").

Examples:

To allow PHP version 5.3.6 and above use something like this:

.. code-block:: xml

    <service id="chameleon_system_core.check_php_version"  parent="chameleon_system_sanity_check.check.php_runtime_version">
        <argument>%chameleon_system_sanity_check.level.error%</argument>
        <argument>5.3.6</argument>
        <tag name="chameleon_system.sanity_check.check" translation_key="label.supportedphpversion" />
    </service>

To allow all PHP versions between 5.4.3 and 5.6.3 but not 5.5.3 use something like this:

.. code-block:: xml

    <service id="chameleon_system_core.check_php_version"  parent="chameleon_system_sanity_check.check.php_runtime_version">
        <argument>%chameleon_system_sanity_check.level.error%</argument>
        <argument type="collection">
            <argument>5.4.3</argument>
            <argument type="collection">
                <argument>5.6.3</argument>
                <argument>&lt;=</argument>
            </argument>
            <argument type="collection">
                <argument>5.5.3</argument>
                <argument>!=</argument>
            </argument>
        </argument>
        <tag name="chameleon_system.sanity_check.check" translation_key="label.supportedphpversion" />
    </service>


Predefined Outputs
------------------

AbstractTranslatingCheckOutput
..............................

Not an output class itself but an abstract base class that provides translation functionality. If you plan to write your
own output class, consider extending this class.

DefaultCheckOutput
..................

Uses `echo` statements to write to the current default output (browser or console).

Alias: `default`

LogCheckOutput
..............

Writes to a configured logger.
When using this output, you will need additional configuration in your config.yml (or .xml):

.. code-block:: yaml

    chameleon_system_sanity_check:
      output:
        log:
          logger: "myLoggerServiceId"

The service defined by `myLoggerServiceId` must implement `Psr\\Log\\LoggerInterface`.

Alias: `log`

NullCheckOutput
...............

Does not write anything. Use this if you think you need to :-)

Alias: `null`

MailCheckOutput
...............

Writes an e-mail if errors occurred.
When using this output, you will need additional configuration in your config.yml (or .xml):

.. code-block:: yaml

    chameleon_system_sanity_check:
      output:
        mail:
          from: "root@localhost"
          to: "admin@example.com"
          implementation: "phpmailer"
          service: "chameleon_system_core.mailer"
          level: 20

The parameter `from` defines the sender of the e-mail (defaults to root@localhost)

The parameter `to` defines the addressee of the e-mail (required)

The parameter `implementation` defines which mailer implementation to use (required). Currently only PHPMailer is supported (value `phpmailer`).

The parameter `service` defines which service to use for sending mails. This way you can pre-configure the mailer with values that are not included in this configuration.
If no service ID is given, a simple instance of the mailer is instantiated.

The parameter `level` defines a minimum level for sending mails. A mail will only be sent if at least one outcome has the defined level (or higher).
For example you may define that a mail is only sent if there are outcomes of level WARNING or higher.

Alias: `mail`
