# Testing Checklist

Use this checklist as a baseline when verifying a WordPress plugin release. Adapt sections as needed for the specific feature set.

## Environment

- [ ] Node.js version recorded (`node -v`)
- [ ] PHP version recorded (`php -v`)
- [ ] WordPress target version installed
- [ ] Required services or API credentials available

## Build & Install

- [ ] `npm install` (or `pnpm/yarn install`) completes without errors
- [ ] `npm run build` (or equivalent) generates production assets
- [ ] Plugin installs without warnings
- [ ] Activation succeeds with no PHP notices/errors
- [ ] Deactivation/uninstall routines run cleanly

## Admin Experience

- [ ] Admin pages load without PHP or JavaScript errors
- [ ] All settings fields render with labels and help text
- [ ] Settings save, persist, and display success/error notices as expected
- [ ] Capability checks prevent unauthorized access

## Core Functionality

- [ ] Primary workflows succeed (create/update/delete data)
- [ ] Validation prevents invalid submissions
- [ ] Error states display clear guidance
- [ ] Background/scheduled tasks run as intended

## Editor Blocks (if applicable)

- [ ] Blocks appear in inserter with correct title, category, and icon
- [ ] Block controls update preview instantly
- [ ] Attributes persist after save and reload
- [ ] No console errors while editing
- [ ] Dynamic blocks render expected markup on frontend

## Frontend Output

- [ ] Pages/posts using the plugin render without errors
- [ ] Links, buttons, and interactive elements function
- [ ] Styling matches design guidelines on desktop and mobile
- [ ] Shortcodes/widgets/templates output correct content

## Performance & Stability

- [ ] No slow queries or excessive API calls observed
- [ ] Asset loading limited to necessary routes
- [ ] Caching hooks (transients/object cache) behave correctly
- [ ] Debug log free of warnings/notices during smoke test

## Accessibility

- [ ] Keyboard navigation reaches all controls
- [ ] Form fields announce labels with screen readers
- [ ] ARIA attributes used where needed
- [ ] Focus states visible and logical

## Security

- [ ] Inputs sanitized and escaped on output
- [ ] Nonces and capability checks guard privileged actions
- [ ] External requests validate responses before use
- [ ] No sensitive data stored in plain text

## Internationalization

- [ ] Strings wrapped in translation functions
- [ ] Text domain matches plugin slug
- [ ] RTL layout spot-check (if applicable)

## Cross-Browser & Devices

- [ ] Latest Chrome/Edge
- [ ] Latest Firefox
- [ ] Latest Safari (desktop + iOS)
- [ ] Android Chrome

## Compatibility

- [ ] Works with default block theme
- [ ] Works with classic theme
- [ ] Multisite install check (network activate/deactivate)
- [ ] Plays well with popular companion plugins (list tested)

## Regression Coverage

- [ ] Previously fixed bugs remain resolved
- [ ] Automated tests (PHP/JS) pass locally or in CI
- [ ] Linting/formatting checks pass

## Release Readiness

- [ ] README/documentation updated
- [ ] Version numbers bumped (plugin header, package.json, etc.)
- [ ] Changelog drafted
- [ ] Translation files regenerated (if needed)
- [ ] Deployment instructions verified

## Issues Found

```
Issue #1:
Description:
Expected:
Actual:
Steps to reproduce:

Issue #2:
...
```

## Test Environment Summary

- WordPress Version: ___________
- PHP Version: ___________
- Browser/Device: ___________
- Node.js Version: ___________
- Date Tested: ___________
- Tester: ___________

## Notes

Additional observations, caveats, or follow-up tasks:

---

## Sign-Off

- [ ] All critical tests passed
- [ ] Open issues documented or resolved
- [ ] Release ready for distribution

**Tested by:** ___________________
**Date:** ___________________
**Approved:** ☐ Yes ☐ No
