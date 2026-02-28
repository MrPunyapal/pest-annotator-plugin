# Pest Annotator

A [Pest PHP](https://pestphp.com) plugin that annotates coverage output with class-level details, type coverage, cyclomatic complexity, baseline diffs, and flexible export formats.

## Requirements

- PHP 8.3+
- Pest PHP 4.0+
- A code coverage driver (Xdebug, PCOV, or PHPDBG)

## Installation

```bash
composer require --dev mrpunyapal/pest-annotator-plugin
```

## Usage

Run Pest with coverage and any of the `--annotate-*` flags:

```bash
./vendor/bin/pest --coverage --annotate
```

Flags can be freely combined:

```bash
./vendor/bin/pest --coverage --annotate-methods --annotate-types --annotate-complexity
```

---

## Flags Reference

### Coverage Annotations

| Flag                  | Description                                                   |
|-----------------------|---------------------------------------------------------------|
| `--annotate`          | Show class-level coverage annotations                         |
| `--annotate-methods`  | Show per-method details with line numbers                     |
| `--annotate-covered`  | Also include fully covered classes and methods in output      |

### Type Coverage

| Flag                  | Description                                                   |
|-----------------------|---------------------------------------------------------------|
| `--annotate-types`    | Analyse PHP files for missing return, parameter, and property types |

### Complexity Analysis

| Flag                     | Description                                                |
|--------------------------|------------------------------------------------------------|
| `--annotate-complexity`  | Show cyclomatic complexity per method, cross-referenced with coverage |

### Baseline & Diff

| Flag                          | Description                                             |
|-------------------------------|---------------------------------------------------------|
| `--annotate-save-baseline`    | Save the current coverage report as a baseline (`.pest-annotator-baseline.json`) |
| `--annotate-diff`             | Show coverage changes relative to the saved baseline    |

### Filtering & Thresholds

| Flag                          | Description                                             |
|-------------------------------|---------------------------------------------------------|
| `--annotate-min=N`            | Exit with a non-zero code if total coverage is below `N`% |
| `--annotate-namespace=Prefix` | Restrict output to classes matching the given namespace prefix |
| `--annotate-exclude=Prefix`   | Exclude classes matching the given namespace prefix     |

### Export

| Flag                          | Description                                             |
|-------------------------------|---------------------------------------------------------|
| `--annotate-format=FORMAT`    | Export coverage report (`json`, `markdown`, or `html`)  |
| `--annotate-output=PATH`      | Write the export to `PATH` (defaults to stdout)         |

---

## Examples

### Basic coverage annotations

```bash
./vendor/bin/pest --coverage --annotate
```

```
  Fully Uncovered Classes

  ░░░░░░░░░░   0.0% (0/3 methods, 0.0% lines) App\Services\InvoiceService

  Partially Covered Classes

  ████████░░  80.0% (4/5 methods, 85.0% lines) App\Jobs\UpdateUserAvatar

  Summary

  Total Classes:      5
  Fully Covered:      3
  Partially Covered:  1
  Fully Uncovered:    1
```

### With per-method details

```bash
./vendor/bin/pest --coverage --annotate-methods
```

```
  Partially Covered Classes

  ████████░░  80.0% (4/5 methods, 85.0% lines) App\Jobs\UpdateUserAvatar
     ✕ failed() L42-58 (0/8 lines)
```

### Type coverage analysis

```bash
./vendor/bin/pest --coverage --annotate-types
```

```
  Type Coverage

  ██████████  100.0% App\Data\UserDto
  ████████░░   80.0% App\Services\UserService
     missing return type: handle()
     missing param type:  handle() $request
```

### Complexity analysis

```bash
./vendor/bin/pest --coverage --annotate-complexity
```

```
  Complexity Analysis

  App\Services\PaymentService
     processPayment()   complexity: 12   coverage:  30.0%  ⚠ high risk
     refund()           complexity:  3   coverage: 100.0%
```

Methods with complexity ≥ 10 **and** coverage below 50 % are flagged as ⚠ high risk.

### Baseline workflow

Save a baseline after a clean run, then compare on subsequent runs:

```bash
# save baseline
./vendor/bin/pest --coverage --annotate-save-baseline

# compare against baseline
./vendor/bin/pest --coverage --annotate-diff
```

```
  Coverage Diff

  ↑ App\Services\UserService    75.0% → 90.0%  (+15.0%)
  ↓ App\Jobs\SendEmail          80.0% → 60.0%  (-20.0%)
```

### Minimum coverage threshold

```bash
./vendor/bin/pest --coverage --annotate --annotate-min=80
```

Exits with a non-zero status code if total coverage drops below 80 %.

### Namespace filtering

```bash
# only show App\Services classes
./vendor/bin/pest --coverage --annotate --annotate-namespace=App\\Services

# exclude test helpers
./vendor/bin/pest --coverage --annotate --annotate-exclude=App\\Testing
```

### Export formats

```bash
# print JSON to stdout
./vendor/bin/pest --coverage --annotate --annotate-format=json

# write Markdown (suitable for PR comments) to a file
./vendor/bin/pest --coverage --annotate --annotate-format=markdown --annotate-output=coverage.md

# write a self-contained HTML report
./vendor/bin/pest --coverage --annotate --annotate-format=html --annotate-output=coverage.html
```

---

## How It Works

1. Hooks into Pest's plugin lifecycle via `HandlesArguments` and `AddsOutput`
2. All `--annotate-*` flags are consumed before they reach Pest/PHPUnit
3. After tests complete, reads the native `CodeCoverage` object
4. Extracts class-level and method-level coverage data including line numbers
5. Optionally analyses PHP files for type declarations (using `nikic/php-parser`) and cyclomatic complexity (using `sebastian/complexity`)
6. Renders annotated output grouped by coverage status, with progress bars and colour indicators

---

## Development

```bash
git clone https://github.com/mrpunyapal/pest-annotator-plugin.git
cd pest-annotator-plugin
composer install
composer test
```

## License

MIT
