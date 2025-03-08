<?php
/**
 * The template for displaying Trek Navigator archives
 *
 * @package Trek_Navigators_Plugin
 */

get_header();
?>

    <main id="main" class="trek-navigators-archive">
        <div class="container">
            <div class="trek-navigators-grid-container">
                <?php if (have_posts()) : ?>
                    <div class="trek-navigators-grid">
                        <?php while (have_posts()) : the_post(); ?>
                            <div class="trek-navigators-grid-item">
                                <a href="<?php the_permalink(); ?>" class="trek-navigators-grid-link">
                                    <?php if (has_post_thumbnail()) : ?>
                                        <div class="trek-navigators-grid-image-wrapper">
                                            <?php
                                            // Use medium size but we'll control dimensions with CSS
                                            the_post_thumbnail('medium', array(
                                                'class' => 'trek-navigators-grid-image',
                                                'alt' => get_the_title()
                                            ));
                                            ?>
                                        </div>
                                    <?php else : ?>
                                        <div class="trek-navigators-grid-image-wrapper trek-navigators-no-image">
                                            <div class="trek-navigators-placeholder">
                                                <?php echo esc_html(get_the_title()); ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </a>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else : ?>
                    <p class="trek-navigators-none"><?php _e('No Trek Navigators found.', 'trek-navigators'); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </main>

<?php get_footer(); ?>