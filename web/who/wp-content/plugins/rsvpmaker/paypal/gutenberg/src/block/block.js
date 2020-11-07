/**
 * BLOCK: rsvpmaker-block
 *
 * Registering a basic block with Gutenberg.
 * Simple block, renders and saves the same content without any interactivity.
 */
//  Import CSS.
import './style.scss';
import './editor.scss';
import './rsvpmaker-sidebar.js';
import './rsvpmaker-sidebar-extra.js';
import './rsvpemail-sidebar.js';		
import './limited_time.js';		
import './schedule.js';
import './form.js';		
import './form-wrapper.js';
import apiFetch from '@wordpress/api-fetch';

const { __ } = wp.i18n; // Import __() from wp.i18n
const { registerBlockType } = wp.blocks; // Import registerBlockType() from wp.blocks
const { SelectControl, TextControl, ToggleControl } = wp.components;

const rsvpupcoming = [{label: 'Next event',value: 'next'},{label: 'Next event - RSVP on',value: 'nextrsvp'}];
apiFetch( {path: rsvpmaker_json_url+'future'} ).then( events => {
	if(Array.isArray(events)) {
		 events.map( function(event) { if(event.ID) { var title = (event.date) ? event.post_title+' - '+event.date : event.post_title; rsvpupcoming.push({value: event.ID, label: title }) } } );
	}
	 else {
		 var eventsarray = Object.values(events);
		 eventsarray.map( function(event) { if(event.ID) { var title = (event.date) ? event.post_title+' - '+event.date : event.post_title; rsvpupcoming.push({value: event.ID, label: title }) } } );
		}
}).catch(err => {
	console.log(err);
});

const rsvptypes = [{value: '', label: 'None selected (optional)'}];
apiFetch( {path: rsvpmaker_json_url+'types'} ).then( types => {
	if(Array.isArray(types))
			types.map( function(type) { if(type.slug && type.name) rsvptypes.push({value: type.slug, label: type.name }) } );
		else {
			var typesarray = Object.values(types);
			typesarray.map( function(type) { if(type.slug && type.name) rsvptypes.push({value: type.slug, label: type.name }) } );
		}
}).catch(err => {
	console.log(err);
});	

/**
 * Register: a Gutenberg Block.
 *
 * Registers a new block provided a unique name and an object defining its
 * behavior. Once registered, the block is made editor as an option to any
 * editor interface where blocks are implemented.
 *
 * @link https://wordpress.org/gutenberg/handbook/block-api/
 * @param  {string}   name     Block name.
 * @param  {Object}   settings Block settings.
 * @return {?WPBlock}          The block, if it has been successfully
 *                             registered; otherwise `undefined`.
 */

