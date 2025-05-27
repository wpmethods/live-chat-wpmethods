<?php
namespace LC_WPMethods;

if (!defined('ABSPATH')) {
    exit;
}

class Admin_Settings_Lcw{
    private $option_key      = 'wpmlc_license_key';
    public function __construct()
    {
        add_action('admin_menu', [$this, 'add_menu_page']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function add_menu_page()
    {
        add_menu_page(
            __('Chat Floating Icons', 'lc-wpmethods'),
            __('Chat Floating Icons', 'lc-wpmethods'),
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
        $min_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_style('lc-wpmethods-admin', LC_WPMETHODS_URL . 'assets/css/admin'. $min_suffix . '.css', [], VERSION_SFIW);
        wp_enqueue_script('lc-wpmethods-admin', LC_WPMETHODS_URL . 'assets/js/admin'. $min_suffix . '.js', ['jquery', 'wp-color-picker'], VERSION_SFIW, true);
        
        // Enqueue FontAwesome for icons
        wp_enqueue_style('fontawesome', LC_WPMETHODS_URL . 'assets/css/all.min.css', [], '6.7.2');
    }


    public function register_settings()
    {
        register_setting('lc_wpmethods_settings_group', 'lc_wpmethods_settings', [
            'sanitize_callback' => [$this, 'sanitize_settings']
        ]);
    }

    private function sanitize_select_position($input) {
        // Sanitize the input
        $input = sanitize_text_field($input ?? '');
        
        // Define valid options for the select field
        $valid_options = ['left', 'right'];
        
        // Return the input if valid, otherwise return a default (e.g., '')
        return in_array($input, $valid_options, true) ? $input : '';
    }
    
   

    public function sanitize_settings($settings)
    {
        if (isset($settings['lc_wpmethods_links']) && is_array($settings['lc_wpmethods_links'])) {
            foreach ($settings['lc_wpmethods_links'] as &$link) {
                $link['url']   = sanitize_text_field($link['url'] ?? '');
                $link['icon']  = sanitize_text_field($link['icon'] ?? '');
                $link['label'] = sanitize_text_field($link['label'] ?? '');
                $link['color'] = sanitize_hex_color($link['color'] ?? '');
                $link['bg_color'] = sanitize_hex_color($link['bg_color'] ?? '');
                $link['s_bg_color'] = sanitize_hex_color($link['s_bg_color'] ?? '');
            }
        }

        // Sanitize style fields
        $settings['toggle_icon_class'] = sanitize_text_field($settings['toggle_icon_class'] ?? '');
        $settings['toggle_bg_color'] = sanitize_hex_color($settings['toggle_bg_color'] ?? '');
        $settings['toggle_gbg_color'] = sanitize_hex_color($settings['toggle_gbg_color'] ?? '');
        $settings['icon_color'] = sanitize_hex_color($settings['icon_color'] ?? '');
        $settings['icon_size'] = sanitize_text_field($settings['icon_size'] ?? '');
        $settings['height_width'] = sanitize_text_field($settings['height_width'] ?? '');
        $settings['hover_color'] = sanitize_hex_color($settings['hover_color'] ?? '');
        $settings['custom_text'] = sanitize_text_field($settings['custom_text'] ?? '');
        $settings['position'] = $this->sanitize_select_position($settings['position'] ?? '');
        $settings['right_offset'] = sanitize_text_field($settings['right_offset'] ?? '');
        $settings['left_offset'] = sanitize_text_field($settings['left_offset'] ?? '');
        $settings['bottom_offset'] = sanitize_text_field($settings['bottom_offset'] ?? '');



        //WooCommerce Section
        if(function_exists('is_woocommerce')){
            $settings['track_woo_product'] = isset($settings['track_woo_product']) ? '1' : '0';
            $settings['woo_product_msg'] = sanitize_text_field($settings['woo_product_msg'] ?? '');
            $settings['woo_product_name'] = sanitize_text_field($settings['woo_product_name'] ?? '');
            $settings['woo_product_price'] = sanitize_text_field($settings['woo_product_price'] ?? '');
        }
        

        return $settings;
    }

    public function settings_page()
    {
        $options = get_option('lc_wpmethods_settings', []);
        $lc_wpmethods_links = $options['lc_wpmethods_links'] ?? [];

        $license_status = get_option('wpmlc_license_status', 'inactive');
        $limit_items = ($license_status !== 'active');

        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Social Floating Icons', 'lc-wpmethods'); ?></h1>

            <?php if (isset($_GET['settings-updated']) && $_GET['settings-updated'] == true) {
                echo '<div class="notice notice-success is-dismissible"><p>Changes saved!</p></div>';
            } ?>

            <div class="lc-tabs">
                <button class="lc-tab-button active" data-tab="general"><?php esc_html_e('Icons & Links', 'lc-wpmethods'); ?></button>
                <button class="lc-tab-button" data-tab="style"><?php esc_html_e('Widget Styles', 'lc-wpmethods'); ?></button>
                <?php if(function_exists('is_woocommerce')) { ?>
                <button class="lc-tab-button" data-tab="woocommerce"><?php esc_html_e('WooCommerce', 'lc-wpmethods'); ?></button>
                <?php } ?>
                <button class="lc-tab-button" data-tab="help"><?php esc_html_e('Help', 'lc-wpmethods'); ?></button>
            </div>

            <form method="post" action="options.php">
                <?php settings_fields('lc_wpmethods_settings_group'); ?>
                <?php do_settings_sections('lc_wpmethods_settings_group'); ?>

                <div class="lc-tab-content active" id="tab-general">
                    <div id="cl-wpmethods-repeater-fields">
                        <?php if (!empty($lc_wpmethods_links)) : ?>
                            <?php 
                            $displayed_count = 0;
                            foreach ($lc_wpmethods_links as $index => $link) : 
                                if ($limit_items && $displayed_count >= 2) {
                                    break;
                                }
                                $displayed_count++;
                                
                                ?>
                                <div class="cl-wpmethods-field-group">
                                    <div class="cl-wpmethods-preview-icon">
                                        <i class="<?php echo esc_attr($link['icon'] ?? 'fab fa-whatsapp'); ?>"></i>
                                    </div>

                                    <div class="flex-cl-wpmethods">
                                        <label for="cl-color-<?php echo esc_attr($index); ?>" class="cl-label-wpm">Icon Color</label>
                                        <input type="text" id="cl-color-<?php echo esc_attr($index); ?>" name="lc_wpmethods_settings[lc_wpmethods_links][<?php echo esc_attr($index); ?>][color]" value="<?php echo esc_attr($link['color'] ?? ''); ?>" class="color-picker" placeholder="Pick Icon Color" />
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
                                        <label for="cl-bgcolor-<?php echo esc_attr($index); ?>" class="cl-label-wpm">Background Color</label>
                                        <input type="text" id="cl-bgcolor-<?php echo esc_attr($index); ?>" name="lc_wpmethods_settings[lc_wpmethods_links][<?php echo esc_attr($index); ?>][bg_color]" value="<?php echo esc_attr($link['bg_color'] ?? ''); ?>" class="color-picker" placeholder="Pick Background Color" />
                                    </div>

                                    <div class="flex-cl-wpmethods">
                                        <label for="cl-sbgcolor-<?php echo esc_attr($index); ?>" class="cl-label-wpm">Gradient Color</label>
                                        <input type="text" id="cl-sbgcolor-<?php echo esc_attr($index); ?>" name="lc_wpmethods_settings[lc_wpmethods_links][<?php echo esc_attr($index); ?>][s_bg_color]" value="<?php echo esc_attr($link['s_bg_color'] ?? ''); ?>" class="color-picker" placeholder="Pick Background Color" />
                                    </div>

                                    <button type="button" class="cl-wpmethods-remove-field">Remove</button>
                                </div>

                            <?php endforeach; ?>
                        <?php else : ?>
                            <div class="cl-wpmethods-field-group">
                                <div class="cl-wpmethods-preview-icon">
                                    <i class="fab fa-whatsapp"></i>
                                </div>

                                <div class="flex-cl-wpmethods">
                                    <label for="cl-color-0" class="cl-label-wpm">Icon Color</label>
                                    <input type="text" id="cl-color-0" name="lc_wpmethods_settings[lc_wpmethods_links][0][color]" class="color-picker" placeholder="Pick Icon Color" />
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
                                    <label for="cl-bgcolor-0" class="cl-label-wpm">Background Color</label>
                                    <input type="text" id="cl-bgcolor-0" name="lc_wpmethods_settings[lc_wpmethods_links][0][bg_color]" class="color-picker" placeholder="Pick Background Color" />
                                </div>

                                <div class="flex-cl-wpmethods">
                                    <label for="cl-sbgcolor-0" class="cl-label-wpm">Gradient Color</label>
                                    <input type="text" id="cl-sbgcolor-0" name="lc_wpmethods_settings[lc_wpmethods_links][0][s_bg_color]" class="color-picker" placeholder="Pick Gradient Color" />
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
                                <th scope="row"><?php esc_html_e('Widget Icon', 'lc-wpmethods'); ?></th>
                                <td><input type="text" name="lc_wpmethods_settings[toggle_icon_class]" value="<?php echo esc_attr($options['toggle_icon_class'] ?? 'fas fa-comment-dots'); ?>" placeholder="Ex: fas fa-comment-dots" /></td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><?php esc_html_e('Button Background', 'lc-wpmethods'); ?></th>
                            <td><input type="text" name="lc_wpmethods_settings[toggle_bg_color]" value="<?php echo esc_attr($options['toggle_bg_color'] ?? '#00DA62'); ?>" class="color-field" /></td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><?php esc_html_e('Gradient Background', 'lc-wpmethods'); ?></th>
                            <td><input type="text" name="lc_wpmethods_settings[toggle_gbg_color]" value="<?php echo esc_attr($options['toggle_gbg_color'] ?? ''); ?>" class="color-field" /></td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><?php esc_html_e('Icon Color', 'lc-wpmethods'); ?></th>
                            <td><input type="text" name="lc_wpmethods_settings[icon_color]" value="<?php echo esc_attr($options['icon_color'] ?? '#FFFFFF'); ?>" class="color-field" /></td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><?php esc_html_e('Icon Size (px)', 'lc-wpmethods'); ?></th>
                            <td><input type="number" name="lc_wpmethods_settings[icon_size]" value="<?php echo esc_attr($options['icon_size'] ?? '30'); ?>" /></td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><?php esc_html_e('Button Size (px)', 'lc-wpmethods'); ?></th>
                            <td><input type="number" name="lc_wpmethods_settings[height_width]" value="<?php echo esc_attr($options['height_width'] ?? '50'); ?>" /></td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><?php esc_html_e('Hover Color', 'lc-wpmethods'); ?></th>
                            <td><input type="text" name="lc_wpmethods_settings[hover_color]" value="<?php echo esc_attr($options['hover_color'] ?? '#128C7E'); ?>" class="color-field" /></td>
                        </tr>

                        <tr valign="top">
                            <th scope="row"><?php esc_html_e('Pulse Animation Circle', 'lc-wpmethods'); ?></th>
                            <td><input type="text" name="lc_wpmethods_settings[pulse_animation_border_color]" value="<?php echo esc_attr($options['pulse_animation_border_color'] ?? '#00DA62'); ?>" class="color-field" /></td>
                        </tr>


                        <tr valign="top">
                            <th scope="row"><?php esc_html_e('Messege', 'lc-wpmethods'); ?></th>
                            <td>
                                <textarea name="lc_wpmethods_settings[custom_text]" rows="4" cols="50" placeholder="Ex: Hi, how are you?" ><?php echo esc_attr($options['custom_text'] ?? ''); ?></textarea>
                            </td>
                        </tr>


                        <tr valign="top">
                            <th scope="row"><?php esc_html_e('Button Position', 'lc-wpmethods'); ?></th>
                            <td>
                                <select name="lc_wpmethods_settings[position]">
                                    <option value="right" <?php selected($options['position'] ?? '', 'right'); ?>><?php esc_html_e('Right', 'lc-wpmethods'); ?></option>
                                    <option value="left" <?php selected($options['position'] ?? '', 'left'); ?>><?php esc_html_e('Left', 'lc-wpmethods'); ?></option>
                                </select>
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row"><?php esc_html_e('Bottom Offset', 'lc-wpmethods'); ?></th>
                            <td>
                                <input type="text" name="lc_wpmethods_settings[bottom_offset]" value="<?php echo esc_attr($options['bottom_offset'] ?? '20px'); ?>" placeholder="Ex: 20px or 5%" />
                                <p class="description"><?php esc_html_e('Distance from the bottom of the screen.', 'lc-wpmethods'); ?></p>
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row"><?php esc_html_e('Left Offset', 'lc-wpmethods'); ?></th>
                            <td>
                                <input type="text" name="lc_wpmethods_settings[left_offset]" value="<?php echo esc_attr($options['left_offset'] ?? '20px'); ?>" placeholder="Ex: 20px" />
                                <p class="description"><?php esc_html_e('Distance from the left if position is set to "Left".', 'lc-wpmethods'); ?></p>
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row"><?php esc_html_e('Right Offset', 'lc-wpmethods'); ?></th>
                            <td>
                                <input type="text" name="lc_wpmethods_settings[right_offset]" value="<?php echo esc_attr($options['right_offset'] ?? '20px'); ?>" placeholder="Ex: 20px" />
                                <p class="description"><?php esc_html_e('Distance from the right if position is set to "Right".', 'lc-wpmethods'); ?></p>
                            </td>
                        </tr>

                    </table>
                </div>

                <?php if(function_exists('is_woocommerce')) { ?>
                <div class="lc-tab-content" id="tab-woocommerce">
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('Track WooCommerce Product', 'lc-wpmethods'); ?></th>
                        <td>
                            <input type="checkbox"
                                name="lc_wpmethods_settings[track_woo_product]"
                                value="1"
                                <?php checked( isset($options['track_woo_product']) && $options['track_woo_product'] == '1' ); ?> />
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('Custom Product Message', 'lc-wpmethods'); ?></th>
                        <td>
                            <textarea name="lc_wpmethods_settings[woo_product_msg]" rows="4" cols="50" placeholder="I want to buy this product"><?php echo esc_textarea($options['woo_product_msg'] ?? 'I want to buy this product and product details below'); ?></textarea>
                            <p class="description"><?php esc_html_e('You can type product message', 'lc-wpmethods'); ?></p>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('Product title label', 'lc-wpmethods'); ?></th>
                        <td><input type="text" name="lc_wpmethods_settings[woo_product_name]" value="<?php echo esc_attr($options['woo_product_name'] ?? 'Product Name'); ?>" placeholder="Product Name" /></td>
                    </tr>


                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('Product price label', 'lc-wpmethods'); ?></th>
                        <td><input type="text" name="lc_wpmethods_settings[woo_product_price]" value="<?php echo esc_attr($options['woo_product_price'] ?? 'Price'); ?>" placeholder="Price" /></td>
                    </tr>

                </table>
                </div>
                <?php } ?>

                <div class="lc-tab-content" id="tab-help">
                    <h2><?php esc_html_e('Chat Social Links Example', 'lc-wpmethods'); ?></h2>
                    <ul>
                        <li><strong>WhatsApp:</strong> https://wa.me/your-number (Ex: https://wa.me/1797150000)</li>
                        <li><strong>Messenger:</strong> https://m.me/your-page</li>
                        <li><strong>Telegram:</strong> https://t.me/your-username</li>
                        <li><strong>Phone/Call:</strong> tel:your-phone-number</li>
                        <li><strong>Skype:</strong> skype:your-username?chat</li>
                        <li><strong>Viber:</strong> viber://chat?number=your-number</li>
                        <li><strong>Signal:</strong> https://signal.me/#p/your-number</li>
                        <li><strong>Snapchat:</strong> https://www.snapchat.com/add/your-username</li>
                        <li><strong>Instagram:</strong> https://instagram.com/your-username</li>
                        <li><strong>Facebook:</strong> https://facebook.com/your-page</li>
                        <li><strong>X (Twitter):</strong> https://x.com/your-username</li>
                        <li><strong>LinkedIn:</strong> https://linkedin.com/in/your-username</li>
                        <li><strong>Discord:</strong> https://discord.com/users/your-user-id</li>
                        <li><strong>WeChat:</strong> weixin://dl/chat?your-wechat-id</li>

                    </ul>

                    <h2><?php esc_html_e('Font Awesome 6 Icon Class Examples', 'lc-wpmethods'); ?></h2>
                    <ul>
                        <li><code>fas fa-home</code></li>
                        <li><code>fab fa-whatsapp</code></li>
                        <li><code>fab fa-facebook-messenger</code></li>
                        <li><code>fab fa-telegram</code></li>
                        <li><code>fab fa-skype</code></li>
                        <li><code>fab fa-viber</code></li>
                        <li><code>fab fa-weixin</code></li>
                        <li><code>fab fa-instagram</code></li>
                        <li><code>fab fa-twitter</code></li>
                        <li><code>fab fa-linkedin</code></li>
                        <li><code>fab fa-pinterest</code></li>
                        <li><code>fab fa-snapchat</code></li>
                        <li><code>fab fa-youtube</code></li>
                        <li><code>fab fa-reddit</code></li>
                        <li><code>fab fa-tiktok</code></li>
                        <li><code>fab fa-discord</code></li>
                        <li><code>fab fa-github</code></li>
                        <li><code>fab fa-soundcloud</code></li>
                        <li><code>fab fa-slack</code></li>
                        <li><code>fab fa-yahoo</code></li>
                        <li><code>fab fa-tumblr</code></li>
                        <li><code>fab fa-flickr</code></li>
                        <li><code>fab fa-vimeo</code></li>
                        <li><code>fas fa-envelope</code></li>
                        <li><code>fas fa-phone-alt</code></li>
                        <li><code>fas fa-comments</code></li>
                        <li><code>fas fa-video</code></li>
                        <li><code>fas fa-camera</code></li>
                        <li><code>fas fa-map-marker-alt</code></li>
                        <li><code>fas fa-search</code></li>
                        <li><code>fas fa-cogs</code></li>
                        <li><code>fas fa-user-circle</code></li>
                        <li><code>fas fa-phone-square-alt</code></li>

                    </ul>

                    <p>
                        <?php esc_html_e('Use any FontAwesome 6 class name for your icon. You can browse icons at:', 'lc-wpmethods'); ?>
                        <a href="https://fontawesome.com/v6/search" target="_blank">https://fontawesome.com/icons</a>
                    </p>

                    <p>
                        You can buy the license to get full access of this plugin <a href="https://wpmethods.com/social-floating-icon" target="_blank">Click here</a>
                    </p>
                </div>


                <?php submit_button(); ?>
            </form>


            <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

            <script>
            document.addEventListener("DOMContentLoaded", function () {
                const addButton = document.getElementById("cl-wpmethods-add-field");
                const container = document.getElementById("cl-wpmethods-repeater-fields");
                const limit = <?php echo $limit_items ? 'true' : 'false'; ?>;
                let counter = <?php echo count($lc_wpmethods_links); ?>;

                // Add Field
                addButton?.addEventListener("click", function () {
                    const count = container.querySelectorAll(".cl-wpmethods-field-group").length;

                    if (limit && count >= 2) {
                        alert("The free version allows only up to 2 links. Please activate your license for unlimited links.");
                        return;
                    }

                    const fieldGroup = document.createElement("div");
                    fieldGroup.className = "cl-wpmethods-field-group";
                    fieldGroup.style.marginBottom = "15px";
                    fieldGroup.innerHTML = `
                        <div class="cl-wpmethods-preview-icon" style="margin-bottom: 8px;">
                            <i class="fab fa-whatsapp"></i>
                        </div>

                        <div class="flex-cl-wpmethods">
                            <label class="cl-label-wpm" for="cl-color-${counter}">Icon Color</label>
                            <input type="text" id="cl-color-${counter}" name="lc_wpmethods_settings[lc_wpmethods_links][${counter}][color]" class="color-picker" placeholder="Pick Icon Color" />
                        </div>

                        <div  class="flex-cl-wpmethods">
                            <label class="cl-label-wpm" for="cl-url-${counter}">Enter URL</label>
                            <input type="text" id="cl-url-${counter}" name="lc_wpmethods_settings[lc_wpmethods_links][${counter}][url]" placeholder="URL (Ex: https://wa.me/88017900000)" />
                        </div>

                        <div class="flex-cl-wpmethods">
                            <label class="cl-label-wpm" for="cl-class-${counter}">Enter Icon Class</label>
                            <input type="text" id="cl-class-${counter}" name="lc_wpmethods_settings[lc_wpmethods_links][${counter}][icon]" class="icon-input" placeholder="Enter Icon Class (e.g. fab fa-facebook)" />
                        </div>

                        <div class="flex-cl-wpmethods">
                            <label class="cl-label-wpm" for="cl-label-${counter}">Enter Label</label>
                            <input type="text" id="cl-label-${counter}" name="lc_wpmethods_settings[lc_wpmethods_links][${counter}][label]" placeholder="Enter Label (Ex: Whatsapp)" />
                        </div>

                        
                        <div class="flex-cl-wpmethods">
                            <label class="cl-label-wpm" for="cl-bgcolor-${counter}">Background Color</label>
                            <input type="text" id="cl-bgcolor-${counter}" name="lc_wpmethods_settings[lc_wpmethods_links][${counter}][bg_color]" class="color-picker" placeholder="Pick Background Color" />
                        </div>

                        <div class="flex-cl-wpmethods">
                            <label class="cl-label-wpm" for="cl-sbgcolor-${counter}">Gradient Color</label>
                            <input type="text" id="cl-sbgcolor-${counter}" name="lc_wpmethods_settings[lc_wpmethods_links][${counter}][s_bg_color]" class="color-picker" placeholder="Gradient Color" />
                        </div>
                        

                        <button type="button" class="cl-wpmethods-remove-field button" style="margin-top: 10px;"><?php esc_html_e('Remove', 'lc-wpmethods'); ?></button>
                    `;

                    container.appendChild(fieldGroup);
                    initializeColorPickers();
                    counter++;
                });

                // Remove field
                container.addEventListener("click", function (e) {
                    if (e.target.classList.contains("cl-wpmethods-remove-field")) {
                        e.target.closest(".cl-wpmethods-field-group").remove();
                    }
                });

                // Live preview for icon
                document.addEventListener("input", function (e) {
                    if (e.target.classList.contains("icon-input")) {
                        const iconClass = e.target.value.trim() || 'fab fa-whatsapp';
                        const preview = e.target.closest('.cl-wpmethods-field-group').querySelector('.cl-wpmethods-preview-icon i');
                        preview.className = iconClass;
                    }
                });

                // Initialize Sortable
                new Sortable(container, {
                    animation: 150,
                    ghostClass: 'sortable-ghost'
                });

                // Initialize Color Pickers (WordPress)
                function initializeColorPickers() {
                    jQuery('.color-picker').wpColorPicker();
                }

                jQuery(document).ready(function () {
                    initializeColorPickers();
                });
            });
            </script>

        </div>
        <?php
    }
}
