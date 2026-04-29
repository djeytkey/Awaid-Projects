<?php
/**
 * Admin meta box markup.
 *
 * @var WP_Post $post
 * @var array<string, mixed> $d
 * @var int $brochure_id
 * @var string $brochure_url
 */

if (!defined('ABSPATH')) {
	exit;
}

$catalog_features = isset($catalog['features']) && is_array($catalog['features']) ? $catalog['features'] : [];
$catalog_warranties = isset($catalog['warranties']) && is_array($catalog['warranties']) ? $catalog['warranties'] : [];
$catalog_nearby = isset($catalog['nearby']) && is_array($catalog['nearby']) ? $catalog['nearby'] : [];
$selected_feature_ids = is_array($selected_feature_ids ?? null) ? $selected_feature_ids : [];
$selected_warranty_ids = is_array($selected_warranty_ids ?? null) ? $selected_warranty_ids : [];
$selected_nearby_ids = is_array($selected_nearby_ids ?? null) ? $selected_nearby_ids : [];
$units      = is_array($d['units'] ?? null) ? $d['units'] : [];
$gallery_ids = is_array($d['gallery_ids'] ?? null) ? array_map('absint', $d['gallery_ids']) : [];
$gallery_ids = array_values(array_filter(array_unique($gallery_ids)));
$gallery_csv = implode(',', $gallery_ids);

