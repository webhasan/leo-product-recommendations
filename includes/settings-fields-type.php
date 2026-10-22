<?php
namespace LoeCoder\Plugin\ProductRecommendations;
/**
 * Settings fields
 *
 * @since      1.0.0
 * @author     LeoCoder
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Wrapper field
 * 
 * @since 1.0.0
 */
function wrapper($field, $base, $setting_id) {
    extract($field);
    ?>
    <fieldset class="lpr-field-<?php echo esc_attr($type); ?>" id="lpr-field-<?php echo esc_attr($id); ?>">
        <div class="fields-container">
            <?php foreach ($children as $child) :

                $field_name = $setting_id . '[' . $child['id'] . ']';
                $title = $child['title'];
                $value = $base->get_setting($child['id']);
                $id = $child['id'];
  
                if ($child['type'] == 'color_picker') :
            ?>
                    <div class="color-selection" id="<?php echo esc_attr($id); ?>">
                        <?php
                        printf(
                            '<input type="text" name="%1$s" class="color-picker" value="%2$s">',
                            esc_attr($field_name),
                            esc_attr($value)
                        ); ?>
                        <label><?php echo esc_html($title); ?></label>
                    </div>

                <?php
                elseif ($child['type'] == 'reset_color') : ?>
                    <div class="reset-colors" id="<?php echo esc_attr($child['id']); ?>">
                        <?php
                        printf(
                            '<a href="%1$s">%2$s</a>',
                            esc_url(wp_nonce_url($child['action_url'], 'lpr_reset_colors')),
                            esc_html($title)
                        );
                        ?>
                    </div>
                <?php

                elseif ($child['type'] == 'checkbox') : 
                    $checked = $value ? 'checked' : '';
                ?>
                    <div class="child-checkbox" id="<?php echo esc_attr($id); ?>">
                        <label>
                            <?php 
                            printf(
                                '<input type="checkbox" value="1" %1$s name="%2$s"/> %3$s',
                                esc_attr($checked),
                                esc_attr($field_name),
                                esc_html($title)
                            )
                            ?>
                        </label>
                    </div>

                <?php 
                    elseif ($child['type'] == 'number') : 
                    $suffix = !empty($child['suffix']) ? $child['suffix'] : '';
                    $min = !empty($child['min']) ? $child['min'] : '';
                    $max = !empty($child['max']) ? $child['max'] : '';
                ?>
                    <div class="child-number" id="<?php echo esc_attr($id); ?>">
                        <label>
                            <?php 
                                printf(
                                    '<span class="lpr-child-title">%6$s</span><span class="lpr-child-control"><input type="number" value="%1$s" name="%2$s" min="%3$s" max="%4$s"/> %5$s</span>',
                                    esc_attr($value),
                                    esc_attr($field_name),
                                    esc_attr($min),
                                    esc_attr($max),
                                    esc_html($suffix),
                                    esc_html($title)
                                )
                            ?>
                        </label>
                    </div>

                <?php 
                    elseif ($child['type'] == 'radio') : 
                    $options = $child['options'];
                ?>
                    <div class="child-radio" id="<?php echo esc_attr($id); ?>">
                        <strong><?php echo esc_html($title); ?></strong>
                        <?php foreach ($options as $key => $option) : ?>
                            
                            <label>
                                <?php
                                $is_checked = ($key === $value) ? ' checked' : '';
                                printf(
                                    '<input type="radio"  name="%1$s" value="%2$s" %3$s>%4$s',
                                    esc_attr($field_name),
                                    esc_attr($key),
                                    esc_attr($is_checked),
                                    esc_html($option)
                                );
                                ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php 
                        elseif ($child['type'] == 'categories_select') : 
                        
                        $product_cats = get_terms(array(
                            'taxonomy'   => "product_cat",
                            'orderby'    => 'name',
                            'hide_empty' => false
                        ));
                    ?>
                    <div class="category-selector" id="<?php echo esc_attr($id); ?>">
                        <label><?php echo esc_html($title); ?></label>
                        <select name="<?php echo esc_attr($field_name.'[]'); ?>" multiple>
                            <?php foreach($product_cats as $cat): ?>
                                <option value="<?php echo esc_attr( $cat->term_id ); ?>"><?php echo esc_html( $cat->name ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <?php 
                        elseif ($child['type'] == 'tags_select') : 
                        
                        $product_tags = get_terms(array(
                            'taxonomy'   => "product_tag",
                            'orderby'    => 'name',
                            'hide_empty' => false
                        ));
                    ?>
                    <div class="tags-selector" id="<?php echo esc_attr($id); ?>">
                        <label><?php echo esc_html($title); ?></label>
                        <select name="<?php echo esc_attr($field_name.'[]'); ?>" multiple>
                            <?php foreach($product_tags as $tag): ?>
                                <option value="<?php echo esc_attr( $tag->term_id ); ?>"><?php echo esc_html( $tag->name ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <?php 
                        elseif ($child['type'] == 'select') : 
                         $options = $child['options'];

                    ?>
                    <div class="child-select" id="<?php echo esc_attr($id); ?>">
                        <select name="<?php echo esc_attr($field_name); ?>">
                            <?php foreach( $options as $key => $option): ?>
                                <option 
                                    value="<?php echo esc_attr($key); ?>"
                                    <?php echo ($key == $value) ? ' selected' : ''; ?>
                                ><?php echo esc_html($option); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <label><?php echo esc_html($title); ?></label>
                    </div>

                <?php endif; ?>


            <?php endforeach; ?>
        </div>

        <?php if (isset($help)): ?>
            <a href="<?php echo esc_url($help); ?>" class="help" data-lity><?php esc_html_e('HELP','leo-product-recommendations'); ?></a>
        <?php endif; ?>
        
        <?php if (isset($description)) : ?>
            <p class="description"><?php echo wp_kses_post($description); ?></p>
        <?php endif; ?>

        <?php if (isset($doc)): ?>
            <p><a href="<?php echo esc_url($doc); ?>" target="_blank"><?php esc_html_e('Documentation »','leo-product-recommendations'); ?></a></p>
        <?php endif; ?>
    </fieldset>
    <?php
}

/**
 * Wrapper Extend input
 * 
 * @since 1.0.0
 */
function wrapper_extend($field, $base, $setting_id) {
    extract($field);
    ?>

    <fieldset class="lpr-field-<?php echo esc_attr($type); ?>" id="lpr-field-<?php echo esc_attr($id); ?>">
        <div class="fields-container">
            <?php foreach ($children as $child) :

                $field_name = $setting_id . '[' . $child['id'] . ']';
                $title = $child['title'];
                $value = $base->get_setting($child['id']);
                $id = $child['id'];
  
                if ($child['type'] == 'color_picker') :
            ?>
                    <div class="color-selection" id="<?php echo esc_attr($id); ?>">
                        <?php
                        printf(
                            '<input type="text" name="%1$s" class="color-picker" value="%2$s">',
                            esc_attr($field_name),
                            esc_attr($value)
                        ); ?>
                        <label><?php echo esc_html($title); ?></label>
                    </div>

                <?php
                elseif ($child['type'] == 'reset_color') : ?>
                    <div class="reset-colors" id="<?php echo esc_attr($child['id']); ?>">
                        <?php
                        printf(
                            '<a href="%1$s">%2$s</a>',
                            esc_url(wp_nonce_url($child['action_url'], 'lpr_reset_colors')),
                            esc_html($title)
                        );
                        ?>
                    </div>
                <?php

                elseif ($child['type'] == 'checkbox') : 
                    $checked = $value ? 'checked' : '';
                ?>
                    <div class="child-checkbox" id="<?php echo esc_attr($id); ?>">
                        <label>
                            <?php 
                            printf(
                                '<input type="checkbox" value="1" %1$s name="%2$s"/> %3$s',
                                esc_attr($checked),
                                esc_attr($field_name),
                                esc_html($title)
                            )
                            ?>
                        </label>
                    </div>

                <?php 
                    elseif ($child['type'] == 'number') : 
                    $suffix = !empty($child['suffix']) ? $child['suffix'] : '';
                ?>
                    <div class="child-number" id="<?php echo esc_attr($id); ?>">
                        <label><?php echo esc_html($title); ?></label>
                        <?php 
                            printf(
                                '<input type="number" value="%1$s" name="%2$s"/> %3$s',
                                esc_attr($value),
                                esc_attr($field_name),
                                esc_html($suffix)
                            )
                        ?>
                    </div>

                <?php 
                    elseif ($child['type'] == 'radio') : 
                    $options = $child['options'];
                ?>
                    <div class="child-radio" id="<?php echo esc_attr($id); ?>">
                        <strong><?php echo esc_html($title); ?></strong>
                        <?php foreach ($options as $key => $option) : ?>
                            
                            <label>
                                <?php
                                $is_checked = ($key === $value) ? ' checked' : '';
                                printf(
                                    '<input type="radio"  name="%1$s" value="%2$s" %3$s>%4$s',
                                    esc_attr($field_name),
                                    esc_attr($key),
                                    esc_attr($is_checked),
                                    esc_html($option)
                                );
                                ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php 
                        elseif ($child['type'] == 'categories_select') : 
                        $product_cats = get_terms(array(
                            'taxonomy'   => "product_cat",
                            'orderby'    => 'name',
                            'hide_empty' => false
                        ));
                    ?>
                    <div class="category-selector" id="<?php echo esc_attr($id); ?>">
                        <label><?php echo esc_html($title); ?></label>
                        <select name="<?php echo esc_attr($field_name.'[]'); ?>" multiple>
                            <?php foreach($product_cats as $cat): ?>
                                <option 
                                    <?php echo (!empty($value) && in_array($cat->term_id, $value)) ? ' selected' : ''; ?>
                                    value="<?php echo esc_attr( $cat->term_id ); ?>">
                                    <?php echo esc_html( $cat->name ); 
                                ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <?php 
                        elseif ($child['type'] == 'tags_select') : 
                        
                        $product_tags = get_terms(array(
                            'taxonomy'   => "product_tag",
                            'orderby'    => 'name',
                            'hide_empty' => false
                        ));
                    ?>
                    <div class="tags-selector" id="<?php echo esc_attr($id); ?>">
                        <label><?php echo esc_html($title); ?></label>
                        <select name="<?php echo esc_attr($field_name.'[]'); ?>"  multiple>
                            <?php foreach($product_tags as $tag): ?>
                                <option 
                                    <?php echo (!empty($value) && in_array($tag->term_id, $value)) ? ' selected' : ''; ?>
                                    value="<?php echo esc_attr( $tag->term_id ); ?>">
                                    <?php echo esc_html( $tag->name ); 
                                ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <?php 
                        elseif ($child['type'] == 'select') : 
                         $options = $child['options'];

                    ?>
                    <div class="child-select" id="<?php echo esc_attr($id); ?>">
                        <label><?php echo esc_html($title); ?></label>
                        <select name="<?php echo esc_attr($field_name); ?>" value="<?php echo esc_attr($value); ?>">
                            <?php foreach( $options as $key => $option): ?>
                                <option 
                                    value="<?php echo esc_attr($key); ?>" 
                                    <?php echo ($key === $value) ? ' selected': ''; ?>
                                >
                                    <?php echo esc_html($option); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                <?php endif; ?>


            <?php endforeach; ?>
        </div>

        <?php if (isset($help)): ?>
            <a href="<?php echo esc_url($help); ?>" class="help" data-lity><?php esc_html_e('HELP','leo-product-recommendations'); ?></a>
        <?php endif; ?>

        <?php if (isset($description)) : ?>
            <p class="description"><?php echo wp_kses_post($description); ?></p>
        <?php endif; ?>

        <?php if (isset($doc)): ?>
        <p><a href="<?php echo esc_url($doc); ?>" target="_blank"><?php esc_html_e('Documentation »','leo-product-recommendations'); ?></a></p>
        <?php endif; ?>
    </fieldset>
<?php
}

/**
 * Input type radio
 * 
 * @since 1.0.0
 */
function radio($field, $base, $setting_id) {
    
    extract($field);
    $value = $base->get_setting($id);
    $field_name = $setting_id . '[' . $id . ']';

    ?>

    <?php
    // Options that need Pro: shown, but locked. A saved locked value shows as the default.
    $locked = isset($field['locked']) ? (array) $field['locked'] : array();
    if (in_array($value, $locked, true) || !array_key_exists((string) $value, $options)) {
        $value = isset($field['default']) ? $field['default'] : $value;
    }
    ?>
    <fieldset class="lpr-field-<?php echo esc_attr($type); ?>" id="lpr-field-<?php echo esc_attr($id); ?>">
        <?php foreach ($options as $key => $option) : ?>
            <?php $is_locked = in_array($key, $locked, true); ?>
            <label<?php echo $is_locked ? ' class="lpr-radio-locked"' : ''; ?>>
                <?php
                printf(
                    '<input type="radio" name="%1$s" value="%2$s"%3$s%4$s>%5$s',
                    esc_attr($field_name),
                    esc_attr($key),
                    ($key === $value && !$is_locked) ? ' checked' : '',
                    $is_locked ? ' disabled' : '',
                    esc_html($option)
                );
                if ($is_locked) {
                    echo ' <span class="badge">' . esc_html__('PRO', 'leo-product-recommendations') . '</span>';
                }
                ?>
            </label>

        <?php endforeach; ?>
    </fieldset>

    <?php if (isset($help)): ?>
        <a href="<?php echo esc_url($help); ?>" class="help" data-lity><?php esc_html_e('HELP','leo-product-recommendations'); ?></a>
    <?php endif; ?>

    <?php if (isset($description)) : ?>
        <p class="description"><?php echo wp_kses_post($description); ?></p>
    <?php endif; ?>

    <?php if (isset($doc)): ?>
        <p><a href="<?php echo esc_url($doc); ?>" target="_blank"><?php esc_html_e('Documentation »','leo-product-recommendations'); ?></a></p>
    <?php endif;
}
/**
 * Input type select
 * 
 * @since 1.0.0
 */
function select($field, $base, $setting_id) {
    extract($field);
    $value = $base->get_setting($id);
    $field_name = $setting_id . '[' . $id . ']';
    ?>
    <fieldset class="lpr-field-<?php echo esc_attr($type); ?>" id="lpr-field-<?php echo esc_attr($id); ?>">
        <select name="<?php echo esc_attr($field_name); ?>">
            <?php foreach( $options as $key => $option): ?>
                <option 
                    value="<?php echo esc_attr($key); ?>"
                    <?php echo ($key == $value) ? ' selected' : ''; ?>
                ><?php echo esc_html($option); ?></option>
            <?php endforeach; ?>
        </select>
    </fieldset>

    <?php if (isset($help)): ?>
        <a href="<?php echo esc_url($help); ?>" class="help" data-lity><?php esc_html_e('HELP','leo-product-recommendations'); ?></a>
    <?php endif; ?>

    <?php if (isset($description)) : ?>
        <p class="description"><?php echo wp_kses_post($description); ?></p>
    <?php endif; ?>

    <?php if (isset($doc)): ?>
        <p><a href="<?php echo esc_url($doc); ?>" target="_blank"><?php esc_html_e('Documentation »','leo-product-recommendations'); ?></a></p>
    <?php endif;
}

/**
 * Input type checkbox
 * 
 * @since 1.0.0
 */
function checkbox($field, $base, $setting_id) {
    extract($field);
    $value = $base->get_setting($id);
    $field_name = $setting_id . '[' . $id . ']';
    ?>
    <fieldset class="lpr-field-<?php echo esc_attr($type); ?><?php echo !empty($switch) ? ' lpr-switch' : ''; ?>" id="lpr-field-<?php echo esc_attr($id); ?>">
        <label>
            <?php 
            $checked = !empty($value) ? ' checked' : '';
            $title = isset($label) ? $label : $title;
            printf(
                '<input type="checkbox" value="1" %1$s name="%2$s"/> %3$s',
                esc_attr($checked),
                esc_attr($field_name),    
                esc_html($title)
            )
            ?>
        </label>

        <?php if (isset($help)): ?>
            <a href="<?php echo esc_url($help); ?>" class="help" data-lity><?php esc_html_e('HELP','leo-product-recommendations'); ?></a>
        <?php endif; ?>
        
        <?php if (isset($description)) : ?>
            <p class="description"><?php echo wp_kses_post($description); ?></p>
        <?php endif; ?>

        <?php if (isset($doc)): ?>
            <p><a href="<?php echo esc_url($doc); ?>" target="_blank"><?php esc_html_e('Documentation »','leo-product-recommendations'); ?></a></p>
        <?php endif; ?>
    </fieldset>
    <?php
}


/**
 * Input type text
 * 
 * @since 1.0.0
 */
function text($field, $base, $setting_id) {  
    extract($field);
    $value = $base->get_setting($id);
    $field_name = $setting_id . '[' . $id . ']';
    ?>

    <fieldset class="lpr-field-<?php echo esc_attr($type); ?>" id="lpr-field-<?php echo esc_attr($id); ?>">
        <input type="text" name="<?php echo esc_attr($field_name); ?>" value="<?php echo esc_attr($value); ?>">
    </fieldset>

    <?php if (isset($help)): ?>
        <a href="<?php echo esc_url($help); ?>" class="help" data-lity><?php esc_html_e('HELP','leo-product-recommendations'); ?></a>
    <?php endif; ?>

    <?php if (isset($description)) : ?>
        <p class="description"><?php echo wp_kses_post($description); ?></p>
    <?php endif;?>

    <?php if (isset($doc)): ?>
        <p><a href="<?php echo esc_url($doc); ?>" target="_blank"><?php esc_html_e('Documentation »','leo-product-recommendations'); ?></a></p>
    <?php endif;
}

/**
 * Input type info
 * 
 * @since 1.0.0
 */
/**
 * Input type info
 * 
 * @since 1.0.0
 */
function info($field, $baser, $setting_id) {
    extract($field);
    $field_name = $setting_id . '[' . $id . ']';
    ?>

    <fieldset class="lpr-field-<?php echo esc_attr($type); ?>" id="lpr-field-<?php echo esc_attr($id); ?>">
        <?php if (isset($link)) : ?>
            <a href="<?php echo esc_url($link); ?>" target="_blank"><?php echo esc_html($label); ?></a>
        <?php else : ?>
            <label><?php echo esc_html(sanitize_title($label)); ?></label>
        <?php endif; ?>
    </fieldset>

    <?php if (isset($help)): ?>
        <a href="<?php echo esc_url($help); ?>" class="help" data-lity><?php esc_html_e('HELP','leo-product-recommendations'); ?></a>
    <?php endif; ?>

    <?php if (isset($description)) : ?>
        <p class="description"><?php echo wp_kses_post($description); ?></p>
    <?php endif; ?>

    <?php if (isset($doc)): ?>
        <p><a href="<?php echo esc_url($doc); ?>" target="_blank"><?php esc_html_e('Documentation »','leo-product-recommendations'); ?></a></p>
    <?php endif;
}

/**
 * Input type css
 * 
 * @since 1.0.0
 */
function css($field, $base, $setting_id) {
    extract($field);
    $value = $base->get_setting($id);
    $field_name = $setting_id . '[' . $id . ']';

    ?>
    <fieldset class="lpr-field-<?php echo esc_attr($type); ?>" id="lpr-field-<?php echo esc_attr($id); ?>">
        <?php 
            printf(
                '<textarea class="css-editor" name="%1$s">%2$s</textarea>',
                esc_attr($field_name),
                esc_textarea($value)
            );
        ?>
    </fieldset>

    <?php if (isset($help)): ?>
        <a href="<?php echo esc_url($help); ?>" class="help" data-lity><?php esc_html_e('HELP','leo-product-recommendations'); ?></a>
    <?php endif; ?>

    <?php if (isset($description)) : ?>
        <p class="description"><?php echo wp_kses_post($description); ?></p>
    <?php endif; ?>

    <?php if (isset($doc)): ?>
        <p><a href="<?php echo esc_url($doc); ?>" target="_blank"><?php esc_html_e('Documentation »','leo-product-recommendations'); ?></a></p>
    <?php endif;
}

/**
 * Image
 *
 * @since 1.0.0
 */
function pro_image($field, $base, $setting_id) {
    extract($field);

    ?>
    <fieldset class="lpr-field-<?php echo esc_attr($type); ?>" id="lpr-field-<?php echo esc_attr($id); ?>">
    <div class="pro-link"><a href="<?php echo esc_url($link); ?>" target="_blank"><?php esc_html_e('Get Pro »', 'leo-product-recommendations'); ?></a></div>
    <div class="field-image"><img src="<?php echo esc_url($image_url); ?>" alt=""></div>
    </fieldset>

    <?php if (isset($help)): ?>
        <a href="<?php echo esc_url($help); ?>" class="help" data-lity><?php esc_html_e('HELP','leo-product-recommendations'); ?></a>
    <?php endif; ?>

    <?php if (isset($description)): ?>
        <p class="description"><?php echo wp_kses_post($description); ?></p>
    <?php endif; ?>

    <?php if (isset($doc)): ?>
        <p><a href="<?php echo esc_url($doc); ?>" target="_blank"><?php esc_html_e('Documentation »','leo-product-recommendations'); ?></a></p>
    <?php endif;
}

/**
 * Heading Selection Field
 *
 * @since 1.0.0
 */
function heading_selection($field, $base, $setting_id) {
    extract($field);
    $value  = $base->get_setting($id);

    if(!$value) {
        $value = $default;
    }
    $field_name = $setting_id . '[' . $id . ']';
    ?>
    <fieldset class="lpr-field-heading_type" id="lpr-field-<?php echo esc_attr($id); ?>">
        <?php foreach ($children as $heading_type):
        $checked = ($value === $heading_type['id']) ? ' checked ' : '';
        ?>
            <label>
                <input type="radio" <?php echo esc_attr($checked); ?> name="<?php echo esc_attr($field_name); ?>" value="<?php echo esc_attr($heading_type['id']); ?>">
                <?php echo esc_html($heading_type['title']); ?>
            </label>
	    <?php endforeach;?>

        <div class="heading-field">
            
            <?php foreach ($children as $heading_type):
                $sub_field_name  = $setting_id . '[' . $heading_type['id'] . ']';
                $sub_field_value = $base->get_setting($heading_type['id']);
                $display         = ($value === $heading_type['id']) ? 'block' : 'none';

                if ($heading_type['type'] === 'text'):
                    $sub_field_value = ($value === 'default_heading') ? $sub_field_value : '';
                    printf(
                        '<div style="display:%1$s" class="%2$s"><input type="text" name="%3$s" value="%4$s"/></div>',
                        esc_attr($display),
                        esc_attr($heading_type['id']),
                        esc_attr($sub_field_name),
                        esc_attr($sub_field_value)
                    );

                elseif ($heading_type['type'] === 'editor'):
                    $sub_field_value = ($value === 'default_heading_description') ? $sub_field_value : '';
                    printf(
                        '<div style="display:%1$s" class="%2$s"><textarea id="default-heading-editor" name="%3$s">%4$s</textarea></div>',
                        esc_attr($display),
                        esc_attr($heading_type['id']),
                        esc_attr($sub_field_name),
                        esc_textarea($sub_field_value)
                    );
                endif;
            endforeach;?>
        </div>
    </fieldset>
            
    <?php if (isset($help)): ?>
        <a href="<?php echo esc_url($help); ?>" class="help" data-lity><?php esc_html_e('HELP','leo-product-recommendations'); ?></a>
    <?php endif; ?>

    <?php if (isset($description)): ?>
        <p class="description"><?php echo wp_kses_post($description); ?></p>
    <?php endif; ?>

    <?php if (isset($doc)): ?>
        <p><a href="<?php echo esc_url($doc); ?>" target="_blank"><?php esc_html_e('Documentation »','leo-product-recommendations'); ?></a></p>
    <?php endif;
}
/**
 * Color swatches: a few preset colors and a custom color picker.
 *
 * Field keys: swatches (list of colors), inherit (label of an extra "use the default"
 * swatch that saves an empty value), also (ids of other color fields that follow this
 * one when a swatch is picked, for the simple color settings).
 *
 * @since 3.6.0
 */
function swatches($field, $base, $setting_id) {
    $field      = wp_parse_args($field, array('swatches' => array(), 'inherit' => '', 'also' => array()));
    $id         = $field['id'];
    $value      = (string) $base->get_setting($id);
    $field_name = $setting_id . '[' . $id . ']';
    ?>
    <fieldset class="lpr-field-swatches" id="lpr-field-<?php echo esc_attr($id); ?>" data-also="<?php echo esc_attr(implode(',', (array) $field['also'])); ?>">
        <div class="lpr-swatches">
            <?php if ($field['inherit']) : ?>
                <button type="button" class="lpr-swatch lpr-swatch--inherit<?php echo '' === $value ? ' is-active' : ''; ?>" data-color="" aria-pressed="<?php echo '' === $value ? 'true' : 'false'; ?>"><?php echo esc_html($field['inherit']); ?></button>
            <?php endif; ?>
            <?php foreach ((array) $field['swatches'] as $color) :
                $active = strtolower($color) === strtolower($value); ?>
                <button type="button" class="lpr-swatch<?php echo $active ? ' is-active' : ''; ?>" data-color="<?php echo esc_attr($color); ?>" style="--lpr-swatch: <?php echo esc_attr($color); ?>" aria-pressed="<?php echo $active ? 'true' : 'false'; ?>" aria-label="<?php echo esc_attr($color); ?>"></button>
            <?php endforeach; ?>
            <span class="lpr-swatch-custom" title="<?php esc_attr_e('Custom color', 'leo-product-recommendations'); ?>">
                <input type="text" name="<?php echo esc_attr($field_name); ?>" class="color-picker lpr-swatch-input" value="<?php echo esc_attr($value); ?>">
            </span>
        </div>
    </fieldset>

    <?php if (isset($field['description'])) : ?>
        <p class="description"><?php echo wp_kses_post($field['description']); ?></p>
    <?php endif;
}

/**
 * Number with minus and plus buttons.
 *
 * @since 3.6.0
 */
function stepper($field, $base, $setting_id) {
    $field      = wp_parse_args($field, array('min' => 1, 'max' => 10, 'suffix' => ''));
    $id         = $field['id'];
    $value      = $base->get_setting($id);
    $field_name = $setting_id . '[' . $id . ']';
    ?>
    <fieldset class="lpr-field-stepper" id="lpr-field-<?php echo esc_attr($id); ?>">
        <div class="lpr-stepper">
            <button type="button" class="lpr-step" data-step="-1" aria-label="<?php esc_attr_e('Fewer', 'leo-product-recommendations'); ?>">&minus;</button>
            <input type="number" name="<?php echo esc_attr($field_name); ?>" value="<?php echo esc_attr($value); ?>" min="<?php echo esc_attr($field['min']); ?>" max="<?php echo esc_attr($field['max']); ?>">
            <button type="button" class="lpr-step" data-step="1" aria-label="<?php esc_attr_e('More', 'leo-product-recommendations'); ?>">+</button>
            <?php if ($field['suffix']) : ?><span class="lpr-stepper__suffix"><?php echo esc_html($field['suffix']); ?></span><?php endif; ?>
        </div>
    </fieldset>

    <?php if (isset($field['description'])) : ?>
        <p class="description"><?php echo wp_kses_post($field['description']); ?></p>
    <?php endif;
}

/**
 * Pro note: a short list of what Pro adds, with a link.
 *
 * @since 3.6.0
 */
function pro_note($field, $base, $setting_id) {
    $field = wp_parse_args($field, array('items' => array(), 'link' => ''));
    ?>
    <fieldset class="lpr-field-pro_note" id="lpr-field-<?php echo esc_attr($field['id']); ?>">
        <?php if (!empty($field['description'])) : ?>
            <p class="description lpr-pro-note__intro"><?php echo esc_html($field['description']); ?></p>
        <?php endif; ?>
        <?php if (!empty($field['items'])) : ?>
        <ul class="lpr-pro-note">
            <?php foreach ((array) $field['items'] as $item) : ?>
                <li><?php echo esc_html($item); ?></li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
        <?php if ($field['link']) : ?>
            <a class="button" href="<?php echo esc_url($field['link']); ?>" target="_blank" rel="noopener"><?php esc_html_e('Get Pro', 'leo-product-recommendations'); ?></a>
        <?php endif; ?>
    </fieldset>
    <?php
}

/**
 * Searchable product picker (select2 with AJAX search). Keeps the order products were picked in.
 *
 * @since 3.6.0
 */
function products_select($field, $base, $setting_id) {
    $ids        = array_filter(array_map('absint', (array) $base->get_setting($field['id'])));
    $field_name = $setting_id . '[' . $field['id'] . ']';
    ?>
    <fieldset class="lpr-field-products_select" id="lpr-field-<?php echo esc_attr($field['id']); ?>">
        <input type="hidden" name="<?php echo esc_attr($field_name); ?>[]" value="">
        <select class="lpr-products-select" name="<?php echo esc_attr($field_name); ?>[]" multiple="multiple" data-placeholder="<?php esc_attr_e('Search products', 'leo-product-recommendations'); ?>" style="width:100%">
            <?php foreach ($ids as $id) :
                $product = wc_get_product($id);
                if (!$product) {
                    continue;
                }
                ?>
                <?php $image = wp_get_attachment_image_url($product->get_image_id(), 'thumbnail'); ?>
                <option value="<?php echo esc_attr($id); ?>" data-image="<?php echo esc_url($image ? $image : wc_placeholder_img_src('thumbnail')); ?>" selected="selected"><?php echo esc_html(wp_strip_all_tags($product->get_name())); ?></option>
            <?php endforeach; ?>
        </select>
        <?php if (!empty($field['description'])) : ?>
            <p class="description"><?php echo wp_kses_post($field['description']); ?></p>
        <?php endif; ?>
    </fieldset>
    <?php
}

/**
 * Locked Pro setting, drawn with the same controls Pro uses (disabled, never saved).
 *
 * 'controls' => list of:
 *   array('type' => 'chips', 'options' => array('Grid', 'Slider'), 'active' => 0)
 *   array('type' => 'switches', 'items' => array('Continue Shopping button', ...))
 *   array('type' => 'swatches', 'label' => 'Button Color', 'colors' => array('#46C28E', ...))
 *   array('type' => 'stepper', 'label' => 'Discount', 'value' => 10, 'suffix' => '%')
 *
 * @since 3.6.0
 */
function pro_preview($field, $base, $setting_id) {
    $field = wp_parse_args($field, array('controls' => array(), 'link' => '', 'description' => ''));
    ?>
    <fieldset class="lpr-field-pro_preview" id="lpr-field-<?php echo esc_attr($field['id']); ?>">
        <div class="lpr-pro-preview" inert aria-hidden="true">
            <?php foreach ((array) $field['controls'] as $control) :
                $type = isset($control['type']) ? $control['type'] : '';
                if ('chips' === $type) : ?>
                    <fieldset class="lpr-field-radio">
                        <?php foreach ((array) $control['options'] as $i => $option) : ?>
                            <label><input type="radio" disabled<?php echo (isset($control['active']) && (int) $control['active'] === $i) ? ' checked' : ''; ?>><?php echo esc_html($option); ?></label>
                        <?php endforeach; ?>
                    </fieldset>
                <?php elseif ('switches' === $type) : ?>
                    <div class="lpr-pro-preview__switches">
                        <?php foreach ((array) $control['items'] as $item) : ?>
                            <fieldset class="lpr-field-checkbox lpr-switch"><label><input type="checkbox" disabled checked> <?php echo esc_html($item); ?></label></fieldset>
                        <?php endforeach; ?>
                    </div>
                <?php elseif ('stepper' === $type) : ?>
                    <div class="lpr-pro-preview__row">
                        <span class="lpr-pro-preview__label"><?php echo esc_html($control['label']); ?></span>
                        <div class="lpr-stepper">
                            <button type="button" class="lpr-step" disabled>&minus;</button>
                            <input type="number" value="<?php echo esc_attr($control['value']); ?>" disabled>
                            <button type="button" class="lpr-step" disabled>+</button>
                            <?php if (!empty($control['suffix'])) : ?><span class="lpr-stepper__suffix"><?php echo esc_html($control['suffix']); ?></span><?php endif; ?>
                        </div>
                    </div>
                <?php elseif ('swatches' === $type) : ?>
                    <div class="lpr-pro-preview__row">
                        <span class="lpr-pro-preview__label"><?php echo esc_html($control['label']); ?></span>
                        <div class="lpr-swatches">
                            <?php foreach ((array) $control['colors'] as $i => $color) : ?>
                                <span class="lpr-swatch<?php echo 0 === $i ? ' is-active' : ''; ?>" style="--lpr-swatch: <?php echo esc_attr($color); ?>"></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif;
            endforeach; ?>
        </div>
        <?php if ($field['description']) : ?>
            <p class="description"><?php echo esc_html($field['description']); ?></p>
        <?php endif; ?>
        <?php if ($field['link']) : ?>
            <a class="lpr-pro-preview__link" href="<?php echo esc_url($field['link']); ?>" target="_blank" rel="noopener"><?php esc_html_e('Get it with Pro', 'leo-product-recommendations'); ?> &rarr;</a>
        <?php endif; ?>
    </fieldset>
    <?php
}

/**
 * Smart picks showcase (Free): what Smart picks would do, with the store's own orders.
 *
 * @since 3.6.0
 */
function smart_teaser($field, $base, $setting_id) {
    $data  = method_exists($base, 'smart_teaser_data') ? $base->smart_teaser_data() : array('orders' => 0, 'pairs' => array());
    $thumb = function ($id) {
        $url = get_the_post_thumbnail_url($id, 'thumbnail');
        return $url ? $url : wc_placeholder_img_src('thumbnail');
    };
    $name = function ($id) {
        return html_entity_decode(wp_strip_all_tags(get_the_title($id)), ENT_QUOTES, get_bloginfo('charset'));
    };
    ?>
    <div class="lpr-smart-teaser" id="lpr-smart-teaser">
        <div class="lpr-smart-teaser__head">
            <span class="lpr-smart-teaser__icon" aria-hidden="true">&#10022;</span>
            <strong><?php esc_html_e('Smart picks', 'leo-product-recommendations'); ?></strong>
            <span class="badge"><?php esc_html_e('PRO', 'leo-product-recommendations'); ?></span>
            <button type="button" class="lpr-smart-teaser__close" aria-label="<?php esc_attr_e('Close', 'leo-product-recommendations'); ?>">&times;</button>
        </div>
        <p class="lpr-smart-teaser__lead"><?php esc_html_e('Recommends what your customers buy together, learned from your own orders and updated every night. No picking products by hand.', 'leo-product-recommendations'); ?></p>

        <?php if (!empty($data['pairs'])) : ?>
            <p class="lpr-smart-teaser__found">
                <?php
                echo esc_html(sprintf(
                    /* translators: %s: number of orders */
                    _n('Found in your last %s order:', 'Found in your last %s orders:', (int) $data['orders'], 'leo-product-recommendations'),
                    number_format_i18n((int) $data['orders'])
                ));
                ?>
            </p>
            <ul class="lpr-smart-teaser__pairs">
                <?php foreach ($data['pairs'] as $pair) : ?>
                    <li>
                        <img src="<?php echo esc_url($thumb($pair[0])); ?>" alt="" width="36" height="36">
                        <span class="lpr-smart-teaser__name" title="<?php echo esc_attr($name($pair[0])); ?>"><?php echo esc_html($name($pair[0])); ?></span>
                        <span class="lpr-smart-teaser__plus" aria-hidden="true">+</span>
                        <img src="<?php echo esc_url($thumb($pair[1])); ?>" alt="" width="36" height="36">
                        <span class="lpr-smart-teaser__name" title="<?php echo esc_attr($name($pair[1])); ?>"><?php echo esc_html($name($pair[1])); ?></span>
                        <span class="lpr-smart-teaser__count">
                            <?php
                            echo esc_html(sprintf(
                                /* translators: %s: number of orders */
                                _n('bought together in %s order', 'bought together in %s orders', (int) $pair[2], 'leo-product-recommendations'),
                                number_format_i18n((int) $pair[2])
                            ));
                            ?>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
            <p class="lpr-smart-teaser__note"><?php esc_html_e('Smart picks would recommend these to each other for you, and keep finding new pairs as you sell.', 'leo-product-recommendations'); ?></p>
        <?php else : ?>
            <p class="lpr-smart-teaser__found"><?php esc_html_e('For example:', 'leo-product-recommendations'); ?></p>
            <ul class="lpr-smart-teaser__pairs is-example">
                <li>
                    <span class="lpr-smart-teaser__name"><?php esc_html_e('Running shoes', 'leo-product-recommendations'); ?></span>
                    <span class="lpr-smart-teaser__plus" aria-hidden="true">+</span>
                    <span class="lpr-smart-teaser__name"><?php esc_html_e('Sport socks', 'leo-product-recommendations'); ?></span>
                    <span class="lpr-smart-teaser__count"><?php esc_html_e('bought together in 38 orders', 'leo-product-recommendations'); ?></span>
                </li>
                <li>
                    <span class="lpr-smart-teaser__name"><?php esc_html_e('Coffee beans', 'leo-product-recommendations'); ?></span>
                    <span class="lpr-smart-teaser__plus" aria-hidden="true">+</span>
                    <span class="lpr-smart-teaser__name"><?php esc_html_e('Paper filters', 'leo-product-recommendations'); ?></span>
                    <span class="lpr-smart-teaser__count"><?php esc_html_e('bought together in 24 orders', 'leo-product-recommendations'); ?></span>
                </li>
            </ul>
            <p class="lpr-smart-teaser__note"><?php esc_html_e('Smart picks finds pairs like these in your own orders and shows them together.', 'leo-product-recommendations'); ?></p>
        <?php endif; ?>

        <ul class="lpr-smart-teaser__features">
            <li><?php esc_html_e('Learns what shoppers add to the cart', 'leo-product-recommendations'); ?></li>
            <li><?php esc_html_e('Personal for each shopper', 'leo-product-recommendations'); ?></li>
            <li><?php esc_html_e('Fills short lists', 'leo-product-recommendations'); ?></li>
            <li><?php esc_html_e('Updates every night', 'leo-product-recommendations'); ?></li>
        </ul>

        <?php if (!empty($field['link'])) : ?>
            <a class="button button-primary lpr-smart-teaser__cta" href="<?php echo esc_url($field['link']); ?>" target="_blank" rel="noopener"><?php esc_html_e('Get Smart picks with Pro', 'leo-product-recommendations'); ?> &rarr;</a>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Short note at the top of the What to Recommend tab: these settings are for all
 * products, and one product can be set differently on its own page.
 *
 * @since 3.6.0
 */
function recommendation_flow($field, $base, $setting_id) {
    // A product to open: the one edited most recently, with its Recommendations tab open.
    $recent = get_posts(array(
        'post_type'      => 'product',
        'post_status'    => array('publish', 'draft', 'private'),
        'orderby'        => 'modified',
        'order'          => 'DESC',
        'posts_per_page' => 1,
        'fields'         => 'ids',
    ));
    $open_url = $recent ? add_query_arg('lpr_tab', '1', get_edit_post_link($recent[0], 'raw')) : admin_url('edit.php?post_type=product');
    ?>
    <div class="lpr-scope-note" role="note">
        <span class="lpr-scope-note__icon dashicons dashicons-info-outline" aria-hidden="true"></span>
        <div class="lpr-scope-note__text">
            <strong><?php esc_html_e('These settings are for all your products.', 'leo-product-recommendations'); ?></strong>
            <span>
                <?php
                printf(
                    /* translators: %s: the word "Recommendations" (name of the product tab), in bold */
                    esc_html__('To change them for one product, edit that product and open its %s tab, in the Product data box.', 'leo-product-recommendations'),
                    '<strong>' . esc_html__('Recommendations', 'leo-product-recommendations') . '</strong>'
                );
                ?>
                <a href="<?php echo esc_url($open_url); ?>" target="_blank" rel="noopener"><?php echo esc_html($recent ? __('Open a product', 'leo-product-recommendations') : __('Go to Products', 'leo-product-recommendations')); ?>&nbsp;&rarr;</a>
            </span>
        </div>
    </div>
    <?php
}

/**
 * Discounts showcase (Free): how the Pro discounts look to shoppers, with the store's own products.
 *
 * @since 3.6.0
 */
function discount_showcase($field, $base, $setting_id) {
    $field = wp_parse_args($field, array('link' => '', 'description' => ''));

    $ids = function_exists('wc_get_products') ? wc_get_products(array(
        'status'       => 'publish',
        'visibility'   => 'catalog',
        'stock_status' => 'instock',
        'type'         => array('simple'),
        'orderby'      => 'popularity',
        'order'        => 'DESC',
        'limit'        => 12,
        'return'       => 'ids',
    )) : array();

    $products = array();
    foreach ($ids as $id) {
        $product = wc_get_product($id);
        if ($product && !$product->is_on_sale() && (float) wc_get_price_to_display($product) > 0) {
            $products[] = $product;
        }
        if (count($products) >= 6) {
            break;
        }
    }

    // Example prices when the store has too few products.
    $item = function ($i, $fallback_name, $fallback_price) use ($products) {
        if (isset($products[$i])) {
            $url = get_the_post_thumbnail_url($products[$i]->get_id(), 'thumbnail');
            return array(
                'name'  => wp_strip_all_tags($products[$i]->get_name()),
                'price' => (float) wc_get_price_to_display($products[$i]),
                'image' => $url ? $url : wc_placeholder_img_src('thumbnail'),
            );
        }
        return array('name' => $fallback_name, 'price' => $fallback_price, 'image' => wc_placeholder_img_src('thumbnail'));
    };
    $main  = $item(0, __('Running shoes', 'leo-product-recommendations'), 80);
    $extra = $item(1, __('Sport socks', 'leo-product-recommendations'), 12);
    $offer = $item(2, __('Shoe cleaner', 'leo-product-recommendations'), 9);
    $add1  = $item(3, __('Water bottle', 'leo-product-recommendations'), 15);
    $add2  = $item(4, __('Sport cap', 'leo-product-recommendations'), 18);
    $extra2 = $item(5, __('Sport bag', 'leo-product-recommendations'), 25);

    $cut = function ($price, $pct) {
        return round($price * (100 - $pct) / 100, wc_get_price_decimals());
    };
    $deal = function ($price, $pct) use ($cut) {
        return '<del>' . wc_price($price) . '</del> <ins>' . wc_price($cut($price, $pct)) . '</ins>';
    };
    ?>
    <div class="lpr-deal-show" id="lpr-field-<?php echo esc_attr($field['id']); ?>">
        <?php if ($field['description']) : ?>
            <p class="description lpr-deal-show__intro"><?php echo esc_html($field['description']); ?></p>
        <?php endif; ?>
        <div class="lpr-deal-show__grid" aria-hidden="true">
            <div class="lpr-deal-show__card">
                <span class="lpr-deal-show__where"><?php esc_html_e('Product page', 'leo-product-recommendations'); ?></span>
                <strong class="lpr-deal-show__title"><?php esc_html_e('Frequently bought together', 'leo-product-recommendations'); ?></strong>
                <span class="lpr-deal-show__save"><?php esc_html_e('Buy together and save: 10% off 1 product, 15% off each on 2.', 'leo-product-recommendations'); ?></span>
                <ul>
                    <li><img src="<?php echo esc_url($main['image']); ?>" alt=""><span><?php echo esc_html($main['name']); ?></span><span class="lpr-deal-show__price"><?php echo wp_kses_post(wc_price($main['price'])); ?></span></li>
                    <li><img src="<?php echo esc_url($extra['image']); ?>" alt=""><span><?php echo esc_html($extra['name']); ?></span><span class="lpr-deal-show__price"><?php echo wp_kses_post($deal($extra['price'], 15)); ?></span></li>
                    <li><img src="<?php echo esc_url($extra2['image']); ?>" alt=""><span><?php echo esc_html($extra2['name']); ?></span><span class="lpr-deal-show__price"><?php echo wp_kses_post($deal($extra2['price'], 15)); ?></span></li>
                </ul>
                <span class="lpr-deal-show__total"><?php esc_html_e('Total:', 'leo-product-recommendations'); ?> <strong><?php echo wp_kses_post(wc_price($main['price'] + $cut($extra['price'], 15) + $cut($extra2['price'], 15))); ?></strong></span>
            </div>
            <div class="lpr-deal-show__card">
                <span class="lpr-deal-show__where"><?php esc_html_e('Popup and cart drawer', 'leo-product-recommendations'); ?></span>
                <strong class="lpr-deal-show__title"><?php echo esc_html(sprintf(/* translators: %s: product name */ __('%s has been added to your cart.', 'leo-product-recommendations'), $main['name'])); ?></strong>
                <span class="lpr-deal-show__save"><?php echo esc_html(sprintf(/* translators: %s: percentage */ __('Add any of these now and save %s%%.', 'leo-product-recommendations'), 10)); ?></span>
                <ul>
                    <li><img src="<?php echo esc_url($add1['image']); ?>" alt=""><span><?php echo esc_html($add1['name']); ?></span><span class="lpr-deal-show__price"><?php echo wp_kses_post($deal($add1['price'], 10)); ?></span></li>
                    <li><img src="<?php echo esc_url($add2['image']); ?>" alt=""><span><?php echo esc_html($add2['name']); ?></span><span class="lpr-deal-show__price"><?php echo wp_kses_post($deal($add2['price'], 10)); ?></span></li>
                </ul>
            </div>
            <div class="lpr-deal-show__card">
                <span class="lpr-deal-show__where"><?php esc_html_e('Checkout', 'leo-product-recommendations'); ?></span>
                <strong class="lpr-deal-show__title"><?php esc_html_e('Last chance to add', 'leo-product-recommendations'); ?></strong>
                <ul>
                    <li><span class="lpr-deal-show__tick"></span><img src="<?php echo esc_url($offer['image']); ?>" alt=""><span><?php echo esc_html(sprintf(/* translators: %s: product name */ __('Yes, add %s', 'leo-product-recommendations'), $offer['name'])); ?></span><span class="lpr-deal-show__price"><?php echo wp_kses_post($deal($offer['price'], 15)); ?> <em class="lpr-deal-show__off"><?php echo esc_html(sprintf(/* translators: %s: percentage */ __('%s%% off', 'leo-product-recommendations'), 15)); ?></em></span></li>
                </ul>
                <span class="lpr-deal-show__where"><?php esc_html_e('Cart total', 'leo-product-recommendations'); ?></span>
                <ul class="lpr-deal-show__totals">
                    <li><span><?php esc_html_e('Bundle saving', 'leo-product-recommendations'); ?></span><span>-<?php echo wp_kses_post(wc_price($extra['price'] - $cut($extra['price'], 15) + $extra2['price'] - $cut($extra2['price'], 15))); ?></span></li>
                    <li><span><?php esc_html_e('Add-on saving', 'leo-product-recommendations'); ?></span><span>-<?php echo wp_kses_post(wc_price($add1['price'] - $cut($add1['price'], 10))); ?></span></li>
                    <li><span><?php esc_html_e('Checkout offer saving', 'leo-product-recommendations'); ?></span><span>-<?php echo wp_kses_post(wc_price($offer['price'] - $cut($offer['price'], 15))); ?></span></li>
                </ul>
            </div>
        </div>
        <p class="lpr-deal-show__note"><?php esc_html_e('A percentage or a fixed amount, with a bigger saving when shoppers add more. Taken off in the cart automatically: shoppers never type a code.', 'leo-product-recommendations'); ?></p>
        <?php if ($field['link']) : ?>
            <a class="button button-primary lpr-deal-show__link" href="<?php echo esc_url($field['link']); ?>" target="_blank" rel="noopener"><?php esc_html_e('Get Discounts with Pro', 'leo-product-recommendations'); ?></a>
        <?php endif; ?>
    </div>
    <?php
}
