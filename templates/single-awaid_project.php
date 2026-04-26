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

	$features   = is_array($d['features'] ?? null) ? $d['features'] : [];
	$warranties = is_array($d['warranties'] ?? null) ? $d['warranties'] : [];
	$nearby     = is_array($d['nearby'] ?? null) ? $d['nearby'] : [];
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
								<div class="awaid-unit-filters" role="tablist" aria-label="<?php esc_attr_e('Filter units', 'awaid-projects'); ?>">
									<button type="button" class="awaid-pill awaid-unit-filter is-active" data-filter="all" aria-pressed="true"><?php esc_html_e('All', 'awaid-projects'); ?></button>
									<button type="button" class="awaid-pill awaid-unit-filter" data-filter="available" aria-pressed="false"><?php esc_html_e('Available', 'awaid-projects'); ?></button>
									<button type="button" class="awaid-pill awaid-unit-filter" data-filter="reserved" aria-pressed="false"><?php esc_html_e('Reserved', 'awaid-projects'); ?></button>
									<button type="button" class="awaid-pill awaid-unit-filter" data-filter="sold" aria-pressed="false"><?php esc_html_e('Sold', 'awaid-projects'); ?></button>
								</div>
								<div class="awaid-units-grid">
									<?php foreach ($units as $unit) : ?>
										<?php
										$code   = isset($unit['code']) ? (string) $unit['code'] : '';
										if ($code === '') {
											continue;
										}
										$status  = isset($unit['status']) ? (string) $unit['status'] : 'available';
										$badge   = awaid_project_status_label($status);
										$is_sold = $status === 'sold';
										?>
										<article class="awaid-unit-card<?php echo $is_sold ? ' awaid-unit-card--sold' : ''; ?>" data-status="<?php echo esc_attr($status); ?>">
											<div class="awaid-unit-card__head">
												<span class="awaid-unit-card__code"><?php echo esc_html($code); ?></span>
												<?php if ($is_sold) : ?>
													<span class="awaid-badge awaid-badge--sold"><?php echo esc_html($badge); ?></span>
												<?php endif; ?>
											</div>
											<?php if (!empty($unit['type'])) : ?>
												<p class="awaid-unit-card__type"><?php echo esc_html((string) $unit['type']); ?></p>
											<?php endif; ?>
											<ul class="awaid-unit-card__meta">
												<?php if (!empty($unit['price'])) : ?>
													<li><span class="awaid-meta-label"><?php esc_html_e('Price', 'awaid-projects'); ?></span> <?php echo esc_html((string) $unit['price']); ?></li>
												<?php endif; ?>
												<?php if (!empty($unit['area'])) : ?>
													<li><span class="awaid-meta-label"><?php esc_html_e('Area', 'awaid-projects'); ?></span> <?php echo esc_html((string) $unit['area']); ?> m²</li>
												<?php endif; ?>
												<?php if (!empty($unit['bedrooms'])) : ?>
													<li><span class="awaid-meta-label"><?php esc_html_e('Bedrooms', 'awaid-projects'); ?></span> <?php echo esc_html((string) $unit['bedrooms']); ?></li>
												<?php endif; ?>
												<?php if (!empty($unit['bathrooms'])) : ?>
													<li><span class="awaid-meta-label"><?php esc_html_e('Bathrooms', 'awaid-projects'); ?></span> <?php echo esc_html((string) $unit['bathrooms']); ?></li>
												<?php endif; ?>
												<?php if (!$is_sold) : ?>
													<li><span class="awaid-meta-label"><?php esc_html_e('Status', 'awaid-projects'); ?></span> <?php echo esc_html($badge); ?></li>
												<?php endif; ?>
											</ul>
										</article>
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
											<h2 class="awaid-section__title"><?php esc_html_e('Features', 'awaid-projects'); ?></h2>
											<div class="awaid-features-grid">
												<?php foreach ($features as $f) : ?>
													<?php
													$t = isset($f['title']) ? trim((string) $f['title']) : '';
													if ($t === '') {
														continue;
													}
													?>
													<div class="awaid-feature-tile">
														<span class="awaid-feature-tile__dot" aria-hidden="true"></span>
														<span class="awaid-feature-tile__text"><?php echo esc_html($t); ?></span>
													</div>
												<?php endforeach; ?>
											</div>
										</div>
									<?php endif; ?>
									<?php if ($warranties) : ?>
										<div class="awaid-split-inner-col awaid-split-inner-col--warranties">
											<h2 class="awaid-section__title"><?php esc_html_e('Warranties', 'awaid-projects'); ?></h2>
											<div class="awaid-warranty-grid">
												<?php foreach ($warranties as $w) : ?>
													<?php
													$t = isset($w['title']) ? trim((string) $w['title']) : '';
													$p = isset($w['period']) ? trim((string) $w['period']) : '';
													if ($t === '' && $p === '') {
														continue;
													}
													?>
													<div class="awaid-warranty-card">
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
									<h2 class="awaid-section__title"><?php esc_html_e('Project location', 'awaid-projects'); ?></h2>
									<?php if (!empty($d['location'])) : ?>
										<p class="awaid-location__address"><?php echo esc_html((string) $d['location']); ?></p>
									<?php endif; ?>
									<?php if ($map_url) : ?>
										<a class="awaid-btn awaid-btn--primary" href="<?php echo esc_url($map_url); ?>" target="_blank" rel="noopener"><?php esc_html_e('Directions', 'awaid-projects'); ?></a>
									<?php endif; ?>
								</div>
								<?php if ($nearby) : ?>
									<div class="awaid-nearby">
										<h3 class="awaid-nearby__title"><?php esc_html_e('Nearby', 'awaid-projects'); ?></h3>
										<ul class="awaid-nearby__list">
											<?php foreach ($nearby as $n) : ?>
												<?php
												$nm = isset($n['name']) ? trim((string) $n['name']) : '';
												$ds = isset($n['distance']) ? trim((string) $n['distance']) : '';
												if ($nm === '' && $ds === '') {
													continue;
												}
												?>
												<li>
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
	</article>

<?php
endwhile;

get_footer();