registerBlockType( 'rsvpmaker/event', {
	// Block name. Block names must be string that contains a namespace prefix. Example: my-plugin/my-custom-block.
	title: __( 'RSVPMaker Embed Event' ), // Block title.
	icon: 'clock', // Block icon from Dashicons → https://developer.wordpress.org/resource/dashicons/.
	category: 'common', // Block category — Group blocks together based on common traits E.g. common, formatting, layout widgets, embed.
	description: __('Displays a single RSVPMaker event post'),
	keywords: [
		__( 'RSVPMaker' ),
		__( 'Event' ),
		__( 'Calendar' ),
	],
       attributes: {
            post_id: {
            type: 'string',
            default: '',
            },
            one_hideauthor: {
                type: 'boolean',
                default: true,
            },
            type: {
                type: 'string',
                default: '',
            },
            one_format: {
                type: 'string',
				default: '',
            },
            hide_past: {
                type: 'string',
                default: '',
            },
        },
	edit: function( props ) {
	const { attributes: { post_id, type, one_hideauthor, one_format, hide_past }, setAttributes, isSelected } = props;
	if(post_id == '')
		setAttributes( { post_id: 'next' } );

	function showFormPrompt () {
		return <p><strong>Click here to set options.</strong></p>
	}

	function showForm() {

			return (
				<form>
<SelectControl
        label={__("Select Post",'rsvpmaker')}
        value={ post_id }
        options={ rsvpupcoming }
        onChange={ ( post_id ) => { setAttributes( { post_id: post_id } ) } }
    />
<SelectControl
        label={__("Format",'rsvpmaker')}
        value={ one_format }
        options={ [
	{label: 'Event with Form', value:''},
	{label: 'Event with Button', value:'button'},
	{label: 'Button Only', value:'button_only'},
	{label: 'Form Only', value:'form'},
	{label: 'Compact (Headline/Date/Button)', value:'compact'},
	{label: 'Dates Only', value:'embed_dateblock'}] }
        onChange={ ( one_format ) => { setAttributes( { one_format: one_format } ) } }
/>

<SelectControl
        label={__("Hide After",'rsvpmaker')}
        value={ hide_past }
        options={ [
	{label: 'Not Set', value:''},
	{label: '1 hour', value:'1'},
	{label: '2 hours', value:'2'},
	{label: '3 hours', value:'3'},
	{label: '4 hours', value:'4'},
	{label: '5 hours', value:'5'},
	{label: '6 hours', value:'6'},
	{label: '7 hours', value:'7'},
	{label: '8 hours', value:'8'},
	{label: '12 hours', value:'12'},
	{label: '18 hours', value:'18'},
	{label: '24 hours', value:'24'},
	{label: '2 days', value:'48'},
	{label: '3 days', value:'72'}] }
        onChange={ ( hide_past ) => { setAttributes( { hide_past: hide_past } ) } }
/>

<SelectControl
        label={__("Event Type",'rsvpmaker')}
        value={ type }
        options={ rsvptypes }
        onChange={ ( type ) => { setAttributes( { type: type } ) } }
    />

<SelectControl
        label={__("Show Author",'rsvpmaker')}
        value={ one_hideauthor }
        options={ [{label: 'No', value:'1'},{label: 'Yes', value:'0'}] }
        onChange={ ( one_hideauthor ) => { setAttributes( { one_hideauthor: one_hideauthor } ) } }
    />
				</form>
			);
		}

		return (
			<div className={ props.className }>
				<p class="dashicons-before dashicons-clock"><strong>RSVPMaker</strong>: Embed a single event.
				</p>
			{ isSelected && ( showForm() ) }
			{ !isSelected && ( showFormPrompt() ) }
			</div>
		);
	},

	save: function() {
		// server render
		return null;
	},
} );

registerBlockType( 'rsvpmaker/embedform', {
	// Block name. Block names must be string that contains a namespace prefix. Example: my-plugin/my-custom-block.
	title: __( 'RSVPMaker Embed Form' ), // Block title.
	icon: 'clock', // Block icon from Dashicons → https://developer.wordpress.org/resource/dashicons/.
	category: 'common', // Block category — Group blocks together based on common traits E.g. common, formatting, layout widgets, embed.
	description: __('Displays the form associated with a single RSVPMaker event post'),
	keywords: [
		__( 'RSVPMaker' ),
		__( 'Event' ),
		__( 'Calendar' ),
	],
       attributes: {
            post_id: {
            type: 'string',
            default: '',
            },
        },
	edit: function( props ) {
	const { attributes: { post_id, type, one_hideauthor, one_format, hide_past }, setAttributes, isSelected } = props;
	if(post_id == '')
		setAttributes( { post_id: 'next' } );

	function showFormPrompt () {
		return <p><strong>Click here to set options.</strong></p>
	}

	function showForm() {

			return (
				<form>
<SelectControl
        label={__("Select Post",'rsvpmaker')}
        value={ post_id }
        options={ rsvpupcoming }
        onChange={ ( post_id ) => { setAttributes( { post_id: post_id } ) } }
    />
	</form>
			);
		}

		return (
			<div className={ props.className }>
				<p class="dashicons-before dashicons-clock"><strong>RSVPMaker</strong>: Embed just the form for a single event.
				</p>
			{ isSelected && ( showForm() ) }
			{ !isSelected && ( showFormPrompt() ) }
			</div>
		);
	},

	save: function() {
		return null;
	},
} );

