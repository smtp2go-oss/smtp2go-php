# Change Log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/).

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