# Documentation

This folder contains project documentation for developers and maintainers. If you’re new here, start with Docker setup, then Testing, then skim the structure docs.

[[_TOC_]]

## Getting started
- [Docker setup](Docker/Docker.md)  
  How to run the project locally using the provided Docker environment.

- [Testing](Testing/Testing.md)  
  How to run the test suite locally and what to expect from it.

## Application architecture

- [About](Application%20Structure/About.md)  
  High-level overview of how the application is organised.

- [Model](Application%20Structure/Model.md)  
  Model responsibilities, patterns, and conventions used in the codebase.

- [Collection](Application%20Structure/Collection.md)  
  How collections are used (and why), plus typical usage patterns.

- [Controller](Application%20Structure/Controller.md)  
  Controller responsibilities and the common request/response flow.

- [Repository](Application%20Structure/Repository.md)  
  Data access conventions and where persistence logic should live.

## Framework notes
These docs explain internal framework components used by the application:

- [Http](Framework/Http.md) — request/response handling, helpers, conventions
- [DataSet](Framework/DataSet.md) — dataset/transfer structure and usage patterns
- [Database](Framework/Database.md) — database access layer behaviour and configuration
- [Messages](Framework/Messages.md) — user/system messages handling conventions
- [Localisation](Framework/Localisation.md) — language/translation/localisation approach

## Modules
Module-specific docs live here:

- [Modules index](Modules/README.md)  
  Overview of available modules and what each one contains.

## Diagrams / images
- [`images/`](images/)  
  Diagrams referenced from documentation (e.g. ERD).

## Maintaining docs
- [ReadmeGenerator](ReadmeGenerator.md)  
  Notes on generating and keeping README/docs consistent.