registerBlockType( 'rsvpmaker/upcoming', {
	// Block name. Block names must be string that contains a namespace prefix. Example: my-plugin/my-custom-block.
	title: __( 'RSVPMaker Upcoming Events' ), // Block title.
	icon: 'calendar-alt', // Block icon from Dashicons → https://developer.wordpress.org/resource/dashicons/.
	category: 'common', // Block category — Group blocks together based on common traits E.g. common, formatting, layout widgets, embed.
	description: __('Displays an RSVPMaker event listing and/or a calendar widget'),
	keywords: [
		__( 'RSVPMaker' ),
		__( 'Events' ),
		__( 'Calendar' ),
	],
       attributes: {
            calendar: {
                type: 'int',
                default: 0,
            },
            nav: {
                type: 'string',
                default: 'bottom',
            },
            days: {
                type: 'int',
				default: 180,
            },
            posts_per_page: {
                type: 'int',
				default: 10,
            },
            type: {
                type: 'string',
                default: '',
			},
            exclude_type: {
                type: 'string',
                default: '',
            },			
            no_events: {
                type: 'string',
                default: 'No events listed',
            },
            hideauthor: {
                type: 'boolean',
                default: false,
            },
        },
	/**
	 * The edit function describes the structure of your block in the context of the editor.
	 * This represents what the editor will render when the block is used.
	 *
	 * The "edit" property must be a valid function.
	 *
	 * @link https://wordpress.org/gutenberg/handbook/block-api/block-edit-save/
	 */
	edit: function( props ) {
		// Creates a <p class='wp-block-cgb-block-toast-block'></p>.
	const { attributes: { calendar, days, posts_per_page, hideauthor, no_events, nav, type, exclude_type }, setAttributes, isSelected } = props;

	function showFormPrompt () {
		return <p><strong>{__('Click here to set options.','rsvpmaker')}</strong></p>
	}
		
	function showForm() {
	
		return (
				<form  >
					<SelectControl
        label={__("Display Calendar",'rsvpmaker')}
        value={ calendar }
        options={ [{value: 1, label: __('Yes - Calendar plus events listing')},{value: 0, label:  __('No - Events listing only')},{value: 2, label: __('Calendar only')}] }
        onChange={ ( calendar ) => { setAttributes( { calendar: calendar } ) } }
    />
					<SelectControl
        label={__("Events Per Page",'rsvpmaker')}
        value={ posts_per_page }
        options={ [{value: 5, label: 5},
			{value: 10, label: 10},
			{value: 15, label: 15},
			{value: 20, label: 20},
			{value: 25, label: 25},
			{value: 30, label: 30},
			{value: 35, label: 35},
			{value: 40, label: 40},
			{value: 45, label: 45},
			{value: 50, label: 50},
			{value: '-1', label: 'No limit'}]}
        onChange={ ( posts_per_page ) => { setAttributes( { posts_per_page: posts_per_page } ) } }
    />
					<SelectControl
        label={__("Date Range",'rsvpmaker')}
        value={ days }
        options={ [{value: 5, label: 5},
			{value: 30, label: '30 Days'},
			{value: 60, label: '60 Days'},
			{value: 90, label: '90 Days'},
			{value: 180, label: '180 Days'},
			{value: 366, label: '1 Year'}] }
        onChange={ ( days ) => { setAttributes( { days: days } ) } }
    />
					<SelectControl
        label={__("Event Type",'rsvpmaker')}
        value={ type }
        options={ rsvptypes }
        onChange={ ( type ) => { setAttributes( { type: type } ) } }
    />
					<SelectControl
        label={__("Exclude Event Type",'rsvpmaker')}
        value={ exclude_type }
        options={ rsvptypes }
        onChange={ ( exclude_type ) => { setAttributes( { exclude_type: exclude_type } ) } }
    />
					<SelectControl
        label={__("Calendar Navigation",'rsvpmaker')}
        value={ nav }
        options={ [{value: 'top', label: __('Top')},{value: 'bottom', label: __('Bottom')},{value: 'both', label: __('Both')}] }
        onChange={ ( nav ) => { setAttributes( { nav: nav } ) } }
    />
				<SelectControl
        label={__("Show Event Author",'rsvpmaker')}
        value={ hideauthor }
        options={ [
            { label: 'No', value: true },
            { label: 'Yes', value: false },
        ] }
        onChange={ ( hideauthor ) => { setAttributes( { hideauthor: hideauthor } ) } }
    />
				<TextControl
        label={__("Text to show for no events listed",'rsvpmaker')}
        value={ no_events }
        onChange={ ( no_events ) => { setAttributes( { no_events: no_events } ) } }
    />
				</form>
			);
		}

		return (
			<div className={ props.className }>
				<p  class="dashicons-before dashicons-calendar-alt"><strong>RSVPMaker</strong>: Add an Events Listing and/or Calendar Display
				</p>
			{ isSelected && ( showForm() ) }
			{ !isSelected && ( showFormPrompt() ) }
			</div>
		);
	},

	/**
	 * The save function defines the way in which the different attributes should be combined
	 * into the final markup, which is then serialized by Gutenberg into post_content.
	 *
	 * The "save" property must be specified and must be a valid function.
	 *
	 * @link https://wordpress.org/gutenberg/handbook/block-api/block-edit-save/
	 */
	save: function( props ) {
		return null;
	},
} );


