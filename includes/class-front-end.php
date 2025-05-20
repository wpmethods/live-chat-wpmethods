<?php
namespace LC_WPMethods;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Front_End {

    public function __construct() {
        add_action('wp_footer', [$this, 'render_chat_buttons']);
        add_action('wp_footer', [$this, 'jquery_custom_lc_wpethods']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function enqueue_assets() {
        $this->maybe_enqueue_fontawesome();
        wp_enqueue_style('lc-wpmethods-css', LC_WPMETHODS_URL . 'assets/css/front-end.css', [], VERSION_SFIW);
        wp_enqueue_script('lc-wpmethods-js', LC_WPMETHODS_URL . 'assets/js/front-end.js', ['jquery'], VERSION_SFIW, true);
    }

    public function render_chat_buttons()
    {// Load settings
        $options = get_option('lc_wpmethods_settings');

        // Repeater field should be an array of arrays
        $lc_wpmethods_links = $options['lc_wpmethods_links'] ?? [[
            'url' => 'https://wa.me/88017900000',
            'icon' => 'fab fa-whatsapp',
            'label' => 'WhatsApp',
            'color' => '#ffffff',
            'bg_color' => '#00DA62',
        ]];

        $toggle_color = $options['toggle_bg_color'] ? $options['toggle_bg_color'] : '#00DA62';
        $icon_color = $options['icon_color'] ? $options['icon_color'] : '#FFFFFF';
        $icon_size = $options['icon_size'] ? $options['icon_size'] : '25';
        $height_width = $options['height_width'] ? $options['height_width'] : '50';
        $hover_color = $options['hover_color'] ?  $options['hover_color'] : '#128C7E';
        $custom_text = $options['custom_text'] ?  $options['custom_text'] : '';
        
        $pulse_animation_border_color = $options['pulse_animation_border_color'] ?  $options['pulse_animation_border_color'] : '#25D366';

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
                    $label = !empty($link['label']) ? $link['label'] : 'WhatsApp';
                    $color = !empty($link['color']) ? $link['color'] : '#ffffff';
                    $bg_color = !empty($link['bg_color']) ? $link['bg_color'] : '#00DA62';

                    if (empty($url) || empty($icon_class)) {
                        continue;
                    }



                    // Generate final message for each button
                    $product_message = $this->lc_wpmethods_get_product_message();
                    $final_message = '';
                    if (!empty($custom_text)) {
                        $final_message = $custom_text;
                        if (!empty($product_message)) {
                            $final_message .= "\n\n" . $product_message;
                        }
                    } elseif (!empty($product_message)) {
                        $final_message = $product_message;
                    }

                    // Ensure UTF-8 encoding and URL-encode for WhatsApp
                    $final_message = mb_convert_encoding($final_message, 'UTF-8');
                    $encoded_final_message = rawurlencode($final_message);
                    $social_link = $url . '?text=' . $encoded_final_message;
                ?>
                    <div class="sfiw-icons">
                        <p class="label-sfiw" style="background: <?php echo esc_attr($bg_color); ?>"><?php echo esc_attr($label); ?></p>
                        <a href="#" target="_blank" style=" background: linear-gradient(45deg, <?php echo esc_attr($bg_color);?> 50%, #8b8b8b 100%); ?>;" class="lc-wpmethods-chat-btn <?php echo sanitize_html_class(strtolower($label)); ?>"
                            data-url="<?php echo esc_url($social_link); ?>" 
                            data-base-message="<?php echo esc_attr($this->lc_wpmethods_get_product_message()); ?>">
                        
                        
                            <i class="<?php echo esc_attr($icon_class); ?>" style="color: <?php echo esc_attr($color); ?>;"></i>
                        </a>
                        
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="lc-wpmethods-chat-toggle lc-wpmethods-pulse" id="lcWpmethodsChatToggle">
                <i class="fas fa-comment-dots" id="lcWpmethodsChatIcon"></i>
            </div>

            <style>
                .lc-wpmethods-chat-toggle, .lc-wpmethods-chat-btn {
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

                .lc-wpmethods-chat-btn i {
                    pointer-events: none;
                    font-size: <?php echo esc_attr($icon_size); ?>px;
                }

                .sfiw-icons {
                    flex-direction: <?php echo $position === 'left' ? 'row-reverse' : 'row'; ?>;
                }

                .label-sfiw {
                    border-radius: <?php echo $position === 'left' ? '0px 20px 20px 0px' : '20px 0px 0px 20px'; ?>;
                    transform: translateY(-90%) <?php echo $position === 'left' ? 'translateX(-10px)' : 'translateX(10px)'; ?>;
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
            global $product;
    
            if ($product && is_a($product, 'WC_Product')) {
                $title = $product->get_name();
                $currency_symbol = html_entity_decode(get_woocommerce_currency_symbol(), ENT_HTML5);
                $currency_code = get_woocommerce_currency();
    
                // Get short description
                $short_description = apply_filters('woocommerce_short_description', $product->get_short_description());
                $plain_description = wp_strip_all_tags($short_description);
    
                $message = "I want to buy this product and product details below:\n";
                $message .= "Product: {$title}\n";
    
                // Handle variable product
                $selected_text = '';
                if ($product->is_type('variable')) {
                    $variations = $product->get_available_variations();
                    if (!empty($variations)) {
                        $message .= "Variations:\n";
                        foreach ($variations as $variation) {
                            $variation_product = wc_get_product($variation['variation_id']);
                            $price = $variation_product->get_price();
                            $attributes = $variation['attributes'];
                            $attribute_string = '';
    
                            foreach ($attributes as $key => $value) {
                                if (!empty($value)) {
                                    $attribute_name = wc_attribute_label(str_replace('attribute_', '', $key), $product);
                                    $attribute_string .= "{$attribute_name}: {$value}";
                                }
                            }
    
                            $line = "- {$attribute_string} - {$currency_symbol}{$price} {$currency_code}\n";
                            $message .= $line;
    
                            // Detect selected variation from $_GET
                            $is_selected = true;
                            foreach ($attributes as $key => $value) {
                                if (!isset($_GET[$key]) || strtolower($_GET[$key]) !== strtolower($value)) {
                                    $is_selected = false;
                                    break;
                                }
                            }
    
                            if ($is_selected) {
                                $selected_text = "Selected: {$attribute_string} - {$currency_symbol}{$price} {$currency_code}\n";
                            }
                        }
    
                        if (!empty($selected_text)) {
                            $message .= "\n" . $selected_text;
                        }
                    }
                } else {
                    $price = $product->get_price();
                    $message .= "Price: {$currency_symbol}{$price} {$currency_code}\n";
                }
    
                // Description
                if (!empty($plain_description)) {
                    $message .= "Description: {$plain_description}\n";
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
                        $message .= "Options:\n" . $options_output;
                    }
                }
    
                return trim($message);
            }
        }
    
        return '';
    }


    public function jquery_custom_lc_wpethods() {
        if (is_product()) {
            ?>
            <script>
    
            jQuery(document).ready(function($) {
                $('.lc-wpmethods-chat-btn').on('click', function(e) {
                    e.preventDefault();
    
                    let baseUrl = $(this).data('url');
                    let baseMessage = $(this).data('base-message');
    
                    let selectedVariation = '';
                    let price = $('.woocommerce-variation-price .price').text().trim();
    
                    // Loop through selected attributes
                    $('.variations select').each(function() {
                        let label = $(this).closest('tr').find('label').text().trim();
                        let value = $(this).val();
                        if (value) {
                            selectedVariation += `${label}: ${value} `;
                        }
                    });
    
                    if (selectedVariation) {
                        baseMessage += `\nSelected: ${selectedVariation}`;
                        if (price) {
                            baseMessage += `- ${price}`;
                        }
                    }
    
                    // Encode and append message to URL
                    const encodedMessage = encodeURIComponent(baseMessage.trim());
    
                    // Check if URL already has query (e.g., `?text=`), otherwise append it
                    let finalUrl = baseUrl.includes('?') 
                        ? `${baseUrl}&text=${encodedMessage}` 
                        : `${baseUrl}?text=${encodedMessage}`;
    
                    window.open(finalUrl, '_blank');
                });
            });
    
    
            
            </script>
            <?php
        }
    }

 
    
    
    
}




