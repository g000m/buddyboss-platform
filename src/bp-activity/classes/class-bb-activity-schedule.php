<?php
/**
 * BuddyBoss Activity Schedule Classes.
 *
 * @package BuddyBoss\Activity
 * @since BuddyBoss [BBVERSION]
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'BB_Activity_Schedule' ) ) {
	/**
	 * BuddyBoss Activity Schedule.
	 * Handles schedule posts.
	 *
	 * @since BuddyBoss [BBVERSION]
	 */
	class BB_Activity_Schedule {
		/**
		 * The single instance of the class.
		 *
		 * @since BuddyBoss [BBVERSION]
		 *
		 * @access private
		 * @var self
		 */
		private static $instance = null;

		/**
		 * Get the instance of this class.
		 *
		 * @since BuddyBoss [BBVERSION]
		 *
		 * @return Controller|BB_Reaction|null
		 */
		public static function instance() {

			if ( null === self::$instance ) {
				$class_name     = __CLASS__;
				self::$instance = new $class_name();
			}

			return self::$instance;
		}

		/**
		 * Constructor method.
		 *
		 * @since BuddyBoss [BBVERSION]
		 */
		public function __construct() {
			add_action( 'bp_activity_after_save', array( $this, 'bb_register_schedule_activity' ), 10, 1 );
			add_action( 'bb_activity_publish', array( $this, 'bb_check_and_publish_scheduled_activity' ) );
		}

		/**
		 * Schedule activity publish event.
		 *
		 * @since BuddyBoss [BBVERSION]
		 *
		 * @param array|object $activity The activity object or array.
		 *
		 * @return bool
		 */
		public function bb_register_schedule_activity( $activity ) {
			if ( empty( $activity->id ) || bb_get_activity_scheduled_status() !== $activity->status ) {
				return false;
			}

			if ( ! wp_next_scheduled( 'bb_activity_publish' ) ) {
				wp_schedule_event( time(), 'bb_schedule_1min', 'bb_activity_publish' );
				return true;
			}

			return false;
		}

		/**
		 * Get all the scheduled activities and publish it.
		 *
		 * @since BuddyBoss [BBVERSION]
		 *
		 * @return void
		 */
		public function bb_check_and_publish_scheduled_activity() {
			global $wpdb;
		
			$bp_prefix        = bp_core_get_table_prefix();
			$current_time     = bp_core_current_time();
			$scheduled_status = bb_get_activity_scheduled_status();
			$published_status = bb_get_activity_published_status();

			// Get all activities that are scheduled and past due.
			$activities = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT id FROM {$bp_prefix}bp_activity
					 WHERE status = %s AND date_recorded <= %s",
					$scheduled_status, $current_time
				)
			);

			foreach ( $activities as $scheduled_activity ) {
				$activity = new BP_Activity_Activity( $scheduled_activity->id );

				if ( $activity ) {

					// Publish the activity.
					$activity->status = $published_status;
					$activity->save();

					// Remove edited time from scheduled activities.
					bp_activity_delete_meta( $activity->id, '_is_edited' );

					$metas = bb_activity_get_metadata( $activity->id );

					// Publish the media.
					if ( ! empty( $metas['bp_media_ids'][0] ) ) {
						$media_ids = explode( ',', $metas['bp_media_ids'][0] );
						$this->bb_publish_schedule_activity_medias_and_documents( $media_ids, 'media' );
					}

					// Publish the video.
					if ( ! empty( $metas['bp_video_ids'][0] ) ) {
						$video_ids = explode( ',', $metas['bp_video_ids'][0] );
						$this->bb_publish_schedule_activity_medias_and_documents( $video_ids, 'video' );
					}

					// Publish the document.
					if ( ! empty( $metas['bp_document_ids'][0] ) ) {
						$document_ids = explode( ',', $metas['bp_document_ids'][0] );
						$this->bb_publish_schedule_activity_medias_and_documents( $document_ids, 'document' );
					}

					// Send mentioned notifications.
					add_filter( 'bp_activity_at_name_do_notifications', '__return_true' );

					if ( ! empty( $activity->item_id ) ) {
						bb_group_activity_at_name_send_emails( $activity->content, $activity->user_id, $activity->item_id, $activity->id );
						bb_subscription_send_subscribe_group_notifications( $activity->content, $activity->user_id, $activity->item_id, $activity->id );
					} else {
						bb_activity_at_name_send_emails( $activity->content, $activity->user_id, $activity->id );
					}

					bb_activity_send_email_to_following_post( $activity->content, $activity->user_id, $activity->id );
				}
			}
		}

		/**
		 * Publish scheduled activity media and individual media activities.
		 *
		 * @since BuddyBoss [BBVERSION]
		 *
		 * @param array  $media_ids media/video/document Ids.
		 * @param string $media_type Media type : 'media', 'video', 'document'.
		 */
		public function bb_publish_schedule_activity_medias_and_documents( $media_ids, $media_type = 'media' ) {
			global $wpdb;

			if ( ! empty( $media_ids ) ) {
				$bp_prefix  = bp_core_get_table_prefix();
				$table_name = "{$bp_prefix}bp_media";
				if ( 'document' === $media_type ) {
					$table_name = "{$bp_prefix}bp_document";
				}

				// Check table exists.
				$table_exists = $wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'" );
				if ( $table_exists ) {
					foreach ( $media_ids as $media_id ) {
						$wpdb->query( $wpdb->prepare( "UPDATE {$table_name} SET status = 'published' WHERE id = %d", $media_id ) );

						// Also update the individual medias/videos/document activity.
						if ( count( $media_ids ) > 1 ) {
							$media_activity_id      = $wpdb->get_var( $wpdb->prepare( "SELECT activity_id FROM {$table_name} WHERE id = %d", $media_id ) );
							$media_activity         = new BP_Activity_Activity( $media_activity_id );
							$media_activity->status = bb_get_activity_published_status();
							$media_activity->save();
						}
					}
				}
			}
		}
	}
}
