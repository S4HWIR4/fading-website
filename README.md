# Fading Website WordPress Plugin

A WordPress plugin that gradually decreases website opacity by 1% every day, creating a unique "fading" effect over time.

## Features

- **Daily Fade Mode**: Automatically reduces opacity by 1% every day
- **Manual Control Mode**: Set opacity manually to any value (1-100%)
- **Stealth Mode**: Hide the plugin from WordPress admin interface
- **Multiple Recovery Methods**: Easy recovery if you lose access
- **Clean Codebase**: Optimized and conflict-free implementation

## Installation

1. Download or clone this repository
2. Upload the plugin files to your `/wp-content/plugins/fading-website/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Go to Settings > Fading Website to configure

## Usage

### Basic Operation
- **Daily Mode**: Plugin automatically reduces opacity by 1% each day
- **Manual Mode**: Set any opacity value between 1-100%
- **Reset**: Return to 100% opacity and restart countdown

### Stealth Mode
Hide the plugin from WordPress admin:
1. Set a recovery phrase (minimum 8 characters, no spaces)
2. Activate stealth mode
3. Plugin disappears from plugins list and admin menu
4. Opacity effects continue working in background

### Recovery Methods

If you lose access to the plugin:

1. **Reactivation** (Easiest):
   - Deactivate and reactivate the plugin
   - All settings reset to defaults

2. **Custom Recovery Phrase**:
   - Visit: `yoursite.com/?your-recovery-phrase`

3. **Emergency Reset**:
   - Visit: `yoursite.com/?fading_reset=1`

## Technical Details

- **Version**: 1.0.0
- **WordPress Compatibility**: 5.0+
- **PHP Compatibility**: 7.4+
- **File Size**: ~285 lines of clean, optimized code

## Files

- `fading-website.php` - Main plugin file
- `README.md` - This documentation
- `plugin-readme.md` - Original plugin readme

## Development Notes

This plugin was developed as a technical challenge and demonstrates:
- WordPress plugin architecture
- Cron job scheduling
- Admin interface design
- Security considerations
- Code optimization and cleanup

### Code Quality
- Removed 66% of original conflicting code (841 → 285 lines)
- Fixed all syntax errors and contradictory logic
- Simplified stealth system from 3 levels to 2
- Eliminated dead code and unused methods

## Changelog

### 1.0.0
- Initial release
- Daily opacity fading functionality
- Manual control mode
- Stealth mode with recovery options
- Complete code cleanup and optimization

## License

This plugin is provided as-is for educational and demonstration purposes.

## Support

For technical questions or issues, please refer to the code comments and documentation within the plugin files.