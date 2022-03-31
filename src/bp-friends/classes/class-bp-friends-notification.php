<?php
/**
 * BuddyBoss Connections Notification Class.
 *
 * @package BuddyBoss\Friends
 *
 * @since BuddyBoss [BBVERSION]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Set up the BP_Friends_Notification class.
 *
 * @since BuddyBoss [BBVERSION]
 */
class BP_Friends_Notification extends BP_Core_Notification_Abstract {

	/**
	 * Instance of this class.
	 *
	 * @since BuddyBoss [BBVERSION]
	 *
	 * @var object
	 */
	private static $instance = null;

	/**
	 * Get the instance of this class.
	 *
	 * @since BuddyBoss [BBVERSION]
	 *
	 * @return null|BP_Friends_Notification|Controller|object
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor method.
	 *
	 * @since BuddyBoss [BBVERSION]
	 */
	public function __construct() {
		// Initialize.
		$this->start();
	}

	/**
	 * Initialize all methods inside it.
	 *
	 * @since BuddyBoss [BBVERSION]
	 *
	 * @return mixed|void
	 */
	public function load() {
		$this->register_notification_group(
			'friends',
			esc_html__( 'Connections', 'buddyboss' ),
			esc_html__( 'Connections Notifications', 'buddyboss' ),
			22
		);

		$this->register_notification_for_friendship_request();
		$this->register_notification_for_friendship_accept();

		$this->register_notification_filter(
			esc_html__( 'Connection requests', 'buddyboss' ),
			array( 'bb_connections_new_request', 'bb_connections_request_accepted' ),
			45
		);
	}

	/**
	 * Register notification for user friendship request.
	 *
	 * @since BuddyBoss [BBVERSION]
	 */
	public function register_notification_for_friendship_request() {
		$this->register_notification_type(
			'bb_connections_new_request',
			esc_html__( 'You receive a new connection request', 'buddyboss' ),
			esc_html__( 'A member receives a new connection request', 'buddyboss' ),
			'friends'
		);

		$this->register_email_type(
			'friends-request',
			array(
				/* translators: do not remove {} brackets or translate its contents. */
				'email_title'         => __( '[{{{site.name}}}] New request to connect from {{initiator.name}}', 'buddyboss' ),
				/* translators: do not remove {} brackets or translate its contents. */
				'email_content'       => __( "<a href=\"{{{initiator.url}}}\">{{initiator.name}}</a> wants to add you as a connection.\n\n{{{member.card}}}\n\n<a href=\"{{{friend-requests.url}}}\">Click here</a> to manage this and all other pending requests.", 'buddyboss' ),
				/* translators: do not remove {} brackets or translate its contents. */
				'email_plain_content' => __( "{{initiator.name}} wants to add you as a connection.\n\nTo accept this request and manage all of your pending requests, visit: {{{friend-requests.url}}}\n\nTo view {{initiator.name}}'s profile, visit: {{{initiator.url}}}", 'buddyboss' ),
				'situation_label'     => __( 'A member recieves a new connection request', 'buddyboss' ),
				'unsubscribe_text'    => __( 'You will no longer receive emails when someone sends you an invitation to connect.', 'buddyboss' ),
			),
			'bb_connections_new_request'
		);

		$this->register_notification(
			'friends',
			'bb_connections_new_request',
			'bb_connections_new_request',
		);

		add_filter( 'bb_friends_bb_connections_new_request_notification', array( $this, 'bb_format_friends_notification' ), 10, 7 );
	}

	/**
	 * Register notification for friendship accept.
	 *
	 * @since BuddyBoss [BBVERSION]
	 */
	public function register_notification_for_friendship_accept() {
		$this->register_notification_type(
			'bb_connections_request_accepted',
			esc_html__( 'Your connection request is accepted', 'buddyboss' ),
			esc_html__( 'A member\'s connection request is accepted', 'buddyboss' ),
			'friends'
		);

		$this->register_email_type(
			'friends-request-accepted',
			array(
				/* translators: do not remove {} brackets or translate its contents. */
				'email_title'         => __( '[{{{site.name}}}] {{friend.name}} accepted your request to connect', 'buddyboss' ),
				/* translators: do not remove {} brackets or translate its contents. */
				'email_content'       => __( "<a href=\"{{{friendship.url}}}\">{{friend.name}}</a> accepted your request to connect.\n\n{{{member.card}}}", 'buddyboss' ),
				/* translators: do not remove {} brackets or translate its contents. */
				'email_plain_content' => __( "{{friend.name}} accepted your friend request.\n\nTo learn more about them, visit their profile: {{{friendship.url}}}", 'buddyboss' ),
				'situation_label'     => __( 'A member\'s connection request is accepted', 'buddyboss' ),
				'unsubscribe_text'    => __( 'You will no longer receive emails when someone accepts your invitation to connect.', 'buddyboss' ),
			),
			'bb_connections_request_accepted'
		);

		$this->register_notification(
			'friends',
			'bb_connections_request_accepted',
			'bb_connections_request_accepted',
		);

		add_filter( 'bb_friends_bb_connections_request_accepted_notification', array( $this, 'bb_format_friends_notification' ), 10, 7 );
	}