registerBlockType( 'rsvpmaker/eventlisting', {
	// Block name. Block names must be string that contains a namespace prefix. Example: my-plugin/my-custom-block.
	title: __( 'RSVPMaker Event Listing' ), // Block title.
	icon: 'calendar-alt', // Block icon from Dashicons → https://developer.wordpress.org/resource/dashicons/.
	category: 'common', // Block category — Group blocks together based on common traits E.g. common, formatting, layout widgets, embed.
	description: __('Displays an RSVPMaker event listing (headlines and dates)'),
	keywords: [
		__( 'RSVPMaker' ),
		__( 'Events' ),
		__( 'Calendar' ),
	],
       attributes: {
            days: {
                type: 'int',
				default: 180,
            },
            posts_per_page: {
                type: 'int',
				default: 10,
            },
            type: {
                type: 'string',
                default: '',
            },
            date_format: {
                type: 'string',
                default: '%A %B %e, %Y',
            },
        },
	/**
	 * The edit function describes the structure of your block in the context of the editor.
	 * This represents what the editor will render when the block is used.
	 *
	 * The "edit" property must be a valid function.
	 *
	 * @link https://wordpress.org/gutenberg/handbook/block-api/block-edit-save/
	 */
	edit: function( props ) {
		// Creates a <p class='wp-block-cgb-block-toast-block'></p>.
	const { attributes: { days, posts_per_page, type, date_format }, setAttributes, isSelected } = props;

	function showFormPrompt () {
		return <p><strong>{__('Click here to set options.','rsvpmaker')}</strong></p>
	}
		
	function showForm() {
			return (
				<form  >
					<SelectControl
        label={__("Events Per Page",'rsvpmaker')}
        value={ posts_per_page }
        options={ [{value: 5, label: 5},
			{value: 10, label: 10},
			{value: 15, label: 15},
			{value: 20, label: 20},
			{value: 25, label: 25},
			{value: 30, label: 30},
			{value: 35, label: 35},
			{value: 40, label: 40},
			{value: 45, label: 45},
			{value: 50, label: 50},
			{value: '-1', label: 'No limit'}]}
        onChange={ ( posts_per_page ) => { setAttributes( { posts_per_page: posts_per_page } ) } }
    />
					<SelectControl
        label={__("Date Range",'rsvpmaker')}
        value={ days }
        options={ [{value: 5, label: 5},
			{value: 30, label: '30 Days'},
			{value: 60, label: '60 Days'},
			{value: 90, label: '90 Days'},
			{value: 180, label: '180 Days'},
			{value: 366, label: '1 Year'}] }
        onChange={ ( days ) => { setAttributes( { days: days } ) } }
    />
					<SelectControl
        label={__("Event Type",'rsvpmaker')}
        value={ type }
        options={ rsvptypes }
        onChange={ ( type ) => { setAttributes( { type: type } ) } }
    />
				<SelectControl
        label={__("Date Format",'rsvpmaker')}
        value={ date_format }
        options={ [
            { label: 'Thursday August 8, 2019', value: '%A %B %e, %Y' },
            { label: 'August 8, 2019', value: '%B %e, %Y' },
            { label: 'August 8', value: '%B %e' },
            { label: 'Aug. 8', value: '%h. %e' },
            { label: '8 August 2019', value: '%e %B %Y' },
        ] }
        onChange={ ( date_format ) => { setAttributes( { date_format: date_format } ) } }
    />
				</form>
			);
		}

		return (
			<div className={ props.className }>
				<p  class="dashicons-before dashicons-calendar-alt"><strong>RSVPMaker</strong>: Add an Events Listing and/or Calendar Display
				</p>
			{ isSelected && ( showForm() ) }
			{ !isSelected && ( showFormPrompt() ) }
			</div>
		);
	},

	/**
	 * The save function defines the way in which the different attributes should be combined
	 * into the final markup, which is then serialized by Gutenberg into post_content.
	 *
	 * The "save" property must be specified and must be a valid function.
	 *
	 * @link https://wordpress.org/gutenberg/handbook/block-api/block-edit-save/
	 */
	save: function( props ) {
		return null;
	},
} );