$awaid_unit_default = [
	'code'         => '',
	'type'         => '',
	'price'        => '',
	'area'         => '',
	'bedrooms'     => '',
	'bathrooms'    => '',
	'status'       => 'available',
	'gallery_ids'  => [],
	'description'  => '',
	'floor'        => '',
	'kitchens'     => '',
	'whatsapp'     => '',
	'phone'        => '',
	'highlights'   => [['icon' => '', 'title' => '', 'text' => '']],
];
if (!$units) {
	$units = [$awaid_unit_default];
}
?>
<div class="awaid-admin-meta">
	<div class="awaid-admin-section">
		<!-- <h3><?php esc_html_e('ملخص', 'awaid-projects'); ?></h3> -->
		<!-- <p class="description"><?php esc_html_e('Project title is the post title. Long description uses the main editor. The gallery below powers the project photo slider on the front; if it is empty, the featured image is used as a single slide.', 'awaid-projects'); ?></p> -->
		<table class="form-table awaid-form-table">
			<tr>
				<th><label for="awaid_location"><?php esc_html_e('موقع المشروع', 'awaid-projects'); ?></label></th>
				<td><input type="text" class="widefat" id="awaid_location" name="awaid_project[location]" value="<?php echo esc_attr((string) $d['location']); ?>" placeholder="<?php esc_attr_e('مثال الرياض - القيروان', 'awaid-projects'); ?>"></td>
			</tr>
			<tr>
				<th><label for="awaid_developer"><?php esc_html_e('المطور', 'awaid-projects'); ?></label></th>
				<td><input type="text" class="widefat" id="awaid_developer" name="awaid_project[developer]" value="<?php echo esc_attr((string) $d['developer']); ?>"></td>
			</tr>
		</table>
	</div>

	<div class="awaid-admin-section">
		<h3><?php esc_html_e('معرض الصور / شريط التمرير', 'awaid-projects'); ?></h3>
		<!-- <p class="description"><?php esc_html_e('Add multiple images for the single-project carousel. Images appear in the order you add them; remove and re-add to change order.', 'awaid-projects'); ?></p> -->
		<input type="hidden" id="awaid_gallery_csv" name="awaid_project[gallery_csv]" value="<?php echo esc_attr($gallery_csv); ?>">
		<p>
			<button type="button" class="button" id="awaid_gallery_add"><?php esc_html_e('أضف صورًا', 'awaid-projects'); ?></button>
		</p>
		<ul class="awaid-gallery-grid" id="awaid_gallery_list">
			<?php foreach ($gallery_ids as $gid) : ?>
				<?php
				if (!$gid || !wp_attachment_is_image($gid)) {
					continue;
				}
				$turl = wp_get_attachment_image_url($gid, 'thumbnail') ?: wp_get_attachment_url($gid);
				?>
				<li class="awaid-gallery-item" data-id="<?php echo esc_attr((string) $gid); ?>">
					<span class="awaid-gallery-thumb"><img src="<?php echo esc_url((string) $turl); ?>" alt="" loading="lazy" decoding="async"></span>
					<button type="button" class="button button-small awaid-gallery-remove" data-id="<?php echo esc_attr((string) $gid); ?>"><?php esc_html_e('حذف', 'awaid-projects'); ?></button>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>

	<div class="awaid-admin-section">
		<h3><?php esc_html_e('المعلومات والتسعير', 'awaid-projects'); ?></h3>
		<table class="form-table awaid-form-table">
			<tr>
				<th><label for="awaid_license"><?php esc_html_e('رخصة الاعلان', 'awaid-projects'); ?></label></th>
				<td><input type="text" class="widefat" id="awaid_license" name="awaid_project[license]" value="<?php echo esc_attr((string) $d['license']); ?>"></td>
			</tr>
			<tr>
				<th><label for="awaid_listing_date"><?php esc_html_e('تاريخ النشر', 'awaid-projects'); ?></label></th>
				<td><input type="text" class="widefat" id="awaid_listing_date" name="awaid_project[listing_date]" value="<?php echo esc_attr((string) $d['listing_date']); ?>" placeholder="<?php esc_attr_e('مثال 2025-04-16', 'awaid-projects'); ?>"></td>
			</tr>
			<tr>
				<th><?php esc_html_e('السعر', 'awaid-projects'); ?></th>
				<td class="awaid-inline-fields">
					<label><span class="screen-reader-text"><?php esc_html_e('Minimum', 'awaid-projects'); ?></span>
						<input type="text" name="awaid_project[price_min]" value="<?php echo esc_attr((string) $d['price_min']); ?>" placeholder="<?php esc_attr_e('من', 'awaid-projects'); ?>">
					</label>
					<span class="awaid-sep">—</span>
					<label><span class="screen-reader-text"><?php esc_html_e('Maximum', 'awaid-projects'); ?></span>
						<input type="text" name="awaid_project[price_max]" value="<?php echo esc_attr((string) $d['price_max']); ?>" placeholder="<?php esc_attr_e('إلى', 'awaid-projects'); ?>">
					</label>
				</td>
			</tr>
			<tr>
				<th><?php esc_html_e('الملف التعريفي', 'awaid-projects'); ?></th>
				<td>
					<input type="hidden" id="awaid_brochure_id" name="awaid_project[brochure_id]" value="<?php echo esc_attr((string) $brochure_id); ?>">
					<button type="button" class="button" id="awaid_brochure_pick"><?php esc_html_e('حدد الملف', 'awaid-projects'); ?></button>
					<button type="button" class="button" id="awaid_brochure_clear"><?php esc_html_e('مسح الملف', 'awaid-projects'); ?></button>
					<p class="description awaid-brochure-file" id="awaid_brochure_label"><?php echo $brochure_url ? esc_html(basename($brochure_url)) : esc_html__('لم يتم تحديد أي ملف.', 'awaid-projects'); ?></p>
				</td>
			</tr>
		</table>
	</div>

	<div class="awaid-admin-section">
		<h3><?php esc_html_e('مواصفات المشروع', 'awaid-projects'); ?></h3>
		<table class="form-table awaid-form-table">
			<tr>
				<th><?php esc_html_e('المساحة (م²)', 'awaid-projects'); ?></th>
				<td class="awaid-inline-fields">
					<input type="text" name="awaid_project[area_min]" value="<?php echo esc_attr((string) $d['area_min']); ?>" placeholder="<?php esc_attr_e('Min', 'awaid-projects'); ?>">
					<span class="awaid-sep">—</span>
					<input type="text" name="awaid_project[area_max]" value="<?php echo esc_attr((string) $d['area_max']); ?>" placeholder="<?php esc_attr_e('Max', 'awaid-projects'); ?>">
				</td>
			</tr>
			<tr>
				<th><label for="awaid_bedrooms"><?php esc_html_e('غرف النوم', 'awaid-projects'); ?></label></th>
				<td><input type="text" id="awaid_bedrooms" name="awaid_project[bedrooms]" value="<?php echo esc_attr((string) $d['bedrooms']); ?>"></td>
			</tr>
			<tr>
				<th><label for="awaid_bathrooms"><?php esc_html_e('الحمامات', 'awaid-projects'); ?></label></th>
				<td><input type="text" id="awaid_bathrooms" name="awaid_project[bathrooms]" value="<?php echo esc_attr((string) $d['bathrooms']); ?>"></td>
			</tr>
			<tr>
				<th><label for="awaid_floors"><?php esc_html_e('المطابخ', 'awaid-projects'); ?></label></th>
				<td><input type="text" id="awaid_floors" name="awaid_project[floors]" value="<?php echo esc_attr((string) $d['floors']); ?>"></td>
			</tr>
		</table>
	</div>

	<div class="awaid-admin-section">
		<h3><?php esc_html_e('الخريطة', 'awaid-projects'); ?></h3>
		<table class="form-table awaid-form-table">
			<tr>
				<th><label for="awaid_map_url"><?php esc_html_e('رابط خرائط جوجل', 'awaid-projects'); ?></label></th>
				<td><input type="url" class="widefat" id="awaid_map_url" name="awaid_project[map_url]" value="<?php echo esc_attr((string) $d['map_url']); ?>"></td>
			</tr>
		</table>
	</div>

	<div class="awaid-admin-section">
		<h3><?php esc_html_e('المميزات', 'awaid-projects'); ?></h3>
		<?php if ($catalog_features) : ?>
			<input type="search" class="widefat awaid-catalog-search" data-target="#awaid-feature-options" placeholder="<?php esc_attr_e('ابحث عن ميزة', 'awaid-projects'); ?>">
			<div class="awaid-catalog-options" id="awaid-feature-options">
				<?php foreach ($catalog_features as $feature) : ?>
					<?php
					$feature_id = isset($feature['id']) ? (string) $feature['id'] : '';
					$feature_title = isset($feature['title']) ? (string) $feature['title'] : '';
					$feature_icon = isset($feature['icon']) ? (string) $feature['icon'] : '';
					if ($feature_id === '' || $feature_title === '') {
						continue;
					}
					?>
					<label class="awaid-catalog-option">
						<input type="checkbox" name="awaid_project[feature_ids][]" value="<?php echo esc_attr($feature_id); ?>" <?php checked(in_array($feature_id, $selected_feature_ids, true)); ?>>
						<span class="awaid-catalog-option__body">
							<?php if ($feature_icon !== '') : ?>
								<i data-lucide="<?php echo esc_attr($feature_icon); ?>" class="awaid-lucide-icon" aria-hidden="true"></i>
							<?php endif; ?>
							<span><?php echo esc_html($feature_title); ?></span>
						</span>
					</label>
				<?php endforeach; ?>
			</div>
		<?php else : ?>
			<p class="description">
				<?php
				printf(
					/* translators: %s: settings url */
					wp_kses_post(__('No features yet. Add them first in <a href="%s">Catalog Settings</a>.', 'awaid-projects')),
					esc_url(admin_url('edit.php?post_type=awaid_project&page=' . Awaid_Projects_Settings::get_settings_slug()))
				);
				?>
			</p>
		<?php endif; ?>
	</div>

	<div class="awaid-admin-section">
		<h3><?php esc_html_e('الضمانات', 'awaid-projects'); ?></h3>
		<?php if ($catalog_warranties) : ?>
			<input type="search" class="widefat awaid-catalog-search" data-target="#awaid-warranty-options" placeholder="<?php esc_attr_e('ابحث عن ضمان', 'awaid-projects'); ?>">
			<div class="awaid-catalog-options" id="awaid-warranty-options">
				<?php foreach ($catalog_warranties as $warranty) : ?>
					<?php
					$warranty_id = isset($warranty['id']) ? (string) $warranty['id'] : '';
					$warranty_title = isset($warranty['title']) ? (string) $warranty['title'] : '';
					$warranty_period = isset($warranty['period']) ? (string) $warranty['period'] : '';
					$warranty_icon = isset($warranty['icon']) ? (string) $warranty['icon'] : '';
					if ($warranty_id === '' || $warranty_title === '') {
						continue;
					}
					?>
					<label class="awaid-catalog-option">
						<input type="checkbox" name="awaid_project[warranty_ids][]" value="<?php echo esc_attr($warranty_id); ?>" <?php checked(in_array($warranty_id, $selected_warranty_ids, true)); ?>>
						<span class="awaid-catalog-option__body">
							<?php if ($warranty_icon !== '') : ?>
								<i data-lucide="<?php echo esc_attr($warranty_icon); ?>" class="awaid-lucide-icon" aria-hidden="true"></i>
							<?php endif; ?>
							<span><?php echo esc_html($warranty_title); ?></span>
							<?php if ($warranty_period !== '') : ?>
								<small class="awaid-catalog-option__meta"><?php echo esc_html($warranty_period); ?></small>
							<?php endif; ?>
						</span>
					</label>
				<?php endforeach; ?>
			</div>
		<?php else : ?>
			<p class="description">
				<?php
				printf(
					/* translators: %s: settings url */
					wp_kses_post(__('No warranties yet. Add them first in <a href="%s">Catalog Settings</a>.', 'awaid-projects')),
					esc_url(admin_url('edit.php?post_type=awaid_project&page=' . Awaid_Projects_Settings::get_settings_slug()))
				);
				?>
			</p>
		<?php endif; ?>
	</div>

	<div class="awaid-admin-section">
		<h3><?php esc_html_e('المعالم القريبة', 'awaid-projects'); ?></h3>
		<?php if ($catalog_nearby) : ?>
			<input type="search" class="widefat awaid-catalog-search" data-target="#awaid-nearby-options" placeholder="<?php esc_attr_e('ابحث عن معلم قريب', 'awaid-projects'); ?>">
			<div class="awaid-catalog-options" id="awaid-nearby-options">
				<?php foreach ($catalog_nearby as $nearby) : ?>
					<?php
					$nearby_id = isset($nearby['id']) ? (string) $nearby['id'] : '';
					$nearby_title = isset($nearby['title']) ? (string) $nearby['title'] : '';
					$nearby_distance = isset($nearby['distance']) ? (string) $nearby['distance'] : '';
					$nearby_icon = isset($nearby['icon']) ? (string) $nearby['icon'] : '';
					if ($nearby_id === '' || $nearby_title === '') {
						continue;
					}
					?>
					<label class="awaid-catalog-option">
						<input type="checkbox" name="awaid_project[nearby_ids][]" value="<?php echo esc_attr($nearby_id); ?>" <?php checked(in_array($nearby_id, $selected_nearby_ids, true)); ?>>
						<span class="awaid-catalog-option__body">
							<?php if ($nearby_icon !== '') : ?>
								<i data-lucide="<?php echo esc_attr($nearby_icon); ?>" class="awaid-lucide-icon" aria-hidden="true"></i>
							<?php endif; ?>
							<span><?php echo esc_html($nearby_title); ?></span>
							<?php if ($nearby_distance !== '') : ?>
								<small class="awaid-catalog-option__meta"><?php echo esc_html($nearby_distance); ?></small>
							<?php endif; ?>
						</span>
					</label>
				<?php endforeach; ?>
			</div>
		<?php else : ?>
			<p class="description">
				<?php
				printf(
					/* translators: %s: settings url */
					wp_kses_post(__('No nearby items yet. Add them first in <a href="%s">Catalog Settings</a>.', 'awaid-projects')),
					esc_url(admin_url('edit.php?post_type=awaid_project&page=' . Awaid_Projects_Settings::get_settings_slug()))
				);
				?>
			</p>
		<?php endif; ?>
	</div>

	<div class="awaid-admin-section">
		<h3><?php esc_html_e('Units / inventory', 'awaid-projects'); ?></h3>
		<p class="description"><?php esc_html_e('Status drives front-end filters. Use “Unit details” for gallery, description, highlights, and contacts shown in the side sheet on the single project page.', 'awaid-projects'); ?></p>
		<table class="widefat awaid-units-table" id="awaid_units_table">
			<thead>
				<tr>
					<th><?php esc_html_e('Code', 'awaid-projects'); ?></th>
					<th><?php esc_html_e('Type', 'awaid-projects'); ?></th>
					<th><?php esc_html_e('Price', 'awaid-projects'); ?></th>
					<th><?php esc_html_e('Area m²', 'awaid-projects'); ?></th>
					<th><?php esc_html_e('Beds', 'awaid-projects'); ?></th>
					<th><?php esc_html_e('Baths', 'awaid-projects'); ?></th>
					<th><?php esc_html_e('Status', 'awaid-projects'); ?></th>
					<th class="awaid-col-actions"></th>
				</tr>
			</thead>
			<?php foreach ($units as $i => $row) : ?>
				<?php
				$row = array_merge($awaid_unit_default, is_array($row) ? $row : []);
				$ug_ids = is_array($row['gallery_ids'] ?? null) ? array_map('absint', $row['gallery_ids']) : [];
				$ug_ids = array_values(array_filter(array_unique($ug_ids)));
				$ug_csv = implode(',', $ug_ids);
				$highlights = isset($row['highlights']) && is_array($row['highlights']) && $row['highlights'] !== [] ? $row['highlights'] : [['icon' => '', 'title' => '', 'text' => '']];
				$uid = (string) $i;
				?>
				<tbody class="awaid-unit-block">
					<tr class="awaid-unit-block__labels">
						<th><?php esc_html_e('Code', 'awaid-projects'); ?></th>
						<th><?php esc_html_e('Type', 'awaid-projects'); ?></th>
						<th><?php esc_html_e('Price', 'awaid-projects'); ?></th>
						<th><?php esc_html_e('Area m²', 'awaid-projects'); ?></th>
						<th><?php esc_html_e('Beds', 'awaid-projects'); ?></th>
						<th><?php esc_html_e('Baths', 'awaid-projects'); ?></th>
						<th><?php esc_html_e('Status', 'awaid-projects'); ?></th>
						<th class="awaid-col-actions"></th>
					</tr>
					<tr class="awaid-repeater-row awaid-unit-block__main">
						<td><input type="text" name="awaid_project[units][<?php echo esc_attr($uid); ?>][code]" value="<?php echo esc_attr((string) ($row['code'] ?? '')); ?>"></td>
						<td><input type="text" name="awaid_project[units][<?php echo esc_attr($uid); ?>][type]" value="<?php echo esc_attr((string) ($row['type'] ?? '')); ?>"></td>
						<td><input type="text" name="awaid_project[units][<?php echo esc_attr($uid); ?>][price]" value="<?php echo esc_attr((string) ($row['price'] ?? '')); ?>"></td>
						<td><input type="text" name="awaid_project[units][<?php echo esc_attr($uid); ?>][area]" value="<?php echo esc_attr((string) ($row['area'] ?? '')); ?>"></td>
						<td><input type="text" name="awaid_project[units][<?php echo esc_attr($uid); ?>][bedrooms]" value="<?php echo esc_attr((string) ($row['bedrooms'] ?? '')); ?>"></td>
						<td><input type="text" name="awaid_project[units][<?php echo esc_attr($uid); ?>][bathrooms]" value="<?php echo esc_attr((string) ($row['bathrooms'] ?? '')); ?>"></td>
						<td>
							<select name="awaid_project[units][<?php echo esc_attr($uid); ?>][status]">
								<?php
								$st = (string) ($row['status'] ?? 'available');
								foreach (['available' => __('متاح', 'awaid-projects'), 'reserved' => __('محجوز', 'awaid-projects'), 'sold' => __('مباع', 'awaid-projects')] as $val => $lab) {
									printf('<option value="%s"%s>%s</option>', esc_attr($val), selected($st, $val, false), esc_html($lab));
								}
								?>
							</select>
						</td>
						<td><button type="button" class="button awaid-unit-remove"><?php esc_html_e('Remove', 'awaid-projects'); ?></button></td>
					</tr>
					<tr class="awaid-unit-block__detail">
						<td colspan="8" class="awaid-unit-block__detail-cell">
							<p><strong><?php esc_html_e('Unit gallery (modal slider)', 'awaid-projects'); ?></strong></p>
							<input type="hidden" class="awaid-unit-gallery-csv" name="awaid_project[units][<?php echo esc_attr($uid); ?>][gallery_csv]" value="<?php echo esc_attr($ug_csv); ?>">
							<p>
								<button type="button" class="button awaid-unit-gallery-add"><?php esc_html_e('Add images', 'awaid-projects'); ?></button>
							</p>
							<ul class="awaid-gallery-grid awaid-unit-gallery-list">
								<?php foreach ($ug_ids as $gid) : ?>
									<?php
									if (!$gid || !wp_attachment_is_image($gid)) {
										continue;
									}
									$turl = wp_get_attachment_image_url($gid, 'thumbnail') ?: wp_get_attachment_url($gid);
									?>
									<li class="awaid-gallery-item" data-id="<?php echo esc_attr((string) $gid); ?>">
										<span class="awaid-gallery-thumb"><img src="<?php echo esc_url((string) $turl); ?>" alt="" loading="lazy" decoding="async"></span>
										<button type="button" class="button button-small awaid-unit-gallery-remove" data-id="<?php echo esc_attr((string) $gid); ?>"><?php esc_html_e('Remove', 'awaid-projects'); ?></button>
									</li>
								<?php endforeach; ?>
							</ul>
							<p><label><strong><?php esc_html_e('Description', 'awaid-projects'); ?></strong><br>
								<textarea class="widefat" rows="3" name="awaid_project[units][<?php echo esc_attr($uid); ?>][description]"><?php echo esc_textarea((string) ($row['description'] ?? '')); ?></textarea></label></p>
							<table class="form-table awaid-form-table">
								<tr>
									<th><?php esc_html_e('Floor', 'awaid-projects'); ?></th>
									<td><input type="text" class="widefat" name="awaid_project[units][<?php echo esc_attr($uid); ?>][floor]" value="<?php echo esc_attr((string) ($row['floor'] ?? '')); ?>"></td>
								</tr>
								<tr>
									<th><?php esc_html_e('Kitchens', 'awaid-projects'); ?></th>
									<td><input type="text" class="widefat" name="awaid_project[units][<?php echo esc_attr($uid); ?>][kitchens]" value="<?php echo esc_attr((string) ($row['kitchens'] ?? '')); ?>"></td>
								</tr>
								<tr>
									<th><?php esc_html_e('WhatsApp number', 'awaid-projects'); ?></th>
									<td><input type="text" class="widefat" name="awaid_project[units][<?php echo esc_attr($uid); ?>][whatsapp]" value="<?php echo esc_attr((string) ($row['whatsapp'] ?? '')); ?>" placeholder="<?php esc_attr_e('e.g. 9665xxxxxxxx', 'awaid-projects'); ?>"></td>
								</tr>
								<tr>
									<th><?php esc_html_e('Phone', 'awaid-projects'); ?></th>
									<td><input type="text" class="widefat" name="awaid_project[units][<?php echo esc_attr($uid); ?>][phone]" value="<?php echo esc_attr((string) ($row['phone'] ?? '')); ?>"></td>
								</tr>
							</table>
							<p><strong><?php esc_html_e('Highlights (icon URL + title + text)', 'awaid-projects'); ?></strong></p>
							<table class="widefat awaid-repeater awaid-unit-highlights" data-unit-index="<?php echo esc_attr($uid); ?>">
								<thead><tr><th><?php esc_html_e('Icon URL', 'awaid-projects'); ?></th><th><?php esc_html_e('Title', 'awaid-projects'); ?></th><th><?php esc_html_e('Text', 'awaid-projects'); ?></th><th class="awaid-col-actions"></th></tr></thead>
								<tbody>
									<?php foreach ($highlights as $j => $hrow) : ?>
										<tr class="awaid-repeater-row">
											<td><input type="url" class="widefat" name="awaid_project[units][<?php echo esc_attr($uid); ?>][highlights][<?php echo esc_attr((string) $j); ?>][icon]" value="<?php echo esc_attr((string) ($hrow['icon'] ?? '')); ?>" placeholder="https://"></td>
											<td><input type="text" class="widefat" name="awaid_project[units][<?php echo esc_attr($uid); ?>][highlights][<?php echo esc_attr((string) $j); ?>][title]" value="<?php echo esc_attr((string) ($hrow['title'] ?? '')); ?>"></td>
											<td><textarea class="widefat" rows="2" name="awaid_project[units][<?php echo esc_attr($uid); ?>][highlights][<?php echo esc_attr((string) $j); ?>][text]"><?php echo esc_textarea((string) ($hrow['text'] ?? '')); ?></textarea></td>
											<td><button type="button" class="button awaid-remove-row"><?php esc_html_e('Remove', 'awaid-projects'); ?></button></td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
							<p><button type="button" class="button" data-awaid-add-unit-highlight><?php esc_html_e('Add highlight', 'awaid-projects'); ?></button></p>
						</td>
					</tr>
				</tbody>
			<?php endforeach; ?>
		</table>
		<p><button type="button" class="button" data-awaid-add-unit><?php esc_html_e('Add unit', 'awaid-projects'); ?></button></p>
	</div>
