import './state.js';
const { withState } = wp.compose;
const { subscribe } = wp.data;
const { DateTimePicker } = wp.components;
const { TimePicker, RadioControl } = wp.components;
//const { getSettings } = wp.date; // removed from Gutenberg
var el = wp.element.createElement;
const { __ } = wp.i18n; // Import __() from wp.i18n
//var PluginPostStatusInfo = wp.editPost.PluginPostStatusInfo;

function RsvpMeta(key,value) {
var xhr = new XMLHttpRequest();
xhr.open("POST", ajaxurl, true);

//Send the proper header information along with the request
xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

xhr.onreadystatechange = function() {//Call a function when the state changes.
    if(this.readyState == XMLHttpRequest.DONE && this.status == 200) {
        // Request finished. Do processing here.
    }
}
wp.data.dispatch('rsvpevent').setRsvpMeta(key,value);
var dateaction = "action=rsvpmaker_meta&nonce="+rsvpmaker_ajax.ajax_nonce+"&post_id="+rsvpmaker_ajax.event_id+ "&key="+key+"&value="+value;
xhr.send(dateaction);
	}

if(rsvpmaker_type == 'rsvpmaker')
	{
wp.data.dispatch('rsvpevent').setRSVPdate(rsvpmaker_ajax._rsvp_first_date);
wp.data.dispatch('rsvpevent').setRsvpMeta('_rsvp_on',rsvpmaker_ajax._rsvp_on);
var datestring = '';
var dateaction = "action=rsvpmaker_date&nonce="+rsvpmaker_ajax.ajax_nonce+"&post_id="+rsvpmaker_ajax.event_id;

function related_link() {
	if(rsvpmaker_ajax.special)
		{
		return <div class="rsvp_related_links"><p><a href={rsvpmaker_ajax.rsvpmaker_details}>Additional Options</a></p></div>;	33
		}
	if(rsvpmaker_json.projected_url)
		{
		return <div class="rsvp_related_links"><p><a href={rsvpmaker_ajax.rsvpmaker_details}>RSVP / Event Options</a></p><p><a href={rsvpmaker_json.projected_url}>{rsvpmaker_json.projected_label}</a></p></div>;	
		}
	if(rsvpmaker_json.template_url)
		{
		return <div class="rsvp_related_links"><p><a href={rsvpmaker_ajax.rsvpmaker_details}>RSVP / Event Options</a></p><p><a href={rsvpmaker_json.template_url}>{rsvpmaker_json.template_label}</a></p></div>;	
		}
	return <div class="rsvp_related_links"><p><a href={rsvpmaker_ajax.rsvpmaker_details}>RSVP / Event Options</a></p></div>;	
	}

function get_template_prompt () {
	var post_id = wp.data.select( 'core/editor' ).getEditedPostAttribute( 'id' );
	let parts = window.location.href.split('wp-admin/');
	let template_url = parts[0] + 'wp-admin/edit.php?post_type=rsvpmaker&page=rsvpmaker_template_list&t=' + post_id;

	var template_prompt='';
	if(post_id)
		return <p id="template_prompt"><a href={template_url}>Create/update events from template</a></p>;
	return;
}

const RSVPMakerSidebarPlugin = function() {

if(rsvpmaker_ajax.template_msg)
	{//if this is a template
		
	return (
		el(
			wp.editPost.PluginPostStatusInfo,
			{},
<div>
<h3>RSVPMaker Template</h3>
{rsvpmaker_ajax.top_message}
<p><RSVPMakerOn /></p>
<p>{rsvpmaker_ajax.template_msg}</p>
<p>{__('To change the schedule, follow the link below.')}</p>
<div class="rsvpmaker_related">
{related_link()}
</div>
{rsvpmaker_ajax.bottom_message}
</div>
		)
	);

	}
else if(rsvpmaker_ajax.special)
	{
		
	return (
		el(
			wp.editPost.PluginPostStatusInfo,
			{},
<div>
<h3>RSVPMaker Special Document</h3>
{rsvpmaker_ajax.top_message}
																																	<div class="rsvpmaker_related">
{related_link()}
</div>
{rsvpmaker_ajax.bottom_message}
</div>
		)
	);

	}
	return (
		el(
			wp.editPost.PluginPostStatusInfo,
			{},
<div>
<h3>RSVPMaker Event Date</h3>
{rsvpmaker_ajax.top_message}
<RSVPMakerDateTimePicker />
<p><RSVPMakerOn /></p>
<div class="rsvpmaker_related">
<p>{related_link()}</p>
</div>
{rsvpmaker_ajax.bottom_message}
</div>
		)
	);
}

const RSVPMakerDateTimePicker = withState( {
	date: new Date(wp.data.select('rsvpevent').getRSVPdate()),
} )( ( { date, setState } ) => {
	//const settings = getSettings();
	const is12HourTime = true;
	/*/a(?!\\)/i.test(
		settings.formats.time
			.toLowerCase() // Test only the lower case a
			.replace( /\\\\/g, '' ) // Replace "//" with empty strings
			.split( '' ).reverse().join( '' ) // Reverse the string and test for "a" not followed by a slash
	);
	*/
	//console.log('datestr '+ datestr);
	console.log('date ' + date);
	
function recordDate(date) {
var xhr = new XMLHttpRequest();
xhr.open("POST", ajaxurl, true);

//Send the proper header information along with the request
xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

xhr.onreadystatechange = function() {//Call a function when the state changes.
    if(this.readyState == XMLHttpRequest.DONE && this.status == 200) {
        // Request finished. Do processing here.
    }
}
wp.data.dispatch('rsvpevent').setRSVPdate(date);
datestring = "&date="+date;
xhr.send(dateaction+datestring);
	}

	var currentdate = wp.data.select('rsvpevent').getRSVPdate();
	return (
		<DateTimePicker
		    is12Hour={ is12HourTime }
		    currentDate={ currentdate }
			onChange={ ( date ) => {setState( {date} ),recordDate( date )} }
		    />
	);
} );

/*
paramaters removed from datetime picker
settings object removed
		    locale={ settings.l10n.locale }
*/

const RSVPMakerOn = withState( {
	on: wp.data.select('rsvpevent').getRSVPMakerOn(),
} )( ( { on, setState } ) => {
	
	on = wp.data.select('rsvpevent').getRSVPMakerOn();
	console.log('on '+ on);
//	var currentdate = wp.data.select('rsvpevent').getRSVPdate();
	return (
    <RadioControl
        label="Collect RSVPs"
        selected={ on }
        options={ [
            { label: 'Yes', value: 'Yes' },
            { label: 'No', value: 'No' },
        ] }
        onChange={ ( on ) => { setState( { on } ), RsvpMeta('_rsvp_on',on) } }
    />
	);
} );

wp.plugins.registerPlugin( 'rsvpmaker-sidebar-plugin', {
	render: RSVPMakerSidebarPlugin,
} );	

var PluginPrePublishPanel = wp.editPost.PluginPrePublishPanel;

function RSVPTemplatePluginPrePublishPanel() {

	return el(
        PluginPrePublishPanel,
        {
            className: 'rsvpmakertemplate-pre-publish-panel',
            title: __( 'RSVPMaker Template' ),
            initialOpen: true,
        },
<div>This is a template you can use to create or update multiple events.</div>
	);
}

function RSVPPluginPrePublishPanel() {
	return el(
        PluginPrePublishPanel,
        {
            className: 'rsvpmaker-pre-publish-panel',
            title: __( 'RSVPMaker Event Date' ),
            initialOpen: true,
        },
        <div><RSVPMakerDateTimePicker /></div>
    );
}

if(rsvpmaker_ajax.template_msg)
wp.plugins.registerPlugin( 'rsvpmaker-template-sidebar-prepublish', {
	render: RSVPTemplatePluginPrePublishPanel,
} );
else
wp.plugins.registerPlugin( 'rsvpmaker-sidebar-prepublish', {
	render: RSVPPluginPrePublishPanel,
} );	

var PluginPostPublishPanel = wp.editPost.PluginPostPublishPanel;

function RSVPPluginPostPublishPanel() {
    return el(
        PluginPostPublishPanel,
        {
            className: 'rsvpmaker-post-publish-panel',
            title: __( 'RSVPMaker Post Published' ),
            initialOpen: true,
        },
        <div>{related_link()}</div>
    );
}

wp.plugins.registerPlugin( 'rsvpmaker-sidebar-postpublish', {
	render: RSVPPluginPostPublishPanel,
} );

}// end initial test that rsvpmaker is set
