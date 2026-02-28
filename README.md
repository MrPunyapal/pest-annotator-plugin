# Pest Coverage Annotator

A CLI tool that parses Clover coverage XML reports and annotates uncovered classes and methods with a clean, colorized terminal output.

Works as a **standalone CLI tool** and pairs perfectly with [Pest PHP](https://pestphp.com) (or any test runner that generates Clover XML coverage).

## Requirements

- PHP 8.2+
- `ext-dom`
- `ext-libxml`

## Installation

```bash
composer require --dev mrpunyapal/pest-coverage-annotator
```

## Usage

### 1. Generate a Clover coverage report

Run your Pest (or PHPUnit) test suite with Clover XML output:

```bash
# Pest
./vendor/bin/pest --coverage --coverage-clover=coverage.xml

# PHPUnit
./vendor/bin/phpunit --coverage-clover=coverage.xml
```

### 2. Run the annotator

```bash
vendor/bin/annotate coverage.xml
```

### Example Output

```
🔍 Pest Coverage Annotator
   Parsing: coverage.xml

━━━ Fully Uncovered Classes ━━━

  📄 Class: App\Services\InvoiceService
     Coverage: 0%
     ❌ Uncovered: cancel(), refund(), generateInvoice()

━━━ Partially Covered Classes ━━━

  📄 Class: App\Services\PaymentService
     Coverage: 66.7%
     ❌ Uncovered: refund()
     ✅ Covered: charge(), validate()

━━━ Summary ━━━

  Total Classes:      5
  Fully Covered:      3
  Partially Covered:  1
  Fully Uncovered:    1
```

## CLI Options

| Option | Short | Description |
|---|---|---|
| `--include` | `-i` | Directory prefixes to include (default: `app/`, `src/`). Repeatable. |
| `--show-covered` | | Also display fully covered classes in the output. |

### Custom directory filters

```bash
# Only scan files under "modules/" and "domain/"
vendor/bin/annotate coverage.xml --include=modules/ --include=domain/
```

### Show all classes (including fully covered)

```bash
vendor/bin/annotate coverage.xml --show-covered
```

## Exit Codes

| Code | Meaning |
|---|---|
| `0` | All classes are covered (or partially covered) |
| `1` | One or more classes are fully uncovered |

This makes it easy to integrate into CI pipelines — fail the build when critical classes have zero coverage.

## How It Works

1. Parses the Clover XML `<file>` and `<class>` nodes using `DOMDocument`
2. Extracts `<line type="method">` entries to identify each method and its hit count
3. Filters files by the configured directory prefixes (default: `app/`, `src/`)
4. Groups methods by class and determines coverage status
5. Renders a colorized report to the terminal via Symfony Console

## Development

```bash
git clone https://github.com/mrpunyapal/pest-coverage-annotator.git
cd pest-coverage-annotator
composer install
composer test
```

## License

MIT
