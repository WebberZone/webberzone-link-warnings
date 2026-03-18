<?php
/**
 * Sidebar
 *
 * @package WebberZone\Link_Warnings
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="postbox-container">
	<a href="https://wzn.io/donate-wz" target="_blank" rel="noopener noreferrer"><img src="<?php echo esc_url( WZLW_PLUGIN_URL . 'includes/admin/images/support.webp' ); ?>" alt="<?php esc_attr_e( 'Support WebberZone Link Warnings', 'webberzone-link-warnings' ); ?>" style="max-width: 100%; height: auto;"></a>

	<div id="qlinksdiv" class="postbox meta-box-sortables">
		<h2 class="metabox-holder"><span><?php esc_html_e( 'Quick links', 'webberzone-link-warnings' ); ?></span></h2>

		<div class="inside">
			<div id="quick-links">
				<ul class="subsub">
					<li>
						<a href="https://webberzone.com/plugins/webberzone-link-warnings/" target="_blank"><?php esc_html_e( 'WebberZone Link Warnings homepage', 'webberzone-link-warnings' ); ?></a>
					</li>

					<li>
						<a href="https://webberzone.com/support/product/webberzone-link-warnings/" target="_blank"><?php esc_html_e( 'Knowledge Base', 'webberzone-link-warnings' ); ?></a>
					</li>

					<li>
						<a href="https://wordpress.org/support/plugin/webberzone-link-warnings/" target="_blank"><?php esc_html_e( 'Support', 'webberzone-link-warnings' ); ?></a>
					</li>

					<li>
						<a href="https://wordpress.org/support/plugin/webberzone-link-warnings/reviews/" target="_blank"><?php esc_html_e( 'Reviews', 'webberzone-link-warnings' ); ?></a>
					</li>

					<li>
						<a href="https://github.com/WebberZone/webberzone-link-warnings" target="_blank"><?php esc_html_e( 'Github repository', 'webberzone-link-warnings' ); ?></a>
					</li>

					<li>
						<a href="https://ajaydsouza.com/" target="_blank"><?php esc_html_e( "Ajay's blog", 'webberzone-link-warnings' ); ?></a>
					</li>
				</ul>
			</div>
		</div><!-- /.inside -->
	</div><!-- /.postbox -->

	<div id="pluginsdiv" class="postbox meta-box-sortables">
		<h2 class="metabox-holder"><span><?php esc_html_e( 'WebberZone plugins', 'webberzone-link-warnings' ); ?></span></h2>

		<div class="inside">
			<div id="quick-links">
				<ul class="subsub">
					<li><a href="https://webberzone.com/plugins/top-10/" target="_blank">Top 10</a></li>
					<li><a href="https://webberzone.com/plugins/contextual-related-posts/" target="_blank">Contextual Related Posts</a></li>
					<li><a href="https://webberzone.com/plugins/better-search/" target="_blank">Better Search</a></li>
					<li><a href="https://webberzone.com/plugins/knowledgebase/" target="_blank">Knowledge Base</a></li>
					<li><a href="https://webberzone.com/plugins/add-to-all/" target="_blank">WebberZone Snippetz</a></li>
					<li><a href="https://webberzone.com/webberzone-followed-posts/" target="_blank">Followed Posts</a></li>
					<li><a href="https://webberzone.com/plugins/popular-authors/" target="_blank">Popular Authors</a></li>
					<li><a href="https://webberzone.com/plugins/autoclose/" target="_blank">Auto-Close</a></li>
				</ul>
			</div>
		</div><!-- /.inside -->
	</div><!-- /.postbox -->

</div>

<div class="postbox-container">
	<div id="followdiv" class="postbox meta-box-sortables">
		<h2 class="metabox-holder"><span><?php esc_html_e( 'Follow us', 'webberzone-link-warnings' ); ?></span></h2>

		<div class="inside" style="text-align: center">
			<a href="https://x.com/webberzone/" target="_blank" rel="noopener noreferrer"><img src="<?php echo esc_url( WZLW_PLUGIN_URL . 'includes/admin/images/x.png' ); ?>" width="100" height="100" alt="X (Twitter)"></a>
			<a href="https://facebook.com/webberzone/" target="_blank" rel="noopener noreferrer"><img src="<?php echo esc_url( WZLW_PLUGIN_URL . 'includes/admin/images/fb.png' ); ?>" width="100" height="100" alt="Facebook"></a>
		</div><!-- /.inside -->
	</div><!-- /.postbox -->
</div>
