<?php
/**
 * Events Archive Template
 */

if ( ! defined( 'ABSPATH' ) ) exit;

get_header();
?>
<div class="kq-archive-container" style="max-width: 1200px; margin: 60px auto; padding: 0 20px;">
    <h1 style="font-size: 42px; font-weight: 800; margin-bottom: 50px; text-align: center;"><?php _e( 'All Events', 'kueue-events-core' ); ?></h1>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 30px;">
        <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); 
            $date = get_post_meta( get_the_ID(), '_kq_start_date', true );
            $venue_id = get_post_meta( get_the_ID(), '_kq_venue_id', true );
            $venue_name = $venue_id ? get_the_title($venue_id) : get_post_meta( get_the_ID(), '_kq_venue_name', true );
            ?>
            <div class="kq-event-card-v" style="background: #fff; border-radius: 20px; overflow: hidden; border: 1px solid #eee; transition: transform 0.3s;">
                <a href="<?php the_permalink(); ?>" style="text-decoration: none; color: inherit;">
                    <div style="height: 200px; background: #eee;">
                        <?php if ( has_post_thumbnail() ) the_post_thumbnail('medium_large', ['style'=>'width:100%; height:100%; object-fit:cover;']); ?>
                    </div>
                    <div style="padding: 25px;">
                        <span style="display: block; font-size: 13px; font-weight: 700; color: #ff3131; margin-bottom: 8px;"><?php echo esc_html($date); ?></span>
                        <h3 style="margin: 0 0 10px; font-size: 20px;"><?php the_title(); ?></h3>
                        <p style="font-size: 14px; color: #666;"><i class="fa fa-location-dot"></i> <?php echo esc_html($venue_name); ?></p>
                    </div>
                </a>
            </div>
        <?php endwhile; endif; ?>
    </div>
</div>
<?php
get_footer();
