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
            <?php foreach ($chields as $child) :

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
                            '<a href="%1$s">%2$s</a></div>',
                            esc_url($child['action_url']),
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
                                    '<input type="number" value="%1$s" name="%2$s" min="%3$s" max="%4$s"/> %5$s <br> %6$s',
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
            <?php foreach ($chields as $child) :

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
                            '<a href="%1$s">%2$s</a></div>',
                            esc_url($child['action_url']),
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

    <fieldset class="lpr-field-<?php echo esc_attr($type); ?>" id="lpr-field-<?php echo esc_attr($id); ?>">
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
    <fieldset class="lpr-field-<?php echo esc_attr($type); ?>" id="lpr-field-<?php echo esc_attr($id); ?>">
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
    <div class="pro-link"><a href="<?php echo esc_url($link); ?>" target="_blank"><?php esc_html_e('Get Pro Version »', 'leo-product-recommendations'); ?></a></div>
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
        <?php foreach ($chields as $heading_type):
        $checked = ($value === $heading_type['id']) ? ' checked ' : '';
        ?>
            <label>
                <input type="radio" <?php echo esc_attr($checked); ?> name="<?php echo esc_attr($field_name); ?>" value="<?php echo esc_attr($heading_type['id']); ?>">
                <?php echo esc_html($heading_type['title']); ?>
            </label>
	    <?php endforeach;?>

        <div class="heading-field">
            
            <?php foreach ($chields as $heading_type):
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