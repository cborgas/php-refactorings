# php-refactorings
[![Tests](https://github.com/cborgas/php-refactorings/actions/workflows/build.yml/badge.svg)](https://github.com/cborgas/php-refactorings/actions/workflows/build.yml)

A collection of refactoring examples in PHP

### [Dependecy Breaking Techniques](DependencyBreakingTechniques)

- [Extract and Override Call](DependencyBreakingTechniques/ExtractAndOverrideCall)
- [Extract and Override Factory Method](DependencyBreakingTechniques/ExtractAndOverrideFactoryMethod)

### About

- Each example has a `before.php` and an `after.php` to show the change of the refactoring.
- Each example will have a `runtime.php` file which contains some basic code to show how it would
be functioning in production. It also shows that you can do these refactorings safely and without introducing any
[regressions](https://en.wikipedia.org/wiki/Software_regression).
- `// ...` represents some other random amount of code that could be present in a real project.
- Each `.php` file will contain multiple namespaces for ease of understanding. 
- This project does not comply with [PSR-4](https://www.php-fig.org/psr/psr-4/) standards.
