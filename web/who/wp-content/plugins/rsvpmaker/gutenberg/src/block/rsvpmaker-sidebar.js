//import './state.js';
//const { withState } = wp.compose;
const { subscribe } = wp.data;
const { DateTimePicker } = wp.components;
const { Panel, PanelBody, PanelRow } = wp.components;
import {MetaDateControl, MetaEndDateControl, MetaTextControl, MetaSelectControl, MetaRadioControl, MetaFormToggle} from './metadata_components.js';

var el = wp.element.createElement;
const { __ } = wp.i18n; // Import __() from wp.i18n

function related_link() {
	if(rsvpmaker_ajax && rsvpmaker_ajax.special)
		{
		return <div class="rsvp_related_links"></div>;
		}
	return <div class="rsvp_related_links"><p><a href={rsvpmaker_ajax.rsvpmaker_details} >RSVP and {__('Event Options','rsvpmaker')}</a></p></div>;	
	}

const RSVPMakerSidebarPlugin = function() {
if(typeof rsvpmaker_ajax === 'undefined')
		return null; //not an rsvpmaker post
const end_times = Array();
const end_times_display = Array();
var datecount = '';
var first_date = '';
var additional_dates = '';
var time_display = '';
const rsvpdates = Array();

if(rsvpmaker_ajax._rsvp_count && (rsvpmaker_ajax._rsvp_count != '1'))
	additional_dates = 'This event spans multiple dates ('+rsvpmaker_ajax._rsvp_count+' total)';
var post_id = wp.data.select( 'core/editor' ).getEditedPostAttribute( 'id' );
if(rsvpmaker_ajax.special)
	{
		
	return (
		el(
			wp.editPost.PluginPostStatusInfo,
			{},
<div>
<h3>RSVPMaker {__('Special Document','rsvpmaker')}</h3>
{rsvpmaker_ajax.top_message}
																																	<div class="rsvpmaker_related">
{related_link()}
</div>
{(rsvpmaker_ajax.special == 'RSVP Form') && <p><a href="https://rsvpmaker.com/knowledge-base/customize-the-rsvp-form/" target="_blank">{__('Documentation','rsvpmaker')}</a></p>}
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
<h3>RSVPMaker {__('Event Date','rsvpmaker')}</h3>
{rsvpmaker_ajax.top_message}
{(!rsvpmaker_ajax.special && !rsvpmaker_ajax.template_msg && (rsvpmaker_ajax._rsvp_count == '1') && 
<div>
<MetaDateControl metaKey='_rsvp_dates' />
<MetaEndDateControl type="date" statusKey="_firsttime" timeKey="_endfirsttime" />
</div>
)}
{(rsvpmaker_ajax._rsvp_count > '1') && <p><a href={rsvpmaker_ajax.rsvpmaker_details} >{__('Edit Multiple Dates')}</a></p>}
{(!rsvpmaker_ajax._rsvp_first_date && rsvpmaker_ajax.projected_url && <div>
			<div class="sked_frequency">
			<p class="varies"><MetaFormToggle
			label="Varies" 
			metaKey="_sked_Varies"/></p>
			<p class="weeknumber"><MetaFormToggle
			label="First" 
			metaKey="_sked_First"/></p>
			<p class="weeknumber"><MetaFormToggle
			label="Second" 
			metaKey="_sked_Second"/></p>
			<p class="weeknumber"><MetaFormToggle
			label="Third" 
			metaKey="_sked_Third"/></p>
			<p class="weeknumber"><MetaFormToggle
			label="Fourth" 
			metaKey="_sked_Fourth"/></p>
			<p class="weeknumber"><MetaFormToggle
			label="Last" 
			metaKey="_sked_Last"/></p>
			<p class="every"><MetaFormToggle
			label="Every" 
			metaKey="_sked_Every"/></p>
			</div>
			<p><MetaFormToggle
			label="Sunday" 
			metaKey="_sked_Sunday"/></p>
			<p><MetaFormToggle
			label="Monday" 
			metaKey="_sked_Monday"/></p>
			<p><MetaFormToggle
			label="Tuesday" 
			metaKey="_sked_Tuesday"/></p>
			<p><MetaFormToggle
			label="Wednesday" 
			metaKey="_sked_Wednesday"/></p>
			<p><MetaFormToggle
			label="Thursday" 
			metaKey="_sked_Thursday"/></p>
			<p><MetaFormToggle
			label="Friday" 
			metaKey="_sked_Friday"/></p>
			<p><MetaFormToggle
			label="Saturday" 
			metaKey="_sked_Saturday"/></p>
			
			<MetaSelectControl
					label={__('Start Time (hour)','rsvpmaker')}
					metaKey="_sked_hour"
					options={ [
						{ label: '12 midnight', value: '00' },
						{ label: '1 am / 01:', value: '01' },
						{ label: '2 am / 02:', value: '02' },
						{ label: '3 am / 03:', value: '03' },
						{ label: '4 am / 04:', value: '04' },
						{ label: '5 am / 05:', value: '05' },
						{ label: '6 am / 06:', value: '06' },
						{ label: '7 am / 07:', value: '07' },
						{ label: '8 am / 08:', value: '08' },
						{ label: '9 am / 09:', value: '09' },
						{ label: '10 am / 10:', value: '10' },
						{ label: '11 am / 11:', value: '11' },
						{ label: '12 noon / 12:', value: '12' },
						{ label: '1 pm / 13:', value: '13' },
						{ label: '2 pm / 14:', value: '14' },
						{ label: '3 pm / 15:', value: '15' },
						{ label: '4 pm / 16:', value: '16' },
						{ label: '5 pm / 17:', value: '17' },
						{ label: '6 pm / 18:', value: '18' },
						{ label: '7 pm / 19:', value: '19' },
						{ label: '8 pm / 20:', value: '20' },
						{ label: '9 pm / 21:', value: '21' },
						{ label: '10 pm / 22:', value: '22' },
						{ label: '11 pm / 23:', value: '23' },
					] }
				/>
			<MetaSelectControl
					label={__('Start Time (minutes)','rsvpmaker')}
					metaKey="_sked_minutes"
					options={ [
						{ label: '00', value: '00' },
						{ label: '01', value: '01' },
						{ label: '02', value: '02' },
						{ label: '03', value: '03' },
						{ label: '04', value: '04' },
						{ label: '05', value: '05' },
						{ label: '06', value: '06' },
						{ label: '07', value: '07' },
						{ label: '08', value: '08' },
						{ label: '09', value: '09' },
						{ label: '10', value: '10' },
						{ label: '11', value: '11' },
						{ label: '12', value: '12' },
						{ label: '13', value: '13' },
						{ label: '14', value: '14' },
						{ label: '15', value: '15' },
						{ label: '16', value: '16' },
						{ label: '17', value: '17' },
						{ label: '18', value: '18' },
						{ label: '19', value: '19' },
						{ label: '20', value: '20' },
						{ label: '21', value: '21' },
						{ label: '22', value: '22' },
						{ label: '23', value: '23' },
						{ label: '24', value: '24' },
						{ label: '25', value: '25' },
						{ label: '26', value: '26' },
						{ label: '27', value: '27' },
						{ label: '28', value: '28' },
						{ label: '29', value: '29' },
						{ label: '30', value: '30' },
						{ label: '31', value: '31' },
						{ label: '32', value: '32' },
						{ label: '33', value: '33' },
						{ label: '34', value: '34' },
						{ label: '35', value: '35' },
						{ label: '36', value: '36' },
						{ label: '37', value: '37' },
						{ label: '38', value: '38' },
						{ label: '39', value: '39' },
						{ label: '40', value: '40' },
						{ label: '41', value: '41' },
						{ label: '42', value: '42' },
						{ label: '43', value: '43' },
						{ label: '44', value: '44' },
						{ label: '45', value: '45' },
						{ label: '46', value: '46' },
						{ label: '47', value: '47' },
						{ label: '48', value: '48' },
						{ label: '49', value: '49' },
						{ label: '50', value: '50' },
						{ label: '51', value: '51' },
						{ label: '52', value: '52' },
						{ label: '53', value: '53' },
						{ label: '54', value: '54' },
						{ label: '55', value: '55' },
						{ label: '56', value: '56' },
						{ label: '57', value: '57' },
						{ label: '58', value: '58' },
						{ label: '59', value: '59' },
					] }
				/>
			<MetaEndDateControl type="template" statusKey="_sked_duration" timeKey="_sked_end" />
			</div>	
)}
<MetaFormToggle
label={__('Collect RSVPs','rsvpmaker')} 
metaKey="_rsvp_on"/>

<div class="rsvpmaker_related">
<p>{related_link()}</p>
<p>{__('Basic options can be edited through the RSVPMaker sidebar (calendar icon above).')}</p>
</div>
{rsvpmaker_ajax.bottom_message}
</div>
		)
	);
}

if(typeof rsvpmaker_ajax !== 'undefined')
wp.plugins.registerPlugin( 'rsvpmaker-sidebar-plugin', {
	render: RSVPMakerSidebarPlugin,
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

if(typeof rsvpmaker_ajax !== 'undefined')
wp.plugins.registerPlugin( 'rsvpmaker-sidebar-postpublish', {
	render: RSVPPluginPostPublishPanel,
} );

if((typeof rsvpmaker_ajax !== 'undefined' ) && rsvpmaker_ajax.projected_url) {

		let wasSavingPost = wp.data.select( 'core/editor' ).isSavingPost();
		let wasAutosavingPost = wp.data.select( 'core/editor' ).isAutosavingPost();
		let wasPreviewingPost = wp.data.select( 'core/editor' ).isPreviewingPost();
		// determine whether to show notice
		subscribe( () => {
			const isSavingPost = wp.data.select( 'core/editor' ).isSavingPost();
			const isAutosavingPost = wp.data.select( 'core/editor' ).isAutosavingPost();
			const isPreviewingPost = wp.data.select( 'core/editor' ).isPreviewingPost();
			const hasActiveMetaBoxes = wp.data.select( 'core/edit-post' ).hasMetaBoxes();
			
			// Save metaboxes on save completion, except for autosaves that are not a post preview.
			const shouldTriggerTemplateNotice = (
					( wasSavingPost && ! isSavingPost && ! wasAutosavingPost ) ||
					( wasAutosavingPost && wasPreviewingPost && ! isPreviewingPost )
				);

			// Save current state for next inspection.
			wasSavingPost = isSavingPost;
			wasAutosavingPost = isAutosavingPost;
			wasPreviewingPost = isPreviewingPost;
	
			if ( shouldTriggerTemplateNotice ) {
				var newurl = rsvpmaker_ajax.projected_url.replace('template_list','setup');
	wp.data.dispatch('core/notices').createNotice(
		'info', // Can be one of: success, info, warning, error.
		__('After updating this template, click'), // Text string to display.
		{
			id: 'rsvptemplateupdate', //assigning an ID prevents the notice from being added repeatedly
			isDismissible: true, // Whether the user can dismiss the notice.
			// Any actions the user can perform.
			actions: [
				{
					url: newurl,
					label: __('New Event based on template'),
				},
				{
					label: ' or ',
				},
				{
					url: rsvpmaker_ajax.projected_url,
					label: __('Create / Update events'),
				},
			]
		}
	);
			}
} );
	
}