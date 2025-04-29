<?php
namespace LC_WPMethods;

if (!defined('ABSPATH')) {
    exit;
}

class Admin_Settings
{
    public function __construct()
    {
        add_action('admin_menu', [$this, 'add_menu_page']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function add_menu_page()
    {
        add_menu_page(
            __('Live Chat WPMethods', 'lc-wpmethods'),
            __('Live Chat', 'lc-wpmethods'),
            'manage_options',
            'lc-wpmethods-settings',
            [$this, 'settings_page'],
            'dashicons-format-chat'
        );
    }

    public function enqueue_assets($hook)
    {
        if ($hook !== 'toplevel_page_lc-wpmethods-settings') {
            return;
        }
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_style('lc-wpmethods-admin', LC_WPMETHODS_URL . 'assets/css/admin.css', [], '1.0');
        wp_enqueue_script('lc-wpmethods-admin', LC_WPMETHODS_URL . 'assets/js/admin.js', ['jquery', 'wp-color-picker'], '1.0', true);
        
        // Enqueue FontAwesome for icons
        wp_enqueue_style('fontawesome', LC_WPMETHODS_URL . 'assets/css/all.min.css', [], '6.7.2');
    }

    public function register_settings()
    {
        register_setting('lc_wpmethods_settings_group', 'lc_wpmethods_settings', [
            'sanitize_callback' => [$this, 'sanitize_settings']
        ]);
    }

    public function sanitize_settings($settings)
    {
        if (isset($settings['lc_wpmethods_links']) && is_array($settings['lc_wpmethods_links'])) {
            foreach ($settings['lc_wpmethods_links'] as &$link) {
                $link['url']   = sanitize_text_field($link['url'] ?? '');
                $link['icon']  = sanitize_text_field($link['icon'] ?? '');
                $link['label'] = sanitize_text_field($link['label'] ?? '');
                $link['color'] = sanitize_hex_color($link['color'] ?? '');
            }
        }

        // Sanitize style fields
        $settings['toggle_bg_color'] = sanitize_hex_color($settings['toggle_bg_color'] ?? '');
        $settings['icon_color'] = sanitize_hex_color($settings['icon_color'] ?? '');
        $settings['hover_color'] = sanitize_hex_color($settings['hover_color'] ?? '');

        return $settings;
    }

    public function settings_page()
    {
        $options = get_option('lc_wpmethods_settings', []);
        $lc_wpmethods_links = $options['lc_wpmethods_links'] ?? [];
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Live Chat Settings', 'lc-wpmethods'); ?></h1>

            <div class="lc-tabs">
                <button class="lc-tab-button active" data-tab="general"><?php esc_html_e('Icons & Links', 'lc-wpmethods'); ?></button>
                <button class="lc-tab-button" data-tab="style"><?php esc_html_e('Styles', 'lc-wpmethods'); ?></button>
            </div>

            <form method="post" action="options.php">
                <?php settings_fields('lc_wpmethods_settings_group'); ?>
                <?php do_settings_sections('lc_wpmethods_settings_group'); ?>

                <div class="lc-tab-content active" id="tab-general">
                    <div id="cl-wpmethods-repeater-fields">
                        <?php if (!empty($lc_wpmethods_links)) : ?>
                            <?php foreach ($lc_wpmethods_links as $index => $link) : ?>
                                <div class="cl-wpmethods-field-group">
                                    <div class="cl-wpmethods-preview-icon">
                                        <i class="<?php echo esc_attr($link['icon'] ?? 'fas fa-comment-dots'); ?>"></i>
                                    </div>

                                    <div class="flex-cl-wpmethods">
                                        <label for="cl-url-<?php echo esc_attr($index); ?>" class="cl-label-wpm">Enter URL</label>
                                        <input type="text" id="cl-url-<?php echo esc_attr($index); ?>" name="lc_wpmethods_settings[lc_wpmethods_links][<?php echo esc_attr($index); ?>][url]" value="<?php echo esc_url($link['url'] ?? ''); ?>" placeholder="URL (Ex: https://wa.me/88017900000)" />
                                    </div>

                                    <div class="flex-cl-wpmethods">
                                        <label for="cl-class-<?php echo esc_attr($index); ?>" class="cl-label-wpm">Enter Icon Class</label>
                                        <input type="text" id="cl-class-<?php echo esc_attr($index); ?>" name="lc_wpmethods_settings[lc_wpmethods_links][<?php echo esc_attr($index); ?>][icon]" value="<?php echo esc_attr($link['icon'] ?? ''); ?>" class="icon-input" placeholder="Icon class (Ex: fas fa-home)" />
                                    </div>

                                    <div class="flex-cl-wpmethods">
                                        <label for="cl-label-<?php echo esc_attr($index); ?>" class="cl-label-wpm">Enter Label</label>
                                        <input type="text" id="cl-label-<?php echo esc_attr($index); ?>" name="lc_wpmethods_settings[lc_wpmethods_links][<?php echo esc_attr($index); ?>][label]" value="<?php echo esc_attr($link['label'] ?? ''); ?>" placeholder="Enter Label (Ex: Whatsapp)" />
                                    </div>

                                    <div class="flex-cl-wpmethods">
                                        <label for="cl-color-<?php echo esc_attr($index); ?>" class="cl-label-wpm">Pick Icon Color</label>
                                        <input type="text" id="cl-color-<?php echo esc_attr($index); ?>" name="lc_wpmethods_settings[lc_wpmethods_links][<?php echo esc_attr($index); ?>][color]" value="<?php echo esc_attr($link['color'] ?? ''); ?>" class="color-picker" placeholder="Pick Icon Color" />
                                    </div>

                                    <div class="flex-cl-wpmethods">
                                        <label for="cl-bgcolor-<?php echo esc_attr($index); ?>" class="cl-label-wpm">Pick Background Color</label>
                                        <input type="text" id="cl-bgcolor-<?php echo esc_attr($index); ?>" name="lc_wpmethods_settings[lc_wpmethods_links][<?php echo esc_attr($index); ?>][bg_color]" value="<?php echo esc_attr($link['bg_color'] ?? ''); ?>" class="color-picker" placeholder="Pick Background Color" />
                                    </div>

                                    <button type="button" class="cl-wpmethods-remove-field">Remove</button>
                                </div>

                            <?php endforeach; ?>
                        <?php else : ?>
                            <div class="cl-wpmethods-field-group">
                                <div class="cl-wpmethods-preview-icon">
                                    <i class="fas fa-comment-dots"></i>
                                </div>

                                <div class="flex-cl-wpmethods">
                                    <label for="cl-url-0" class="cl-label-wpm">Enter URL</label>
                                    <input type="text" id="cl-url-0" name="lc_wpmethods_settings[lc_wpmethods_links][0][url]" placeholder="URL (Ex: https://wa.me/88017900000)" />
                                </div>

                                <div class="flex-cl-wpmethods">
                                    <label for="cl-class-0" class="cl-label-wpm">Enter Icon Class</label>
                                    <input type="text" id="cl-class-0" name="lc_wpmethods_settings[lc_wpmethods_links][0][icon]" class="icon-input" placeholder="Icon class (Ex: fas fa-home)" />
                                </div>

                                <div class="flex-cl-wpmethods">
                                    <label for="cl-label-0" class="cl-label-wpm">Enter Label</label>
                                    <input type="text" id="cl-label-0" name="lc_wpmethods_settings[lc_wpmethods_links][0][label]" placeholder="Enter Label (Ex: Whatsapp)" />
                                </div>

                                <div class="flex-cl-wpmethods">
                                    <label for="cl-color-0" class="cl-label-wpm">Pick Icon Color</label>
                                    <input type="text" id="cl-color-0" name="lc_wpmethods_settings[lc_wpmethods_links][0][color]" class="color-picker" placeholder="Pick Icon Color" />
                                </div>

                                <div class="flex-cl-wpmethods">
                                    <label for="cl-bgcolor-0" class="cl-label-wpm">Pick Background Color</label>
                                    <input type="text" id="cl-bgcolor-0" name="lc_wpmethods_settings[lc_wpmethods_links][0][bg_color]" class="color-picker" placeholder="Pick Background Color" />
                                </div>

                                <button type="button" class="cl-wpmethods-remove-field">Remove</button>
                            </div>
                        <?php endif; ?>
                    </div>

                    <button type="button" id="cl-wpmethods-add-field">Add Link</button>
                </div>

                <div class="lc-tab-content" id="tab-style">
                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row"><?php esc_html_e('Toggle Button Color', 'lc-wpmethods'); ?></th>
                            <td><input type="text" name="lc_wpmethods_settings[toggle_bg_color]" value="<?php echo esc_attr($options['toggle_bg_color'] ?? '#00DA62'); ?>" class="color-field" /></td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><?php esc_html_e('Icon Color', 'lc-wpmethods'); ?></th>
                            <td><input type="text" name="lc_wpmethods_settings[icon_color]" value="<?php echo esc_attr($options['icon_color'] ?? '#FFFFFF'); ?>" class="color-field" /></td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><?php esc_html_e('Hover Color', 'lc-wpmethods'); ?></th>
                            <td><input type="text" name="lc_wpmethods_settings[hover_color]" value="<?php echo esc_attr($options['hover_color'] ?? '#128C7E'); ?>" class="color-field" /></td>
                        </tr>
                    </table>
                </div>

                <?php submit_button(); ?>
            </form>


            <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

            <script>
                let counter = <?php echo count($lc_wpmethods_links); ?>;

                function initializeColorPickers() {
                    jQuery('.color-picker').wpColorPicker();
                }

                function updateIconPreview(input) {
                    const iconClass = input.value.trim() || 'fas fa-comment-dots';
                    const preview = input.closest('.cl-wpmethods-field-group').querySelector('.cl-wpmethods-preview-icon i');
                    preview.className = iconClass;
                }

                document.getElementById('cl-wpmethods-add-field').addEventListener('click', function() {
                    const container = document.getElementById('cl-wpmethods-repeater-fields');

                    const fieldGroup = document.createElement('div');
                    fieldGroup.className = 'cl-wpmethods-field-group';
                    fieldGroup.innerHTML = `
                        <div class="cl-wpmethods-preview-icon">
                            <i class="fas fa-comment-dots"></i>
                        </div>
                        <div class="flex-cl-wpmethods">
                            <label for="cl-url-${counter}">Enter URL</label>
                            <input type="text" id="cl-url-${counter}" name="lc_wpmethods_settings[lc_wpmethods_links][${counter}][url]" placeholder="URL (Ex: https://wa.me/88017900000)" />
                        </div>

                        <div class="flex-cl-wpmethods">
                            <label for="cl-class-${counter}">Enter Icon Class</label>
                            <input type="text" id="cl-class-${counter}" name="lc_wpmethods_settings[lc_wpmethods_links][${counter}][icon]" class="icon-input" placeholder="Enter Icon Class" />
                        </div>

                        <div class="flex-cl-wpmethods">
                            <label for="cl-label-${counter}">Enter Label</label>
                            <input type="text" id="cl-label-${counter}" name="lc_wpmethods_settings[lc_wpmethods_links][${counter}][label]" placeholder="Enter Label (Ex: Whatsapp)" />
                        </div>

                        <div class="flex-cl-wpmethods">
                            <label for="cl-color-${counter}">Pick Icon Color</label>
                            <input type="text" id="cl-color-${counter}" name="lc_wpmethods_settings[lc_wpmethods_links][${counter}][color]" class="color-picker" placeholder="Pick Icon Color" />
                        </div>

                        <div class="flex-cl-wpmethods">
                            <label for="cl-bgcolor-${counter}">Pick Background Color</label>
                            <input type="text" id="cl-bgcolor-${counter}" name="lc_wpmethods_settings[lc_wpmethods_links][${counter}][bg_color]" class="color-picker" placeholder="Pick Background Color" />
                        </div>
                        <button type="button" class="cl-wpmethods-remove-field">Remove</button>
                    `;

                    container.appendChild(fieldGroup);

                    initializeColorPickers();
                    counter++;
                });

                // Remove field
                document.addEventListener('click', function(e) {
                    if (e.target && e.target.classList.contains('cl-wpmethods-remove-field')) {
                        e.target.parentElement.remove();
                    }
                });

                // Update icon live preview
                document.addEventListener('input', function(e) {
                    if (e.target && e.target.classList.contains('icon-input')) {
                        updateIconPreview(e.target);
                    }
                });

                new Sortable(document.getElementById('cl-wpmethods-repeater-fields'), {
                    animation: 150,
                    ghostClass: 'sortable-ghost'
                });

                jQuery(document).ready(function($) {
                    initializeColorPickers();
                });
            </script>
        </div>
        <?php
    }
}
