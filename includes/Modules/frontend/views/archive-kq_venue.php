<?php
/**
 * Venues Archive Template
 */

if ( ! defined( 'ABSPATH' ) ) exit;

get_header();
?>
<div class="kq-archive-container" style="max-width: 1200px; margin: 60px auto; padding: 0 20px;">
    <h1 style="font-size: 42px; font-weight: 800; margin-bottom: 50px; text-align: center;"><?php _e( 'Our Venues', 'kueue-events-core' ); ?></h1>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 40px;">
        <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); 
            $city = get_post_meta( get_the_ID(), '_kq_venue_city', true );
            $country = get_post_meta( get_the_ID(), '_kq_venue_country', true );
            ?>
            <div class="kq-venue-card-v" style="background: #fff; border-radius: 20px; overflow: hidden; border: 1px solid #eee; transition: all 0.3s; text-align: center; padding-bottom: 30px;">
                <a href="<?php the_permalink(); ?>" style="text-decoration: none; color: inherit;">
                    <div style="height: 250px; background: #eee; margin-bottom: 20px;">
                        <?php if ( has_post_thumbnail() ) the_post_thumbnail('medium_large', ['style'=>'width:100%; height:100%; object-fit:cover;']); ?>
                    </div>
                    <h3 style="margin: 0 0 10px; font-size: 24px; font-weight: 800;"><?php the_title(); ?></h3>
                    <p style="font-size: 16px; color: #666;"><?php echo esc_html($city); ?>, <?php echo esc_html($country); ?></p>
                    <span style="color: #ff3131; font-weight: 700; font-size: 14px; text-transform: uppercase; margin-top: 15px; display: inline-block;">Explore Venue <i class="fa fa-arrow-right"></i></span>
                </a>
            </div>
        <?php endwhile; endif; ?>
    </div>
</div>
<?php
get_footer();