registerBlockType( 'rsvpmaker/stripecharge', {
	// Block name. Block names must be string that contains a namespace prefix. Example: my-plugin/my-custom-block.
	title: __( 'Stripe Charge (RSVPMaker)' ), // Block title.
	icon: 'products', // Block icon from Dashicons → https://developer.wordpress.org/resource/dashicons/.
	category: 'common', // Block category — Group blocks together based on common traits E.g. common, formatting, layout widgets, embed.
	description: __('Displays a payment widget for the Stripe service'),
	keywords: [
		__( 'RSVPMaker' ),
		__( 'Payment' ),
		__( 'Charge' ),
	],
       attributes: {
            description: {
            type: 'string',
            default: '',
            },
            showdescription: {
            type: 'string',
            default: 'no',
            },
            amount: {
            type: 'string',
            default: '',
            },
            paymentType: {
            type: 'string',
            default: 'once',
            },
            amount: {
            type: 'string',
            default: '',
            },
            january: {
            type: 'string',
            default: '',
            },
            february: {
            type: 'string',
            default: '',
            },
            march: {
            type: 'string',
            default: '',
            },
            april: {
            type: 'string',
            default: '',
            },
            may: {
            type: 'string',
            default: '',
            },
            june: {
            type: 'string',
            default: '',
            },
            july: {
            type: 'string',
            default: '',
            },
            august: {
            type: 'string',
            default: '',
            },
            september: {
            type: 'string',
            default: '',
            },
            october: {
            type: 'string',
            default: '',
            },
            november: {
            type: 'string',
            default: '',
            },
            december: {
            type: 'string',
            default: '',
            },
        },
	edit: function( props ) {
		// Creates a <p class='wp-block-cgb-block-toast-block'></p>.
	const { attributes: { description, showdescription, amount, paymentType, january, february, march, april, may, june, july, august, september, october, november, december }, setAttributes, isSelected } = props;
		var show = (paymentType.toString() == 'schedule') ? true : false;
		//alert(show);
		
		if(!isSelected)
			return (
			<div className={ props.className }>
				<p class="dashicons-before dashicons-products"><strong>Payment Button</strong>: Embed in any post or page (not meant to be included in events). Clicke to set price and options.
				</p>
				</div>
			);
		
		return (
			<div className={ props.className }>
				<p class="dashicons-before dashicons-products"><strong>Payment Button</strong>: Embed in any post or page (not meant to be included in events).
				</p>
	<TextControl
        label={ __( 'Description', 'rsvpmaker' ) }
        value={ description }
        onChange={ ( description ) => setAttributes( { description } ) }
    />	
<div>		<SelectControl
			label={ __( 'Show Amount/Description Under Button', 'rsvpmaker' ) }
			value={ showdescription }
			onChange={ ( showdescription ) => setAttributes( { showdescription } ) }
			options={ [
				{ value: 'yes', label: __( 'Yes', 'rsvpmaker' ) },
				{ value: 'no', label: __( 'No', 'rsvpmaker' ) },
			] }
		/>

		<SelectControl
			label={ __( 'Payment Type', 'rsvpmaker' ) }
			value={ paymentType }
			onChange={ ( paymentType ) => setAttributes( { paymentType } ) }
			options={ [
				{ value: 'one-time', label: __( 'One time, fixed fee', 'rsvpmaker' ) },
				{ value: 'schedule', label: __( 'Dues schedule', 'rsvpmaker' ) },
				{ value: 'donation', label: __( 'Donation', 'rsvpmaker' ) },
			] }
		/>
				</div>
{
!show &&	<TextControl
        label={ __( 'Fee', 'rsvpmaker' ) }
        value={ amount }
		placeholder="$0.00"
        onChange={ ( amount ) => setAttributes( { amount } ) }
    />			
}
			{
show &&	
<div>    <TextControl
        label={ __( 'January', 'rsvpmaker' ) }
        value={ january }
        onChange={ ( january ) => setAttributes( { january } ) }
    />
    <TextControl
        label={ __( 'February', 'rsvpmaker' ) }
        value={ february }
        onChange={ ( february ) => setAttributes( { february } ) }
    />
    <TextControl
        label={ __( 'March', 'rsvpmaker' ) }
        value={ march }
        onChange={ ( march ) => setAttributes( { march } ) }
    />
    <TextControl
        label={ __( 'April', 'rsvpmaker' ) }
        value={ april }
        onChange={ ( april ) => setAttributes( { april } ) }
    />
    <TextControl
        label={ __( 'May', 'rsvpmaker' ) }
        value={ may }
        onChange={ ( may ) => setAttributes( { may } ) }
    />
    <TextControl
        label={ __( 'June', 'rsvpmaker' ) }
        value={ june }
        onChange={ ( june ) => setAttributes( { june } ) }
    />
    <TextControl
        label={ __( 'July', 'rsvpmaker' ) }
        value={ july }
        onChange={ ( july ) => setAttributes( { july } ) }
    />
    <TextControl
        label={ __( 'August', 'rsvpmaker' ) }
        value={ august }
        onChange={ ( august ) => setAttributes( { august } ) }
    />
    <TextControl
        label={ __( 'September', 'rsvpmaker' ) }
        value={ september }
        onChange={ ( september ) => setAttributes( { september } ) }
    />
    <TextControl
        label={ __( 'October', 'rsvpmaker' ) }
        value={ october }
        onChange={ ( october ) => setAttributes( { october } ) }
    />
    <TextControl
        label={ __( 'November', 'rsvpmaker' ) }
        value={ november }
        onChange={ ( november ) => setAttributes( { november } ) }
    />
    <TextControl
        label={ __( 'December', 'rsvpmaker' ) }
        value={ december }
        onChange={ ( december ) => setAttributes( { december } ) }
    />
</div>
 }
			</div>
		);
	},

	/**
	 * The save function defines the way in which the different attributes should be combined
	 * into the final markup, which is then serialized by Gutenberg into post_content.
	 *
	 * The "save" property must be specified and must be a valid function.
	 *
	 * @link https://wordpress.org/gutenberg/handbook/block-api/block-edit-save/
	 */
	save: function() {
		// server render
		return null;
	},
} );

