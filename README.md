# PHP Scout

**PHP Scout** is a high-performance, production-grade CLI tool designed to analyze large-scale PHP and Laravel codebases. It uses **Abstract Syntax Tree (AST)** parsing to provide deep insights into code structure and metrics.

## Features
- **Deep Analysis:** Detects classes, methods, and namespaces using `nikic/php-parser`.
- **Memory Efficient:** Uses stream-based line counting to handle projects with millions of lines of code.
- **Laravel Ready:** Automatically detects Laravel environments and prepares for route/model mapping.
- **Clean Architecture:** Designed with extensibility in mind (Strategy and Visitor patterns).

## Installation
```bash
composer install
composer dump-autoload
```

## Usage

```bash
php bin/analyze <project path>
```
