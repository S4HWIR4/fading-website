# Fading Website Plugin

A unique WordPress plugin that gradually reduces your website's opacity over time, creating a subtle fading effect. Perfect for temporary sites, maintenance periods, or creative visual effects.

## 🌟 Features

### Core Functionality
- **Daily Automatic Fading**: Reduces website opacity by 1% every day
- **Manual Opacity Control**: Set any opacity level from 1% to 100%
- **Dual Mode Operation**: Switch between automatic daily fading and manual control
- **Reset Capability**: Instantly restore full opacity (100%)

### Stealth Mode
- **Complete Admin Hiding**: Hide the plugin from WordPress admin interface
- **Plugin List Concealment**: Remove from installed plugins list
- **Menu Removal**: Hide settings menu from admin sidebar
- **Emergency Recovery**: Admin-only reset URL for complete recovery
- **Secure Access Control**: Only administrators can use recovery features

### Professional Features
- **WordPress Standards Compliant**: Follows all WordPress coding standards
- **Version Compatibility Checks**: Automatic PHP and WordPress version validation
- **Clean Uninstall**: Proper cleanup on deactivation
- **Security Focused**: Admin-only access with proper permission checks
- **Error Handling**: Graceful failure with helpful error messages

## 📋 Requirements

- **WordPress**: 4.2 or higher
- **PHP**: 5.4 or higher
- **User Role**: Administrator access required for configuration

## 🚀 Installation

### Method 1: WordPress Admin (Recommended)
1. Download the plugin ZIP file
2. Go to **Plugins > Add New** in WordPress admin
3. Click **Upload Plugin** and select the ZIP file
4. Click **Install Now** and then **Activate**

### Method 2: Manual Installation
1. Upload the `fading-website` folder to `/wp-content/plugins/`
2. Go to **Plugins** in WordPress admin
3. Find "Fading Website" and click **Activate**

### Method 3: WP-CLI
```bash
wp plugin install fading-website.zip --activate
```

## 🎛️ Configuration

After activation, go to **Settings > Fading Website** to configure:

### Mode Selection
- **Daily Fade**: Automatically reduces opacity by 1% every day
- **Manual Control**: Set opacity manually with instant updates

### Opacity Control
- **Current Status**: View current opacity level and days elapsed
- **Manual Adjustment**: Set any value from 1% to 100%
- **Reset Function**: Restore to 100% opacity instantly

### Stealth Mode
- **Activation**: Hide plugin completely from WordPress admin
- **Recovery**: Use emergency reset URL for recovery
- **Security**: Admin-only access with confirmation required

## 📖 Usage Examples

### Temporary Website Fading
```
Day 1: 100% opacity (fully visible)
Day 30: 70% opacity (slightly faded)
Day 60: 40% opacity (noticeably faded)
Day 99: 1% opacity (nearly invisible)
```

### Maintenance Mode Effect
1. Set to **Manual Control** mode
2. Reduce opacity to 50% during maintenance
3. Reset to 100% when maintenance complete

### Creative Visual Effects
- Gradual site retirement over time
- Seasonal opacity changes
- Special event countdown effects

## 🔧 Advanced Usage

### Emergency Recovery
If you lose access due to stealth mode:
```
https://yoursite.com/?fading_reset=1
```
*Note: Only works for administrators*

### Stealth Mode Best Practices
1. **Bookmark recovery URL** before activating stealth
2. **Test on staging site** first
3. **Inform other admins** about the hidden plugin
4. **Document the emergency reset URL** securely

## 🛡️ Security Features

- **Admin-Only Access**: All configuration requires `manage_options` capability
- **Nonce Protection**: Forms protected against CSRF attacks
- **Input Sanitization**: All user inputs properly sanitized
- **Permission Checks**: Multiple layers of access control
- **Secure Recovery**: Emergency reset requires admin authentication

## 🔍 Troubleshooting

### Plugin Not Visible in Settings
- Check if stealth mode is active
- Use emergency reset URL: `yoursite.com/?fading_reset=1`
- Verify admin permissions

### Opacity Not Changing
- Check selected mode (Daily vs Manual)
- Verify WordPress cron is working
- Check for theme conflicts

### Activation Errors
- Verify PHP version (5.4+ required)
- Verify WordPress version (4.2+ required)
- Check file permissions

## 📝 Changelog

### Version 1.0.0
- Initial release
- Daily automatic fading functionality
- Manual opacity control
- Stealth mode with complete admin hiding
- Emergency recovery system
- WordPress and PHP compatibility checks
- Professional admin interface

## 🤝 Support

### Before Requesting Support
1. Check the troubleshooting section above
2. Verify your WordPress and PHP versions meet requirements
3. Test with default theme and no other plugins

### Getting Help
- Check plugin settings under **Settings > Fading Website**
- Use emergency reset if plugin is hidden
- Ensure you have administrator privileges

## 🔒 Privacy

This plugin:
- Does not collect any user data
- Does not make external API calls
- Only stores configuration data locally
- Respects WordPress privacy standards

---

**Note**: This plugin creates visual effects that may impact website accessibility. Consider your users' needs when implementing opacity changes.