	/**
	 * Format the notifications.
	 *
	 * @since BuddyBoss [BBVERSION]
	 *
	 * @param string $content               Notification content.
	 * @param int    $item_id               Notification item ID.
	 * @param int    $secondary_item_id     Notification secondary item ID.
	 * @param int    $total_items           Number of notifications with the same action.
	 * @param string $component_action_name Canonical notification action.
	 * @param string $component_name        Notification component ID.
	 * @param int    $notification_id       Notification ID.
	 * @param string $screen                Notification Screen type.
	 *
	 * @return array
	 */
	public function format_notification( $content, $item_id, $secondary_item_id, $total_items, $component_action_name, $component_name, $notification_id, $screen ) {
		return $content;
	}

	/**
	 * Format friends notifications.
	 *
	 * @since BuddyBoss [BBVERSION]
	 *
	 * @param string $content               Notification content.
	 * @param int    $item_id               Notification item ID.
	 * @param int    $secondary_item_id     Notification secondary item ID.
	 * @param int    $total_items           Number of notifications with the same action.
	 * @param string $format                Format of return. Either 'string' or 'object'.
	 * @param int    $notification_id       Notification ID.
	 * @param string $screen                Notification Screen type.
	 *
	 * @return array
	 */
	public function bb_format_friends_notification( $content, $item_id, $secondary_item_id, $total_items, $format, $notification_id, $screen ) {

		$notification = bp_notifications_get_notification( $notification_id );

		// Friends request accepted.
		if ( ! empty( $notification ) && 'friends' === $notification->component_name && 'bb_connections_request_accepted' === $notification->component_action ) {

			$notification_link = trailingslashit( bp_loggedin_user_domain() . bp_get_friends_slug() . '/my-friends' );

			// Set up the string and the filter.
			if ( (int) $total_items > 1 ) {
				$text = sprintf(
				/* translators: total members count */
					esc_html__( '%d members accepted your connection requests', 'buddyboss' ),
					(int) $total_items
				);
				$amount = 'multiple';
			} else {
				$text = sprintf(
				/* translators: member name */
					esc_html__( '%s has accepted your connection request', 'buddyboss' ),
					bp_core_get_user_displayname( $item_id )
				);
				$amount = 'single';
			}

			$content = apply_filters(
				'bb_friends_' . $amount . '_' . $notification->component_action . '_notification',
				array(
					'link' => $notification_link,
					'text' => $text,
				),
				$notification,
				$text,
				$notification_link
			);
		}

		// Friends request sent.
		if ( ! empty( $notification ) && 'friends' === $notification->component_name && 'bb_connections_new_request' === $notification->component_action ) {

			$notification_link = bp_loggedin_user_domain() . bp_get_friends_slug() . '/requests/?new';

			// Set up the string and the filter.
			if ( (int) $total_items > 1 ) {
				$text = sprintf(
					/* translators: total number. */
					esc_html__( 'You have %d pending requests to connect', 'buddyboss' ),
					(int) $total_items
				);

				$amount = 'multiple';
			} else {
				$text = sprintf(
					/* translators: users display name. */
					esc_html__( '%s has sent you a connection request', 'buddyboss' ),
					bp_core_get_user_displayname( $item_id )
				);

				$amount = 'single';
			}

			$content = apply_filters(
				'bb_friends_' . $amount . '_' . $notification->component_action . '_notification',
				array(
					'link' => $notification_link,
					'text' => $text,
				),
				$notification,
				$text,
				$notification_link
			);
		}

		// Validate the return value & return if validated.
		if (
			! empty( $content ) &&
			is_array( $content ) &&
			isset( $content['text'] ) &&
			isset( $content['link'] )
		) {
			if ( 'string' === $format ) {
				if ( empty( $content['link'] ) ) {
					$content = esc_html( $content['text'] );
				} else {
					$content = '<a href="' . esc_url( $content['link'] ) . '">' . esc_html( $content['text'] ) . '</a>';
				}
			} else {
				$content = array(
					'text' => $content['text'],
					'link' => $content['link'],
				);
			}
		}

		return $content;
	}

}
