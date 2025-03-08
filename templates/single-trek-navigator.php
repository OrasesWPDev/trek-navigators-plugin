<?php
/**
 * The template for displaying single Trek Navigator posts
 *
 * @package Trek_Navigators_Plugin
 */

get_header();
?>

    <main id="main" class="trek-navigators-single">
        <?php while (have_posts()) : the_post(); ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class('trek-navigators-article'); ?>>
                <!-- Header Block - Will contain title and breadcrumbs -->
                <div class="trek-navigators-section-wrapper trek-navigators-navigator-header">
                    <?php echo do_shortcode('[block id="trek-navigator-header"]'); ?>
                </div>

                <div class="container">
                    <!-- Header Image Section -->
                    <?php if (function_exists('get_field') && $header_image = get_field('navigator_header_image')) : ?>
                        <div class="trek-navigators-header-image-wrapper">
                            <img src="<?php echo esc_url($header_image['url']); ?>"
                                 alt="<?php echo esc_attr(get_the_title()); ?>"
                                 class="trek-navigators-header-image" />
                        </div>
                    <?php endif; ?>

                    <!-- Navigator Start Date -->
                    <?php if (function_exists('get_field') && $start_date = get_field('navigator_start_date')) : ?>
                        <div class="trek-navigators-start-date-wrapper">
                            <span class="trek-navigators-start-date"><?php echo esc_html($start_date); ?></span>
                        </div>
                    <?php endif; ?>

                    <!-- Main Content from Editor -->
                    <div class="trek-navigators-main-content">
                        <?php the_content(); ?>
                    </div>

                    <!-- Two Column Layout for Remaining Content -->
                    <div class="trek-navigators-two-column-layout">
                        <!-- Left Column (Video and Content Sections) -->
                        <div class="trek-navigators-column trek-navigators-column-left">
                            <!-- Video Embed -->
                            <?php if (function_exists('get_field') && $video = get_field('navigator_video_embed')) : ?>
                                <div class="trek-navigators-video-wrapper">
                                    <div class="trek-navigators-video-responsive">
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
                                    <div class="trek-navigators-content-section">
                                        <h4 class="trek-navigators-section-title"><?php echo esc_html($section_title); ?></h4>
                                        <div class="trek-navigators-section-content">
                                            <?php echo $section_content; ?>
                                        </div>
                                    </div>
                                <?php
                                endwhile;
                            endif;
                            ?>
                        </div>

                        <!-- Right Column (Badges, Digital Badges URL, More About) -->
                        <div class="trek-navigators-column trek-navigators-column-right">
                            <!-- Favorite PTCB Badges -->
                            <?php if (function_exists('get_field') && $badges = get_field('navigator_favorite_badges')) : ?>
                                <div class="trek-navigators-badges-wrapper">
                                    <img src="<?php echo esc_url($badges['url']); ?>"
                                         alt="Favorite PTCB Badges"
                                         class="trek-navigators-badges-image" />
                                </div>
                            <?php endif; ?>

                            <!-- Digital Badges URL -->
                            <?php if (function_exists('get_field') && $badges_url = get_field('navigator_digital_badges_url')) : ?>
                                <div class="trek-navigators-digital-badges-wrapper">
                                    <a href="<?php echo esc_url($badges_url); ?>"
                                       target="_blank"
                                       class="trek-navigators-digital-badges-button">
                                        VIEW ALL DIGITAL BADGES
                                    </a>
                                </div>
                            <?php endif; ?>

                            <!-- More About Image -->
                            <?php if (function_exists('get_field') && $more_about = get_field('navigator_more_about_image')) : ?>
                                <div class="trek-navigators-more-about-wrapper">
                                    <img src="<?php echo esc_url($more_about['url']); ?>"
                                         alt="More About"
                                         class="trek-navigators-more-about-image" />
                                </div>
                            <?php endif; ?>

                            <!-- Learn More Trek Image (Hard-coded) -->
                            <div class="trek-navigators-learn-more-trek-wrapper">
                                <a href="<?php echo esc_url(home_url('/techtrek')); ?>" class="trek-navigators-learn-more-trek-link">
                                    <img src="<?php echo esc_url(home_url('/wp-content/uploads/2025/03/LearnMoreTrek.webp')); ?>"
                                         alt="Learn More About TechTrek"
                                         class="trek-navigators-learn-more-trek-image" />
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </article>
        <?php endwhile; ?>
    </main>

<?php get_footer(); ?>