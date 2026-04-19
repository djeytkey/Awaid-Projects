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

$features   = is_array($d['features'] ?? null) ? $d['features'] : [];
$warranties = is_array($d['warranties'] ?? null) ? $d['warranties'] : [];
$nearby     = is_array($d['nearby'] ?? null) ? $d['nearby'] : [];
$units      = is_array($d['units'] ?? null) ? $d['units'] : [];

if (!$features) {
	$features = [['title' => '']];
}
if (!$warranties) {
	$warranties = [['title' => '', 'period' => '']];
}
if (!$nearby) {
	$nearby = [['name' => '', 'distance' => '']];
}
if (!$units) {
	$units = [['code' => '', 'type' => '', 'price' => '', 'area' => '', 'bedrooms' => '', 'bathrooms' => '', 'status' => 'available']];
}
?>
<div class="awaid-admin-meta">
	<div class="awaid-admin-section">
		<h3><?php esc_html_e('Summary', 'awaid-projects'); ?></h3>
		<p class="description"><?php esc_html_e('Project title is the post title. Long description uses the main editor.', 'awaid-projects'); ?></p>
		<table class="form-table awaid-form-table">
			<tr>
				<th><label for="awaid_location"><?php esc_html_e('Location line', 'awaid-projects'); ?></label></th>
				<td><input type="text" class="widefat" id="awaid_location" name="awaid_project[location]" value="<?php echo esc_attr((string) $d['location']); ?>" placeholder="<?php esc_attr_e('e.g. Riyadh – Al Qirawan', 'awaid-projects'); ?>"></td>
			</tr>
			<tr>
				<th><label for="awaid_developer"><?php esc_html_e('Developer', 'awaid-projects'); ?></label></th>
				<td><input type="text" class="widefat" id="awaid_developer" name="awaid_project[developer]" value="<?php echo esc_attr((string) $d['developer']); ?>"></td>
			</tr>
		</table>
	</div>

	<div class="awaid-admin-section">
		<h3><?php esc_html_e('Listing & pricing', 'awaid-projects'); ?></h3>
		<table class="form-table awaid-form-table">
			<tr>
				<th><label for="awaid_license"><?php esc_html_e('Ad license', 'awaid-projects'); ?></label></th>
				<td><input type="text" class="widefat" id="awaid_license" name="awaid_project[license]" value="<?php echo esc_attr((string) $d['license']); ?>"></td>
			</tr>
			<tr>
				<th><label for="awaid_listing_date"><?php esc_html_e('Listing / publish date', 'awaid-projects'); ?></label></th>
				<td><input type="text" class="widefat" id="awaid_listing_date" name="awaid_project[listing_date]" value="<?php echo esc_attr((string) $d['listing_date']); ?>" placeholder="<?php esc_attr_e('e.g. 25-09-20', 'awaid-projects'); ?>"></td>
			</tr>
			<tr>
				<th><?php esc_html_e('Price range', 'awaid-projects'); ?></th>
				<td class="awaid-inline-fields">
					<label><span class="screen-reader-text"><?php esc_html_e('Minimum', 'awaid-projects'); ?></span>
						<input type="text" name="awaid_project[price_min]" value="<?php echo esc_attr((string) $d['price_min']); ?>" placeholder="<?php esc_attr_e('From', 'awaid-projects'); ?>">
					</label>
					<span class="awaid-sep">—</span>
					<label><span class="screen-reader-text"><?php esc_html_e('Maximum', 'awaid-projects'); ?></span>
						<input type="text" name="awaid_project[price_max]" value="<?php echo esc_attr((string) $d['price_max']); ?>" placeholder="<?php esc_attr_e('To', 'awaid-projects'); ?>">
					</label>
				</td>
			</tr>
			<tr>
				<th><?php esc_html_e('Brochure PDF', 'awaid-projects'); ?></th>
				<td>
					<input type="hidden" id="awaid_brochure_id" name="awaid_project[brochure_id]" value="<?php echo esc_attr((string) $brochure_id); ?>">
					<button type="button" class="button" id="awaid_brochure_pick"><?php esc_html_e('Select file', 'awaid-projects'); ?></button>
					<button type="button" class="button" id="awaid_brochure_clear"><?php esc_html_e('Clear', 'awaid-projects'); ?></button>
					<p class="description awaid-brochure-file" id="awaid_brochure_label"><?php echo $brochure_url ? esc_html(basename($brochure_url)) : esc_html__('No file selected.', 'awaid-projects'); ?></p>
				</td>
			</tr>
		</table>
	</div>

	<div class="awaid-admin-section">
		<h3><?php esc_html_e('Project specs (summary strip)', 'awaid-projects'); ?></h3>
		<table class="form-table awaid-form-table">
			<tr>
				<th><?php esc_html_e('Area (m²)', 'awaid-projects'); ?></th>
				<td class="awaid-inline-fields">
					<input type="text" name="awaid_project[area_min]" value="<?php echo esc_attr((string) $d['area_min']); ?>" placeholder="<?php esc_attr_e('Min', 'awaid-projects'); ?>">
					<span class="awaid-sep">—</span>
					<input type="text" name="awaid_project[area_max]" value="<?php echo esc_attr((string) $d['area_max']); ?>" placeholder="<?php esc_attr_e('Max', 'awaid-projects'); ?>">
				</td>
			</tr>
			<tr>
				<th><label for="awaid_bedrooms"><?php esc_html_e('Bedrooms', 'awaid-projects'); ?></label></th>
				<td><input type="text" id="awaid_bedrooms" name="awaid_project[bedrooms]" value="<?php echo esc_attr((string) $d['bedrooms']); ?>"></td>
			</tr>
			<tr>
				<th><label for="awaid_bathrooms"><?php esc_html_e('Bathrooms', 'awaid-projects'); ?></label></th>
				<td><input type="text" id="awaid_bathrooms" name="awaid_project[bathrooms]" value="<?php echo esc_attr((string) $d['bathrooms']); ?>"></td>
			</tr>
			<tr>
				<th><label for="awaid_floors"><?php esc_html_e('Floors / parking', 'awaid-projects'); ?></label></th>
				<td><input type="text" id="awaid_floors" name="awaid_project[floors]" value="<?php echo esc_attr((string) $d['floors']); ?>"></td>
			</tr>
		</table>
	</div>

	<div class="awaid-admin-section">
		<h3><?php esc_html_e('Map', 'awaid-projects'); ?></h3>
		<table class="form-table awaid-form-table">
			<tr>
				<th><label for="awaid_map_url"><?php esc_html_e('Google Maps URL', 'awaid-projects'); ?></label></th>
				<td><input type="url" class="widefat" id="awaid_map_url" name="awaid_project[map_url]" value="<?php echo esc_attr((string) $d['map_url']); ?>"></td>
			</tr>
		</table>
	</div>

	<div class="awaid-admin-section">
		<h3><?php esc_html_e('Features', 'awaid-projects'); ?></h3>
		<p class="description"><?php esc_html_e('Short highlight items (tiles on the front).', 'awaid-projects'); ?></p>
		<table class="widefat awaid-repeater" id="awaid_features_table">
			<thead><tr><th><?php esc_html_e('Title', 'awaid-projects'); ?></th><th class="awaid-col-actions"></th></tr></thead>
			<tbody>
				<?php foreach ($features as $i => $row) : ?>
					<tr class="awaid-repeater-row">
						<td><input type="text" class="widefat" name="awaid_project[features][<?php echo esc_attr((string) $i); ?>][title]" value="<?php echo esc_attr((string) ($row['title'] ?? '')); ?>"></td>
						<td><button type="button" class="button awaid-remove-row"><?php esc_html_e('Remove', 'awaid-projects'); ?></button></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<p><button type="button" class="button" data-awaid-add-feature><?php esc_html_e('Add feature', 'awaid-projects'); ?></button></p>
	</div>

	<div class="awaid-admin-section">
		<h3><?php esc_html_e('Warranties', 'awaid-projects'); ?></h3>
		<table class="widefat awaid-repeater" id="awaid_warranties_table">
			<thead><tr><th><?php esc_html_e('Item', 'awaid-projects'); ?></th><th><?php esc_html_e('Period', 'awaid-projects'); ?></th><th class="awaid-col-actions"></th></tr></thead>
			<tbody>
				<?php foreach ($warranties as $i => $row) : ?>
					<tr class="awaid-repeater-row">
						<td><input type="text" class="widefat" name="awaid_project[warranties][<?php echo esc_attr((string) $i); ?>][title]" value="<?php echo esc_attr((string) ($row['title'] ?? '')); ?>"></td>
						<td><input type="text" class="widefat" name="awaid_project[warranties][<?php echo esc_attr((string) $i); ?>][period]" value="<?php echo esc_attr((string) ($row['period'] ?? '')); ?>" placeholder="<?php esc_attr_e('e.g. 5 years', 'awaid-projects'); ?>"></td>
						<td><button type="button" class="button awaid-remove-row"><?php esc_html_e('Remove', 'awaid-projects'); ?></button></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<p><button type="button" class="button" data-awaid-add-warranty><?php esc_html_e('Add warranty', 'awaid-projects'); ?></button></p>
	</div>

	<div class="awaid-admin-section">
		<h3><?php esc_html_e('Nearby landmarks', 'awaid-projects'); ?></h3>
		<table class="widefat awaid-repeater" id="awaid_nearby_table">
			<thead><tr><th><?php esc_html_e('Name', 'awaid-projects'); ?></th><th><?php esc_html_e('Distance', 'awaid-projects'); ?></th><th class="awaid-col-actions"></th></tr></thead>
			<tbody>
				<?php foreach ($nearby as $i => $row) : ?>
					<tr class="awaid-repeater-row">
						<td><input type="text" class="widefat" name="awaid_project[nearby][<?php echo esc_attr((string) $i); ?>][name]" value="<?php echo esc_attr((string) ($row['name'] ?? '')); ?>"></td>
						<td><input type="text" class="widefat" name="awaid_project[nearby][<?php echo esc_attr((string) $i); ?>][distance]" value="<?php echo esc_attr((string) ($row['distance'] ?? '')); ?>" placeholder="<?php esc_attr_e('e.g. 1.5 km', 'awaid-projects'); ?>"></td>
						<td><button type="button" class="button awaid-remove-row"><?php esc_html_e('Remove', 'awaid-projects'); ?></button></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<p><button type="button" class="button" data-awaid-add-nearby><?php esc_html_e('Add landmark', 'awaid-projects'); ?></button></p>
	</div>

	<div class="awaid-admin-section">
		<h3><?php esc_html_e('Units / inventory', 'awaid-projects'); ?></h3>
		<p class="description"><?php esc_html_e('Status drives front-end filters (available / reserved / sold).', 'awaid-projects'); ?></p>
		<table class="widefat awaid-repeater awaid-units-table" id="awaid_units_table">
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
			<tbody>
				<?php foreach ($units as $i => $row) : ?>
					<tr class="awaid-repeater-row">
						<td><input type="text" name="awaid_project[units][<?php echo esc_attr((string) $i); ?>][code]" value="<?php echo esc_attr((string) ($row['code'] ?? '')); ?>"></td>
						<td><input type="text" name="awaid_project[units][<?php echo esc_attr((string) $i); ?>][type]" value="<?php echo esc_attr((string) ($row['type'] ?? '')); ?>"></td>
						<td><input type="text" name="awaid_project[units][<?php echo esc_attr((string) $i); ?>][price]" value="<?php echo esc_attr((string) ($row['price'] ?? '')); ?>"></td>
						<td><input type="text" name="awaid_project[units][<?php echo esc_attr((string) $i); ?>][area]" value="<?php echo esc_attr((string) ($row['area'] ?? '')); ?>"></td>
						<td><input type="text" name="awaid_project[units][<?php echo esc_attr((string) $i); ?>][bedrooms]" value="<?php echo esc_attr((string) ($row['bedrooms'] ?? '')); ?>"></td>
						<td><input type="text" name="awaid_project[units][<?php echo esc_attr((string) $i); ?>][bathrooms]" value="<?php echo esc_attr((string) ($row['bathrooms'] ?? '')); ?>"></td>
						<td>
							<select name="awaid_project[units][<?php echo esc_attr((string) $i); ?>][status]">
								<?php
								$st = (string) ($row['status'] ?? 'available');
								foreach (['available' => __('Available', 'awaid-projects'), 'reserved' => __('Reserved', 'awaid-projects'), 'sold' => __('Sold', 'awaid-projects')] as $val => $lab) {
									printf('<option value="%s"%s>%s</option>', esc_attr($val), selected($st, $val, false), esc_html($lab));
								}
								?>
							</select>
						</td>
						<td><button type="button" class="button awaid-remove-row"><?php esc_html_e('Remove', 'awaid-projects'); ?></button></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<p><button type="button" class="button" data-awaid-add-unit><?php esc_html_e('Add unit', 'awaid-projects'); ?></button></p>
	</div>
