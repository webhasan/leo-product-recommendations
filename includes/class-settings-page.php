<?php
namespace LoeCoder\Plugin\ProductRecommendations;
/**
 * Plugin settings pages framework (shared by Free and Pro since 3.4.0)
 *
 * Pages, sections and fields come from Product_Recommendations::settings_pages().
 * Pro adds its own pages and fields with the `lpr_settings_pages` filter and can
 * render extra field types with the `lpr_settings_field_{type}` action.
 *
 * @since      1.0.0
 * @author     LeoCoder
 */

if (!defined('ABSPATH')) {
	exit;
}

class Settings_Page {
	/**
	 * Free plugin base class
	 * @var Product_Recommendations
	 */
	private $base;

	/**
	 * Admin page hooks mapped to their page definitions
	 * @var array
	 */
	private $hook = array();

	/**
	 * Allowed tags for plain text settings (same list as get_setting()).
	 * @var array
	 */
	private $text_tags = array(
		'span'   => array('class' => array()),
		'b'      => array(),
		'strong' => array(),
		'i'      => array(),
		'br'     => array(),
	);

	/**
	 * Setting constructor
	 * Initialize all setting actions
	 *
	 * @since      1.0.0
	 */
	public function __construct($base) {
		if (!is_admin()) {
			return;
		}

		$this->base = $base;

		add_action('admin_menu', array($this, 'add_pages'));
		add_action('admin_init', array($this, 'add_sections'));
		add_action('admin_init', array($this, 'add_fields'));
	}

	/**
	 * Settings pages (cached by the base class).
	 *
	 * @since 3.4.0
	 * @return array
	 */
	private function get_pages() {
		return $this->base->settings_pages();
	}

