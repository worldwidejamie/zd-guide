# WWJ Zendesk Guide Plugin - AI Development Guide

This WordPress plugin integrates Zendesk Guide articles into WordPress as custom posts with hierarchical taxonomy organization, built with modern WordPress 6.8+ development practices.

## Architecture Overview

**Core Components:**
- `wwj-zdguide.php` - Main plugin file with admin interface and API credential management
- `post-types.php` - Custom post type (`zd_article`) and taxonomies (`zd_category`, `zd_section`)
- `sync.php` - Zendesk API integration with hierarchical sync workflow
- `src/` - Modern block development using @wordpress/scripts and JSX
- `build/` - Compiled block assets (auto-generated, not tracked in git)

**Data Flow:**
1. Categories synced first (creates `zd_category` terms with `zendesk_category_id` meta)
2. Sections synced per category (creates `zd_section` terms with parent-child relationships)
3. Articles synced per section (creates `zd_article` posts with taxonomy assignments)

**Critical Design Patterns:**
- Uses WordPress term meta to store Zendesk IDs for mapping (`zendesk_category_id`, `zendesk_section_id`)
- Basic Auth with Zendesk API using email/token combination
- Admin notices for sync feedback via WordPress hooks system
- Hierarchical taxonomy structure mirrors Zendesk's category → section → article organization
- Modern block development with `block.json` and JSX components

## Modern WordPress Development Standards (WordPress 6.8+)

**Block Development:**
- Use `@wordpress/scripts` for build process (no custom webpack configs)
- JSX components with modern React patterns and hooks
- `block.json` for block registration (replaces PHP registration)
- Server-side rendering with `render.php` files
- TypeScript support encouraged for larger blocks
- Use `@wordpress/data` store patterns for complex state management

**JavaScript Standards:**
- ES6+ syntax with JSX for blocks
- Use WordPress components from `@wordpress/components`
- Follow React best practices: functional components, hooks, proper dependency arrays
- Use `@wordpress/element` instead of raw React
- Leverage `@wordpress/data` for complex state management
- Use `@wordpress/api-fetch` for REST API calls

**PHP Standards:**
- PHP 8.0+ minimum (WordPress 6.8 requirement)
- Use typed properties and return types where appropriate
- Follow PSR-4 autoloading patterns for larger plugins
- Use WordPress coding standards with modern PHP features
- Leverage WordPress 6.8 features like block themes and site editor APIs

**Build Process:**
- `npm start` for development with hot reload
- `npm run build` for production builds
- Use `.nvmrc` for Node.js version consistency
- Include `package.json` with @wordpress/scripts dependency
- No custom webpack configuration - rely on @wordpress/scripts defaults

## Development Workflow

**Setup:**
```bash
npm install
npm start  # Development mode
npm run build  # Production build
```

**Code Quality:**
- Run `phpcs` for WordPress Coding Standards compliance
- Use `@wordpress/eslint-plugin` for JavaScript linting
- Use `.phpcs.xml.dist` config with modern standards
- EditorConfig enforces consistent formatting
- Use WordPress 6.8+ hook patterns and modern PHP syntax

**Testing:**
- PHPUnit for PHP code testing
- Jest for JavaScript unit testing (via @wordpress/scripts)
- E2E testing with Playwright (WordPress 6.8+ standard)
- Test against WordPress 6.8+ and latest PHP versions
- Use WordPress test utilities and modern testing patterns

**Text Domain:** All strings use `wwj-zdguide` - maintain consistency for i18n

## Block Development Architecture

**File Structure:**
```
src/
├── blocks/
│   └── article/
│       ├── block.json
│       ├── edit.js
│       ├── save.js
│       ├── render.php
│       └── style.scss
├── components/
│   └── ArticleSelector.js
└── utils/
    └── api.js
```

**Block Registration Pattern:**
- Use `block.json` for metadata (title, description, attributes, etc.)
- Use `render.php` for server-side rendering
- Use `edit.js` for editor experience
- Use `save.js` for static blocks (return null for dynamic blocks)

**Modern Component Patterns:**
- Functional components with hooks
- Use `useSelect` and `useDispatch` from `@wordpress/data`
- Custom hooks for reusable logic
- Proper TypeScript types for better development experience

## Integration Points

**Zendesk API Endpoints:**
- Categories: `https://{subdomain}.zendesk.com/api/v2/help_center/categories.json`
- Sections: `https://{subdomain}.zendesk.com/api/v2/help_center/categories/{id}/sections.json`
- Articles: `https://{subdomain}.zendesk.com/api/v2/help_center/sections/{id}/articles.json`

**WordPress Integration:**
- Custom post type `zd_article` with public URLs (`help-center` slug)
- Taxonomies: `zd_category` (hierarchical), `zd_section` (hierarchical)
- Admin menu under Settings → Zendesk Guide
- Manual sync buttons with nonce protection in admin interface
- REST API endpoints for block editor integration
- Modern block patterns and templates

**Modern WordPress APIs:**
- Use `register_block_type()` with `block.json`
- Leverage WordPress 6.8 Site Editor APIs
- Use modern hook patterns (`wp_enqueue_block_editor_assets`, etc.)
- REST API integration for dynamic content loading
- Use WordPress 6.8 performance improvements

## Project-Specific Conventions

**File Organization:**
- `src/` for development files
- `build/` for compiled assets (gitignored)
- Single-responsibility files with modern PHP class structure
- Constants defined in main file: `WWJ_ZDGUIDE_VERSION`, `WWJ_ZDGUIDE_PLUGIN_DIR`, `WWJ_ZDGUIDE_PLUGIN_URL`

**Error Handling:**
- API failures handled gracefully with user-friendly admin notices
- Credential validation before API calls
- Use WordPress transients for API rate limiting and caching
- Modern error boundaries in React components

**Data Persistence:**
- WordPress options API for settings (`wwj_zdguide_options`)
- Post meta for Zendesk article IDs (`zendesk_article_id`)
- Term meta for Zendesk category/section IDs
- No custom database tables - uses WordPress native storage
- Consider using WordPress 6.8 meta capabilities for better performance

**Performance Considerations:**
- Use WordPress 6.8 performance APIs
- Implement proper caching strategies
- Use lazy loading for block components
- Optimize database queries with modern WordPress patterns
- Leverage WordPress 6.8 script and style loading improvements

## Dependencies

**Required:**
- WordPress 6.8+
- PHP 8.0+
- Node.js 18+ (for build process)

**Build Dependencies:**
- `@wordpress/scripts` (includes webpack, babel, eslint)
- `@wordpress/components`
- `@wordpress/data`
- `@wordpress/element`
- `@wordpress/api-fetch`

**Development Dependencies:**
- WordPress Coding Standards (PHPCS)
- ESLint with WordPress configuration
- Jest for JavaScript testing
- Playwright for E2E testing
