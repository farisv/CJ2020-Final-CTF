<?php
/**
 * Sample template for displaying single events, derived from the Twenty Ten theme. Eliminates the prominent display of post date, which may tend to clash with the display of event dates.
 *
 * @package WordPress
 * @subpackage Twenty_Ten
 * @since Twenty Ten 1.0
 */

get_header();?>

		<div id="container">
			<div id="content" role="main">

<?php if ( have_posts() ) while ( have_posts() ) : the_post();?>

				<div id="nav-above" class="navigation">
					<div class="nav-previous"><?php previous_post_link( '%link', '<span class="meta-nav">' . _x( '&larr;', 'Previous post link', 'twentyten' ) . '</span> %title' );?></div>
					<div class="nav-next"><?php next_post_link( '%link', '%title <span class="meta-nav">' . _x( '&rarr;', 'Next post link', 'twentyten' ) . '</span>' );?></div>
				</div><!-- #nav-above -->

				<div id="rsvpmaker-<?php the_ID();?>" <?php post_class();?>>
					<h1 class="rsvpmaker-entry-title"><?php the_title();?></h1>

					<div class="rsvpmaker-entry-content">
						<?php the_content();?>
						<?php wp_link_pages( array( 'before' => '<div class="page-link">' . __( 'Pages:', 'twentyten' ), 'after' => '</div>' ) );?>
					</div><!-- .entry-content -->


					<div class="rsvpmaker-entry-utility">
						<?php twentyten_posted_in();?>
						<?php edit_post_link( __( 'Edit', 'twentyten' ), '<span class="edit-link">', '</span>' );?>
					</div><!-- .entry-utility -->
				</div><!-- #post-## -->

				<div id="nav-below" class="navigation">
					<div class="nav-previous"><?php previous_post_link( '%link', '<span class="meta-nav">' . _x( '&larr;', 'Previous post link', 'twentyten' ) . '</span> %title' );?></div>
					<div class="nav-next"><?php next_post_link( '%link', '%title <span class="meta-nav">' . _x( '&rarr;', 'Next post link', 'twentyten' ) . '</span>' );?></div>
				</div><!-- #nav-below -->

<?php endwhile; // end of the loop. ;?>

			</div><!-- #content -->
		</div><!-- #container -->

<?php get_sidebar();?>
<?php get_footer();?>
