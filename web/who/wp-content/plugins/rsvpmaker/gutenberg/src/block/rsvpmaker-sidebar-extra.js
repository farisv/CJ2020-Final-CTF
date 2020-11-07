/**
 * BLOCK: blocknewrsvp
 *
 * Registering a basic block with Gutenberg.
 * Simple block, renders and saves the same content without any interactivity.
 */


const { __ } = wp.i18n; // Import __() from wp.i18n
const el = wp.element.createElement;
const {Fragment} = wp.element;
const { registerPlugin } = wp.plugins;
const { PluginSidebar, PluginSidebarMoreMenuItem } = wp.editPost;
const { Panel, PanelBody, PanelRow } = wp.components;
//import {TemplateTextControl} from './template-settings.js';  

import {MetaDateControl, MetaEndDateControl, MetaTextControl, MetaSelectControl, MetaTextareaControl, MetaFormToggle} from './metadata_components.js';

function recordChange(metaKey, metaValue) {
	console.log(metaKey + ': ', metaValue);
}

//<!-- RSVPTemplate / -->
function related_link() {
	if(rsvpmaker_ajax && rsvpmaker_ajax.special)
		{
		return <div class="rsvp_related_links"></div>;
		}
	return <div class="rsvp_related_links"><p><a href={rsvpmaker_ajax.rsvpmaker_details}>RSVP / Event Options</a></p></div>;	
	}

