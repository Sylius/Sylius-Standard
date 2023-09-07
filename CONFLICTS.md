# CONFLICTS

This document explains why certain conflicts were added to `composer.json` and
references related issues.

- `symfony/framework-bundle:6.2.8`:

  This version is missing the service alias `validator.expression`
  which causes ValidatorException exception to be thrown when using `Expression` constraint. 
