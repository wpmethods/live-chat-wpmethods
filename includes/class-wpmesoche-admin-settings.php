<?php
namespace WPMESOCHE;

if (!defined('ABSPATH')) {
    exit;
}

class Wpmesoche_Admin_Settings{
    public function __construct()
    {
        add_action('admin_menu', [$this, 'wpmesoche_add_menu_page']);
        add_action('admin_init', [$this, 'wpmesoche_register_settings']);
        add_action('admin_enqueue_scripts', [$this, 'wpmesoche_enqueue_admin_scripts']);
    }

    public function wpmesoche_add_menu_page()
    {
        add_menu_page(
            __('Chat Floating Icons', 'wpmethods-social-chat-floating-icons'),
            __('Chat Floating Icons', 'wpmethods-social-chat-floating-icons'),
            'manage_options',
            'wpmethods-social-chat-floating-icons-settings',
            [$this, 'wpmesoche_settings_page'],
            'dashicons-format-chat'
        );
    }

    public function wpmesoche_enqueue_admin_scripts($hook) {
        if ($hook !== 'toplevel_page_wpmethods-social-chat-floating-icons-settings') {
            return;
        }
    
        $min_suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
    
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_style('wpmethods-social-chat-floating-icons-admin', WPMESOCHE_WPMETHODS_URL . 'assets/css/admin' . $min_suffix . '.css', [], WPMESOCHE_PLUGIN_VERSION);
    
        // Enqueue Admin JS 
        wp_enqueue_script('wpmethods-social-chat-floating-icons-admin', WPMESOCHE_WPMETHODS_URL . 'assets/js/admin' . $min_suffix . '.js', ['jquery', 'wp-color-picker'], WPMESOCHE_PLUGIN_VERSION, true);
    
        // Now localize the script (AFTER enqueue)
        $links = get_option('lc_wpmethods_settings');
        $existing_links = !empty($links['lc_wpmethods_links']) ? count($links['lc_wpmethods_links']) : 0;
    
        wp_localize_script('wpmethods-social-chat-floating-icons-admin', 'wpmesoche_data', [
            'counter' => $existing_links,
            'limit'   => apply_filters('wpmethods_social_chat_link_limit', 2),
        ]);
    
        // FontAwesome and Sortable
        wp_enqueue_style('fontawesome', WPMESOCHE_WPMETHODS_URL . 'assets/css/all.min.css', [], '6.7.2');
        wp_enqueue_script('wpmethods-social-chat-floating-icons-sortable', WPMESOCHE_WPMETHODS_URL . 'assets/js/Sortable.min.js', ['jquery'], '1.15.6', true);
    }
    


    public function wpmesoche_register_settings()
    {
        register_setting('lc_wpmethods_settings_group', 'lc_wpmethods_settings', [
            'sanitize_callback' => [$this, 'wpmesoche_sanitize_settings']
        ]);
    }

    private function wpmesoche_sanitize_select_position($input) {
        // Sanitize the input
        $input = sanitize_text_field($input ?? '');
        
        // Define valid options for the select field
        $valid_options = ['left', 'right'];
        
        // Return the input if valid, otherwise return a default (e.g., '')
        return in_array($input, $valid_options, true) ? $input : '';
    }
    
   

