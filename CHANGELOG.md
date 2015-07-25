# Change Log

## v1.4

### Fixed

### Improved

- Users now indicate if they "must lock".

### Added

- User accounts can now be locked.

### Changed

### Backwards Incompatible Changes

- New virtual users now have `is_active` defaulting to `false`. Adjust
  your code where you create those users and explicitly create them
  with `is_active` set to `true`.