	/**
	 * Add Settings Pages
	 *
	 * @since      1.0.0
	 */
	public function add_pages() {
		foreach ($this->get_pages() as $page) {
			if (empty($page['parent'])) {
				$hook = add_menu_page(
					$page['page_title'],
					$page['menu_title'],
					'manage_options',
					$page['slug'],
					array($this, 'display_page'),
					isset($page['icon']) ? $page['icon'] : '',
					isset($page['position']) ? $page['position'] : null
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

			if (empty($page['hidden'])) {
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

		if (!$screen || empty($this->hook[$screen->id])) {
			return;
		}

		$page = $this->hook[$screen->id];

		$this->maybe_reset_colors();
		?>
		<div class="wrap lpr-setting-page">
			<h2><?php echo esc_html($page['page_title']); ?></h2>
			<?php settings_errors(); ?>

			<form action="options.php" method="post">
				<?php
				$this->section_tab($page);
				$this->sections_content($page);
				settings_fields($page['id']);
				?>
				<p class="submit">
					<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_attr_e('Save Settings', 'leo-product-recommendations'); ?>">
				</p>
			</form>
		</div>
		<?php
	}

	/**
	 * Handle the "Reset colors" link (?action=rest_color&color_ids=<wrapper id>).
	 *
	 * Requires a valid nonce and an existing color group. Only that group's
	 * sub-settings are removed; nothing happens for an unknown group.
	 *
	 * @since 3.4.0
	 * @return void
	 */
	private function maybe_reset_colors() {
		$action = isset($_GET['action']) ? sanitize_key(wp_unslash($_GET['action'])) : '';

		if ('rest_color' !== $action || empty($_GET['color_ids'])) {
			return;
		}

		$redirect = get_admin_url(null, 'admin.php?page=lpr-settings&sec=lpr-style-settings');
		$nonce = isset($_GET['_wpnonce']) ? sanitize_text_field(wp_unslash($_GET['_wpnonce'])) : '';

		if (!current_user_can('manage_options') || !wp_verify_nonce($nonce, 'lpr_reset_colors')) {
			wp_safe_redirect($redirect);
			exit;
		}

		$color_fields = $this->get_sub_field(sanitize_key(wp_unslash($_GET['color_ids'])));

		if (!empty($color_fields)) {
			$this->remove_settings($color_fields);
		}

		wp_safe_redirect($redirect);
		exit;
	}

	/**
	 * Add settings sections
	 *
	 * @since      1.0.0
	 */
	public function add_sections() {
		foreach ($this->get_pages() as $page) {
			foreach ($this->get_sections($page) as $section) {
				add_settings_section($section['id'], isset($section['title']) ? $section['title'] : '', '__return_false', $section['id']);
			}
		}
	}

	/**
	 * Add settings fields
	 *
	 * @since      1.0.0
	 */
	public function add_fields() {
		foreach ($this->get_pages() as $page) {
			if (!empty($page['hidden'])) {
				continue;
			}

			register_setting($page['id'], $page['id'], array(
				'type' => 'array',
				'sanitize_callback' => array($this, 'sanitize_settings'),
			));

			foreach ($this->get_fields($page) as $field) {
				add_settings_field($field['id'], $field['title'], array($this, 'display_field'), $field['section'], $field['section'], $field);
			}
		}
	}

	/**
	 * Section Tab
	 *
	 * @since      1.0.0
	 */
	public function section_tab($page) {
		$sections = $this->get_sections($page);

		if (count($sections) < 2) {
			return;
		}

		$active_section = isset($_GET['sec']) ? sanitize_key(wp_unslash($_GET['sec'])) : $sections[0]['id'];
		?>
		<div class="nav-tab-wrapper">
			<?php foreach ($sections as $section): ?>
				<a href="?page=<?php echo esc_attr($page['slug']); ?>&sec=<?php echo esc_attr($section['id']); ?>" class="nav-tab<?php echo ($active_section === $section['id']) ? ' nav-tab-active' : ''; ?>"><?php echo esc_html($section['tab_title']); ?></a>
			<?php endforeach; ?>
		</div>
		<?php
	}

	/**
	 * Section tab content
	 *
	 * All sections render in the same form; inactive ones are hidden so that saving
	 * any tab posts every field.
	 *
	 * @since      1.0.0
	 */
	public function sections_content($page) {
		$sections = $this->get_sections($page);

		if (empty($sections)) {
			return;
		}

		$active_section = isset($_GET['sec']) ? sanitize_key(wp_unslash($_GET['sec'])) : $sections[0]['id'];

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
	 * Print a field with its field type renderer.
	 *
	 * Built-in types are functions in settings-fields-type.php. Other plugins (Pro)
	 * can add types with the `lpr_settings_field_{type}` action.
	 *
	 * @since      1.0.0
	 */
	public function display_field($field) {
		require_once $this->base->get_path('includes/settings-fields-type.php');

		$field      = Product_Recommendations::normalize_settings_field($field);
		$type       = isset($field['type']) ? $field['type'] : '';
		$field_type = __NAMESPACE__ . '\\' . $type;

		if ($type && function_exists($field_type)) {
			$field_type($field, $this->base, $this->base->get_settings_id());
		} elseif ($type && has_action('lpr_settings_field_' . $type)) {
			do_action('lpr_settings_field_' . $type, $field, $this->base, $this->base->get_settings_id());
		} else {
			/* translators: %s: Name of field type */
			printf(esc_html__('%s field type not found!', 'leo-product-recommendations'), '<strong>' . esc_html($type) . '</strong>');
		}
	}

	/**
	 * Get Sub Fields Ids By Wrapper Field
	 *
	 * @return array Ids of sub fields
	 */
	public function get_sub_field($wrapper) {
		$sub_fields = array();

		foreach ($this->base->get_field_map() as $id => $field) {
			if (isset($field['_parent']) && $field['_parent'] === $wrapper) {
				$sub_fields[] = $id;
			}
		}

		return $sub_fields;
	}

	/**
	 * Get sections of a page
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
	 * Get top level fields of a page
	 *
	 * @return array of settings field
	 */
	public function get_fields($page) {
		$fields = array();

		foreach ($this->get_sections($page) as $section) {
			if (empty($section['fields'])) {
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

	/**
	 * Fields (including sub-fields) that a settings page (option) manages.
	 *
	 * @since 3.4.0
	 * @param string $option_id Option name / page id.
	 * @return array Field definitions keyed by id.
	 */
	private function get_page_field_map($option_id) {
		$fields = array();

		foreach ($this->base->get_field_map() as $id => $field) {
			if (isset($field['_page']) && $field['_page'] === $option_id) {
				$fields[$id] = $field;
			}
		}

		return $fields;
	}

	/**
	 * Sanitize settings before saving.
	 *
	 * Built to accept every value that Free and Pro have stored so far:
	 * - Stored keys this page does not manage are kept (for example Pro settings while
	 *   Pro is inactive). Before 3.4.0 they were lost on save.
	 * - Colors accept hex, rgb() and rgba() as saved by the color picker.
	 * - Unknown posted keys are kept, sanitized as text.
	 *
	 * WordPress also runs this on every update_option() of the option, so it must be
	 * safe to apply to an already sanitized array.
	 *
	 * @since      1.0.0
	 * @param array $input The unsanitized input array
	 * @return array The sanitized settings array
	 */
	public function sanitize_settings($input) {
		$input = is_array($input) ? $input : array();

		$option_id = $this->current_option_id();
		$fields    = $this->get_page_field_map($option_id);
		$existing  = get_option($option_id);
		$sanitized = array();

		// Keep stored values that this page does not manage.
		if (is_array($existing)) {
			foreach ($existing as $key => $value) {
				if (!isset($fields[$key])) {
					$sanitized[$key] = $value;
				}
			}
		}

		foreach ($input as $key => $value) {
			$key   = sanitize_key($key);
			$field = isset($fields[$key]) ? $fields[$key] : array();
			$type  = isset($field['type']) ? $field['type'] : '';
			$clean = $this->sanitize_value($value, $type);

			if (null === $clean) {
				unset($sanitized[$key]);
				continue;
			}

			$sanitized[$key] = $clean;
		}

		return $sanitized;
	}

	/**
	 * Option name being sanitized (the filter name is sanitize_option_{$option}).
	 *
	 * @since 3.4.0
	 * @return string
	 */
	private function current_option_id() {
		$filter = current_filter();

		if (0 === strpos($filter, 'sanitize_option_')) {
			return substr($filter, strlen('sanitize_option_'));
		}

		return $this->base->get_settings_id();
	}

	/**
	 * Sanitize one value by field type.
	 *
	 * @since 3.4.0
	 * @param mixed  $value Raw value.
	 * @param string $type  Field type, empty when unknown.
	 * @return mixed|null Clean value, or null to leave the key unset (use default).
	 */
	private function sanitize_value($value, $type) {
		switch ($type) {
			case 'color_picker':
				return is_scalar($value) ? self::sanitize_color((string) $value) : '';

			case 'number':
				if (is_array($value) || '' === trim((string) $value)) {
					return null; // empty: fall back to the default
				}
				return absint($value);

			case 'checkbox':
				return !empty($value) ? 1 : '';

			case 'categories_select':
			case 'tags_select':
				return is_array($value) ? array_values(array_map('absint', array_filter($value))) : array();

			case 'css':
				return is_scalar($value) ? wp_strip_all_tags((string) $value) : '';

			case 'editor':
			case 'textarea':
				return is_scalar($value) ? wp_kses_post((string) $value) : '';

			case 'text':
				return is_scalar($value) ? wp_kses((string) $value, $this->text_tags) : '';

			default:
				// radio, select, heading_selection and unknown keys
				if (is_array($value)) {
					return map_deep($value, 'sanitize_text_field');
				}
				return is_scalar($value) ? sanitize_text_field((string) $value) : '';
		}
	}

	/**
	 * Accept CSS colors as saved by the settings color picker.
	 *
	 * Allowed: '' (no color), #rgb, #rgba, #rrggbb, #rrggbbaa, rgb(), rgba(),
	 * 'transparent' and plain color names. Anything else becomes ''.
	 *
	 * @since 3.4.0
	 * @param string $color Raw color.
	 * @return string
	 */
	public static function sanitize_color($color) {
		$color = trim($color);

		if ('' === $color) {
			return '';
		}

		if (preg_match('/^#([0-9a-f]{3}|[0-9a-f]{4}|[0-9a-f]{6}|[0-9a-f]{8})$/i', $color)) {
			return $color;
		}

		$channel = '\s*(\d{1,3}(\.\d+)?%?)\s*';
		$alpha   = '\s*(0|1|0?\.\d+|1\.0+|\d{1,3}%)\s*';

		if (preg_match('/^rgba?\(' . $channel . ',' . $channel . ',' . $channel . '(,' . $alpha . ')?\)$/i', $color)) {
			return $color;
		}

		if (preg_match('/^[a-z]{3,20}$/i', $color)) {
			return strtolower($color);
		}

		return '';
	}

	/**
	 * Remove specific settings from database
	 *
	 * @param array $fields Setting ids to remove. Empty does nothing.
	 */
	public function remove_settings($fields = array()) {
		if (empty($fields)) {
			return;
		}

		$settings = $this->base->get_settings();
		$settings = is_array($settings) ? $settings : array();

		$filter_settings = array_filter($settings, function ($key) use ($fields) {
			return !in_array($key, $fields, true);
		}, ARRAY_FILTER_USE_KEY);

		$this->base->set_settings($filter_settings);
	}
}