const PluginRSVPMaker = () => {
    return(
		<Fragment>
		<PluginSidebarMoreMenuItem target="plugin-rsvpmaker-extra" icon="calendar-alt">RSVPMaker</PluginSidebarMoreMenuItem>
        <PluginSidebar
            name='plugin-rsvpmaker-extra'
            title='RSVPMaker'
            icon="calendar-alt"
        >
<p>{__('For additional options, events spanning multiple dates, and event pricing see','rsvpmaker')}: {related_link()}</p>
<Panel header={__('"RSVPMaker Event Options"','rsvpmaker')}>
<PanelBody
            title={__("Set Basic Options",'rsvpmaker')}
            icon="calendar-alt"
            initialOpen={ true }
        >
{ /* <MetaEndDateControl type="date" statusKey="_firsttime" timeKey="_endfirsttime" />
 */
(!rsvpmaker_ajax.special && !rsvpmaker_ajax.template_msg && (rsvpmaker_ajax._rsvp_count == '1') && <div>
<MetaDateControl metaKey='_rsvp_dates' />
<MetaEndDateControl type="date" statusKey="_firsttime" timeKey="_endfirsttime" />
</div>
)}
{(rsvpmaker_ajax._rsvp_count > '1') && <PanelRow><a href={rsvpmaker_ajax.rsvpmaker_details} >{__('Edit Multiple Dates')}</a></PanelRow>}
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
<p><MetaFormToggle
label="Collect RSVPs" 
metaKey="_rsvp_on"/></p>
</PanelBody>
<PanelBody
            title="Related"
            icon="admin-links"
            initialOpen={ false }
        >
<ul>
<li><a href={wp.data.select('core/editor').getPermalink()}>{__('View Event','rsvpmaker')}</a></li>
{rsvpmaker_ajax.related_document_links.map( function (x) {return <li class={x.class}><a href={x.href}>{x.title}</a></li>} )}
</ul>
</PanelBody>

<PanelBody
            title="Display"
            icon="admin-settings"
            initialOpen={ false }
        >
<MetaFormToggle
label={__('"Show Add to Google/Outlook Calendar Icons" ','rsvpmaker')}
metaKey="_calendar_icons"/>

<MetaFormToggle
		label={__("Add Timezone to Date",'rsvpmaker')}
		metaKey="_add_timezone"
	/>
<MetaFormToggle
label={__("Show Timezone Conversion Button",'rsvpmaker')}
metaKey="_convert_timezone"/>

<MetaFormToggle
label={__("Show RSVP Count",'rsvpmaker')} 
metaKey="_rsvp_count"/>

<MetaSelectControl
		label={__("Display attendee names / RSVP note field",'rsvpmaker')}
		metaKey="_rsvp_show_attendees"
		options={ [
			{ label: 'No', value: '0' },
			{ label: 'Yes', value: '1' },
			{ label: 'Only for Logged In Users', value: '2' },
		] }
	/>

</PanelBody>
        <PanelBody
            title={__("Notifications / Reminders",'rsvpmaker')}
            icon="email"
            initialOpen={ false }
        >
			<MetaTextControl title={__("Send notifications to:",'rsvpmaker')} metaKey="_rsvp_to" />
		<MetaFormToggle
		label={__("Send Confirmation Email",'rsvpmaker')}
		metaKey="_rsvp_rsvpmaker_send_confirmation_email"
	/>
	<MetaFormToggle
		label={__("Confirm AFTER Payment",'rsvpmaker')}
		metaKey="_rsvp_confirmation_after_payment"
	/>
<MetaFormToggle
		label={__('"Include Event Content with Confirmation"','rsvpmaker')}
		metaKey="_rsvp_confirmation_include_event"
	/>
            <PanelRow>{__('Confirmation Message (exerpt)','rsvpmaker')}: {rsvpmaker_ajax.confirmation_excerpt}</PanelRow>
{rsvpmaker_ajax.confirmation_links.map( function(x) {return <PanelRow><a href={x.href}>{x.title}</a></PanelRow>} )}
<PanelRow><a href={rsvpmaker_ajax.reminders} >{__('Create / Edit Reminders')}</a></PanelRow>

<PanelRow>
<MetaSelectControl
		label={__("Email Template for Confirmations",'rsvpmaker')}
		metaKey="rsvp_tx_template"
		options={ rsvpmaker_ajax.rsvp_tx_template_choices }
	/>
</PanelRow>
<div>Venue:<br />
<MetaTextControl title={__("Venue",'rsvpmaker')} metaKey="venue" />
<br /><em>{__('A street address or web address to include on the calendar invite attachment included with confirmations. If not specifed, RSVPMaker includes a link to the event post.','rsvpmaker')}</em></div>
</PanelBody>
        <PanelBody
            title={__("RSVP Form",'rsvpmaker')}
            icon="yes-alt"
            initialOpen={ false }
        >
		<PanelRow>{rsvpmaker_ajax.form_fields}</PanelRow>
		<PanelRow><em>{rsvpmaker_ajax.form_type}</em></PanelRow>
		{rsvpmaker_ajax.form_links.map( function(x) {return <PanelRow><a href={x.href}>{x.title}</a></PanelRow>} )}
		<MetaFormToggle
		label={__("Login required to RSVP",'rsvpmaker')}
		metaKey="_rsvp_login_required"
	/>

<MetaFormToggle
		label={__("Captcha security challenge",'rsvpmaker')}
		metaKey="_rsvp_captcha"
	/>

<MetaFormToggle
		label={__("Show Yes/No Options on Registration Form",'rsvpmaker')}
		metaKey="_rsvp_yesno"
	/>
<MetaFormToggle
		label={__("Show Date and Time on Form",'rsvpmaker')}
		metaKey='_rsvp_form_show_date'
	/>
<MetaTextControl
		label={__('Maximum number of participants (0 for no limit)','rsvpmaker')}
		metaKey="_rsvp_max"
	/>
<MetaTextareaControl
		label={__('Form Instructions for User','rsvpmaker')}
		metaKey="_rsvp_instructions"
/>
		</PanelBody>
<PanelBody
	title={__("Pricing",'rsvpmaker')}
	icon="smiley"
	initialOpen={ false }
>
{(rsvpmaker_ajax.complex_pricing != '') && 
<PanelRow>{rsvpmaker_ajax.complex_pricing}</PanelRow>
}
{(rsvpmaker_ajax.complex_pricing == '') && 
<div>
<MetaTextControl
		label={__("Label for Payments")}
		metaKey="simple_price_label"
	/>
<MetaTextControl
		label={__("Price")}
		metaKey="simple_price"
/>


</div>
}
{
	(rsvpmaker_ajax.edit_payment_confirmation != '') && <p>See <strong>Confirmation/Notifications</strong> for paymment confirmation message.</p> 
}
{
	(rsvpmaker_ajax.edit_payment_confirmation == '') && <p>{__('Neither PayPal nor Stripe is active','rsvpmaker')}</p> 
}
</PanelBody>
</Panel>

<div>For additional options, including multiple dates and complex event pricing see: {related_link()}</div>
        </PluginSidebar>
		</Fragment>
    )
}

if ((typeof rsvpmaker_ajax !== 'undefined') && !rsvpmaker_ajax.special) 
	registerPlugin( 'plugin-rsvpmaker', { render: PluginRSVPMaker } );
