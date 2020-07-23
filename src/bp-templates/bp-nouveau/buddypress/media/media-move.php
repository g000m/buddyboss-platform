<?php

/**
 * BuddyBoss - Document Activity Folder Move
 *
 * @since BuddyBoss 1.4.0
 * @package BuddyBoss\Core
 */

?>
<div class="bp-media-move-file" style="display: none;" id="bp-media-move-file-<?php bp_document_id(); ?>" data-activity-id="">
	<transition name="modal">
		<div class="modal-mask bb-white bbm-model-wrap">
			<div class="modal-wrapper">
				<div id="boss-media-create-album-popup" class="modal-container has-folderlocationUI">
					<header class="bb-model-header">
						<h4><span class="target_name"><?php esc_html_e( 'Move Photo to...', 'buddyboss' ); ?></span></h4>
					</header>
					<?php
						$ul = bp_document_user_document_folder_tree_view_li_html( bp_loggedin_user_id() );
					?>
					<div class="bb-field-wrap">
						<?php bp_get_template_part( 'media/location-move' ); ?>
						<?php bp_get_template_part( 'media/media-create-album' ); ?>
					</div>
					<footer class="bb-model-footer">
						<a href="#" class="bp-media-open-create-popup-folder"><?php esc_html_e( 'Create new album', 'buddyboss' ); ?></a>
						<a class="ac-media-close-button" href="#"><?php esc_html_e( 'Cancel', 'buddyboss' ); ?></a>
						<a class="button bp-media-move bp-media-move-activity" id="<?php bp_document_id(); ?>" href="#"><?php esc_html_e( 'Move', 'buddyboss' ); ?></a>
					</footer>
				</div>
			</div>
		</div>
	</transition>
</div>
