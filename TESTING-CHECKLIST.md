# Testing Checklist

Use this checklist to verify the refactored plugin works correctly.

## Pre-Testing Setup

- [ ] Node.js 18+ installed (`node -v`)
- [ ] PHP 8.0+ available (`php -v`)
- [ ] WordPress 6.8+ running
- [ ] Zendesk API credentials ready

## Installation Testing

### Dependencies
- [ ] Run `npm install` - completes without errors
- [ ] Run `npm run build` - creates `build/` directory
- [ ] Verify `build/blocks/article/` contains:
  - [ ] `block.json`
  - [ ] `index.js`
  - [ ] `index.css`
  - [ ] `style-index.css`

### Plugin Activation
- [ ] Deactivate plugin (if already active)
- [ ] Reactivate plugin
- [ ] No PHP errors in debug.log
- [ ] No fatal errors on activation
- [ ] Rewrite rules flushed successfully

## Admin Settings Testing

### Settings Page
- [ ] Navigate to Settings → Zendesk Guide
- [ ] Page loads without errors
- [ ] All fields present:
  - [ ] Subdomain field
  - [ ] Email field
  - [ ] API Token field
- [ ] All buttons present:
  - [ ] Test Connection
  - [ ] Sync Categories
  - [ ] Sync Sections
  - [ ] Sync Articles

### API Connection
- [ ] Enter valid Zendesk subdomain
- [ ] Enter valid email address
- [ ] Enter valid API token
- [ ] Click "Save Changes" - saves successfully
- [ ] Click "Test Connection" - shows success notice
- [ ] Try invalid credentials - shows error notice

## Sync Testing

### Categories Sync
- [ ] Click "Sync Categories"
- [ ] Wait for completion
- [ ] Success notice appears with count
- [ ] Navigate to Zendesk Guide → Categories
- [ ] Verify categories imported
- [ ] Check category has proper:
  - [ ] Name
  - [ ] Slug (matches Zendesk ID)
  - [ ] Description
  - [ ] zendesk_category_id meta

### Sections Sync
- [ ] Click "Sync Sections"
- [ ] Wait for completion
- [ ] Success notice appears with count
- [ ] Navigate to Zendesk Guide → Sections
- [ ] Verify sections imported
- [ ] Check section has proper:
  - [ ] Name
  - [ ] Parent (category)
  - [ ] Slug (matches Zendesk ID)
  - [ ] zendesk_section_id meta

### Articles Sync
- [ ] Click "Sync Articles"
- [ ] Wait for completion
- [ ] Success notice appears with count
- [ ] Navigate to Zendesk Guide → All Articles
- [ ] Verify articles imported
- [ ] Check article has proper:
  - [ ] Title
  - [ ] Content (body)
  - [ ] Category assigned
  - [ ] Section assigned
  - [ ] zendesk_article_id meta
  - [ ] Published status

### Error Handling
- [ ] Sync with no credentials - shows error
- [ ] Sync sections before categories - shows warning
- [ ] Sync articles before sections - shows warning
- [ ] Invalid API credentials - shows error
- [ ] Network error - handles gracefully

## Block Editor Testing

### Block Availability
- [ ] Create new post/page
- [ ] Open block inserter
- [ ] Search for "Zendesk Article"
- [ ] Block appears in results
- [ ] Block has correct icon (book-alt)
- [ ] Block category is "Widgets"

### Block Functionality
- [ ] Insert "Zendesk Article" block
- [ ] Block inserts without errors
- [ ] Placeholder shows
- [ ] Inspector controls visible in sidebar
- [ ] Article dropdown loads
- [ ] Article options populated
- [ ] Select an article from dropdown
- [ ] Preview renders in editor
- [ ] Toggle "Show Excerpt" - updates preview
- [ ] Toggle "Show Meta Information" - updates preview

### Block Inspector
- [ ] "Article Settings" panel present
- [ ] "Select Article" dropdown works
- [ ] "Show Excerpt" toggle works
- [ ] "Show Meta Information" toggle works
- [ ] Settings persist when block reselected
- [ ] Settings save with post

### Block Preview (Editor)
- [ ] Article title displays
- [ ] Article excerpt displays (if enabled)
- [ ] Category badge shows (if enabled)
- [ ] Section badge shows (if enabled)
- [ ] "Read full article" link present
- [ ] Styling looks correct
- [ ] No console errors

