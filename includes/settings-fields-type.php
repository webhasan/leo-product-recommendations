<?php
/**
 * Settins Fields
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
    <fieldset class="wpr-field-<?php echo $type; ?>" id="wpr-field-<?php echo $id; ?>">
        <div class="fields-container">
            <?php foreach ($childs as $child) :

                $field_name = $setting_id . '[' . $child['id'] . ']';
                $title = $child['title'];
                $value = $base->get_setting($child['id']);
                $id = $child['id'];
  
                if ($child['type'] == 'color_picker') :
            ?>
                    <div class="color-selection" id="<?php echo $id; ?>">
                        <?php
                        printf(
                            '<input type="text" name="%1$s" class="color-picker" value="%2$s">',
                            $field_name,
                            $value
                        ); ?>
                        <label><?php echo $title; ?></label>
                    </div>

                <?php
                elseif ($child['type'] == 'reset_color') : ?>
                    <div class="reset-colors" id="<?php echo $child['id'] ?>">
                        <?php
                        printf(
                            '<a href="%1$s">%2$s</a></div>',
                            esc_url($child['action_url']),
                            $title
                        );
                        ?>
                    </div>
                <?php

                elseif ($child['type'] == 'checkbox') : 
                    $checked = $value ? 'checked' : '';
                ?>
                    <div class="child-checkbox" id="<?php echo $id; ?>">
                        <label>
                            <?php 
                            printf(
                                '<input type="checkbox" value="1" %1$s name="%2$s"/> %3$s',
                                $checked,
                                $field_name,
                                $title
                            )
                            ?>
                        </label>
                    </div>

                <?php 
                    elseif ($child['type'] == 'number') : 
                    $sufix = !empty($child['sufix']) ? $child['sufix'] : '';
                    $min = !empty($child['min']) ? $child['min'] : '';
                    $max = !empty($child['max']) ? $child['max'] : '';
                ?>
                    <div class="child-number" id="<?php echo $id; ?>">
                        <label>
                            <?php 
                                printf(
                                    '<input type="number" value="%1$s" name="%2$s" min="%3$s" max="%4$s"/> %5$s <br> %6$s',
                                    $value,
                                    $field_name,
                                    $min,
                                    $max,
                                    $sufix,
                                    $title
                                )
                            ?>
                        </label>
                    </div>

                <?php 
                    elseif ($child['type'] == 'radio') : 
                    $options = $child['options'];
                ?>
                    <div class="child-radio" id="<?php echo $id; ?>">
                        <strong><?php echo $title; ?></strong>
                        <?php foreach ($options as $key => $option) : ?>
                            
                            <label>
                                <?php
                                $is_checked = ($key === $value) ? ' checked' : '';
                                printf(
                                    '<input type="radio"  name="%1$s" value="%2$s" %3$s>%4$s',
                                    $field_name,
                                    $key,
                                    $is_checked,
                                    $option
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
                    <div class="category-selector" id="<?php echo $id; ?>">
                        <label><?php echo $title; ?></label>
                        <select name="<?php  $field_name.'[]'; ?>" multiple>
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
                    <div class="tags-selector" id="<?php echo $id; ?>">
                        <label><?php echo $title; ?></label>
                        <select name="<?php  $field_name.'[]'; ?>" multiple>
                            <?php foreach($product_tags as $tag): ?>
                                <option value="<?php echo esc_attr( $tag->term_id ); ?>"><?php echo esc_html( $tag->name ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <?php 
                        elseif ($child['type'] == 'select') : 
                         $options = $child['options'];

                    ?>
                    <div class="child-select" id="<?php echo $id; ?>">
                        <select name="<?php echo $field_name; ?>">
                            <?php foreach( $options as $key => $option): ?>
                                <option 
                                    value="<?php echo $key; ?>"
                                    <?php echo ($key == $value) ? ' selected' : ''; ?>
                                ><?php echo $option; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <label><?php echo $title; ?></label>
                    </div>

                <?php endif; ?>


            <?php endforeach; ?>
        </div>
        <?php if (isset($description)) : ?>
            <p class="description"><?php echo $description; ?></p>
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

    <fieldset class="wpr-field-<?php echo $type; ?>" id="wpr-field-<?php echo $id; ?>">
        <div class="fields-container">
            <?php foreach ($childs as $child) :

                $field_name = $setting_id . '[' . $child['id'] . ']';
                $title = $child['title'];
                $value = $base->get_setting($child['id']);
                $id = $child['id'];
  
                if ($child['type'] == 'color_picker') :
            ?>
                    <div class="color-selection" id="<?php echo $id; ?>">
                        <?php
                        printf(
                            '<input type="text" name="%1$s" class="color-picker" value="%2$s">',
                            $field_name,
                            $value
                        ); ?>
                        <label><?php echo $title; ?></label>
                    </div>

                <?php
                elseif ($child['type'] == 'reset_color') : ?>
                    <div class="reset-colors" id="<?php echo $child['id'] ?>">
                        <?php
                        printf(
                            '<a href="%1$s">%2$s</a></div>',
                            esc_url($child['action_url']),
                            $title
                        );
                        ?>
                    </div>
                <?php

                elseif ($child['type'] == 'checkbox') : 
                    $checked = $value ? 'checked' : '';
                ?>
                    <div class="child-checkbox" id="<?php echo $id; ?>">
                        <label>
                            <?php 
                            printf(
                                '<input type="checkbox" value="1" %1$s name="%2$s"/> %3$s',
                                $checked,
                                $field_name,
                                $title
                            )
                            ?>
                        </label>
                    </div>

                <?php 
                    elseif ($child['type'] == 'number') : 
                    $sufix = !empty($child['sufix']) ? $child['sufix'] : '';
                ?>
                    <div class="child-number" id="<?php echo $id; ?>">
                        <label><?php echo $title; ?></label>
                        <?php 
                            printf(
                                '<input type="number" value="%1$s" name="%2$s"/> %3$s',
                                $value,
                                $field_name,
                                $sufix
                            )
                        ?>
                    </div>

                <?php 
                    elseif ($child['type'] == 'radio') : 
                    $options = $child['options'];
                ?>
                    <div class="child-radio" id="<?php echo $id; ?>">
                        <strong><?php echo $title; ?></strong>
                        <?php foreach ($options as $key => $option) : ?>
                            
                            <label>
                                <?php
                                $is_checked = ($key === $value) ? ' checked' : '';
                                printf(
                                    '<input type="radio"  name="%1$s" value="%2$s" %3$s>%4$s',
                                    $field_name,
                                    $key,
                                    $is_checked,
                                    $option
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
                    <div class="category-selector" id="<?php echo $id; ?>">
                        <label><?php echo $title; ?></label>
                        <select name="<?php echo $field_name.'[]'; ?>" multiple>
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
                    <div class="tags-selector" id="<?php echo $id; ?>">
                        <label><?php echo $title; ?></label>
                        <select name="<?php echo $field_name.'[]'; ?>"  multiple>
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
                    <div class="child-select" id="<?php echo $id; ?>">
                        <label><?php echo $title; ?></label>
                        <select name="<?php echo $field_name; ?>" value="<?php echo $value; ?>">
                            <?php foreach( $options as $key => $option): ?>
                                <option 
                                    value="<?php echo $key; ?>" 
                                    <?php echo ($key === $value) ? ' selected': ''; ?>
                                >
                                    <?php echo $option; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                <?php endif; ?>


            <?php endforeach; ?>
        </div>
        <?php if (isset($description)) : ?>
            <p class="description"><?php echo $description; ?></p>
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

    <fieldset class="wpr-field-<?php echo $type; ?>" id="wpr-field-<?php echo $id; ?>">
        <?php foreach ($options as $key => $option) : ?>
            <label>
                <?php
                $is_checked = ($key === $value) ? ' checked' : '';
                printf(
                    '<input type="radio"  name="%1$s" value="%2$s" %3$s>%4$s',
                    $field_name,
                    $key,
                    $is_checked,
                    $option
                );
                ?>
            </label>

        <?php endforeach; ?>
    </fieldset>

    <?php if (isset($description)) : ?>
        <p class="description"><?php echo $description; ?></p>
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

    <fieldset class="wpr-field-<?php echo $type; ?>" id="wpr-field-<?php echo $id; ?>">
        <label>
            <?php 
            $checked = !empty($value) ? ' checked' : '';
            $title = isset($label) ? $label : $title;
            printf(
                '<input type="checkbox" value="1" %1$s name="%2$s"/> %3$s',
                $checked,
                $field_name,
                $title
            )
            ?>
        </label>
        <?php if (isset($description)) : ?>
            <p class="description"><?php echo $description; ?></p>
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

    <fieldset class="wpr-field-<?php echo $type; ?>" id="wpr-field-<?php echo $id; ?>">
        <input type="text" name="<?php echo $field_name; ?>" value="<?php echo $value; ?>">
    </fieldset>

    <?php if (isset($description)) : ?>
        <p class="description"><?php echo $description; ?></p>
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

    <fieldset class="wpr-field-<?php echo $type; ?>" id="wpr-field-<?php echo $id; ?>">
        <?php if (isset($link)) : ?>
            <a href="<?php esc_url($link); ?>" target="_blank"><?php echo $label; ?></a>
        <?php else : ?>
            <label><?php echo sanitize_title($label); ?></label>
        <?php endif; ?>
    </fieldset>

    <?php if (isset($description)) : ?>
        <p class="description"><?php echo $description; ?></p>
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
    <fieldset class="wpr-field-<?php echo $type; ?>" id="wpr-field-<?php echo $id; ?>">
        <?php 
            printf(
                '<textarea class="css-editor" name="%1$s">%2$s</textarea>',
                $field_name,
                $value
            );
        ?>
    </fieldset>

    <?php if (isset($description)) : ?>
        <p class="description"><?php echo $description; ?></p>
    <?php endif;
}