</div>

<script type="text/html" id="tmpl-awaid-unit-highlight-row">
	<tr class="awaid-repeater-row">
		<td><input type="url" class="widefat" name="awaid_project[units][__i__][highlights][__j__][icon]" value="" placeholder="https://"></td>
		<td><input type="text" class="widefat" name="awaid_project[units][__i__][highlights][__j__][title]" value=""></td>
		<td><textarea class="widefat" rows="2" name="awaid_project[units][__i__][highlights][__j__][text]"></textarea></td>
		<td><button type="button" class="button awaid-remove-row"><?php echo esc_html(__('Remove', 'awaid-projects')); ?></button></td>
	</tr>
</script>
<script type="text/html" id="tmpl-awaid-unit-block">
	<tbody class="awaid-unit-block">
		<tr class="awaid-unit-block__labels">
			<th><?php echo esc_html(__('Code', 'awaid-projects')); ?></th>
			<th><?php echo esc_html(__('Type', 'awaid-projects')); ?></th>
			<th><?php echo esc_html(__('Price', 'awaid-projects')); ?></th>
			<th><?php echo esc_html(__('Area m²', 'awaid-projects')); ?></th>
			<th><?php echo esc_html(__('Beds', 'awaid-projects')); ?></th>
			<th><?php echo esc_html(__('Baths', 'awaid-projects')); ?></th>
			<th><?php echo esc_html(__('Status', 'awaid-projects')); ?></th>
			<th class="awaid-col-actions"></th>
		</tr>
		<tr class="awaid-repeater-row awaid-unit-block__main">
			<td><input type="text" name="awaid_project[units][__i__][code]" value=""></td>
			<td><input type="text" name="awaid_project[units][__i__][type]" value=""></td>
			<td><input type="text" name="awaid_project[units][__i__][price]" value=""></td>
			<td><input type="text" name="awaid_project[units][__i__][area]" value=""></td>
			<td><input type="text" name="awaid_project[units][__i__][bedrooms]" value=""></td>
			<td><input type="text" name="awaid_project[units][__i__][bathrooms]" value=""></td>
			<td>
				<select name="awaid_project[units][__i__][status]">
					<option value="available"><?php echo esc_html(__('متاح', 'awaid-projects')); ?></option>
					<option value="reserved"><?php echo esc_html(__('محجوز', 'awaid-projects')); ?></option>
					<option value="sold"><?php echo esc_html(__('مباع', 'awaid-projects')); ?></option>
				</select>
			</td>
			<td><button type="button" class="button awaid-unit-remove"><?php echo esc_html(__('Remove', 'awaid-projects')); ?></button></td>
		</tr>
		<tr class="awaid-unit-block__detail">
			<td colspan="8" class="awaid-unit-block__detail-cell">
				<p><strong><?php echo esc_html(__('Unit gallery (modal slider)', 'awaid-projects')); ?></strong></p>
				<input type="hidden" class="awaid-unit-gallery-csv" name="awaid_project[units][__i__][gallery_csv]" value="">
				<p><button type="button" class="button awaid-unit-gallery-add"><?php echo esc_html(__('Add images', 'awaid-projects')); ?></button></p>
				<ul class="awaid-gallery-grid awaid-unit-gallery-list"></ul>
				<p><label><strong><?php echo esc_html(__('Description', 'awaid-projects')); ?></strong><br>
					<textarea class="widefat" rows="3" name="awaid_project[units][__i__][description]"></textarea></label></p>
				<table class="form-table awaid-form-table">
					<tr><th><?php echo esc_html(__('Floor', 'awaid-projects')); ?></th><td><input type="text" class="widefat" name="awaid_project[units][__i__][floor]" value=""></td></tr>
					<tr><th><?php echo esc_html(__('Kitchens', 'awaid-projects')); ?></th><td><input type="text" class="widefat" name="awaid_project[units][__i__][kitchens]" value=""></td></tr>
					<tr><th><?php echo esc_html(__('WhatsApp number', 'awaid-projects')); ?></th><td><input type="text" class="widefat" name="awaid_project[units][__i__][whatsapp]" value="" placeholder="<?php echo esc_attr(__('e.g. 9665xxxxxxxx', 'awaid-projects')); ?>"></td></tr>
					<tr><th><?php echo esc_html(__('Phone', 'awaid-projects')); ?></th><td><input type="text" class="widefat" name="awaid_project[units][__i__][phone]" value=""></td></tr>
				</table>
				<p><strong><?php echo esc_html(__('Highlights (icon URL + title + text)', 'awaid-projects')); ?></strong></p>
				<table class="widefat awaid-repeater awaid-unit-highlights" data-unit-index="__i__">
					<thead><tr><th><?php echo esc_html(__('Icon URL', 'awaid-projects')); ?></th><th><?php echo esc_html(__('Title', 'awaid-projects')); ?></th><th><?php echo esc_html(__('Text', 'awaid-projects')); ?></th><th class="awaid-col-actions"></th></tr></thead>
					<tbody>
						<tr class="awaid-repeater-row">
							<td><input type="url" class="widefat" name="awaid_project[units][__i__][highlights][0][icon]" value="" placeholder="https://"></td>
							<td><input type="text" class="widefat" name="awaid_project[units][__i__][highlights][0][title]" value=""></td>
							<td><textarea class="widefat" rows="2" name="awaid_project[units][__i__][highlights][0][text]"></textarea></td>
							<td><button type="button" class="button awaid-remove-row"><?php echo esc_html(__('Remove', 'awaid-projects')); ?></button></td>
						</tr>
					</tbody>
				</table>
				<p><button type="button" class="button" data-awaid-add-unit-highlight><?php echo esc_html(__('Add highlight', 'awaid-projects')); ?></button></p>
			</td>
		</tr>
	</tbody>
</script>
