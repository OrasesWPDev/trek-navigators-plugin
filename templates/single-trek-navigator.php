<?php
/**
 * The template for displaying single Trek Navigator posts
 *
 * @package Trek_Navigators_Plugin
 */

get_header();
?>

    <main id="main" class="trek-navigator-single">
        <?php while (have_posts()) : the_post(); ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class('trek-navigator-article'); ?>>
                <!-- Header Block - Will contain title and breadcrumbs -->
                <div class="section-wrapper navigator-header">
                    <?php echo do_shortcode('[block id="trek-navigator-header"]'); ?>
                </div>

                <div class="container">
                    <!-- Header Image Section -->
                    <?php if (function_exists('get_field') && $header_image = get_field('navigator_header_image')) : ?>
                        <div class="navigator-header-image-wrapper">
                            <img src="<?php echo esc_url($header_image['url']); ?>"
                                 alt="<?php echo esc_attr(get_the_title()); ?>"
                                 class="navigator-header-image" />
                        </div>
                    <?php endif; ?>

                    <!-- Navigator Start Date -->
                    <?php if (function_exists('get_field') && $start_date = get_field('navigator_start_date')) : ?>
                        <div class="navigator-start-date-wrapper">
                            <span class="navigator-start-date"><?php echo esc_html($start_date); ?></span>
                        </div>
                    <?php endif; ?>

                    <!-- Main Content from Editor -->
                    <div class="navigator-main-content">
                        <?php the_content(); ?>
                    </div>

                    <!-- Two Column Layout for Remaining Content -->
                    <div class="navigator-two-column-layout">
                        <!-- Left Column (Video and Content Sections) -->
                        <div class="navigator-column navigator-column-left">
                            <!-- Video Embed -->
                            <?php if (function_exists('get_field') && $video = get_field('navigator_video_embed')) : ?>
                                <div class="navigator-video-wrapper">
                                    <div class="navigator-video-responsive">
                                        <?php echo $video; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Content Sections -->
                            <?php
                            if (function_exists('get_field') && have_rows('navigator_content_sections')) :
                                while (have_rows('navigator_content_sections')) : the_row();
                                    $section_title = get_sub_field('section_title');
                                    $section_content = get_sub_field('section_content');
                                    ?>
                                    <div class="navigator-content-section">
                                        <h4 class="navigator-section-title"><?php echo esc_html($section_title); ?></h4>
                                        <div class="navigator-section-content">
                                            <?php echo $section_content; ?>
                                        </div>
                                    </div>
                                <?php
                                endwhile;
                            endif;
                            ?>
                        </div>

                        <!-- Right Column (Badges, Digital Badges URL, More About) -->
                        <div class="navigator-column navigator-column-right">
                            <!-- Favorite PTCB Badges -->
                            <?php if (function_exists('get_field') && $badges = get_field('navigator_favorite_badges')) : ?>
                                <div class="navigator-badges-wrapper">
                                    <img src="<?php echo esc_url($badges['url']); ?>"
                                         alt="Favorite PTCB Badges"
                                         class="navigator-badges-image" />
                                </div>
                            <?php endif; ?>

                            <!-- Digital Badges URL -->
                            <?php if (function_exists('get_field') && $badges_url = get_field('navigator_digital_badges_url')) : ?>
                                <div class="navigator-digital-badges-wrapper">
                                    <a href="<?php echo esc_url($badges_url); ?>"
                                       target="_blank"
                                       class="navigator-digital-badges-button">
                                        VIEW ALL DIGITAL BADGES
                                    </a>
                                </div>
                            <?php endif; ?>

                            <!-- More About Image -->
                            <?php if (function_exists('get_field') && $more_about = get_field('navigator_more_about_image')) : ?>
                                <div class="navigator-more-about-wrapper">
                                    <img src="<?php echo esc_url($more_about['url']); ?>"
                                         alt="More About"
                                         class="navigator-more-about-image" />
                                </div>
                            <?php endif; ?>

                            <!-- Learn More Trek Image (Hard-coded) -->
                            <div class="navigator-learn-more-trek-wrapper">
                                <a href="<?php echo esc_url(home_url('/techtrek')); ?>" class="navigator-learn-more-trek-link">
                                    <img src="<?php echo esc_url(home_url('/wp-content/uploads/2025/03/LearnMoreTrek.webp')); ?>"
                                         alt="Learn More About TechTrek"
                                         class="navigator-learn-more-trek-image" />
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </article>
        <?php endwhile; ?>
    </main>

<?php get_footer(); ?>