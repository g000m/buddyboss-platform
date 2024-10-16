<?php
/**
 * The template for schedule activity loop.
 *
 * This template can be overridden by copying it to yourtheme/buddypress/schedule-activity/schedule-activity-loop.php.
 *
 * @since   BuddyBoss [BBVERSION]
 * @version 1.0.0
 */

bp_nouveau_before_loop();
$args = array(
	'sort' => 'ASC',
);

if ( isset( $_POST['status'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
	$args['status'] = sanitize_text_field( $_POST['status'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
}

$activity_schedule_args = bp_parse_args(
	bp_ajax_querystring( 'activity' ),
	$args,
	'activity_schedule_args'
);

$activity_schedule_args['user_id'] = bp_loggedin_user_id();
$activity_schedule_args['scope']   = '';
if ( bp_has_activities( $activity_schedule_args ) ) :

	if ( empty( $_POST['page'] ) || 1 === (int) $_POST['page'] ) : // phpcs:ignore WordPress.Security.NonceVerification.Missing
		?>
		<ul class="activity-list item-list bp-list">
		<?php
	endif;

	while ( bp_activities() ) :
		bp_the_activity();
		bp_get_template_part( 'schedule-activity/entry' );
	endwhile;

	if ( bp_activity_has_more_items() ) :
		?>
		<li class="load-more">
			<a class="button outline" href="<?php bp_activity_load_more_link(); ?>"><?php esc_html_e( 'Load More', 'buddyboss' ); ?></a>
		</li>
		<?php
	endif;

	if ( empty( $_POST['page'] ) || 1 === (int) $_POST['page'] ) : // phpcs:ignore WordPress.Security.NonceVerification.Missing
		?>
		</ul>
		<?php
	endif;

else :
	bp_nouveau_user_feedback( 'activity-loop-none' );
endif;

bp_nouveau_after_loop();
