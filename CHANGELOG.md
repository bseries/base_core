# Change Log

## v1.4

### Fixed

### Improved

### Added

### Changed

### Backwards Incompatible Changes

- New virtual users now have `is_active` defaulting to `false`. Adjust
  your code where you create those users and explicitly create them
  with `is_active` set to `true`.
