<?php

if (!defined('ABSPATH')) {
	exit;
}

class Awaid_Projects_Settings
{

	private const OPTION_KEY = 'awaid_projects_catalog';
	private const SETTINGS_KEY = 'awaid_projects_settings_group';
	private const PAGE_SLUG = 'awaid-projects-settings';

	public static function init(): void
	{
		add_action('admin_menu', [__CLASS__, 'register_menu']);
		add_action('admin_init', [__CLASS__, 'register_settings']);
	}

	public static function register_menu(): void
	{
		add_submenu_page(
			'edit.php?post_type=awaid_project',
			__('إعدادات المشاريع', 'awaid-projects'),
			__('الإعدادات', 'awaid-projects'),
			'manage_options',
			self::PAGE_SLUG,
			[__CLASS__, 'render_page']
		);
	}

	public static function register_settings(): void
	{
		register_setting(
			self::SETTINGS_KEY,
			self::OPTION_KEY,
			[
				'type' => 'array',
				'sanitize_callback' => [__CLASS__, 'sanitize_catalog'],
				'default' => self::get_default_catalog(),
			]
		);
	}

	public static function get_settings_slug(): string
	{
		return self::PAGE_SLUG;
	}

	/**
	 * @return array<string, array<int, array<string, string>>>
	 */
	public static function get_default_catalog(): array
	{
		return [
			'features' => [],
			'warranties' => [],
			'nearby' => [],
		];
	}

	/**
	 * @return array<string, array<int, array<string, string>>>
	 */
	public static function get_catalog(): array
	{
		$raw = get_option(self::OPTION_KEY, self::get_default_catalog());
		if (!is_array($raw)) {
			$raw = [];
		}
		return self::sanitize_catalog($raw);
	}

	/**
	 * @param mixed $raw
	 * @return array<string, array<int, array<string, string>>>
	 */
	public static function sanitize_catalog($raw): array
	{
		$out = self::get_default_catalog();

		$types = ['features', 'warranties', 'nearby'];
		foreach ($types as $type) {
			$rows = isset($raw[$type]) && is_array($raw[$type]) ? $raw[$type] : [];
			foreach ($rows as $row) {
				if (!is_array($row)) {
					continue;
				}
				$title = isset($row['title']) ? sanitize_text_field((string) $row['title']) : '';
				if ($title === '') {
					continue;
				}
				$id = isset($row['id']) ? sanitize_key((string) $row['id']) : '';
				if ($id === '') {
					$id = sanitize_key($type . '_' . wp_generate_password(10, false, false));
				}
				$icon = isset($row['icon']) ? self::sanitize_icon_name((string) $row['icon']) : '';
				$item = [
					'id' => $id,
					'title' => $title,
					'icon' => $icon,
				];
				if ($type === 'warranties') {
					$item['period'] = isset($row['period']) ? sanitize_text_field((string) $row['period']) : '';
				}
				$out[$type][] = $item;
			}
		}

		return $out;
	}

	/**
	 * @param mixed $stored
	 * @return array<int, string>
	 */
	public static function extract_selected_ids(string $type, $stored): array
	{
		$catalog = self::get_catalog();
		$items = isset($catalog[$type]) && is_array($catalog[$type]) ? $catalog[$type] : [];
		$by_id = [];
		$by_title = [];
		foreach ($items as $item) {
			$id = (string) ($item['id'] ?? '');
			$title = (string) ($item['title'] ?? '');
			if ($id !== '') {
				$by_id[$id] = true;
			}
			if ($title !== '') {
				$by_title[self::lower_text($title)] = $id;
			}
		}

		$selected = [];
		$rows = is_array($stored) ? $stored : [];
		foreach ($rows as $row) {
			if (is_scalar($row)) {
				$id = sanitize_key((string) $row);
				if ($id !== '' && isset($by_id[$id])) {
					$selected[] = $id;
				}
				continue;
			}

			if (!is_array($row)) {
				continue;
			}

			$row_id = isset($row['id']) ? sanitize_key((string) $row['id']) : '';
			if ($row_id !== '' && isset($by_id[$row_id])) {
				$selected[] = $row_id;
				continue;
			}

			$title = '';
			if (isset($row['title'])) {
				$title = sanitize_text_field((string) $row['title']);
			} elseif (isset($row['name'])) {
				$title = sanitize_text_field((string) $row['name']);
			}

			if ($title !== '') {
				$lk = self::lower_text($title);
				if (isset($by_title[$lk])) {
					$selected[] = (string) $by_title[$lk];
				}
			}
		}

		return array_values(array_unique(array_filter($selected)));
	}

