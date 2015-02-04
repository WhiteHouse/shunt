# Shunt

## Contents of This File

- [Introduction](#introduction)
- [Installation](#installation)
- [Usage](#usage)
- [Implementation](#implementation)


## Introduction

Current maintainer: [whitehouse](https://www.drupal.org/u/whitehouse)

> In electronics, a shunt is a device which allows electric current to pass
> around another point in the circuit by creating a low resistance path.
>
> -- <cite>[Wikipedia](http://en.wikipedia.org/wiki/Shunt_(electrical))</cite>

Shunt module provides a facility for developers to create virtual "shunts" that
site administrators can trip in emergency situations, instructing Drupal to fail
gracefully where functionality depends on them.

For example, you might create a shunt that disables certain expensive database
operations so that in case of an overwhelming traffic event like a denial of
service (DOS) attack you have a way of both reducing load on the server and
saving legitimate users the frustration of getting white screens or losing form
submissions.

This is an API module. It doesn't do anything by itself. Rather, it provides
module developers the ability to define shunts and make functionality dependant
on them, and it gives site administrators the ability to trip and reset said
shunts via the web UI or Drush.


## Installation

Shunt module is installed in the usual way. See [Installing contributed
modules](https://www.drupal.org/documentation/install/modules-themes/modules-8).


## Usage

Shunts can be administered via the web UI at admin/config/development/shunts and
tripped and reset there or via Drush. For a list of available Drush commands
execute the following:

```bash
drush --filter=shunt
```


## Implementation

For an example of shunt-enabling a module, look at the included Shunt Example
module, beginning with its README.