registerBlockType( 'rsvpmaker/rsvpdateblock', {
	// Block name. Block names must be string that contains a namespace prefix. Example: my-plugin/my-custom-block.
	title: __( 'RSVPMaker Dateblock' ), // Block title.
	icon: 'products', // Block icon from Dashicons → https://developer.wordpress.org/resource/dashicons/.
	category: 'common', // Block category — Group blocks together based on common traits E.g. common, formatting, layout widgets, embed.
	description: __('Changes the display of the date / time block from the default (top of the post)'),
	keywords: [
		__( 'RSVPMaker' ),
		__( 'Event' ),
		__( 'Calendar' ),
	],
	edit: function( props ) {

			return (
			<div className={ props.className }>
				<p class="dashicons-before dashicons-clock">Changes placement of date/time block from default (top of the post)
				</p>
				</div>
			);
	},

	/**
	 * The save function defines the way in which the different attributes should be combined
	 * into the final markup, which is then serialized by Gutenberg into post_content.
	 *
	 * The "save" property must be specified and must be a valid function.
	 *
	 * @link https://wordpress.org/gutenberg/handbook/block-api/block-edit-save/
	 */
	save: function() {
		// server render
		return null;
	},
} );

registerBlockType( 'rsvpmaker/placeholder', {
	// Block name. Block names must be string that contains a namespace prefix. Example: my-plugin/my-custom-block.
	title: __( 'Placeholder' ), // Block title.
	icon: 'products', // Block icon from Dashicons → https://developer.wordpress.org/resource/dashicons/.
	category: 'formatting', // Block category — Group blocks together based on common traits E.g. common, formatting, layout widgets, embed.
	description: __('Placeholder for content to be added later'),
	keywords: [
		__( 'RSVPMaker' ),
		__( 'Placeholder' ),
		__( 'Layout' ),
	],
       attributes: {
            text: {
            type: 'string',
            default: '',
            },
        },
	edit: function( props ) {
		const { attributes: { text }, setAttributes, isSelected } = props;
			
		if(isSelected)
		return (
			<div className={ props.className }>
	<TextControl
        label={ __( 'Text', 'rsvpmaker' ) }
        value={ text }
        onChange={ ( text ) => setAttributes( { text } ) }
    />	
	<p class="dashicons-before dashicons-welcome-write-blog"><em>(Not shown on front end. Delete from finished post)</em></p>
				</div>
			);
		
		return (
			<div className={ props.className }>
				<p class="dashicons-before dashicons-welcome-write-blog">{text} <em>(Placeholder: Not shown on front end)</em></p>
				</div>
			);
	},

	/**
	 * The save function defines the way in which the different attributes should be combined
	 * into the final markup, which is then serialized by Gutenberg into post_content.
	 *
	 * The "save" property must be specified and must be a valid function.
	 *
	 * @link https://wordpress.org/gutenberg/handbook/block-api/block-edit-save/
	 */
	save: function() {
		// server render
		return null;
	},
} );

