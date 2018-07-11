Specification
=============

SanityCheckCommand
------------------

The SanityCheckCommand is used to perform checks from the console.


Configure checks
----------------

A compiler pass named AddSanityChecksPass is used to collect all the checks defined in the project.
It collects all services tagged with "chameleon_system.sanity_check.check".
