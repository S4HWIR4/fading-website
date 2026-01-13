<?php
/*
Plugin Name: Fading Website
Description: Gradually decreases website opacity by 1% every day
Version: 1.0.0
Author: WP Wildcards
Requires at least: 4.2
Tested up to: 6.4
Requires PHP: 5.4
*/

if (!defined('ABSPATH')) {
    exit;
}

// Check requirements on activation
register_activation_hook(__FILE__, 'fading_website_check_requirements');

function fading_website_check_requirements() {
    // Check PHP version
    if (version_compare(PHP_VERSION, '5.4.0', '<')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(
            '<h1>Plugin Activation Error</h1>' .
            '<p><strong>Fading Website</strong> requires PHP 5.4.0 or higher.</p>' .
            '<p>You are currently running PHP ' . PHP_VERSION . '</p>' .
            '<p>Please contact your hosting provider to upgrade PHP.</p>' .
            '<p><a href="' . admin_url('plugins.php') . '">&laquo; Back to Plugins</a></p>',
            'Plugin Activation Error',
            array('back_link' => true)
        );
    }
    
    // Check WordPress version
    if (version_compare(get_bloginfo('version'), '4.2', '<')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(
            '<h1>Plugin Activation Error</h1>' .
            '<p><strong>Fading Website</strong> requires WordPress 4.2 or higher.</p>' .
            '<p>You are currently running WordPress ' . get_bloginfo('version') . '</p>' .
            '<p>Please update WordPress to use this plugin.</p>' .
            '<p><a href="' . admin_url('plugins.php') . '">&laquo; Back to Plugins</a></p>',
            'Plugin Activation Error',
            array('back_link' => true)
        );
    }
}

// Runtime compatibility check
if (version_compare(PHP_VERSION, '5.4.0', '<') || version_compare(get_bloginfo('version'), '4.2', '<')) {
    add_action('admin_notices', 'fading_website_compatibility_notice');
    return; // Stop loading the plugin
}

function fading_website_compatibility_notice() {
    $php_ok = version_compare(PHP_VERSION, '5.4.0', '>=');
    $wp_ok = version_compare(get_bloginfo('version'), '4.2', '>=');
    
    echo '<div class="notice notice-error">';
    echo '<p><strong>Fading Website Plugin Error:</strong></p>';
    
    if (!$php_ok) {
        echo '<p>Requires PHP 5.4+ (you have ' . PHP_VERSION . ')</p>';
    }
    
    if (!$wp_ok) {
        echo '<p>Requires WordPress 4.2+ (you have ' . get_bloginfo('version') . ')</p>';
    }
    
    echo '<p>The plugin has been automatically deactivated.</p>';
    echo '</div>';
    
    // Deactivate the plugin
    deactivate_plugins(plugin_basename(__FILE__));
}

class FadingWebsitePlugin {
    private static $instance = null;
    
