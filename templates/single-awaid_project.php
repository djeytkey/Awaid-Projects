<?php

/**
 * Single project template (plugin) — Riva-inspired 3/9 split (see https://riva.sa/project/aoshn-01).
 */

if (!defined('ABSPATH')) {
	exit;
}

get_header();

while (have_posts()) :
	the_post();

	$d            = Awaid_Projects_Meta::get_all(get_the_ID());
	$gallery_raw  = is_array($d['gallery_ids'] ?? null) ? $d['gallery_ids'] : [];
	$slide_ids    = [];
	foreach ($gallery_raw as $gid) {
		$gid = absint($gid);
		if ($gid && wp_attachment_is_image($gid)) {
			$slide_ids[] = $gid;
		}
	}
	$slide_ids = array_values(array_unique($slide_ids));
	if (!$slide_ids) {
		$thumb_id = get_post_thumbnail_id();
		if ($thumb_id) {
			$slide_ids[] = (int) $thumb_id;
		}
	}
	$slides_data = [];
	foreach ($slide_ids as $sid) {
		$full_url = wp_get_attachment_image_url((int) $sid, 'full');
		if (!$full_url) {
			continue;
		}
		$alt = trim((string) get_post_meta((int) $sid, '_wp_attachment_image_alt', true));
		if ($alt === '') {
			$alt = get_the_title();
		}
		$slides_data[] = [
			'id'       => (int) $sid,
			'full_url' => $full_url,
			'alt'      => $alt,
		];
	}
	$has_lead_slides = $slides_data !== [];
	$slide_count     = count($slides_data);
	$brochure_id  = (int) ($d['brochure_id'] ?? 0);
	$brochure_url = $brochure_id ? wp_get_attachment_url($brochure_id) : '';
	$map_url      = isset($d['map_url']) ? (string) $d['map_url'] : '';
	$map_lat      = null;
	$map_lng      = null;
	if ($map_url !== '') {
		$q_raw = wp_parse_url($map_url, PHP_URL_QUERY);
		if (is_string($q_raw) && $q_raw !== '') {
			$map_query = [];
			parse_str($q_raw, $map_query);
			$map_parts = [];
			if (!empty($map_query['q']) && is_string($map_query['q'])) {
				$map_parts = array_map('trim', explode(',', (string) $map_query['q']));
			}
			if (count($map_parts) < 2 && !empty($map_query['destination']) && is_string($map_query['destination'])) {
				$map_parts = array_map('trim', explode(',', (string) $map_query['destination']));
			}
			if (count($map_parts) >= 2) {
				$lat_try = is_numeric($map_parts[0]) ? (float) $map_parts[0] : null;
				$lng_try = is_numeric($map_parts[1]) ? (float) $map_parts[1] : null;
				if ($lat_try !== null && $lng_try !== null) {
					$map_lat = $lat_try;
					$map_lng = $lng_try;
				}
			}
		}
	}

	$features   = Awaid_Projects_Settings::resolve_selected_items('features', $d['features'] ?? []);
	$warranties = Awaid_Projects_Settings::resolve_selected_items('warranties', $d['warranties'] ?? []);
	$nearby     = Awaid_Projects_Settings::resolve_selected_items('nearby', $d['nearby'] ?? []);
	$units      = is_array($d['units'] ?? null) ? $d['units'] : [];

	$area_range  = awaid_project_area_range_display($d);
	$price_range = awaid_project_price_range_display($d);

	$wrap_classes = ['awaid-project'];
	if (is_rtl()) {
		$wrap_classes[] = 'awaid-project--rtl';
	}

	$upload_base = wp_upload_dir();
	$upload_sub  = isset($upload_base['baseurl']) ? rtrim((string) $upload_base['baseurl'], '/') : '';
	$spec_icons  = [
		'area'      => $upload_sub !== '' ? $upload_sub . '/2026/04/move.png' : '',
		'bedrooms'  => $upload_sub !== '' ? $upload_sub . '/2026/04/bed.png' : '',
		'bathrooms' => $upload_sub !== '' ? $upload_sub . '/2026/04/bathtub-01.png' : '',
		'currency' => $upload_sub !== '' ? $upload_sub . '/2026/04/currency.svg' : '',
	];
	$specs_panel_dir = is_rtl() ? 'rtl' : 'ltr';

	$awaid_unit_defaults = [
		'code' => '',
		'type' => '',
		'price' => '',
		'area' => '',
		'bedrooms' => '',
		'bathrooms' => '',
		'status' => 'available',
		'gallery_ids' => [],
		'description' => '',
		'floor' => '',
		'kitchens' => '',
		'whatsapp' => '',
		'phone' => '',
		'highlights' => [],
	];
	$units_modal_units = [];
	foreach ($units as $u_raw) {
		$u = array_merge($awaid_unit_defaults, is_array($u_raw) ? $u_raw : []);
		$code = trim((string) ($u['code'] ?? ''));
		if ($code === '') {
			continue;
		}
		$ug_ids = isset($u['gallery_ids']) && is_array($u['gallery_ids']) ? $u['gallery_ids'] : [];
		$unit_gallery = [];
		foreach ($ug_ids as $ug) {
			$ug = absint($ug);
			if (!$ug || !wp_attachment_is_image($ug)) {
				continue;
			}
			$full_u = wp_get_attachment_image_url($ug, 'full');
			if (!$full_u) {
				continue;
			}
			$large_u = wp_get_attachment_image_url($ug, 'large') ?: $full_u;
			$alt_u    = trim((string) get_post_meta($ug, '_wp_attachment_image_alt', true));
			if ($alt_u === '') {
				$alt_u = get_the_title() . ' — ' . $code;
			}
			$unit_gallery[] = [
				'full'  => $full_u,
				'large' => $large_u,
				'alt'   => $alt_u,
			];
		}
		$uhl = [];
		foreach (is_array($u['highlights'] ?? null) ? $u['highlights'] : [] as $h) {
			$uhl[] = [
				'icon'  => isset($h['icon']) ? (string) $h['icon'] : '',
				'title' => isset($h['title']) ? (string) $h['title'] : '',
				'text'  => isset($h['text']) ? (string) $h['text'] : '',
			];
		}
		$wa_raw = trim((string) ($u['whatsapp'] ?? ''));
		$wa_digits = preg_replace('/\D+/', '', $wa_raw);
		$wa_url    = '';
		if ($wa_digits !== '') {
			$wa_msg = sprintf(
				/* translators: 1: project title, 2: unit code, 3: permalink */
				__('I am interested in unit %2$s at %1$s %3$s', 'awaid-projects'),
				get_the_title(),
				$code,
				get_permalink()
			);
			$wa_url = 'https://wa.me/' . $wa_digits . '?text=' . rawurlencode($wa_msg);
		}
		$ph_raw = trim((string) ($u['phone'] ?? ''));
		$tel_u  = $ph_raw !== '' ? 'tel:' . preg_replace('/[^\d+]/', '', $ph_raw) : '';

		$units_modal_units[] = [
			'code'          => $code,
			'type'          => (string) ($u['type'] ?? ''),
			'status'        => (string) ($u['status'] ?? 'available'),
			'statusLabel'   => awaid_project_status_label((string) ($u['status'] ?? 'available')),
			'price'         => (string) ($u['price'] ?? ''),
			'area'          => (string) ($u['area'] ?? ''),
			'bedrooms'      => (string) ($u['bedrooms'] ?? ''),
			'bathrooms'     => (string) ($u['bathrooms'] ?? ''),
			'floor'         => (string) ($u['floor'] ?? ''),
			'kitchens'      => (string) ($u['kitchens'] ?? ''),
			'description'   => (string) ($u['description'] ?? ''),
			'gallery'       => $unit_gallery,
			'highlights'    => $uhl,
			'whatsappUrl'   => $wa_url,
			'telUrl'        => $tel_u,
		];
	}
	$units_modal_payload = [
		'icons' => $spec_icons,
		'labels' => [
			'detailsTitle' => __('تفاصيل الوحدة', 'awaid-projects'),
			'bathrooms'    => __('Bathrooms', 'awaid-projects'),
			'bedrooms'     => __('Bedrooms', 'awaid-projects'),
			'area'         => __('Area', 'awaid-projects'),
			'kitchens'     => __('Kitchens', 'awaid-projects'),
			'floor'        => __('الدور', 'awaid-projects'),
			'features'     => __('المميزات', 'awaid-projects'),
			'price'        => __('السعر', 'awaid-projects'),
			'whatsapp'     => __('تواصل واتس اب', 'awaid-projects'),
			'call'         => __('اتصال', 'awaid-projects'),
			'close'        => __('Close', 'awaid-projects'),
			'openDetails'  => __('View unit details', 'awaid-projects'),
		],
		'units' => $units_modal_units,
	];
	$units_modal_json = wp_json_encode(
		$units_modal_payload,
		JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE
	);

	$render_sidebar = static function (string $sidebar_mod) use ($d, $area_range, $price_range, $brochure_url, $map_url, $units, $spec_icons, $specs_panel_dir): void {
		$base = 'awaid-sidebar-card ' . $sidebar_mod;
?>
		<div class="<?php echo esc_attr($base); ?>">
			<div class="awaid-sidebar-card__block">
				<p class="awaid-sidebar-card__eyebrow"><?php esc_html_e('المطور', 'awaid-projects'); ?></p>
				<?php if (!empty($d['developer'])) : ?>
					<p class="awaid-sidebar-card__strong"><?php echo esc_html((string) $d['developer']); ?></p>
				<?php endif; ?>
			</div>

			<?php
			$spec_has_area = $area_range !== '';
			$spec_has_bed  = trim((string) ($d['bedrooms'] ?? '')) !== '';
			$spec_has_bath = trim((string) ($d['bathrooms'] ?? '')) !== '';
			$spec_has_kitchens = trim((string) ($d['floors'] ?? '')) !== '';
			$spec_has_any      = $spec_has_area || $spec_has_bed || $spec_has_bath || $spec_has_kitchens;
			$spec_row1         = $spec_has_area || $spec_has_bed;
			$spec_row2         = $spec_has_bath || $spec_has_kitchens;
			?>
			<?php if ($spec_has_any) : ?>
				<div class="awaid-panel awaid-panel--specs p-2 py-3 mt-2 rounded" dir="<?php echo esc_attr($specs_panel_dir); ?>" style="background: #f1f1f19e !important;">
					<p class="mb-1 text-gray-800"><?php esc_html_e('مواصفات المشروع', 'awaid-projects'); ?></p>
					<?php if ($spec_row1) : ?>
						<ul class="post-meta row mb-2">
							<?php if ($spec_has_area) : ?>
								<li class="col-md-6">
									<?php if (!empty($spec_icons['area'])) : ?>
										<img src="<?php echo esc_url($spec_icons['area']); ?>" class="dark-image" style="width: 20px;" alt="<?php esc_attr_e('Area', 'awaid-projects'); ?>" loading="lazy" decoding="async" width="20" height="20">
									<?php endif; ?>
									<span class="me-1 text-dark fs-14"><?php echo esc_html($area_range); ?></span>
								</li>
							<?php endif; ?>
							<?php if ($spec_has_bed) : ?>
								<li class="col-md-6">
									<?php if (!empty($spec_icons['bedrooms'])) : ?>
										<img src="<?php echo esc_url($spec_icons['bedrooms']); ?>" class="dark-image" style="width: 20px;" alt="<?php esc_attr_e('Bedrooms', 'awaid-projects'); ?>" loading="lazy" decoding="async" width="20" height="20">
									<?php endif; ?>
									<span class="me-1 text-dark fs-14"><?php echo esc_html((string) $d['bedrooms']); ?></span>
								</li>
							<?php endif; ?>
						</ul>
					<?php endif; ?>
					<?php if ($spec_row2) : ?>
						<ul class="post-meta row">
							<?php if ($spec_has_bath) : ?>
								<li class="col-md-6">
									<?php if (!empty($spec_icons['bathrooms'])) : ?>
										<img src="<?php echo esc_url($spec_icons['bathrooms']); ?>" class="dark-image" style="width: 20px;" alt="<?php esc_attr_e('Bathrooms', 'awaid-projects'); ?>" loading="lazy" decoding="async" width="20" height="20">
									<?php endif; ?>
									<span class="me-1 text-dark fs-14"><?php echo esc_html((string) $d['bathrooms']); ?></span>
								</li>
							<?php endif; ?>
							<?php if ($spec_has_kitchens) : ?>
								<li class="col-md-6">
									<svg class="awaid-spec-kitchen-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="19" height="19" fill="none" aria-hidden="true" focusable="false">
										<path d="M21 17C18.2386 17 16 14.7614 16 12C16 9.23858 18.2386 7 21 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
										<path d="M21 21C16.0294 21 12 16.9706 12 12C12 7.02944 16.0294 3 21 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
										<path d="M6 3L6 8M6 21L6 11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
										<path d="M3.5 8H8.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
										<path d="M9 3L9 7.35224C9 12.216 3 12.2159 3 7.35207L3 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
									</svg>
									<span class="awaid-sr-only"><?php esc_html_e('Kitchens', 'awaid-projects'); ?></span>
									<span class="me-1 text-dark fs-14"><?php echo esc_html((string) $d['floors']); ?></span>
								</li>
							<?php endif; ?>
						</ul>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<div class="awaid-panel">
				<ul class="awaid-meta-rows row post-meta mb-2">
					<?php if (!empty($d['license'])) : ?>
						<li class="col-md-6">
							<span class="awaid-meta-rows__k"><?php esc_html_e('رخصة الاعلان', 'awaid-projects'); ?></span>
							<span class="awaid-meta-rows__v"><?php echo esc_html((string) $d['license']); ?></span>
						</li>
					<?php endif; ?>
					<?php if (!empty($d['listing_date'])) : ?>
						<li class="col-md-6">
							<span class="awaid-meta-rows__k"><?php esc_html_e('تاريخ النشر', 'awaid-projects'); ?></span>
							<span class="awaid-meta-rows__v"><?php echo esc_html((string) $d['listing_date']); ?></span>
						</li>
					<?php endif; ?>
				</ul>
				<?php if ($price_range !== '') : ?>
					<p class="awaid-meta-rows__k awaid-mt"><?php esc_html_e('السعر', 'awaid-projects'); ?></p>
					<p class="awaid-price-range">
						<?php echo esc_html($price_range); ?>
						&nbsp;
						<span class="awaid-price-range__currency">
							<?php if (!empty($spec_icons['currency'])) : ?>
								<img src="<?php echo esc_url($spec_icons['currency']); ?>" class="dark-image" style="width: 14px;" alt="<?php esc_attr_e('Currency', 'awaid-projects'); ?>" loading="lazy" decoding="async" width="20" height="20">
							<?php else : ?>
								<span class="awaid-price-range__currency-symbol">ر.س</span>
							<?php endif; ?>
						</span>
					</p>
				<?php endif; ?>
			</div>

			<div class="awaid-sidebar-card__actions">
				<?php if ($brochure_url) : ?>
					<a class="awaid-btn awaid-btn--secondary awaid-btn--block" href="<?php echo esc_url($brochure_url); ?>" target="_blank" rel="noopener"><?php esc_html_e('View brochure', 'awaid-projects'); ?></a>
				<?php endif; ?>
				<?php if ($map_url) : ?>
					<a class="awaid-btn awaid-btn--primary awaid-btn--block" href="<?php echo esc_url($map_url); ?>" target="_blank" rel="noopener"><?php esc_html_e('Open in Google Maps', 'awaid-projects'); ?></a>
				<?php endif; ?>
				<?php if ($units) : ?>
					<a class="awaid-btn awaid-btn--primary awaid-btn--block" href="#awaid-units"><?php esc_html_e('Units', 'awaid-projects'); ?></a>
				<?php endif; ?>
			</div>
		</div>
	<?php
	};
	?>

	<article id="post-<?php the_ID(); ?>" <?php post_class($wrap_classes); ?>>
		<section class="awaid-project-shell">
			<div class="awaid-container-wide">
				<div class="row awaid-split-row align-items-start">
					<div class="col-12 col-lg-9 awaid-split-main">
						<?php if ($has_lead_slides || $brochure_url || $map_url) : ?>
							<?php
							$awaid_lead_dir = is_rtl() ? 'rtl' : 'ltr';
							$awaid_brochure_label = __('حمل الملف التعريفي', 'awaid-projects');
							?>
							<div class="awaid-lead-media awaid-lead-media--card">
								<?php if ($brochure_url) : ?>
									<a
										class="awaid-hero-floating awaid-hero-floating--brochure<?php echo $map_url ? '' : ' awaid-hero-floating--brochure-only'; ?>"
										href="<?php echo esc_url($brochure_url); ?>"
										download
										dir="<?php echo esc_attr($awaid_lead_dir); ?>">
										<span class="awaid-hero-badge">
											<?php echo esc_html($awaid_brochure_label); ?>
											<i class="fa fa-download awaid-hero-badge__icon" aria-hidden="true"></i>
										</span>
									</a>
								<?php endif; ?>
								<?php if ($map_url) : ?>
									<a
										class="awaid-hero-floating awaid-hero-floating--map"
										href="<?php echo esc_url($map_url); ?>"
										target="_blank"
										rel="noopener"
										dir="<?php echo esc_attr($awaid_lead_dir); ?>">
										<span class="awaid-hero-badge awaid-hero-badge--icon-only">
											<i class="fa fa-map-marker" aria-hidden="true"></i>
											<span class="awaid-sr-only"><?php esc_html_e('Open in Google Maps', 'awaid-projects'); ?></span>
										</span>
									</a>
								<?php endif; ?>

								<?php if ($has_lead_slides) : ?>
									<div
										class="awaid-swiper-shell"
										dir="ltr"
										data-awaid-swiper
										data-margin="10"
										data-dots="false"
										data-nav="true">
										<div class="swiper awaid-project-swiper">
											<div class="swiper-wrapper">
												<?php foreach ($slides_data as $i => $row) : ?>
													<div class="swiper-slide">
														<a
															class="item-link"
															href="<?php echo esc_url($row['full_url']); ?>"
															data-awaid-lightbox-open
															data-src="<?php echo esc_url($row['full_url']); ?>"
															data-alt="<?php echo esc_attr($row['alt']); ?>"
															aria-label="<?php esc_attr_e('Open image in lightbox', 'awaid-projects'); ?>">
															<figure class="awaid-slide-figure">
																<?php
																echo wp_get_attachment_image(
																	$row['id'],
																	'large',
																	false,
																	[
																		'class'    => 'awaid-slide-img',
																		'loading'  => $i === 0 ? 'eager' : 'lazy',
																		'decoding' => 'async',
																	]
																);
																?>
															</figure>
														</a>
													</div>
												<?php endforeach; ?>
											</div>
										</div>
										<?php if ($slide_count > 1) : ?>
											<div class="swiper-controls awaid-swiper-controls">
												<div class="swiper-navigation">
													<div
														class="swiper-button-prev awaid-swiper-nav-btn"
														tabindex="0"
														role="button"
														aria-label="<?php esc_attr_e('Previous slide', 'awaid-projects'); ?>"></div>
													<div
														class="swiper-button-next awaid-swiper-nav-btn"
														tabindex="0"
														role="button"
														aria-label="<?php esc_attr_e('Next slide', 'awaid-projects'); ?>"></div>
												</div>
											</div>
										<?php endif; ?>
									</div>
								<?php else : ?>
									<div class="awaid-lead-media__placeholder" aria-hidden="true"></div>
								<?php endif; ?>
							</div>
						<?php endif; ?>

						<div class="awaid-sidebar-mobile">
							<?php $render_sidebar('awaid-sidebar--mobile'); ?>
						</div>

						<div class="awaid-main-card">
							<!-- <p class="awaid-main-card__crumb">
							<a href="<?php echo esc_url(get_post_type_archive_link('awaid_project')); ?>"><?php esc_html_e('Projects', 'awaid-projects'); ?></a>
						</p> -->
							<h1 class="awaid-main-card__title"><?php the_title(); ?></h1>
							<?php if (!empty($d['location'])) : ?>
								<?php if ($map_url) : ?>
									<a class="awaid-main-card__location" href="<?php echo esc_url($map_url); ?>" target="_blank" rel="noopener"><i class="fas fa-map-pin mr-2"></i><?php echo esc_html((string) $d['location']); ?></a>
								<?php else : ?>
									<p class="awaid-main-card__location"><?php echo esc_html((string) $d['location']); ?></p>
								<?php endif; ?>
							<?php endif; ?>
							<?php if (get_the_content()) : ?>
								<div class="awaid-main-card__content awaid-entry-content">
									<?php the_content(); ?>
								</div>
							<?php endif; ?>
						</div>

						<?php if ($units) : ?>
							<div class="awaid-main-card">
								<!-- <section class="awaid-section awaid-section--inset" id="awaid-units"> -->
								<!-- <h2 class="awaid-section__title"><?php esc_html_e('Units', 'awaid-projects'); ?></h2> -->
								<ul class="awaid-unit-filters nav nav-tabs nav-pills tab-box w-fit" dir="rtl" role="tablist" aria-label="<?php esc_attr_e('Filter units', 'awaid-projects'); ?>">
									<li class="nav-item">
										<button type="button" class="nav-link awaid-unit-filter is-active" data-filter="all" aria-pressed="true"><i class="fa fa-bars" aria-hidden="true"></i> <?php esc_html_e('الكل', 'awaid-projects'); ?></button>
									</li>
									<li class="nav-item">
										<button type="button" class="nav-link awaid-unit-filter" data-filter="available" aria-pressed="false"><i class="fa fa-unlock" aria-hidden="true"></i> <?php esc_html_e('متاح', 'awaid-projects'); ?></button>
									</li>
									<li class="nav-item">
										<button type="button" class="nav-link awaid-unit-filter" data-filter="reserved" aria-pressed="false"><i class="fa fa-lock" aria-hidden="true"></i> <?php esc_html_e('محجوز', 'awaid-projects'); ?></button>
									</li>
									<li class="nav-item">
										<button type="button" class="nav-link awaid-unit-filter" data-filter="sold" aria-pressed="false"><i class="fa fa-times" aria-hidden="true"></i> <?php esc_html_e('مباع', 'awaid-projects'); ?></button>
									</li>
								</ul>
								<div class="awaid-units-grid row">
									<?php foreach ($units as $unit) : ?>
										<?php
										$code   = isset($unit['code']) ? (string) $unit['code'] : '';
										if ($code === '') {
											continue;
										}
										$status  = isset($unit['status']) ? (string) $unit['status'] : 'available';
										$badge   = awaid_project_status_label($status);
										$is_sold = $status === 'sold';
										$u_type = isset($unit['type']) ? (string) $unit['type'] : '';
										$u_price = isset($unit['price']) ? (string) $unit['price'] : '';
										$u_area = isset($unit['area']) ? (string) $unit['area'] : '';
										$u_bedrooms = isset($unit['bedrooms']) ? (string) $unit['bedrooms'] : '';
										$u_bathrooms = isset($unit['bathrooms']) ? (string) $unit['bathrooms'] : '';
										$u_gallery_ids = isset($unit['gallery_ids']) && is_array($unit['gallery_ids']) ? $unit['gallery_ids'] : [];
										$u_thumb = '';
										foreach ($u_gallery_ids as $u_gid) {
											$u_gid = absint($u_gid);
											if (!$u_gid || !wp_attachment_is_image($u_gid)) {
												continue;
											}
											$u_thumb = wp_get_attachment_image_url($u_gid, 'large');
											if ($u_thumb) {
												break;
											}
										}
										?>
										<div class="awaid-unit-col col-md-6 col-lg-4 mb-2">
											<article
												class="post rounded border awaid-unit-card<?php echo $is_sold ? ' awaid-unit-card--sold' : ''; ?> awaid-unit-card--interactive"
												data-status="<?php echo esc_attr($status); ?>"
												data-awaid-unit-code="<?php echo esc_attr($code); ?>"
												role="button"
												tabindex="0"
												aria-label="<?php echo esc_attr(sprintf(/* translators: %s: unit code */__('View details for unit %s', 'awaid-projects'), $code)); ?>">
												<?php if ($u_thumb) : ?>
													<figure class="rounded-top position-relative awaid-unit-card__media">
														<img src="<?php echo esc_url($u_thumb); ?>" style="max-height: 207px;" alt="<?php echo esc_attr($code); ?>" loading="lazy" decoding="async">
														<?php if ($is_sold) : ?>
															<div class="awaid-unit-card__sold-overlay">
																<span class="badge bg-danger"><?php esc_html_e('تم البيع', 'awaid-projects'); ?></span>
															</div>
														<?php endif; ?>
													</figure>
												<?php endif; ?>
												<div class="post-header project-data-card rounded-bottom bg-white awaid-unit-card__body">
													<div class="d-flex align-content-start justify-content-between w-100 awaid-unit-card__top">
														<h2 class="post-title h6 mt-0 mb-0 awaid-unit-card__title">
															<strong class="unit_code"><?php echo esc_html($code); ?></strong>
															<span class="badge bg-pale-ash text-dark rounded-pill"><?php esc_html_e('عرض بيانات الوحدة', 'awaid-projects'); ?></span>
														</h2>
														<?php if ($u_type !== '') : ?>
															<div>
																<span class="badge bg-pale-ash text-dark rounded-pill"><?php echo esc_html($u_type); ?></span>
															</div>
														<?php endif; ?>
													</div>
													<?php if ($is_sold) : ?>
														<p class="awaid-price-range">
															<?php esc_html_e('تواصل معنا', 'awaid-projects'); ?>
														</p>
													<?php elseif ($u_price !== '') : ?>
														<p class="awaid-price-range">
															<?php echo esc_html($u_price); ?>
															<?php if (!empty($spec_icons['currency'])) : ?>
																<img src="<?php echo esc_url($spec_icons['currency']); ?>" width="14" alt="">
															<?php endif; ?>
														</p>
													<?php endif; ?>
													<ul class="post-meta mb-0 awaid-unit-card__meta">
														<?php if ($u_area !== '') : ?>
															<li class="post-comments">
																<?php if (!empty($spec_icons['area'])) : ?>
																	<img src="<?php echo esc_url($spec_icons['area']); ?>" class="dark-image" style="width: 20px;" alt="<?php esc_attr_e('Area', 'awaid-projects'); ?>">
																<?php endif; ?>
																<span class="me-1 fs-15 text-gray-800"><?php echo esc_html($u_area); ?> م²</span>
															</li>
														<?php endif; ?>
														<?php if ($u_bedrooms !== '') : ?>
															<li class="post-author">
																<?php if (!empty($spec_icons['bedrooms'])) : ?>
																	<img src="<?php echo esc_url($spec_icons['bedrooms']); ?>" class="dark-image" style="width: 20px;" alt="<?php esc_attr_e('Bedrooms', 'awaid-projects'); ?>">
																<?php endif; ?>
																<span class="me-1 fs-15 text-gray-800"><?php echo esc_html($u_bedrooms); ?></span>
															</li>
														<?php endif; ?>
														<?php if ($u_bathrooms !== '') : ?>
															<li class="post-date">
																<?php if (!empty($spec_icons['bathrooms'])) : ?>
																	<img src="<?php echo esc_url($spec_icons['bathrooms']); ?>" class="dark-image" style="width: 20px;" alt="<?php esc_attr_e('Bathrooms', 'awaid-projects'); ?>">
																<?php endif; ?>
																<span class="me-1 fs-15 text-gray-800"><?php echo esc_html($u_bathrooms); ?></span>
															</li>
														<?php endif; ?>
													</ul>
												</div>
											</article>
										</div>
									<?php endforeach; ?>
								</div>
								<!-- </section> -->
							</div>
						<?php endif; ?>

						<?php if ($features || $warranties) : ?>
							<div class="awaid-main-card">
								<!-- <section class="awaid-section awaid-section--muted awaid-section--inset"> -->
								<div class="awaid-split-inner-row<?php echo $features && $warranties ? ' awaid-split-inner-row--twocol' : ''; ?>">
									<?php if ($features) : ?>
										<div class="awaid-split-inner-col awaid-split-inner-col--features">
											<h2 class="awaid-section__title"><?php esc_html_e('المميزات', 'awaid-projects'); ?></h2>
											<div class="awaid-features-grid">
												<?php foreach ($features as $f) : ?>
													<?php
													$t = isset($f['title']) ? trim((string) $f['title']) : '';
													$icon = isset($f['icon']) ? trim((string) $f['icon']) : '';
													if ($t === '') {
														continue;
													}
													?>
													<div class="awaid-feature-tile">
														<?php if ($icon !== '') : ?>
															<i data-lucide="<?php echo esc_attr($icon); ?>" class="awaid-feature-tile__icon awaid-lucide-icon" aria-hidden="true"></i>
														<?php else : ?>
															<span class="awaid-feature-tile__dot" aria-hidden="true"></span>
														<?php endif; ?>
														<span class="awaid-feature-tile__text"><?php echo esc_html($t); ?></span>
													</div>
												<?php endforeach; ?>
											</div>
										</div>
									<?php endif; ?>
									<?php if ($warranties) : ?>
										<div class="awaid-split-inner-col awaid-split-inner-col--warranties">
											<h2 class="awaid-section__title"><?php esc_html_e('الضمانات', 'awaid-projects'); ?></h2>
											<div class="awaid-warranty-grid">
												<?php foreach ($warranties as $w) : ?>
													<?php
													$t = isset($w['title']) ? trim((string) $w['title']) : '';
													$p = isset($w['period']) ? trim((string) $w['period']) : '';
													$icon = isset($w['icon']) ? trim((string) $w['icon']) : '';
													if ($t === '' && $p === '') {
														continue;
													}
													?>
													<div class="awaid-warranty-card">
														<?php if ($icon !== '') : ?>
															<i data-lucide="<?php echo esc_attr($icon); ?>" class="awaid-warranty-card__icon awaid-lucide-icon" aria-hidden="true"></i>
														<?php endif; ?>
														<?php if ($t !== '') : ?>
															<h3 class="awaid-warranty-card__title"><?php echo esc_html($t); ?></h3>
														<?php endif; ?>
														<?php if ($p !== '') : ?>
															<p class="awaid-warranty-card__period"><?php echo esc_html($p); ?></p>
														<?php endif; ?>
													</div>
												<?php endforeach; ?>
											</div>
										</div>
									<?php endif; ?>
								</div>
								<!-- </section> -->
							</div>
						<?php endif; ?>

						<div class="awaid-main-card">
							<!-- <section class="awaid-section awaid-section--inset awaid-section--location"> -->
							<div class="awaid-location awaid-location--stack">
								<div>
									<h2 class="awaid-section__title"><?php esc_html_e('موقع المشروع', 'awaid-projects'); ?></h2>
									<?php if (!empty($d['location'])) : ?>
										<p class="awaid-location__address"><?php echo esc_html((string) $d['location']); ?></p>
									<?php endif; ?>
									<?php if ($map_lat !== null && $map_lng !== null) : ?>
										<div
											id="awaid-project-map"
											class="awaid-location-map"
											data-lat="<?php echo esc_attr((string) $map_lat); ?>"
											data-lng="<?php echo esc_attr((string) $map_lng); ?>"
											data-title="<?php echo esc_attr(get_the_title()); ?>"
											data-location="<?php echo esc_attr((string) ($d['location'] ?? '')); ?>"></div>
										<div class="awaid-location-map__actions">
											<a class="awaid-btn awaid-btn--primary awaid-location-map__btn" href="<?php echo esc_url('https://www.google.com/maps?q=' . rawurlencode($map_lat . ',' . $map_lng)); ?>" target="_blank" rel="noopener"><?php esc_html_e('فتح في خرائط Google', 'awaid-projects'); ?></a>
											<a class="awaid-btn awaid-btn--primary awaid-location-map__btn" href="<?php echo esc_url('https://www.google.com/maps/dir/?api=1&destination=' . rawurlencode($map_lat . ',' . $map_lng)); ?>" target="_blank" rel="noopener"><?php esc_html_e('الاتجاهات', 'awaid-projects'); ?></a>
										</div>
									<?php elseif ($map_url) : ?>
										<a class="awaid-btn awaid-btn--primary" href="<?php echo esc_url($map_url); ?>" target="_blank" rel="noopener"><?php esc_html_e('Directions', 'awaid-projects'); ?></a>
									<?php endif; ?>
								</div>
								<?php if ($nearby) : ?>
									<div class="awaid-nearby">
										<h3 class="awaid-nearby__title"><?php esc_html_e('المعالم القريبة', 'awaid-projects'); ?></h3>
										<ul class="awaid-nearby__list">
											<?php foreach ($nearby as $n) : ?>
												<?php
												$nm = isset($n['title']) ? trim((string) $n['title']) : '';
												$ds = isset($n['distance']) ? trim((string) $n['distance']) : '';
												$icon = isset($n['icon']) ? trim((string) $n['icon']) : '';
												if ($nm === '') {
													continue;
												}
												?>
												<li>
													<?php if ($icon !== '') : ?>
														<i data-lucide="<?php echo esc_attr($icon); ?>" class="awaid-nearby__icon awaid-lucide-icon" aria-hidden="true"></i>
													<?php endif; ?>
													<strong><?php echo esc_html($nm); ?></strong>
													<?php if ($ds !== '') : ?>
														<span class="awaid-nearby__dist"><?php echo esc_html($ds); ?></span>
													<?php endif; ?>
												</li>
											<?php endforeach; ?>
										</ul>
									</div>
								<?php endif; ?>
							</div>
							<!-- </section> -->
						</div>
					</div>

					<aside class="col-12 col-lg-3 awaid-split-sidebar">
						<?php $render_sidebar('awaid-sidebar--desktop'); ?>
					</aside>
				</div>
			</div>
		</section>
		<?php if ($has_lead_slides) : ?>
			<div class="awaid-lightbox" id="awaid-project-lightbox" data-awaid-lightbox hidden>
				<div class="awaid-lightbox__backdrop" data-awaid-lightbox-close tabindex="-1"></div>
				<div class="awaid-lightbox__dialog" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e('Enlarged image', 'awaid-projects'); ?>">
					<button type="button" class="awaid-lightbox__close" data-awaid-lightbox-close aria-label="<?php esc_attr_e('Close', 'awaid-projects'); ?>"></button>
					<?php if ($slide_count > 1) : ?>
						<button type="button" class="awaid-lightbox__arrow awaid-lightbox__arrow--prev" data-awaid-lightbox-prev aria-label="<?php esc_attr_e('Previous image', 'awaid-projects'); ?>"></button>
						<button type="button" class="awaid-lightbox__arrow awaid-lightbox__arrow--next" data-awaid-lightbox-next aria-label="<?php esc_attr_e('Next image', 'awaid-projects'); ?>"></button>
					<?php endif; ?>
					<img class="awaid-lightbox__img" src="" alt="" data-awaid-lightbox-img decoding="async">
				</div>
			</div>
		<?php endif; ?>
		<?php if ($units_modal_units) : ?>
			<script type="application/json" id="awaid-units-modal-data">
				<?php echo $units_modal_json; ?>
			</script>
			<div id="awaid-unit-modal" class="awaid-unit-modal" hidden data-awaid-unit-modal>
				<div class="awaid-unit-modal__backdrop" data-awaid-unit-modal-close tabindex="-1"></div>
				<div class="awaid-unit-modal__panel" role="dialog" aria-modal="true" aria-labelledby="awaid-unit-modal-title">
					<button type="button" class="awaid-unit-modal__close" data-awaid-unit-modal-close aria-label="<?php esc_attr_e('Close', 'awaid-projects'); ?>"><span aria-hidden="true">&times;</span></button>
					<div class="awaid-unit-modal__toolbar">
						<h2 id="awaid-unit-modal-title" class="awaid-unit-modal__toolbar-title"><?php esc_html_e('تفاصيل الوحدة', 'awaid-projects'); ?></h2>
					</div>
					<div class="awaid-unit-modal__body" id="awaid-unit-modal-body"></div>
				</div>
			</div>
		<?php endif; ?>
	</article>

<?php
endwhile;

get_footer();
