# I Recommend This Plugin

"I Recommend This" is a WordPress plugin that allows users to recommend/like posts with a simple click. It provides a modern, lightweight alternative to comment-based engagement by letting visitors show appreciation for content.

## Features

- Simple recommendation/like system for WordPress posts and pages
- Customizable like button with thumb or heart icons
- Counter display showing the number of recommendations
- Widget to showcase most recommended posts
- Shortcodes for displaying recommendation buttons and top posts
- Gutenberg block integration
- GDPR compliance with IP anonymization
- Extensive developer hooks and filters

## Documentation

For detailed information, see the following documentation:

- [Overview](docs/overview.md) - Plugin architecture, file structure, and process flow
- [Developers Guide](docs/developers.md) - Hooks, filters, and integration methods
- [Advanced Usage](docs/advanced-usage.md) - GDPR, caching, and customization examples
- [Technical Reference](docs/technical-reference.md) - Functions, classes, and methods reference


## Development Setup

1. Clone the repository:

   ```sh
   git clone https://github.com/webtions/i-recommend-this.git
   ```

2. Navigate to the plugin directory:

   ```sh
   cd i-recommend-this
   ```

3. Install dependencies:

   ```sh
   npm install
   ```

## Development Commands

- Build assets:

  ```sh
  npm run build
  ```

- Watch for changes and rebuild assets automatically:

  ```sh
  npm run start
  ```

The build process compiles the JavaScript and CSS assets, including the Gutenberg block component. The generated files are placed in the `blocks/recommend/build/` directory.

## Requirements

- WordPress 6.0 or higher
- PHP 7.4 or higher

## License

This project is licensed under the GPL-3.0 License - see the [GNU General Public License](http://www.gnu.org/licenses/gpl-3.0.txt) for details.

## Security

Please report security bugs found in the source code through the [Patchstack Vulnerability Disclosure Program](https://patchstack.com/database/vdp/i-recommend-this). The Patchstack team will assist you with verification, CVE assignment, and notify the developers of this plugin.