	/**
	 * @param mixed $stored
	 * @return array<int, array<string, string>>
	 */
	public static function resolve_selected_items(string $type, $stored): array
	{
		$catalog = self::get_catalog();
		$items = isset($catalog[$type]) && is_array($catalog[$type]) ? $catalog[$type] : [];
		$ids = self::extract_selected_ids($type, $stored);
		$map = [];
		foreach ($items as $item) {
			$id = (string) ($item['id'] ?? '');
			if ($id !== '') {
				$map[$id] = $item;
			}
		}
		$out = [];
		foreach ($ids as $id) {
			if (isset($map[$id])) {
				$out[] = $map[$id];
			}
		}
		return $out;
	}

	public static function sanitize_icon_name(string $value): string
	{
		$value = trim($value);
		if ($value === '') {
			return '';
		}
		$parts = preg_split('/\s+/', $value);
		if (is_array($parts) && $parts !== []) {
			$value = (string) $parts[0];
		}
		if (str_starts_with($value, 'dripicons-')) {
			$value = substr($value, 10);
		}
		$value = self::lower_text($value);
		$value = preg_replace('/[^a-z0-9-]/', '', $value);
		$value = trim((string) $value, '-');
		return $value;
	}

	private static function lower_text(string $value): string
	{
		if (function_exists('mb_strtolower')) {
			return (string) mb_strtolower($value);
		}
		return strtolower($value);
	}

	/**
	 * @return array<int, string>
	 */
	public static function get_lucide_icons(): array
	{
		return [
			'activity',
			'alarm-clock',
			'archive',
			'arrow-down',
			'arrow-left',
			'arrow-right',
			'arrow-up',
			'badge-check',
			'banknote',
			'bed-double',
			'bell',
			'bookmark',
			'briefcase',
			'building',
			'building-2',
			'bus',
			'calendar',
			'camera',
			'car',
			'check',
			'chevron-down',
			'chevron-left',
			'chevron-right',
			'chevron-up',
			'circle-help',
			'clipboard-list',
			'clock-3',
			'cloud',
			'compass',
			'construction',
			'contact',
			'credit-card',
			'crown',
			'diamond',
			'door-open',
			'download',
			'droplets',
			'earth',
			'eye',
			'file-text',
			'filter',
			'flag',
			'folder',
			'gauge',
			'gift',
			'graduation-cap',
			'grid-2x2',
			'hammer',
			'handshake',
			'heart',
			'home',
			'hotel',
			'house',
			'image',
			'info',
			'key',
			'landmark',
			'layers',
			'lightbulb',
			'link',
			'list',
			'loader',
			'lock',
			'mail',
			'map',
			'map-pin',
			'maximize',
			'medal',
			'megaphone',
			'menu',
			'message-circle',
			'monitor',
			'moon',
			'move',
			'navigation',
			'network',
			'package',
			'paint-roller',
			'parking-circle',
			'pen-tool',
			'phone',
			'pin',
			'plus',
			'pocket',
			'printer',
			'rocket',
			'ruler',
			'school',
			'search',
			'shield',
			'shield-check',
			'shopping-cart',
			'sparkles',
			'star',
			'store',
			'sun',
			'tag',
			'tickets',
			'train',
			'trash-2',
			'trophy',
			'truck',
			'undo-2',
			'university',
			'unlock',
			'upload',
			'user',
			'users',
			'utensils-crossed',
			'wallet',
			'warehouse',
			'wifi',
			'wrench',
			'zap',
		];
	}

