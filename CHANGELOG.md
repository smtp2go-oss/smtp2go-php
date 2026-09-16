# Change Log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/).

## [Unreleased]

### Fixed

- Incorrect setting of cURL options in `ApiClient`

### Changed

- Strip control characters from addresses and custom header values
- Quote and escape display names in `to`, `cc` and `bcc`, matching the existing `sender` behaviour
- Trim custom header values
- **BC**: `CustomHeader` now throws `InvalidArgumentException` when given a header name containing a colon, whitespace, or any other character not permitted by RFC 5322. Such names previously produced a malformed header

## [1.2.0] - 2026-05-22
- Make getRegionWithUrls static

## [1.1.8] - 2026-05-12
- Expand README examples
- Improve test coverage
- Fix Send@addAttachment not accepting the FileAttachment type
- Add support for the new fastaccept parameter
- Exclude tests from package distribution
- code improvements from phpstan

## [1.1.7] - 2025-09-16
- Fix getResponseBody() to try/catch JSON decode errors, return empty object on JSON decode error

## [1.1.6] - 2025-02-25

- ApiClient stores failedAttemptInfo for debugging
- Better request exception handling
- Better tests for retry feature

## [1.1.5] - 2024-12-11

- add new FileAttachment type which does not require an absolute path
- Fix possible fatal error in getLastRequest if the last request is null
- add GetLastResponse() and GetLastResponseStatusCode() methods to ApiClient

## [1.1.4] - 2023-10-06

### Added

- add send templated email + example
- add retry options

### Changed

- update PHP version requirement to 7.4

### Fixed

- .gitignore composer.lock
- README code example fixed