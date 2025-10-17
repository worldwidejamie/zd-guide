# WWJ Zendesk Guide Plugin - AI Development Guide

This WordPress plugin integrates Zendesk Guide articles into WordPress as custom posts with hierarchical taxonomy organization, built with modern WordPress 6.8+ development practices and a clear **freemium feature model**.

## Architecture Overview

**Core Components:**

* `wwj-zdguide.php` - Main plugin file with admin interface and API credential management. Also handles freemium licensing checks.
* `post-types.php` - Custom post type (`zd_article`) and taxonomies (`zd_category`, `zd_section`).
* `sync.php` - Zendesk API integration with hierarchical sync workflow and caching logic.
* `src/` - Modern block development using @wordpress/scripts and JSX.
* `build/` - Compiled block assets (auto-generated, not tracked in git).
* **Pro Add-on (separate plugin):** Contains all premium feature code, gated by a license check.

**Data Flow:**

1. Categories synced first (creates `zd_category` terms with `zendesk_category_id` meta).

2. Sections synced per category (creates `zd_section` terms with parent-child relationships).

3. Articles synced per section (creates `zd_article` posts with taxonomy assignments).

**Critical Design Patterns:**

* Uses WordPress term meta to store Zendesk IDs for mapping (`zendesk_category_id`, `zendesk_section_id`).
* Basic Auth with Zendesk API using email/token combination.
* Admin notices for sync feedback via WordPress hooks system.
* Hierarchical taxonomy structure mirrors Zendesk's category → section → article organization.
* Modern block development with `block.json` and JSX components.

---

## 🚀 Freemium Strategy and Feature Tiers

The plugin follows a **freemium model**. The free version provides the fundamental, manual "sync and display" functionality, while the Pro version focuses on **automation, enhanced UX, and deep service integration**.

### Free Version (Core `wwj-zdguide` Plugin)

The Free version is the foundation, providing the essential value needed to run a help center with manual management.

| **Feature Category** | **Description** | **Implementation Focus** |
| :--- | :--- | :--- |
| **Core Sync & Persistence** | Manual, one-click synchronization of all Categories, Sections, and Articles from Zendesk Guide. | Robust, secure data fetching using `wp_remote_get()` and efficient use of WordPress post/term meta for data storage. |
| **Display Integration** | A simple, default Gutenberg Block and Shortcode to render the complete hierarchical help center list. The styling should rely primarily on theme inheritance. | Lightweight component rendering with server-side rendering (`render.php`) for speed and SEO. |
| **Basic Search** | A functional search utility that connects directly to the Zendesk Search API to provide accurate, real-time results from the knowledge base. | Secure server-side request to the Zendesk `/search.json` endpoint; results link to the local `zd_article` post URLs. |
| **Performance** | Essential **Transient-based caching** for all API requests to protect against rate limits and ensure fast page load times. | Use of WordPress Transients API with a sensible expiration time (e.g., 30 minutes). |

### Pro Version (Add-on Plugin `wwj-zdguide-pro`)

The Pro features are reserved for advanced users or organizations that prioritize efficiency, professional design, and full support workflow integration. This code must reside in a separate plugin directory.

| **Feature Category** | **Description** | **Value Proposition** |
| :--- | :--- | :--- |
| **Automation** | Introduction of an **Automated Scheduled Sync** feature, allowing administrators to configure hourly or daily background updates using WP-Cron. | Saves significant administrator time and ensures the help center content is always fresh without manual intervention. |
| **Advanced Content Selection** | A sophisticated Gutenberg Block that allows users to selectively display specific categories, sections, or even individual articles, rather than the entire knowledge base. | Provides flexibility for building context-specific help pages (e.g., a "Product A Support" page showing only relevant articles). |
| **Layout & Design Tools** | Built-in controls within the block editor to apply diverse layouts (Grid, Accordion, Tabbed views) and style overrides, ensuring the help center perfectly matches the brand. | Eliminates the need for custom CSS and design work, dramatically improving the professional look and feel. |
| **Support Workflow Integration** | Dedicated components for capturing user feedback (e.g., "Was this helpful?") and a new block to create a **Zendesk Support Ticket Form**. | Closes the loop on the self-service process, providing valuable content intelligence and a seamless escalation path to human support. |
| **Advanced Search** | Enhancements to the basic search, including faceted filtering by Categories and Sections, providing a more precise and desktop-like knowledge base search experience. | Improves user satisfaction by making it faster and easier to navigate large knowledge bases. |

## Modern WordPress Development Standards (WordPress 6.8+)

**Block Development:**

* Use `@wordpress/scripts` for build process (no custom webpack configs).
* JSX components with modern React patterns and hooks.
* **`block.json`** for block registration (replaces PHP registration).
* Server-side rendering with `render.php` files for dynamic content.
* TypeScript support encouraged for larger blocks.

**JavaScript Standards:**

* ES6+ syntax with JSX for blocks.
* Use components from **`@wordpress/components`** for native WP look-and-feel.
* Functional components, hooks, and proper dependency arrays.
* Leverage **`@wordpress/data`** for complex state management (e.g., in Pro-level blocks).
* Use **`@wordpress/api-fetch`** for communication with the WordPress REST API.

**PHP Standards:**

* **PHP 8.0+ minimum** (WordPress 6.8 requirement).
* Use typed properties and return types where appropriate.
* Follow PSR-4 autoloading patterns for larger plugins.
* Use WordPress coding standards with modern PHP features.

**Build Process:**

* `npm start` for development with hot reload.
* `npm run build` for production builds.
* Include `package.json` with `@wordpress/scripts` dependency.

**Text Domain:** All strings use **`wwj-zdguide`** - maintain consistency for i18n.

## Technical Gating Strategy

The Pro version must be robustly gated to protect premium features while still being compatible with the free core.

1.  **Dependency and Activation Check:** The Pro plugin's main file should check for the free plugin's presence and define a constant if active.

    ```php
    // In wwj-zdguide-pro.php
    if ( ! defined( 'WWJ_ZDGUIDE_VERSION' ) ) return; // Ensure free plugin is active
    define( 'WWJ_ZDGUIDE_PRO_ACTIVE', true );
    ```

2.  **Conditional Execution:** All premium code in the **Free plugin's files** (e.g., conditional UI, hooks for the scheduled sync) must be wrapped in a constant check.

    ```php
    // Example in sync.php (Free Plugin)
    if ( defined( 'WWJ_ZDGUIDE_PRO_ACTIVE' ) ) {
        // Only register wp_cron job if Pro is active
        add_action( 'wwj_zdguide_scheduled_sync', 'wwj_zdguide_run_sync' );
    }
    ```

3.  **UI Gating:** In the free version's admin UI, premium settings (e.g., the scheduler frequency control) must be visible but disabled, directing users to the Pro upgrade page.

## Block Development Architecture

**File Structure:**

```text
src/
├── blocks/
│   ├── help-center/  // Free Block
│   └── selective-content/ // Pro Block (in Pro plugin)
│       ├── block.json
│       ├── edit.js
│       └── render.php
├── components/
│   └── ProUpgradeNotice.js // UI component for gating
└── utils/
    └── api.js // API handling (shared between Free/Pro via include)
```

**Block Registration Pattern:**

* Use `block.json` for metadata (title, description, attributes, etc.).
* Use `render.php` for server-side rendering of dynamic content.
* Use `edit.js` for the editor experience.

**Modern Component Patterns:**

* Functional components with hooks.
* Use `useSelect` and `useDispatch` from `@wordpress/data` for accessing settings or post data.