	public static function render_page(): void
	{
		if (!current_user_can('manage_options')) {
			return;
		}
		$catalog = self::get_catalog();
		$lucide_icons = self::get_lucide_icons();
		?>
		<div class="wrap awaid-catalog-settings">
			<h1><?php esc_html_e('إعدادات مشاريع عوائد العقارية', 'awaid-projects'); ?></h1>
			<!-- <p class="description"><?php esc_html_e('Create reusable items once, then select them inside each project.', 'awaid-projects'); ?></p> -->
			<p>
				<a href="https://lucide.dev/icons/" target="_blank"
					rel="noopener"><?php esc_html_e('تصفح جميع أيقونات لوسيد', 'awaid-projects'); ?></a>
			</p>
			<form method="post" action="options.php">
				<?php settings_fields(self::SETTINGS_KEY); ?>

				<?php self::render_table('features', __('المميزات', 'awaid-projects'), __('الأيقونة', 'awaid-projects'), __('المدة', 'awaid-projects'), $catalog['features'], $lucide_icons); ?>
				<?php self::render_table('warranties', __('الضمانات', 'awaid-projects'), __('الأيقونة', 'awaid-projects'), __('المدة', 'awaid-projects'), $catalog['warranties'], $lucide_icons); ?>
				<?php self::render_table('nearby', __('المعالم القريبة', 'awaid-projects'), __('الأيقونة', 'awaid-projects'), __('المدة', 'awaid-projects'), $catalog['nearby'], $lucide_icons); ?>

				<?php submit_button(__('حفظ الإعدادات', 'awaid-projects')); ?>
			</form>
		</div>

		<datalist id="awaid-lucide-icons-list">
			<?php foreach ($lucide_icons as $icon): ?>
				<option value="<?php echo esc_attr($icon); ?>"></option>
			<?php endforeach; ?>
		</datalist>
		<script type="application/json" id="awaid-lucide-fallback-icons"><?php echo wp_json_encode($lucide_icons); ?></script>

		<script type="text/html" id="tmpl-awaid-settings-row-feature">
					<?php self::render_template_row('features', ['id' => '', 'title' => '', 'icon' => '']); ?>
				</script>
		<script type="text/html" id="tmpl-awaid-settings-row-warranty">
					<?php self::render_template_row('warranties', ['id' => '', 'title' => '', 'period' => '', 'icon' => '']); ?>
				</script>
		<script type="text/html" id="tmpl-awaid-settings-row-nearby">
					<?php self::render_template_row('nearby', ['id' => '', 'title' => '', 'icon' => '']); ?>
				</script>

		<div class="awaid-icon-modal" id="awaid-icon-modal" hidden>
			<div class="awaid-icon-modal__backdrop" data-awaid-icon-modal-close tabindex="-1"></div>
			<div class="awaid-icon-modal__panel" role="dialog" aria-modal="true" aria-labelledby="awaid-icon-modal-title">
				<div class="awaid-icon-modal__head">
					<h2 id="awaid-icon-modal-title"><?php esc_html_e('Choose Lucide Icon', 'awaid-projects'); ?></h2>
					<button type="button" class="button button-small"
						data-awaid-icon-modal-close><?php esc_html_e('Close', 'awaid-projects'); ?></button>
				</div>
				<input type="search" class="widefat" id="awaid-icon-modal-search"
					placeholder="<?php esc_attr_e('Search icons...', 'awaid-projects'); ?>">
				<div class="awaid-icon-modal__grid" id="awaid-icon-modal-grid"></div>
			</div>
		</div>
		<?php
	}

	/**
	 * @param array<int, array<string, string>> $rows
	 * @param array<int, string>                $icons
	 */
	private static function render_table(string $type, string $title, string $col2, string $col3, array $rows, array $icons): void
	{
		if (!$rows) {
			$rows = [['id' => '', 'title' => '', 'icon' => '', 'period' => '']];
		}
		?>
		<div class="awaid-admin-section">
			<h2><?php echo esc_html($title); ?></h2>
			<table class="widefat awaid-repeater awaid-settings-table" id="awaid_settings_<?php echo esc_attr($type); ?>">
				<thead>
					<tr>
						<th><?php esc_html_e('المسمى', 'awaid-projects'); ?></th>
						<?php if ($type === 'warranties'): ?>
							<th><?php echo esc_html($col3); ?></th>
							<th><?php esc_html_e('الأيقونة', 'awaid-projects'); ?></th>
						<?php else: ?>
							<th><?php echo esc_html($col2); ?></th>
						<?php endif; ?>
						<th><?php esc_html_e('المعاينة', 'awaid-projects'); ?></th>
						<th class="awaid-col-actions"></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($rows as $i => $row): ?>
						<?php self::render_row($type, (string) $i, $row, $icons); ?>
					<?php endforeach; ?>
				</tbody>
			</table>
			<p>
				<button type="button" class="button" data-awaid-settings-add="<?php echo esc_attr($type); ?>">
					<?php
					if ($type === 'features') {
						esc_html_e('إضافة ميزة', 'awaid-projects');
					} elseif ($type === 'warranties') {
						esc_html_e('إضافة ضمانة', 'awaid-projects');
					} else {
						esc_html_e('إضافة معلم قريب', 'awaid-projects');
					}
					?>
				</button>
			</p>
		</div>
		<?php
	}

