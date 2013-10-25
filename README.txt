
CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Installation
 * Usage
 * Implementation


INTRODUCTION
------------

  "In electronics, a shunt is a device which allows electric current to pass
  around another point in the circuit by creating a low resistance path."
  - source: http://en.wikipedia.org/wiki/Shunt_(electrical)

Shunt module provides a facility for developers to create virtual "shunts" that
site administrators can trip in emergency situations, instructing Drupal to fail
gracefully where functionality depends on them.

For example, you might create a shunt that disable certain expensive database
operations, so that in case of an overwhelming traffic event like a denial of
service (DOS) attack you have a way of both reducing load on the server and
saving legitimate users the frustration of getting white screens or losing form
submissions.

This is an API module. By itself it doesn't add such functionality. Rather, it
provides module developers the ability to define shunts and make functionality
dependant on them, and it gives site administrators the ability to trip and
reset shunts via the web UI and via Drush.

Note: For implementing modules to effectively degrade features gracefully, they
should not require cache clears.


INSTALLATION
------------

Shunt module is installed in the usual way. See
http://drupal.org/documentation/install/modules-themes/modules-7.


USAGE
-----

To see all available drush commands, execute this command:

  drush --filter=shunt


Enable ("trip") the shunt to disable targeted site functionality like this:

  A. Via Drush:

    drush shunt-enable


  B. Via admin GUI

    Go here:
      admin/settings/shunt

      Enable shunt check box. Save.


Reset the shunt to re-enable site functionality like this:

  A. Via Drush:

    drush shunt-disable


  B. Via admin GUI

    Go here:
      admin/settings/shunt

      Disable shunt check box. Save.


IMPLEMENTATION
--------------

For instructions and examples on how to shunt-enable a module, see
shunt.api.php.
