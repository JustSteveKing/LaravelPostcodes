# Changelog

All notable changes to `LaravelPostcodes` will be documented in this file.

Updates should follow the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## v1.2.0 - 2020-03-03

### Added
- A Pre validate option to validate, allowing regex matching to save service calls
- A regex based outcode validation, this was a PR by PHPAdam that needed some work
- Tests for both of the above
- Laravel 7.* support

## v1.1.0 - 2019-10-25

### Added
- Make tests more stringent
- Unit tests make API calls 
- Update Postcode Service
- Bulk lookup postcodes
- Get nearest postcodes for a given longitude & latitude
- Nearest postcodes for postcode
- Autocomplete a postcode partial
- Query for postcode
- Lookup terminated postcode
- Lookup Outward Code
- Nearest outward code for outward code
- Get nearest outward codes for a given longitude & latitude
- Convert all returns to return Collection instead off Array where appropriate

### Deprecated
- Nothing

### Fixed
- Nothing

### Removed
- Nothing

### Security
- Nothing