	/**
	 * @param array<string, string> $row
	 * @param array<int, string>    $icons
	 */
	private static function render_row(string $type, string $index, array $row, array $icons): void
	{
		$row = array_merge(
			['id' => '', 'title' => '', 'icon' => '', 'period' => ''],
			$row
		);
		$id = sanitize_key((string) $row['id']);
		$icon = self::sanitize_icon_name((string) $row['icon']);
		?>
		<tr class="awaid-repeater-row">
			<td>
				<input type="hidden"
					name="awaid_projects_catalog[<?php echo esc_attr($type); ?>][<?php echo esc_attr($index); ?>][id]"
					value="<?php echo esc_attr($id); ?>">
				<input type="text" class="widefat"
					name="awaid_projects_catalog[<?php echo esc_attr($type); ?>][<?php echo esc_attr($index); ?>][title]"
					value="<?php echo esc_attr((string) $row['title']); ?>">
			</td>
			<?php if ($type === 'warranties'): ?>
				<td>
					<input type="text" class="widefat"
						name="awaid_projects_catalog[<?php echo esc_attr($type); ?>][<?php echo esc_attr($index); ?>][period]"
						value="<?php echo esc_attr((string) ($row['period'] ?? '')); ?>">
				</td>
			<?php endif; ?>
			<td>
				<div class="awaid-icon-input-wrap">
					<input type="hidden" class="awaid-icon-class-field"
						name="awaid_projects_catalog[<?php echo esc_attr($type); ?>][<?php echo esc_attr($index); ?>][icon]"
						value="<?php echo esc_attr($icon); ?>">
					<button type="button"
						class="button awaid-icon-picker-open"><?php esc_html_e('إختر', 'awaid-projects'); ?></button>
				</div>
			</td>
			<td class="awaid-icon-preview-cell">
				<span class="awaid-icon-preview-wrap">
					<i data-lucide="<?php echo esc_attr($icon); ?>" class="awaid-icon-preview awaid-lucide-icon"></i>
				</span>
			</td>
			<td><button type="button" class="button awaid-remove-row"><?php esc_html_e('حذف', 'awaid-projects'); ?></button>
			</td>
		</tr>
		<?php
	}

	/**
	 * @param array<string, string> $row
	 */
	private static function render_template_row(string $type, array $row): void
	{
		$row = array_merge(['id' => '', 'title' => '', 'icon' => '', 'period' => ''], $row);
		?>
		<tr class="awaid-repeater-row">
			<td>
				<input type="hidden" name="awaid_projects_catalog[<?php echo esc_attr($type); ?>][__i__][id]"
					value="<?php echo esc_attr((string) $row['id']); ?>">
				<input type="text" class="widefat" name="awaid_projects_catalog[<?php echo esc_attr($type); ?>][__i__][title]"
					value="<?php echo esc_attr((string) $row['title']); ?>">
			</td>
			<?php if ($type === 'warranties'): ?>
				<td><input type="text" class="widefat" name="awaid_projects_catalog[<?php echo esc_attr($type); ?>][__i__][period]"
						value="<?php echo esc_attr((string) ($row['period'] ?? '')); ?>"></td>
			<?php endif; ?>
			<td>
				<div class="awaid-icon-input-wrap">
					<input type="hidden" class="awaid-icon-class-field"
						name="awaid_projects_catalog[<?php echo esc_attr($type); ?>][__i__][icon]"
						value="<?php echo esc_attr((string) $row['icon']); ?>">
					<button type="button"
						class="button awaid-icon-picker-open"><?php esc_html_e('إختر', 'awaid-projects'); ?></button>
				</div>
			</td>
			<td class="awaid-icon-preview-cell"><span class="awaid-icon-preview-wrap"><i
						data-lucide="<?php echo esc_attr((string) $row['icon']); ?>"
						class="awaid-icon-preview awaid-lucide-icon"></i></span></td>
			<td><button type="button" class="button awaid-remove-row"><?php esc_html_e('حذف', 'awaid-projects'); ?></button>
			</td>
		</tr>
		<?php
	}
}