    public function wpmesoche_sanitize_settings($settings){
        $limit = apply_filters('wpmethods_social_chat_link_limit', 2);
        if (isset($settings['lc_wpmethods_links']) && is_array($settings['lc_wpmethods_links'])) {
            //If limit is active (PRO inactive), cut extra items
            if (count($settings['lc_wpmethods_links']) > $limit) {
                $settings['lc_wpmethods_links'] = array_slice($settings['lc_wpmethods_links'], 0, $limit);
            }
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
        $settings['position'] = $this->wpmesoche_sanitize_select_position($settings['position'] ?? '');
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

    public function wpmesoche_settings_page()
    {
        $options = get_option('lc_wpmethods_settings', []);
        $lc_wpmethods_links = $options['lc_wpmethods_links'] ?? [];

        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Social Floating Icons', 'wpmethods-social-chat-floating-icons'); ?></h1>
            
            <?php 
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            if (isset($_GET['settings-updated']) && $_GET['settings-updated'] == true) {
                echo '<div class="notice notice-success is-dismissible"><p>Changes saved!</p></div>';
            } ?>

            <?php do_action('wpmethods_social_chat_pro_notice'); ?>

            <div class="lc-tabs">
                <button class="lc-tab-button active" data-tab="general"><?php esc_html_e('Icons & Links', 'wpmethods-social-chat-floating-icons'); ?></button>
                <button class="lc-tab-button" data-tab="style"><?php esc_html_e('Widget Styles', 'wpmethods-social-chat-floating-icons'); ?></button>
                <?php if(function_exists('is_woocommerce')) { ?>
                <button class="lc-tab-button" data-tab="woocommerce"><?php esc_html_e('WooCommerce', 'wpmethods-social-chat-floating-icons'); ?></button>
                <?php } ?>
                <button class="lc-tab-button" data-tab="help"><?php esc_html_e('Help', 'wpmethods-social-chat-floating-icons'); ?></button>
            </div>

            <form method="post" action="options.php">
                <?php settings_fields('lc_wpmethods_settings_group'); ?>
                <?php do_settings_sections('lc_wpmethods_settings_group'); ?>

                <div class="lc-tab-content active" id="tab-general">
                    <div id="cl-wpmethods-repeater-fields">
                        <?php 
                        $limit = apply_filters('wpmethods_social_chat_link_limit', 2);
                        if (!empty($lc_wpmethods_links)) : ?>
                            <?php 
                            $displayed_count = 0;
                            foreach ($lc_wpmethods_links as $index => $link) : 
                                if ($displayed_count >= $limit) {
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

                    <button type="button" id="cl-wpmethods-add-field" style="display: none">Add Link</button>
                    <?php if ( ! defined( 'WPMESOCHE_PRO_VERSION' ) ) : ?>
                        <p style="margin-top: 10px; color: #d63638; font-weight: 500;">
                            Want to add more links? <a href="https://wpmethods.com/product/social-chat-floating-icons-wordpress-plugin" target="_blank" style="text-decoration: underline;">Upgrade to Pro</a>
                        </p>
                    <?php endif; ?>
                </div>

                <div class="lc-tab-content" id="tab-style">
                    <table class="form-table">
                        <tr valign="top">
                                <th scope="row"><?php esc_html_e('Widget Icon', 'wpmethods-social-chat-floating-icons'); ?></th>
                                <td><input type="text" name="lc_wpmethods_settings[toggle_icon_class]" value="<?php echo esc_attr($options['toggle_icon_class'] ?? 'fas fa-comment-dots'); ?>" placeholder="Ex: fas fa-comment-dots" /></td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><?php esc_html_e('Button Background', 'wpmethods-social-chat-floating-icons'); ?></th>
                            <td><input type="text" name="lc_wpmethods_settings[toggle_bg_color]" value="<?php echo esc_attr($options['toggle_bg_color'] ?? '#00DA62'); ?>" class="color-field" /></td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><?php esc_html_e('Gradient Background', 'wpmethods-social-chat-floating-icons'); ?></th>
                            <td><input type="text" name="lc_wpmethods_settings[toggle_gbg_color]" value="<?php echo esc_attr($options['toggle_gbg_color'] ?? ''); ?>" class="color-field" /></td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><?php esc_html_e('Icon Color', 'wpmethods-social-chat-floating-icons'); ?></th>
                            <td><input type="text" name="lc_wpmethods_settings[icon_color]" value="<?php echo esc_attr($options['icon_color'] ?? '#FFFFFF'); ?>" class="color-field" /></td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><?php esc_html_e('Icon Size (px)', 'wpmethods-social-chat-floating-icons'); ?></th>
                            <td><input type="number" name="lc_wpmethods_settings[icon_size]" value="<?php echo esc_attr($options['icon_size'] ?? '30'); ?>" /></td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><?php esc_html_e('Button Size (px)', 'wpmethods-social-chat-floating-icons'); ?></th>
                            <td><input type="number" name="lc_wpmethods_settings[height_width]" value="<?php echo esc_attr($options['height_width'] ?? '50'); ?>" /></td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><?php esc_html_e('Hover Color', 'wpmethods-social-chat-floating-icons'); ?></th>
                            <td><input type="text" name="lc_wpmethods_settings[hover_color]" value="<?php echo esc_attr($options['hover_color'] ?? '#128C7E'); ?>" class="color-field" /></td>
                        </tr>

                        <tr valign="top">
                            <th scope="row"><?php esc_html_e('Pulse Animation Circle', 'wpmethods-social-chat-floating-icons'); ?></th>
                            <td><input type="text" name="lc_wpmethods_settings[pulse_animation_border_color]" value="<?php echo esc_attr($options['pulse_animation_border_color'] ?? '#00DA62'); ?>" class="color-field" /></td>
                        </tr>


                        <tr valign="top">
                            <th scope="row"><?php esc_html_e('Messege', 'wpmethods-social-chat-floating-icons'); ?></th>
                            <td>
                                <textarea name="lc_wpmethods_settings[custom_text]" rows="4" cols="50" placeholder="Ex: Hi, how are you?" ><?php echo esc_attr($options['custom_text'] ?? ''); ?></textarea>
                            </td>
                        </tr>


                        <tr valign="top">
                            <th scope="row"><?php esc_html_e('Button Position', 'wpmethods-social-chat-floating-icons'); ?></th>
                            <td>
                                <select name="lc_wpmethods_settings[position]">
                                    <option value="right" <?php selected($options['position'] ?? '', 'right'); ?>><?php esc_html_e('Right', 'wpmethods-social-chat-floating-icons'); ?></option>
                                    <option value="left" <?php selected($options['position'] ?? '', 'left'); ?>><?php esc_html_e('Left', 'wpmethods-social-chat-floating-icons'); ?></option>
                                </select>
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row"><?php esc_html_e('Bottom Offset', 'wpmethods-social-chat-floating-icons'); ?></th>
                            <td>
                                <input type="text" name="lc_wpmethods_settings[bottom_offset]" value="<?php echo esc_attr($options['bottom_offset'] ?? '20px'); ?>" placeholder="Ex: 20px or 5%" />
                                <p class="description"><?php esc_html_e('Distance from the bottom of the screen.', 'wpmethods-social-chat-floating-icons'); ?></p>
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row"><?php esc_html_e('Left Offset', 'wpmethods-social-chat-floating-icons'); ?></th>
                            <td>
                                <input type="text" name="lc_wpmethods_settings[left_offset]" value="<?php echo esc_attr($options['left_offset'] ?? '20px'); ?>" placeholder="Ex: 20px" />
                                <p class="description"><?php esc_html_e('Distance from the left if position is set to "Left".', 'wpmethods-social-chat-floating-icons'); ?></p>
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row"><?php esc_html_e('Right Offset', 'wpmethods-social-chat-floating-icons'); ?></th>
                            <td>
                                <input type="text" name="lc_wpmethods_settings[right_offset]" value="<?php echo esc_attr($options['right_offset'] ?? '20px'); ?>" placeholder="Ex: 20px" />
                                <p class="description"><?php esc_html_e('Distance from the right if position is set to "Right".', 'wpmethods-social-chat-floating-icons'); ?></p>
                            </td>
                        </tr>

                    </table>
                </div>

                <?php if(function_exists('is_woocommerce')) { ?>
                <div class="lc-tab-content" id="tab-woocommerce">
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('Track WooCommerce Product', 'wpmethods-social-chat-floating-icons'); ?></th>
                        <td>
                            <input type="checkbox"
                                name="lc_wpmethods_settings[track_woo_product]"
                                value="1"
                                <?php checked( isset($options['track_woo_product']) && $options['track_woo_product'] == '1' ); ?> />
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('Custom Product Message', 'wpmethods-social-chat-floating-icons'); ?></th>
                        <td>
                            <textarea name="lc_wpmethods_settings[woo_product_msg]" rows="4" cols="50" placeholder="I want to buy this product"><?php echo esc_textarea($options['woo_product_msg'] ?? 'I want to buy this product and product details below'); ?></textarea>
                            <p class="description"><?php esc_html_e('You can type product message', 'wpmethods-social-chat-floating-icons'); ?></p>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('Product title label', 'wpmethods-social-chat-floating-icons'); ?></th>
                        <td><input type="text" name="lc_wpmethods_settings[woo_product_name]" value="<?php echo esc_attr($options['woo_product_name'] ?? 'Product Name'); ?>" placeholder="Product Name" /></td>
                    </tr>


                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('Product price label', 'wpmethods-social-chat-floating-icons'); ?></th>
                        <td><input type="text" name="lc_wpmethods_settings[woo_product_price]" value="<?php echo esc_attr($options['woo_product_price'] ?? 'Price'); ?>" placeholder="Price" /></td>
                    </tr>

                </table>
                </div>
                <?php } ?>

                <div class="lc-tab-content" id="tab-help">
                    <h2><?php esc_html_e('Chat Social Links Example', 'wpmethods-social-chat-floating-icons'); ?></h2>
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

                    <h2><?php esc_html_e('Font Awesome 6 Icon Class Examples', 'wpmethods-social-chat-floating-icons'); ?></h2>
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
                        <?php esc_html_e('Use any FontAwesome 6 class name for your icon. You can browse icons at:', 'wpmethods-social-chat-floating-icons'); ?>
                        <a href="https://fontawesome.com/v6/search" target="_blank">https://fontawesome.com/icons</a>
                    </p>

                    <p>
                        You can buy the license to get full access of this plugin <a href="https://wpmethods.com/product/social-chat-floating-icons-wordpress-plugin" target="_blank">Click here</a>
                    </p>
                </div>


                <?php submit_button(); ?>
            </form>

        </div>
        <?php
    }
}
