
CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Installation


INTRODUCTION
------------

Shunt Example is an example implementation of Shunt module, demonstrating how to
define a shunt and make site functionality depend on it.

It defines the "shuntexample" shunt in shuntexample_shunt_info() and creates a
"Shunt example" page at the path shuntexample. The content of the page is made
dependent on the state of the shunt in
\Drupal\shunt\Controller\ShuntexampleController::hello(). Visit the page and see
it change as you variously enable and disable the shunt.


INSTALLATION
------------

Shunt Example is installed in the usual way. See
https://drupal.org/documentation/install/modules-themes/modules-8.
