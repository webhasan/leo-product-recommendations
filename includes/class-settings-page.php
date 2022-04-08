<?php
namespace LoeCoder\Plugin\ProductRecommendations;
/**
 * Plugin Setting page
 *
 * @since      1.0.0
 * @author     LeoCoder
 */

 if (!defined('ABSPATH')) {
    exit;
}

class Settings_Page {
    /**
     * Copy of plugins base class
     * @var LC_Leo_Product_Recommendations_Pro
     */
    private $base;

    /**
     * pages setting array
     * @var array
     */
    private $pages;

    private $hook = array();

    /**
     * Setting constructor
     * Initialize all setting actions
     *
     * @since      1.0.0
     */
    public function __construct($base) {
        if (!is_admin()) {
            return false;
        }

        $this->base  = $base;
        $this->pages = $base->settings_pages();

        add_action('admin_menu', array($this, 'add_pages'));
        add_action('admin_init', array($this, 'add_sections'));
        add_action('admin_init', array($this, 'add_fields'));
    }

    /**
     * Add Settings Pages
     *
     * @since      1.0.0
     */
    public function add_pages() {

        foreach ($this->pages as $page) {

            if (!isset($page['parent']) || $page['parent'] == '') {
                $hook = add_menu_page(
                    $page['page_title'],
                    $page['menu_title'],
                    'manage_options',
                    $page['slug'],
                    array($this, 'display_page'),
                    $page['icon'],
                    $page['position']
                );

            } else {

                $hook = add_submenu_page(
                    $page['parent'],
                    $page['page_title'],
                    $page['menu_title'],
                    'manage_options',
                    $page['slug'],
                    array($this, 'display_page')
                );
            }

            if (!isset($page['hidden']) || $page['hidden'] == false) {
                $this->hook[$hook] = $page;
            }

        }
    }

    /**
     * Display pages
     *
     * @since      1.0.0
     */
    public function display_page() {

        $screen = get_current_screen();

        foreach ($this->pages as $page) {
            if (isset($page['hidden']) && $page['hidden'] == true) {
                continue;
            }

            if ($this->hook[$screen->id] === $page) {
                // rest color settings
                if (isset($_GET['action']) && $_GET['action'] === 'rest_color' && $_GET['color_ids']) {
                    $color_ids = sanitize_key($_GET['color_ids']);
                    
                    $color_fields = $this->get_sub_field($color_ids);
                    $this->remove_settings($color_fields);
                    wp_redirect(get_admin_url(null, 'admin.php?page=lpr-settings&sec=lpr-style-settings'));
                }
                ?>

				<div class="wrap lpr-setting-page">
					<h2><?php echo $page['page_title']; ?></h2>
					<?php settings_errors();?>

					<form action="options.php" method="post">
						<?php
                            $this->section_tab($page);
                            $this->sections_content($page);
                            settings_fields($page['id']);
                        ?>
                        <p class="submit">
                            <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Save Settings', 'leo-product-recommendations')?>">
                        </p>
					</form>
				</div>
			<?php }
        }
        return false;
    }

    /**
     * Add settings sections
     *
     * @since      1.0.0
     */
    public function add_sections() {
        foreach ($this->pages as $page) {
            foreach ($this->get_sections($page) as $section) {
                add_settings_section($section['id'], $section['title'], '__return_false', $section['id']);
            }
        }
    }

    /**
     * Add settings fields
     *
     * @since      1.0.0
     */
    public function add_fields() {

        foreach ($this->pages as $page) {

            if (isset($page['hidden']) && $page['hidden'] == true) {
                continue;
            }

            register_setting($page['id'], $page['id']);

            foreach ($this->get_fields($page) as $section => $field) {
                add_settings_field($field['id'], $field['title'], array($this, 'display_field'), $field['section'], $field['section'], $field);
            }
        }
    }

