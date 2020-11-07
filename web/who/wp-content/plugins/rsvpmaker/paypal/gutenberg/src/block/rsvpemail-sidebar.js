var el = wp.element.createElement;
const { __ } = wp.i18n; // Import __() from wp.i18n

const RSVPEmailSidebarPlugin = function() {
let type = wp.data.select( 'core/editor' ).getCurrentPostType();
	if(type != 'rsvpemail')
		return null;
	return	el(
			wp.editPost.PluginPostStatusInfo,
			{},
<div><h3>{__('Email Editor','rsvpmaker')}</h3><p>{__('Use the WordPress editor to compose the body of your message, with the post title as your subject line. View post will display your content in an email template, with a user interface for addressing options.','rsvpmaker')}</p>
</div>
);
}
if(rsvpmaker_type == 'rsvpemail')
wp.plugins.registerPlugin( 'rsvpmailer-sidebar-plugin', {
	render: RSVPEmailSidebarPlugin,
} );
