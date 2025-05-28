<?php
namespace LC_WPMethods;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Front_End {

    public function __construct() {
        add_action('wp_footer', [$this, 'render_chat_buttons']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function enqueue_assets() {
        $min_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
        $this->maybe_enqueue_fontawesome();
        wp_enqueue_style('lc-wpmethods-css', LC_WPMETHODS_URL . 'assets/css/front-end'. $min_suffix . '.css', [], VERSION_SFIW);
        wp_enqueue_script('lc-wpmethods-js', LC_WPMETHODS_URL . 'assets/js/front-end'. $min_suffix . '.js', ['jquery'], VERSION_SFIW, true);
        //wp_enqueue_script('lc-wpmethods-button-js', LC_WPMETHODS_URL . 'assets/js/custom-button'. $min_suffix . '.js', ['jquery'], VERSION_SFIW, true);
    }

    public function render_chat_buttons()
    {// Load settings
        $options = get_option('lc_wpmethods_settings');

        // Repeater field should be an array of arrays
        $lc_wpmethods_links = $options['lc_wpmethods_links'] ?? [[
            'url' => 'https://wa.me/88017900000',
            'icon' => 'fab fa-whatsapp',
            'label' => '',
            'color' => '#ffffff',
            'bg_color' => '#00DA62',
            's_bg_color' => '',
        ]];

        $toggle_icon_class = $options['toggle_icon_class'] ? $options['toggle_icon_class'] : 'fas fa-comment-dots';
        $toggle_color = $options['toggle_bg_color'] ? $options['toggle_bg_color'] : '#00DA62';
        $toggle_g_color = $options['toggle_gbg_color'] ? $options['toggle_gbg_color'] : '';
        $icon_color = $options['icon_color'] ? $options['icon_color'] : '#FFFFFF';
        $icon_size = $options['icon_size'] ? $options['icon_size'] : '30';
        $height_width = $options['height_width'] ? $options['height_width'] : '50';
        $hover_color = $options['hover_color'] ?  $options['hover_color'] : '#128C7E';
       
        
        $pulse_animation_border_color = $options['pulse_animation_border_color'] ?  $options['pulse_animation_border_color'] : '#00DA62';

        // Position settings
        $position = $options['position'] ?? 'right'; // 'left' or 'right'
        $bottom_offset = $options['bottom_offset'] ?? '20px';
        $left_offset = $options['left_offset'] ?? '20px';
        $right_offset = $options['right_offset'] ?? '20px';

        // Decide position style
        $side_offset_style = $position === 'left'
            ? "left: {$left_offset}; right: auto;"
            : "right: {$right_offset}; left: auto;";
        
        
        if (empty($lc_wpmethods_links) || !is_array($lc_wpmethods_links)) {
            return; // Nothing to render
        }
        ?>

        <div class="lc-wpmethods-chat-container" id="lcWpmethodsChatContainer"  style="bottom: <?php echo esc_attr($bottom_offset); ?>; <?php echo esc_attr($side_offset_style); ?> z-index: 9999;">
            <div class="lc-wpmethods-chat-options" id="chatOptions">
                <?php foreach ($lc_wpmethods_links as $link) : 
                    $url = !empty($link['url']) ? $link['url'] : 'https://wa.me/your-whatsapp-number';
                    $icon_class = !empty($link['icon']) ? $link['icon'] : 'fab fa-whatsapp';
                    $label = !empty($link['label']) ? $link['label'] : '';
                    $color = !empty($link['color']) ? $link['color'] : '#ffffff';
                    $bg_color = !empty($link['bg_color']) ? $link['bg_color'] : '#00DA62';
                    $s_bg_color = !empty($link['s_bg_color']) ? $link['s_bg_color'] : '';

                    if (empty($url) || empty($icon_class)) {
                        continue;
                    }

                    $custom_text = !empty($options['custom_text']) ? $options['custom_text'] : '';
                    $is_product_page = function_exists('is_product') && is_product();
                    $track_product = !empty($options['track_woo_product']) ? $options['track_woo_product'] : '0';

                    $base_message = $is_product_page ? $this->lc_wpmethods_get_product_message() : '';
                    if (!empty($custom_text)) {
                        $base_message = $custom_text . "\n" . $base_message;
                    }

                    $social_link = esc_url($url);
                ?>
                    <div class="sfiw-icons">
                        <?php if(!empty($label)) {?>
                        <div class="label-sfiw" style="background: <?php echo esc_attr($bg_color); ?>"><?php echo esc_html($label); ?></div>
                        <?php }?>
                        <button type="button"
                            class="lc-wpmethods-chat-btn <?php echo sanitize_html_class(strtolower($label)); ?>"
                            data-url="<?php echo esc_url($social_link); ?>" 
                            data-base-message="<?php echo esc_attr($base_message); ?>"
                            data-custom-text="<?php echo esc_attr($custom_text); ?>"
                            data-is-product="<?php echo $is_product_page ? '1' : '0'; ?>"
                            data-is-product-track="<?php echo esc_attr($track_product); ?>"
                            style="background: linear-gradient(120deg, <?php echo esc_attr($bg_color);?> <?php echo $s_bg_color ? '50%,' : '' ; echo esc_attr($s_bg_color); ?> 100%);"
                        >
                            <i class="<?php echo esc_attr($icon_class); ?>" style="color: <?php echo esc_attr($color); ?>;"></i>
                        </button>

                    </div>
                <?php endforeach; ?>
            </div>


            <div class="lc-wpmethods-chat-toggle lc-wpmethods-pulse" id="lcWpmethodsChatToggle"  style="background: linear-gradient(120deg, <?php echo esc_attr($toggle_color);?> <?php echo $toggle_g_color ? '50%,' : '' ; echo esc_attr($toggle_g_color); ?> 100%);">
                <i class="<?php echo esc_attr($toggle_icon_class); ?>" data-toggle-icon="<?php echo esc_attr($toggle_icon_class); ?>" id="lcWpmethodsChatIcon"></i>
            </div>

            <style>
                .lc-wpmethods-chat-toggle, [type=button].lc-wpmethods-chat-btn {
                    height: <?php echo esc_attr($height_width); ?>px;
                    width: <?php echo esc_attr($height_width); ?>px;
                }
                .lc-wpmethods-chat-container {
                    align-items: <?php echo $position === 'left' ? 'flex-start' : 'flex-end'; ?>;
                }
                .lc-wpmethods-chat-toggle{
                    color: <?php echo esc_attr($icon_color); ?>;
                    background: <?php echo esc_attr($toggle_color); ?>;
                }
                .lc-wpmethods-chat-toggle:hover{
                    background: <?php echo esc_attr($hover_color); ?>;
                }

                .lc-wpmethods-chat-btn i, .lc-wpmethods-chat-toggle i{
                    pointer-events: none;
                    font-size: <?php echo esc_attr($icon_size); ?>px;
                }

                .sfiw-icons {
                    flex-direction: <?php echo $position === 'left' ? 'row-reverse' : 'row'; ?>;
                }

                .label-sfiw {
                    border-radius: <?php echo $position === 'left' ? '0px 20px 20px 0px' : '20px 0px 0px 20px'; ?>;
                    transform: translateY(-50%) <?php echo $position === 'left' ? 'translateX(-10px)' : 'translateX(10px)'; ?>;
                    <?php if ($position === 'left'): ?>
                        left: 65%;
                        margin-left: 8px;
                        padding-left: 25px;
                    <?php else: ?>
                        right: 65%;
                        margin-right: 8px;
                        padding-right: 25px;
                    <?php endif; ?>
                }

             

               @keyframes lc-wpmethods-pulse {
                    0% {
                        box-shadow: 0 0 0 0 <?php echo esc_attr($pulse_animation_border_color); ?>;
                        transform: scale(1);
                    }

                    70% {
                        transform: scale(1.2);
                        box-shadow: 0 0 0 7px rgba(242, 105, 34, 0);
                    }

                    100% {
                        transform: scale(1);
                        box-shadow: 0 0 0 0 rgba(242, 105, 34, 0);
                    }
                }
            </style>
        </div>
        <?php
    }




    private function maybe_enqueue_fontawesome() {
        global $wp_styles;
        $already_loaded = false;

        if (!empty($wp_styles->registered)) {
            foreach ($wp_styles->registered as $handle => $style) {
                if (strpos($handle, 'fontawesome') !== false || strpos($handle, 'font-awesome') !== false) {
                    $already_loaded = true;
                    break;
                }
            }
        }

        if (!$already_loaded) {
            wp_enqueue_style('fontawesome', LC_WPMETHODS_URL . 'assets/css/all.min.css', [], '6.7.2');
        }
    }



    private function lc_wpmethods_get_product_message() {
        if (function_exists('is_woocommerce') && is_singular('product')) {
            $options = get_option('lc_wpmethods_settings');
            $track_product = !empty($options['track_woo_product']) ? $options['track_woo_product'] : '0';

            if ($track_product == '0') {
                return;
            }

            $product_msg = !empty($options['woo_product_msg']) ? $options['woo_product_msg'] : 'I want to buy this product and product details below';
            $woo_product_name = !empty($options['woo_product_name']) ? $options['woo_product_name'] : 'Product Name';
            $woo_product_price = !empty($options['woo_product_price']) ? $options['woo_product_price'] : 'Price';


            global $product;

            if ($product && is_a($product, 'WC_Product')) {
                $title = $product->get_name();
                $currency_symbol = html_entity_decode(get_woocommerce_currency_symbol(), ENT_HTML5);
                $currency_code = get_woocommerce_currency();

                $message = "{$product_msg}:\n";
                $message .= "{$woo_product_name}: {$title}\n";

                // Handle variable product
                $selected_text = '';
                $variations_text = '';
                if ($product->is_type('variable')) {
                    $variations = $product->get_available_variations();
                    if (!empty($variations)) {
                        $is_selected = false;
                    

                        foreach ($variations as $variation) {
                            $variation_product = wc_get_product($variation['variation_id']);
                            $price = number_format((float)$variation_product->get_price(), 2, '.', '');
                            $attributes = $variation['attributes'];
                            $attribute_string = '';

                            foreach ($attributes as $key => $value) {
                                if (!empty($value)) {
                                    $attribute_name = wc_attribute_label(str_replace('attribute_', '', $key), $product);
                                    $attribute_string .= "{$attribute_name}: {$value} ";
                                }
                            }

                            // Add to variations list with consistent formatting
                            $variations_text .= "- {$attribute_string}- {$currency_symbol}{$price} {$currency_code}\n";

                            // Detect selected variation from $_GET
                            $is_selected_var = true;
                            foreach ($attributes as $key => $value) {
                                if (!isset($_GET[$key]) || strtolower($_GET[$key]) !== strtolower($value)) {
                                    $is_selected_var = false;
                                    break;
                                }
                            }

                            if ($is_selected_var) {
                                $is_selected = true;
                                $regular_price = number_format((float)$variation_product->get_regular_price(), 2, '.', '');
                                $sale_price = $variation_product->get_sale_price();
                                if ($sale_price && $sale_price !== $regular_price) {
                                    $sale_price = number_format((float)$sale_price, 2, '.', '');
                                    $selected_text = "Selected Variation: {$attribute_string}- {$currency_symbol}{$regular_price}\n";
                                    $selected_text .= "Original price was: {$currency_symbol}{$regular_price}\n";
                                    $selected_text .= "*The discounted price is: {$currency_symbol}{$sale_price}.*\n";
                                    $selected_text .= $plain_description;
                                } else {
                                    $selected_text = "*Selected Variation: {$attribute_string}- {$currency_symbol}{$regular_price} {$currency_code}.*\n";
                                    $selected_text .= $plain_description;
                                }
                            }
                        }

                        // Include variations list only if no variation is selected
                        if (!$is_selected) {
                            $message .= "\nVariations:\n" . $variations_text;
                        } else {
                            $message .= "\n" . $selected_text;
                        }
                    }
                } else {
                    // Handle simple product price
                    $regular_price = number_format((float)$product->get_regular_price(), 2, '.', '');
                    $sale_price = $product->get_sale_price();
                    if ($sale_price && $sale_price !== $regular_price) {
                        $sale_price = number_format((float)$sale_price, 2, '.', '');
                        $message .= "Original price was: {$currency_symbol}{$regular_price}\n";
                        $message .= "*The discounted price is: {$currency_symbol}{$sale_price}.*";
                    } else {
                        $message .= "*{$woo_product_price}: {$currency_symbol}{$regular_price} {$currency_code}*";
                    }
                }

                // Non-variation attributes
                $attributes = $product->get_attributes();
                $options_output = '';
                if (!empty($attributes)) {
                    foreach ($attributes as $attribute) {
                        if ($attribute->get_visible() && !$attribute->get_variation()) {
                            $name = wc_attribute_label($attribute->get_name());
                            $options = $attribute->get_options();
                            $option_values = array();
                            foreach ($options as $option) {
                                $option_values[] = wc_attribute_label($option);
                            }
                            if (!empty($option_values)) {
                                $options_output .= "- {$name}: " . implode(', ', $option_values) . "\n";
                            }
                        }
                    }

                    if (!empty($options_output)) {
                        $message .= "\nOptions:\n" . $options_output;
                    }
                }

                return trim($message);
            }
        }

        return '';
    }

    
}