    /**
     * Section Tab
     *
     * @since      1.0.0
     */
    public function section_tab($page) {?>
		<?php
        $sections = $this->get_sections($page);
        if (count($sections) < 2) {
            return false;
        }

        $active_section = isset($_GET['sec']) ? sanitize_key($_GET['sec']) : $sections[0]['id'];
        ?>
		<div class="nav-tab-wrapper">
			<?php foreach ($sections as $section): ?>
				<a href="?page=<?php echo $page['slug']; ?>&sec=<?php echo $section['id']; ?>" class="nav-tab <?php if ($active_section == $section['id']) {
                echo ' nav-tab-active'; }?>">
                <?php echo $section['tab_title'] ?></a>
			<?php endforeach;?>
		</div>
		<?php
    }

    /**
     * Section tab content
     *
     * @since      1.0.0
     */
    public function sections_content($page) {
        $sections = $this->get_sections($page);

        $active_section = isset($_GET['sec']) ? sanitize_key($_GET['sec']) : $sections[0]['id'];

        foreach ($sections as $section) {

            $is_template   = !empty($section['template']);
            $is_active_tab = $active_section === $section['id'];

            if ($is_active_tab && !$is_template) {
                do_settings_sections($section['id']);
            } elseif ($is_active_tab && $is_template) {
                include_once $section['template'];
            } elseif (!$is_template) {
                echo '<div class="hidden">';
                do_settings_sections($section['id']);
                echo '</div>';
            }
        }
    }

    /**
     * Printing according field setting
     *
     * @since      1.0.0
     */
    public function display_field($field) {
        require_once $this->base->get_path('includes/settings-fields-type.php');
        
        $namespace = 'LoeCoder\Plugin\ProductRecommendations';
        $field_type = $namespace . '\\'.$field['type'];

        if (function_exists($field_type)) {
            $field_type($field, $this->base, $this->base->get_settings_id());
        } else {
            /* translators: %s: Name of field type */
            printf(__('<strong>%s</strong> field type not found!','leo-product-recommendations'), $field_type);
        }
    }

    /**
     * Get Sub Fields Ids By Wrapper Field
     *
     * @return array Ids of sub fields
     */
    public function get_sub_field($wrapper) {
        $pages     = $this->base->settings_pages();
        $sub_fields = array();

        foreach ($pages as $page) {
            if (isset($page['sections']) && !empty($page['sections'])) {

                foreach ($page['sections'] as $section) {

                    if (isset($section['fields']) && !empty($section['fields'])) {
                        foreach ($section['fields'] as $field) {
                            if (($field['id'] === $wrapper) && !empty($field['chields'])) {
                                foreach ($field['chields'] as $sub_field) {
                                    $sub_fields[] = $sub_field['id'];
                                }
                            }
                        }
                    }
                }
            }
        }

        return $sub_fields;
    }

    /**
     * Get sections of all pages
     *
     * @return array array contains all sections
     */
    public function get_sections($page) {

        if (!empty($page) && isset($page['sections'])) {
            return $page['sections'];
        }

        return array();
    }

    /**
     * Get settings fields
     *
     * @return array of settings field
     */
    public function get_fields($page) {

        if (!empty($page) && isset($page['sections'])) {

            $fields = array();

            foreach ($page['sections'] as $section) {
                if (!isset($section['fields'])) {
                    continue;
                }

                foreach ($section['fields'] as $field) {

                    $field['section']     = $section['id'];
                    $field['register_id'] = $page['id'];
                    $field['label_for']   = $field['id'];

                    $fields[] = $field;
                }

            }

            return $fields;
        }

        return array();
    }

    /**
     * Remove specific settings from database
     *
     * @param array $fields
     */
    public function remove_settings($fields = array()) {
        if (!empty($fields)) {
            $settings        = $this->base->get_settings();
            $filter_settings = array_filter($settings, function ($key) use ($fields) {
                return !in_array($key, $fields);
            }, ARRAY_FILTER_USE_KEY);

            $this->base->set_settings($filter_settings);
        } else {
            $this->base->set_settings(null);
        }
    }
}
