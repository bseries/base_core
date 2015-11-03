# Change Log

## v1.4

### Fixed

### Improved

- New access control framework.
- Tightened reset password with reset_answer and reset_token.
- Better Brute force protection using delay on failed login attempts.
- Removed signature from login page.
- Material icons.
- Tag fields can be styled via `input--tags` class.
- Better scheme negotiation.
- Jobs now uses new dependency resolver and drops dependency.

### Added

- User accounts can now be locked and indicate if the "must lock".
- Automatic g11n bootstrapping.
- Token authentication via `auth_token` and `uuid`.

### Changed

- Jobs has been moved into the "async" namespace.
- Jobs now does not check for the _ASYNC_JOBS constant anymore.

### Backwards Incompatible Changes

- Users now have an `uuid` field it must be populated using 
  `./li3.php users migrateUuid`.

- New virtual users now have `is_active` defaulting to `false`. Adjust
  your code where you create those users and explicitly create them
  with `is_active` set to `true`.

- Modules must remove config/g11n.php.

- The url() method now always needs a scheme.
