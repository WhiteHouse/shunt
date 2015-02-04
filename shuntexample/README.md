# Shunt Example

## Contents of This File

- [Introduction](#introduction)
- [Installation](#installation)


## Introduction

Shunt Example is an example implementation of Shunt module, demonstrating how to
make site functionality depend on a shunt.

It includes the "shunt_example" shunt and creates a "Shunt example" page at the
path shuntexample. The content of the page is made dependent on the state of the
shunt in `\Drupal\shunt\Controller\ShuntexampleController::hello()`. Visit the
page and see it change as you variously trip and reset the shunt.


## Installation

Shunt Example is installed in the usual way. See
https://drupal.org/documentation/install/modules-themes/modules-8.
