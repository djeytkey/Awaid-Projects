<?php
/**
 * Single project template (plugin).
 */

if (!defined('ABSPATH')) {
	exit;
}

get_header();

while (have_posts()) :
	the_post();

	$d       = Awaid_Projects_Meta::get_all(get_the_ID());
	$img_id  = get_post_thumbnail_id();
	$img_url = $img_id ? wp_get_attachment_image_url($img_id, 'full') : '';
	$brochure_id = (int) ($d['brochure_id'] ?? 0);
	$brochure_url = $brochure_id ? wp_get_attachment_url($brochure_id) : '';
	$map_url = isset($d['map_url']) ? (string) $d['map_url'] : '';

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
	?>

<article id="post-<?php the_ID(); ?>" <?php post_class($wrap_classes); ?>>
	<header class="awaid-hero"<?php echo $img_url ? ' style="' . esc_attr(sprintf('--awaid-hero-image:url(%s)', esc_url($img_url))) . '"' : ''; ?>>
		<div class="awaid-hero__inner">
			<div class="awaid-hero__crumb">
				<a href="<?php echo esc_url(get_post_type_archive_link('awaid_project')); ?>"><?php esc_html_e('Projects', 'awaid-projects'); ?></a>
			</div>
			<h1 class="awaid-hero__title"><?php the_title(); ?></h1>
			<?php if (!empty($d['location'])) : ?>
				<p class="awaid-hero__location"><?php echo esc_html((string) $d['location']); ?></p>
			<?php endif; ?>
			<div class="awaid-hero__actions">
				<?php if ($brochure_url) : ?>
					<a class="awaid-btn awaid-btn--ghost" href="<?php echo esc_url($brochure_url); ?>" target="_blank" rel="noopener"><?php esc_html_e('Download brochure', 'awaid-projects'); ?></a>
				<?php endif; ?>
				<?php if ($map_url) : ?>
					<a class="awaid-btn awaid-btn--primary" href="<?php echo esc_url($map_url); ?>" target="_blank" rel="noopener"><?php esc_html_e('Open in Google Maps', 'awaid-projects'); ?></a>
				<?php endif; ?>
			</div>
		</div>
	</header>

	<section class="awaid-strip">
		<div class="awaid-container awaid-strip__grid">
			<?php if (!empty($d['developer'])) : ?>
				<div class="awaid-strip__item">
					<span class="awaid-strip__label"><?php esc_html_e('Developer', 'awaid-projects'); ?></span>
					<span class="awaid-strip__value"><?php echo esc_html((string) $d['developer']); ?></span>
				</div>
			<?php endif; ?>
			<?php if ($area_range !== '') : ?>
				<div class="awaid-strip__item">
					<span class="awaid-strip__label"><?php esc_html_e('Area', 'awaid-projects'); ?></span>
					<span class="awaid-strip__value"><?php echo esc_html($area_range); ?></span>
				</div>
			<?php endif; ?>
			<?php if (!empty($d['bedrooms'])) : ?>
				<div class="awaid-strip__item">
					<span class="awaid-strip__label"><?php esc_html_e('Bedrooms', 'awaid-projects'); ?></span>
					<span class="awaid-strip__value"><?php echo esc_html((string) $d['bedrooms']); ?></span>
				</div>
			<?php endif; ?>
			<?php if (!empty($d['bathrooms'])) : ?>
				<div class="awaid-strip__item">
					<span class="awaid-strip__label"><?php esc_html_e('Bathrooms', 'awaid-projects'); ?></span>
					<span class="awaid-strip__value"><?php echo esc_html((string) $d['bathrooms']); ?></span>
				</div>
			<?php endif; ?>
			<?php if (!empty($d['floors'])) : ?>
				<div class="awaid-strip__item">
					<span class="awaid-strip__label"><?php esc_html_e('Floors', 'awaid-projects'); ?></span>
					<span class="awaid-strip__value"><?php echo esc_html((string) $d['floors']); ?></span>
				</div>
			<?php endif; ?>
			<?php if (!empty($d['license'])) : ?>
				<div class="awaid-strip__item">
					<span class="awaid-strip__label"><?php esc_html_e('Ad license', 'awaid-projects'); ?></span>
					<span class="awaid-strip__value"><?php echo esc_html((string) $d['license']); ?></span>
				</div>
			<?php endif; ?>
			<?php if (!empty($d['listing_date'])) : ?>
				<div class="awaid-strip__item">
					<span class="awaid-strip__label"><?php esc_html_e('Listing date', 'awaid-projects'); ?></span>
					<span class="awaid-strip__value"><?php echo esc_html((string) $d['listing_date']); ?></span>
				</div>
			<?php endif; ?>
			<?php if ($price_range !== '') : ?>
				<div class="awaid-strip__item awaid-strip__item--wide">
					<span class="awaid-strip__label"><?php esc_html_e('Price range', 'awaid-projects'); ?></span>
					<span class="awaid-strip__value"><?php echo esc_html($price_range); ?></span>
				</div>
			<?php endif; ?>
		</div>
	</section>

	<?php if (get_the_content()) : ?>
		<section class="awaid-section awaid-prose">
			<div class="awaid-container">
				<div class="awaid-entry-content">
					<?php the_content(); ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php if ($units) : ?>
		<section class="awaid-section" id="awaid-units">
			<div class="awaid-container">
				<h2 class="awaid-section__title"><?php esc_html_e('Units', 'awaid-projects'); ?></h2>
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
						$status = isset($unit['status']) ? (string) $unit['status'] : 'available';
						$badge  = awaid_project_status_label($status);
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
			</div>
		</section>
	<?php endif; ?>

	<?php if ($features) : ?>
		<section class="awaid-section awaid-section--muted">
			<div class="awaid-container">
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
		</section>
	<?php endif; ?>

	<?php if ($warranties) : ?>
		<section class="awaid-section">
			<div class="awaid-container">
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
		</section>
	<?php endif; ?>

	<section class="awaid-section awaid-section--location">
		<div class="awaid-container awaid-location">
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
	</section>
</article>

	<?php
endwhile;

get_footer();