registerBlockType( 'rsvpmaker/upcoming-by-json', {
	// Block name. Block names must be string that contains a namespace prefix. Example: my-plugin/my-custom-block.
	title: __( 'RSVPMaker Events (fetch via API)' ), // Block title.
	icon: 'calendar-alt', // Block icon from Dashicons → https://developer.wordpress.org/resource/dashicons/.
	category: 'common', // Block category — Group blocks together based on common traits E.g. common, formatting, layout widgets, embed.
	description: __('Displays a listing of RSVPMaker events from a remote site'),
	keywords: [
		__( 'RSVPMaker' ),
		__( 'Events' ),
		__( 'Calendar' ),
	],
       attributes: {
            limit: {
                type: 'int',
				default: 10,
            },
            url: {
                type: 'string',
                default: '',
            },
            morelink: {
                type: 'string',
                default: '',
            },
        },
	/**
	 * The edit function describes the structure of your block in the context of the editor.
	 * This represents what the editor will render when the block is used.
	 *
	 * The "edit" property must be a valid function.
	 *
	 * @link https://wordpress.org/gutenberg/handbook/block-api/block-edit-save/
	 */
	edit: function( props ) {
	const { attributes: { limit, url, morelink }, setAttributes, isSelected } = props;
	let typelist = '';
	if(rsvpupcoming && (rsvpupcoming.length > 2))
	{
		typelist = 'API urls for  this site:\n'+window.location.protocol+'//'+window.location.hostname+'/wp-json/rsvpmaker/v1/future\n';
		rsvptypes.forEach(showTypes);	
	}

function showTypes (data, index) {
	if(index > 0)
		typelist = typelist.concat(rsvpmaker_json_url+'type/'+data.value + '\n'); 
}

function showForm() {
return (<div>
	<TextControl
        label={ __( 'JSON API url', 'rsvpmaker' ) }
        value={ url }
        onChange={ ( url ) => setAttributes( { url } ) }
    />
	<TextControl
        label={ __( 'Limit', 'rsvpmaker' ) }
        value={ limit }
		help={__('For no limit, enter 0')}
        onChange={ ( limit ) => setAttributes( { limit } ) }
    />	
	<TextControl
        label={ __( 'Link URL for more results (optional)', 'rsvpmaker' ) }
        value={ morelink }
        onChange={ ( morelink ) => setAttributes( { morelink } ) }
    />	
	<p><em>Enter JSON API url for this site or another in the format:
	<br />https://rsvpmaker.com/wp-json/rsvpmaker/v1/future
	<br />or
	<br />https://rsvpmaker.com/wp-json/rsvpmaker/v1/type/featured</em></p>
<pre>{typelist}</pre>
</div>);
}

function showFormPrompt () {
    return (<p><em>Click to set options</em></p>);
}

		return (
			<div className={ props.className }>
				<p  class="dashicons-before dashicons-calendar-alt"><strong>RSVPMaker </strong>: Add an Events Listing that dynamically loads via JSON API endpoint
				</p>
			{ isSelected && ( showForm() ) }
			{ !isSelected && ( showFormPrompt() ) }
			</div>
		);
	},

	/**
	 * The save function defines the way in which the different attributes should be combined
	 * into the final markup, which is then serialized by Gutenberg into post_content.
	 *
	 * The "save" property must be specified and must be a valid function.
	 *
	 * @link https://wordpress.org/gutenberg/handbook/block-api/block-edit-save/
	 */
	save: function( props ) {
		return null;
	},
} );

