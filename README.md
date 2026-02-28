# Pest Annotator

A [Pest PHP](https://pestphp.com) plugin that automatically annotates uncovered classes and methods with clean, colorized output when running `pest --coverage`.

## Requirements

- PHP 8.2+
- Pest PHP 3.0+
- A code coverage driver (Xdebug, PCOV, or PHPDBG)

## Installation

```bash
composer require --dev mrpunyapal/pest-annotator-plugin
```

## Usage

Just run Pest with coverage enabled — the plugin activates automatically:

```bash
./vendor/bin/pest --coverage
```

### Example Output

After Pest's default per-file coverage table, the plugin appends class-level annotations:

```
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

## How It Works

1. Hooks into Pest's plugin lifecycle via `HandlesArguments` and `AddsOutput`
2. Detects `--coverage` flag — no extra flags needed
3. After tests complete, reads the native `CodeCoverage` object (before Pest's own coverage plugin processes it)
4. Extracts class-level and method-level coverage data from `SebastianBergmann\CodeCoverage`
5. Renders annotated output grouped by coverage status (uncovered → partial → summary)

## Development

```bash
git clone https://github.com/mrpunyapal/pest-annotator-plugin.git
cd pest-annotator-plugin
composer install
composer test
```

## License

MIT