</div>

<script type="text/html" id="tmpl-awaid-feature-row">
	<tr class="awaid-repeater-row">
		<td><input type="text" class="widefat" name="awaid_project[features][__i__][title]" value=""></td>
		<td><button type="button" class="button awaid-remove-row"><?php echo esc_html(__('Remove', 'awaid-projects')); ?></button></td>
	</tr>
</script>
<script type="text/html" id="tmpl-awaid-warranty-row">
	<tr class="awaid-repeater-row">
		<td><input type="text" class="widefat" name="awaid_project[warranties][__i__][title]" value=""></td>
		<td><input type="text" class="widefat" name="awaid_project[warranties][__i__][period]" value=""></td>
		<td><button type="button" class="button awaid-remove-row"><?php echo esc_html(__('Remove', 'awaid-projects')); ?></button></td>
	</tr>
</script>
<script type="text/html" id="tmpl-awaid-nearby-row">
	<tr class="awaid-repeater-row">
		<td><input type="text" class="widefat" name="awaid_project[nearby][__i__][name]" value=""></td>
		<td><input type="text" class="widefat" name="awaid_project[nearby][__i__][distance]" value=""></td>
		<td><button type="button" class="button awaid-remove-row"><?php echo esc_html(__('Remove', 'awaid-projects')); ?></button></td>
	</tr>
</script>
<script type="text/html" id="tmpl-awaid-unit-row">
	<tr class="awaid-repeater-row">
		<td><input type="text" name="awaid_project[units][__i__][code]" value=""></td>
		<td><input type="text" name="awaid_project[units][__i__][type]" value=""></td>
		<td><input type="text" name="awaid_project[units][__i__][price]" value=""></td>
		<td><input type="text" name="awaid_project[units][__i__][area]" value=""></td>
		<td><input type="text" name="awaid_project[units][__i__][bedrooms]" value=""></td>
		<td><input type="text" name="awaid_project[units][__i__][bathrooms]" value=""></td>
		<td>
			<select name="awaid_project[units][__i__][status]">
				<option value="available"><?php echo esc_html(__('Available', 'awaid-projects')); ?></option>
				<option value="reserved"><?php echo esc_html(__('Reserved', 'awaid-projects')); ?></option>
				<option value="sold"><?php echo esc_html(__('Sold', 'awaid-projects')); ?></option>
			</select>
		</td>
		<td><button type="button" class="button awaid-remove-row"><?php echo esc_html(__('Remove', 'awaid-projects')); ?></button></td>
	</tr>
</script>