## Frontend Testing

### Published Content
- [ ] Publish post/page with block
- [ ] View on frontend
- [ ] Block renders correctly
- [ ] Article title displays
- [ ] Article excerpt shows (if enabled)
- [ ] Meta information shows (if enabled)
- [ ] "Read full article" link works
- [ ] Link goes to correct article
- [ ] Styling looks correct
- [ ] Responsive on mobile

### Article Single Page
- [ ] Navigate to single article (zd_article)
- [ ] Article displays correctly
- [ ] Title shown
- [ ] Content displayed
- [ ] Categories shown
- [ ] Sections shown
- [ ] Permalink works
- [ ] Archive page works

## Performance Testing

### Load Times
- [ ] Settings page loads quickly (< 1s)
- [ ] Block inserter opens quickly
- [ ] Article dropdown loads within 2s
- [ ] Frontend renders quickly
- [ ] No slow database queries

### Resource Usage
- [ ] Check browser Network tab
- [ ] Scripts load only when needed
- [ ] Styles load only when needed
- [ ] No 404 errors for assets
- [ ] No duplicate asset loading

## JavaScript Testing

### Browser Console
- [ ] No errors in console
- [ ] No warnings (except deprecation notices)
- [ ] React DevTools shows proper component tree
- [ ] State updates correctly

### Build Process
- [ ] `npm start` runs without errors
- [ ] Hot reload works during development
- [ ] Changes reflect immediately
- [ ] `npm run build` creates minified files
- [ ] Production build smaller than dev

## PHP Testing

### Code Standards
- [ ] Run `composer phpcs` (if installed)
- [ ] No coding standard violations
- [ ] All functions documented
- [ ] No deprecated functions used

### Error Logging
- [ ] Enable WP_DEBUG
- [ ] Enable WP_DEBUG_LOG
- [ ] Check debug.log for:
  - [ ] No PHP errors
  - [ ] No PHP warnings
  - [ ] No deprecated notices

### Type Safety
- [ ] All methods have return types
- [ ] All parameters have type hints
- [ ] Properties have type declarations
- [ ] No type errors

## Accessibility Testing

### Keyboard Navigation
- [ ] Can tab to block
- [ ] Can open block settings
- [ ] Can navigate dropdown
- [ ] Can toggle switches
- [ ] All interactive elements focusable

### Screen Reader
- [ ] Block has proper ARIA labels
- [ ] Settings have labels
- [ ] Error messages announced
- [ ] Success messages announced

## Security Testing

### Input Sanitization
- [ ] Settings fields sanitized
- [ ] API responses validated
- [ ] User input escaped
- [ ] Nonces checked

### Permissions
- [ ] Settings page requires 'manage_options'
- [ ] Sync actions require proper caps
- [ ] No unauthorized access possible

## Cross-Browser Testing

- [ ] Chrome/Edge (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest)
- [ ] Mobile browsers (iOS Safari, Chrome Android)

## WordPress Compatibility

### Core Features
- [ ] Block themes work
- [ ] Classic themes work
- [ ] Multisite compatible
- [ ] No conflicts with common plugins

### REST API
- [ ] Articles available via REST API
- [ ] Categories available via REST API
- [ ] Sections available via REST API
- [ ] Proper authentication

## Cleanup Testing

### After Successful Tests
- [ ] Remove old files:
  - [ ] `article-block.php`
  - [ ] `post-types.php`
  - [ ] `sync.php`
  - [ ] `assets/js/block-editor.js`
  - [ ] `assets/css/block-editor.css`
- [ ] Plugin still works
- [ ] No errors after removal

## Final Verification

- [ ] All checkboxes above completed
- [ ] No open issues
- [ ] Documentation reviewed
- [ ] Ready for production

## Issues Found

Document any issues discovered during testing:

```
Issue #1:
Description:
Expected:
Actual:
Steps to reproduce:

Issue #2:
...
```

## Test Environment

- WordPress Version: ___________
- PHP Version: ___________
- Browser: ___________
- Node.js Version: ___________
- Date Tested: ___________
- Tester: ___________

## Notes

Additional observations or comments:

---

## Sign-Off

- [ ] All critical tests passed
- [ ] All blockers resolved
- [ ] Plugin ready for use

**Tested by:** ___________________
**Date:** ___________________
**Approved:** ☐ Yes ☐ No
