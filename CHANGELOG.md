# Changelog

All notable changes to `pest-coverage-annotator` will be documented in this file.

## Unreleased

- Pest 4 only (dropped Pest 3 support)
- Requires PHP 8.3+
- Initial release
- Clover XML coverage parsing with DOMDocument
- Colorized CLI output for uncovered/partially covered/fully covered classes
- Directory prefix filtering (default: `app/`, `src/`)
- `--show-covered` flag to display fully covered classes
- `--include` option for custom directory filters
- CI-friendly exit codes