    const MODE_DAILY = 'daily';
    const MODE_MANUAL = 'manual';
    const STEALTH_VISIBLE = 0;
    const STEALTH_HIDDEN = 1;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('wp_loaded', [$this, 'handle_emergency_reset'], 1);
        add_action('wp_head', [$this, 'add_opacity_css']);
        add_action('wp', [$this, 'schedule_daily_fade']);
        add_action('fading_website_daily_fade', [$this, 'decrease_opacity']);
        add_action('init', [$this, 'handle_recovery']);
        add_action('admin_post_fading_stealth', [$this, 'handle_stealth_activation']);
        add_action('admin_notices', [$this, 'show_admin_notices']);
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_filter('all_plugins', [$this, 'hide_from_plugins_list']);
        add_filter('show_advanced_plugins', [$this, 'hide_from_advanced_plugins'], 10, 2);
        add_filter('plugin_action_links', [$this, 'hide_plugin_actions'], 10, 2);
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), [$this, 'add_plugin_action_links']);
        
        register_activation_hook(__FILE__, [$this, 'activate_plugin']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate_plugin']);
    }
    
    public function activate_plugin() {
        // FORCE complete reset on activation - this should fix the stealth issue
        $this->reset_to_defaults();
        
        // Double-check that stealth is definitely disabled
        update_option('fading_stealth_level', self::STEALTH_VISIBLE);
        delete_option('fading_recovery_phrase');
        
        set_transient('fading_website_activated', true, 30);
    }
    
    public function deactivate_plugin() {
        wp_clear_scheduled_hook('fading_website_daily_fade');
    }
    
    private function reset_to_defaults() {
        delete_option('fading_recovery_phrase');
        delete_option('fading_recovery_attempts');
        delete_option('fading_stealth_level');
        
        // Get current date in WordPress timezone
        $current_date = wp_date('Y-m-d');
        
        update_option('fading_website_opacity', 100);
        update_option('fading_website_mode', self::MODE_DAILY);
        update_option('fading_website_start_date', $current_date);
        update_option('fading_stealth_level', self::STEALTH_VISIBLE);
        update_option('fading_plugin_version', '1.0.0');
        
        wp_clear_scheduled_hook('fading_website_daily_fade');
        wp_schedule_event(time(), 'daily', 'fading_website_daily_fade');
        
        // Force fix the date issue - delete and recreate the option
        delete_option('fading_website_start_date');
        add_option('fading_website_start_date', $current_date);
    }
    
    public function handle_emergency_reset() {
        if (isset($_GET['fading_reset']) && $_GET['fading_reset'] == '1') {
            // SECURITY: Only allow admins to use emergency reset
            if (!current_user_can('manage_options')) {
                wp_die('Access denied. Only administrators can use the emergency reset function.');
            }
            
            // FORCE complete reset - clear everything
            delete_option('fading_recovery_phrase');
            delete_option('fading_recovery_attempts');
            delete_option('fading_stealth_level');
            
            // Set defaults with explicit values
            update_option('fading_website_opacity', 100);
            update_option('fading_website_mode', self::MODE_DAILY);
            update_option('fading_website_start_date', wp_date('Y-m-d'));
            update_option('fading_stealth_level', 0); // Explicitly set to 0
            update_option('fading_plugin_version', '1.0.0');
            
            wp_clear_scheduled_hook('fading_website_daily_fade');
            wp_schedule_event(time(), 'daily', 'fading_website_daily_fade');
            
            set_transient('fading_website_reset_success', true, 30);
            
            wp_redirect(admin_url('plugins.php?reset_success=1'));
            exit;
        }
    }
    
    public function handle_recovery() {
        // Only emergency reset is available now - no custom phrases
        // Custom recovery phrase functionality has been removed for simplicity
    }
    
    public function hide_from_plugins_list($plugins) {
        $stealth_level = intval(get_option('fading_stealth_level', self::STEALTH_VISIBLE));
        
        if ($stealth_level === self::STEALTH_HIDDEN) {
            $plugin_file = plugin_basename(__FILE__);
            
            if (isset($plugins[$plugin_file])) {
                unset($plugins[$plugin_file]);
            }
        }
        
        return $plugins;
    }
    
    public function hide_from_advanced_plugins($show, $type) {
        // Hide from advanced plugin views
        if ($type === 'dropins' || $type === 'mustuse') {
            return $show;
        }
        
        $stealth_level = intval(get_option('fading_stealth_level', self::STEALTH_VISIBLE));
        if ($stealth_level === self::STEALTH_HIDDEN) {
            // Additional hiding for advanced views
            return $show;
        }
        
        return $show;
    }
    
    public function hide_plugin_actions($actions, $plugin_file) {
        $stealth_level = intval(get_option('fading_stealth_level', self::STEALTH_VISIBLE));
        
        if ($stealth_level === self::STEALTH_HIDDEN) {
            $our_plugin = plugin_basename(__FILE__);
            if ($plugin_file === $our_plugin) {
                // Remove all action links for our plugin when in stealth
                return [];
            }
        }
        
        return $actions;
    }
    
    public function add_plugin_action_links($actions) {
        $stealth_level = intval(get_option('fading_stealth_level', self::STEALTH_VISIBLE));
        
        // Only show links when not in stealth mode
        if ($stealth_level === self::STEALTH_VISIBLE) {
            $settings_link = '<a href="' . admin_url('options-general.php?page=fading-website') . '">Settings</a>';
            $visit_site_link = '<a href="https://www.youtube.com/@wpwildcards" target="_blank">Visit Site</a>';
            
            // Add links to the beginning of the array
            array_unshift($actions, $settings_link, $visit_site_link);
        }
        
        return $actions;
    }
    
    public function handle_stealth_activation() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        // No need for recovery phrase validation - just activate stealth
        if (!isset($_POST['confirm_stealth'])) {
            wp_redirect(admin_url('options-general.php?page=fading-website&error=not_confirmed'));
            exit;
        }
        
        // Activate stealth mode
        update_option('fading_stealth_level', self::STEALTH_HIDDEN);
        
        // Redirect to WordPress dashboard since admin menu will disappear
        wp_redirect(admin_url('index.php?stealth_activated=1'));
        exit;
    }
    
    public function show_admin_notices() {
        if (get_transient('fading_website_reset_success') || isset($_GET['reset_success'])) {
            delete_transient('fading_website_reset_success');
            echo '<div class="notice notice-success is-dismissible">';
            echo '<p><strong>Plugin Reset!</strong> All settings restored to defaults. Stealth mode disabled.</p>';
            echo '</div>';
        }
        
        if (get_transient('fading_website_activated')) {
            delete_transient('fading_website_activated');
            echo '<div class="notice notice-success is-dismissible">';
            echo '<p><strong>Plugin Activated!</strong> Settings reset to defaults.</p>';
            echo '</div>';
        }
        
        if (isset($_GET['recovered'])) {
            echo '<div class="notice notice-success is-dismissible">';
            echo '<p><strong>Plugin Recovered!</strong> Settings reset to defaults.</p>';
            echo '</div>';
        }
    }
    
    public function schedule_daily_fade() {
        $mode = get_option('fading_website_mode', self::MODE_DAILY);
        if ($mode === self::MODE_DAILY && !wp_next_scheduled('fading_website_daily_fade')) {
            wp_schedule_event(time(), 'daily', 'fading_website_daily_fade');
        } elseif ($mode === self::MODE_MANUAL && wp_next_scheduled('fading_website_daily_fade')) {
            wp_clear_scheduled_hook('fading_website_daily_fade');
        }
    }
    
    public function decrease_opacity() {
        $current_opacity = intval(get_option('fading_website_opacity', 100));
        $new_opacity = max(1, $current_opacity - 1);
        update_option('fading_website_opacity', $new_opacity);
    }
    
    public function add_opacity_css() {
        $opacity = get_option('fading_website_opacity', 100);
        if ($opacity < 100) {
            $opacity_decimal = $opacity / 100;
            echo '<style>body { opacity: ' . $opacity_decimal . ' !important; transition: opacity 0.3s ease; }</style>';
        }
    }
    
    public function add_admin_menu() {
        $stealth_level = intval(get_option('fading_stealth_level', self::STEALTH_VISIBLE));
        
        if ($stealth_level === self::STEALTH_VISIBLE) {
            add_options_page(
                'Fading Website',
                'Fading Website',
                'manage_options',
                'fading-website',
                [$this, 'admin_page']
            );
        }
    }
    
    public function admin_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        if (isset($_GET['error'])) {
            $error = $_GET['error'];
            $messages = [
                'not_confirmed' => 'You must confirm that you have bookmarked the emergency reset URL.'
            ];
            if (isset($messages[$error])) {
                echo '<div class="notice notice-error"><p>' . esc_html($messages[$error]) . '</p></div>';
            }
        }
        
        if (isset($_POST['switch_mode'])) {
            $new_mode = $_POST['mode'];
            if (in_array($new_mode, [self::MODE_DAILY, self::MODE_MANUAL])) {
                update_option('fading_website_mode', $new_mode);
                $this->schedule_daily_fade();
                echo '<div class="notice notice-success"><p>Mode switched to ' . ucfirst($new_mode) . '</p></div>';
            }
        }
        
        if (isset($_POST['set_opacity']) && get_option('fading_website_mode') === self::MODE_MANUAL) {
            $opacity = intval($_POST['opacity']);
            $opacity = max(1, min(100, $opacity));
            update_option('fading_website_opacity', $opacity);
            echo '<div class="notice notice-success"><p>Opacity set to ' . $opacity . '%</p></div>';
        }
        
        if (isset($_POST['reset_opacity'])) {
            update_option('fading_website_opacity', 100);
            update_option('fading_website_start_date', wp_date('Y-m-d'));
            echo '<div class="notice notice-success"><p>Opacity reset to 100%</p></div>';
        }
        
        $current_opacity = get_option('fading_website_opacity', 100);
        $current_mode = get_option('fading_website_mode', self::MODE_DAILY);
        $start_date = get_option('fading_website_start_date', wp_date('Y-m-d'));
        $days_elapsed = (strtotime(wp_date('Y-m-d')) - strtotime($start_date)) / (60 * 60 * 24);
        ?>
        <div class="wrap">
            <h1>Fading Website Settings</h1>
            
            <div class="card" style="background: white; padding: 15px; margin: 20px 0; border: 1px solid #ccd0d4;">
                <h2>Mode Selection</h2>
                <form method="post">
                    <p>
                        <label>
                            <input type="radio" name="mode" value="<?php echo self::MODE_DAILY; ?>" <?php checked($current_mode, self::MODE_DAILY); ?> />
                            <strong>Daily Fade</strong> - Automatically reduces opacity by 1% every day
                        </label>
                    </p>
                    <p>
                        <label>
                            <input type="radio" name="mode" value="<?php echo self::MODE_MANUAL; ?>" <?php checked($current_mode, self::MODE_MANUAL); ?> />
                            <strong>Manual Control</strong> - Set opacity manually
                        </label>
                    </p>
                    <input type="submit" name="switch_mode" class="button-primary" value="Switch Mode" />
                </form>
            </div>
            
            <div class="card" style="background: white; padding: 15px; margin: 20px 0; border: 1px solid #ccd0d4;">
                <h2>Current Status</h2>
                <p><strong>Mode:</strong> <?php echo ucfirst($current_mode); ?></p>
                <p><strong>Current Opacity:</strong> <?php echo $current_opacity; ?>%</p>
                <?php if ($current_mode === self::MODE_DAILY): ?>
                    <p><strong>Start Date:</strong> <?php echo $start_date; ?></p>
                    <p><strong>Days Elapsed:</strong> <?php echo floor($days_elapsed); ?></p>
                    <p><strong>Expected Opacity:</strong> <?php echo max(1, 100 - floor($days_elapsed)); ?>%</p>
                <?php endif; ?>
            </div>
            
            <?php if ($current_mode === self::MODE_MANUAL): ?>
            <div class="card" style="background: white; padding: 15px; margin: 20px 0; border: 1px solid #ccd0d4;">
                <h2>Manual Control</h2>
                <form method="post">
                    <table class="form-table">
                        <tr>
                            <th>Opacity (%)</th>
                            <td>
                                <input type="number" name="opacity" value="<?php echo $current_opacity; ?>" min="1" max="100" />
                                <input type="submit" name="set_opacity" class="button-primary" value="Set Opacity" />
                            </td>
                        </tr>
                    </table>
                </form>
            </div>
            <?php endif; ?>
            
            <div class="card" style="background: white; padding: 15px; margin: 20px 0; border: 1px solid #ccd0d4;">
                <h2>Reset</h2>
                <form method="post">
                    <p>
                        <input type="submit" name="reset_opacity" class="button" value="Reset to 100%" 
                               onclick="return confirm('Are you sure?')" />
                    </p>
                </form>
            </div>
            
            <div class="card" style="background: #fff2f2; padding: 15px; margin: 20px 0; border: 2px solid #d63638;">
                <h2>Stealth Mode</h2>
                <p><strong>WARNING:</strong> This will hide the plugin from WordPress admin.</p>
                
                <div style="background: #fff8e1; padding: 15px; margin: 15px 0;">
                    <h3>What Stealth Mode Does</h3>
                    <ul>
                        <li><strong>Hides plugin from plugins list</strong> - Won't appear in WordPress admin</li>
                        <li><strong>Removes admin menu</strong> - Settings > Fading Website will disappear</li>
                        <li><strong>Plugin continues working</strong> - Opacity effects remain active</li>
                        <li><strong>Recovery via emergency URL only</strong> - No custom phrases needed</li>
                    </ul>
                </div>
                
                <div style="background: #f0f0f0; padding: 15px; margin: 15px 0;">
                    <h3>Recovery Method</h3>
                    <p><strong>Emergency Reset URL:</strong></p>
                    <p><code><?php echo home_url('?fading_reset=1'); ?></code></p>
                    <p><small>Bookmark this URL before activating stealth mode!</small></p>
                </div>
                
                <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" id="stealth-form">
                    <input type="hidden" name="action" value="fading_stealth" />
                    
                    <p>
                        <input type="checkbox" name="confirm_stealth" id="confirm_stealth" required />
                        <label for="confirm_stealth">I have bookmarked the emergency reset URL and understand the plugin will be hidden</label>
                    </p>
                    
                    <input type="submit" id="stealth_submit" class="button button-secondary" value="Activate Stealth Mode" disabled style="opacity: 0.5;" />
                </form>
                
                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const checkbox = document.getElementById('confirm_stealth');
                    const submitBtn = document.getElementById('stealth_submit');
                    
                    if (checkbox && submitBtn) {
                        checkbox.addEventListener('change', function() {
                            if (this.checked) {
                                submitBtn.disabled = false;
                                submitBtn.style.opacity = '1';
                                submitBtn.classList.remove('button-secondary');
                                submitBtn.classList.add('button-primary');
                            } else {
                                submitBtn.disabled = true;
                                submitBtn.style.opacity = '0.5';
                                submitBtn.classList.remove('button-primary');
                                submitBtn.classList.add('button-secondary');
                            }
                        });
                    }
                });
                </script>
            </div>
        </div>
        <?php
    }
}

// Initialize the plugin only if requirements are met
if (fading_website_requirements_met()) {
    FadingWebsitePlugin::get_instance();
}

function fading_website_requirements_met() {
    return version_compare(PHP_VERSION, '5.4.0', '>=') && 
           version_compare(get_bloginfo('version'), '4.2', '>=');
}