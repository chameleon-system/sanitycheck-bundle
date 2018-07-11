Installation
============

Step 1: Download the Bundle
---------------------------

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

.. code-block:: bash

    $ composer require chameleon-system/sanitycheck-bundle "~6.0"

This command requires you to have Composer installed globally, as explained
in the `installation chapter`_ of the Composer documentation.
Be sure to adjust the version information "~6.0" to the actual version you need.

Step 2: Enable the Bundle
-------------------------

Then, enable the bundle by adding the following line in the `app/AppKernel.php`
file of your project:

.. code-block:: php

    <?php
    // app/AppKernel.php

    // ...

    public function registerBundles()
    {
        $bundles = array(
            // ...
            new \ChameleonSystem\SanityCheckBundle\ChameleonSystemSanityCheckBundle(),
        );
    }


Step 3: Create Checks
---------------------

Create checks as described in the usage document in this documentation.

.. _installation chapter: https://getcomposer.org/doc/00-intro.md