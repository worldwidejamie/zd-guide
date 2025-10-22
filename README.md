<div style="text-align: center;">

# WWJ Zendesk Guide Plugin

</div>

<p>A modern WordPress plugin that integrates Zendesk Guide articles into WordPress as custom posts with hierarchical taxonomy organization.</p>

[![ko-fi](https://ko-fi.com/img/githubbutton_sm.svg)](https://ko-fi.com/G2G2VK9I7)

## Features

- 🔄 Sync Zendesk Guide articles, sections, and categories
- 📦 Custom post type for articles with full block editor support
- 🏷️ Hierarchical taxonomies for categories and sections
- 🎨 Modern Gutenberg block for displaying article snippets
- 🔐 Secure API credential management

## Requirements

- WordPress 6.8 or higher
- PHP 8.0 or higher
- Node.js 18 or higher (for development)

## Installation

1. Clone this repository into your WordPress plugins directory
2. Run `pnpm install` to install dependencies
3. Run `pnpm build` to compile assets
  1. You can also run `pnpm start` for development with hot reloading
4. Activate the plugin through WordPress admin

## Development

### Setup

```bash
# Install dependencies
npm install

# Start development mode with hot reload
npm start

# Build for production
npm run build
```

### Code Quality

```bash
# Run PHP Code Sniffer
npm run phpcs

# Fix PHP code style issues
npm run phpcbf

# Run PHPUnit tests
npm run test

# Lint JavaScript
npm run lint:js

# Lint CSS
npm run lint:css
```

### Project Structure

```
wwj-zdguide/
├── src/                    # Source files
│   └── blocks/            # Block development
│       └── article/       # Article block
│           ├── block.json # Block configuration
│           ├── edit.js    # Editor component
│           ├── save.js    # Save component
│           ├── render.php # Server-side render
│           └── style.scss # Block styles
├── includes/              # PHP classes
│   ├── Admin/            # Admin functionality
│   ├── API/              # API client
│   ├── PostTypes/        # Custom post types
│   ├── Taxonomies/       # Custom taxonomies
│   ├── Sync/             # Sync handlers
│   └── Plugin.php        # Main plugin class
├── build/                 # Compiled assets (auto-generated)
├── assets/               # Static assets
├── languages/            # Translation files
└── tests/                # PHPUnit tests
```

## Architecture

### Modern WordPress Development

This plugin follows WordPress 6.8+ best practices:

- **Block Development**: Uses `@wordpress/scripts` with JSX and `block.json`
- **PHP 8.0+**: Typed properties, return types, and modern patterns
- **OOP Architecture**: Namespaced classes with PSR-4 autoloading
- **REST API**: Leverages WordPress core data for block editor integration
- **No Custom Webpack**: Relies on `@wordpress/scripts` defaults

### Data Flow

1. **Categories** synced first (creates `zd_category` terms)
2. **Sections** synced per category (creates `zd_section` terms with parent relationships)
3. **Articles** synced per section (creates `zd_article` posts with taxonomy assignments)

### Key Classes

- `WwjZdguide\Plugin`: Main plugin orchestrator (singleton pattern)
- `WwjZdguide\Admin\Settings`: Admin settings page and credential management
- `WwjZdguide\API\Zendesk_Client`: Zendesk API communication
- `WwjZdguide\Sync\Sync_Handler`: Data synchronization logic
- `WwjZdguide\PostTypes\Article`: Article post type registration
- `WwjZdguide\Taxonomies\Category`: Category taxonomy
- `WwjZdguide\Taxonomies\Section`: Section taxonomy

## Usage

### Configuration

1. Navigate to **Settings > Zendesk Guide** in WordPress admin
2. Enter your Zendesk credentials:
   - Subdomain (e.g., "yourcompany" from yourcompany.zendesk.com)
   - Admin email address
   - API token
   - You will need an API token which can be created through your Zendesk settings [here](https://developer.zendesk.com/api-reference/introduction/security-and-auth/).

3. Click **Test Connection** to verify credentials

### Syncing Content

1. Click **Sync Categories** to import categories from Zendesk
2. Click **Sync Sections** to import sections (requires categories)
3. Click **Sync Articles** to import articles (requires sections)

### Using the Block

1. In the block editor, add the **Zendesk Article** block
2. Select an article from the block settings sidebar
3. Configure display options (show excerpt, show meta)
4. The block renders server-side for optimal performance

## API Integration

### Zendesk API Endpoints

- Categories: `https://{subdomain}.zendesk.com/api/v2/help_center/categories.json`
- Sections: `https://{subdomain}.zendesk.com/api/v2/help_center/categories/{id}/sections.json`
- Articles: `https://{subdomain}.zendesk.com/api/v2/help_center/sections/{id}/articles.json`

### Authentication

Uses Basic Auth with email/token combination:
```
Authorization: Basic base64(email/token:api_token)
```

## Contributing

1. Follow WordPress Coding Standards
2. Use modern PHP 8.0+ syntax
3. Write PHPUnit tests for new features
4. Use `@wordpress/scripts` for JavaScript
5. Document all functions and classes

## License

GPL-2.0-or-later

## Support

For issues and questions, please use the GitHub issue tracker.

## Project Notes
This is my first open source project! Always open to suggestions or advice for improvement. Additionally, your patience is appreciated.

### How do I configure the plugin?
You will need an API token which can be created through your Zendesk settings.

Instructions can be found [here](https://developer.zendesk.com/api-reference/introduction/security-and-auth/).