registerBlockType( 'rsvpmaker/future-rsvp-links', {
	// Block name. Block names must be string that contains a namespace prefix. Example: my-plugin/my-custom-block.
	title: __( 'Future RSVP Links' ), // Block title.
	icon: 'calendar-alt', // Block icon from Dashicons → https://developer.wordpress.org/resource/dashicons/.
	category: 'common', // Block category — Group blocks together based on common traits E.g. common, formatting, layout widgets, embed.
	description: __('Displays a list of links to the RSVP Form for upcoming events with RSVPs turned on'),
	keywords: [
		__( 'RSVPMaker' ),
		__( 'Events' ),
		__( 'Calendar' ),
	],
       attributes: {
            limit: {
                type: 'int',
				default: 5,
            },
            skipfirst: {
                type: 'boolean',
                default: false,
            },
        },
	/**
	 * The edit function describes the structure of your block in the context of the editor.
	 * This represents what the editor will render when the block is used.
	 *
	 * The "edit" property must be a valid function.
	 *
	 * @link https://wordpress.org/gutenberg/handbook/block-api/block-edit-save/
	 */
	edit: function( props ) {
	const { attributes: { limit, skipfirst }, setAttributes, isSelected } = props;

		return (
			<div className={ props.className }>
				<p  class="dashicons-before dashicons-calendar-alt"><strong>RSVPMaker </strong>: Display a list of links to the RSVP Form for upcoming events
				</p>
<div>
<SelectControl
        label={__("Limit",'rsvpmaker')}
        value={ limit }
        options={ [{label:'3',value: '3'},{label:'5',value: '5'},{label:'7',value: '7'},{label:'10',value: '10'}] }
        onChange={ ( limit ) => { setAttributes( { limit } ) } }
    />
<ToggleControl
        label={__("Skip First Date",'rsvpmaker')}
        checked={ skipfirst }
		help={__('For example, to pick up after an embedded date block that features the first event in the series.')}
        onChange={ ( skipfirst ) => { setAttributes( { skipfirst } ) } }
    />
</div>		
			</div>
		);
	},

	/**
	 * The save function defines the way in which the different attributes should be combined
	 * into the final markup, which is then serialized by Gutenberg into post_content.
	 *
	 * The "save" property must be specified and must be a valid function.
	 *
	 * @link https://wordpress.org/gutenberg/handbook/block-api/block-edit-save/
	 */
	save: function( props ) {
		return null;
	},
} );
