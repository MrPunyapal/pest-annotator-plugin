# Pest Annotator

A [Pest PHP](https://pestphp.com) plugin that automatically annotates uncovered classes and methods with clean, colorized output when running `pest --coverage`.

## Requirements

- PHP 8.3+
- Pest PHP 4.0+
- A code coverage driver (Xdebug, PCOV, or PHPDBG)

## Installation

```bash
composer require --dev mrpunyapal/pest-annotator-plugin
```

## Usage

Just run Pest with coverage and the `--annotate` flag:

```bash
./vendor/bin/pest --coverage --annotate
```

### Flags

| Flag                  | Description                                          |
|-----------------------|------------------------------------------------------|
| `--annotate`          | Show class-level coverage annotations                |
| `--annotate-methods`  | Show per-method details with line numbers             |
| `--annotate-covered`  | Include fully covered classes and covered methods     |

Flags can be combined:

```bash
./vendor/bin/pest --coverage --annotate-methods --annotate-covered
```

### Example Output

After Pest's default per-file coverage table, the plugin appends class-level annotations:

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

With `--annotate-methods`:

```
  Partially Covered Classes

  ████████░░  80.0% (4/5 methods, 85.0% lines) App\Jobs\UpdateUserAvatar
     ✕ failed() L42-58 (0/8 lines)
```

## How It Works

1. Hooks into Pest's plugin lifecycle via `HandlesArguments` and `AddsOutput`
2. Activated by `--annotate` flag (requires `--coverage`)
3. After tests complete, reads the native `CodeCoverage` object
4. Extracts class-level and method-level coverage data including line numbers
5. Renders annotated output with progress bars, method/line coverage stats, grouped by coverage status

## Development

```bash
git clone https://github.com/mrpunyapal/pest-annotator-plugin.git
cd pest-annotator-plugin
composer install
composer test
```

## License

MIT
