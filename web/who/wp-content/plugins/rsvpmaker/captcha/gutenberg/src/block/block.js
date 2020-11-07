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
import './rsvpemail-sidebar.js';		

const { __ } = wp.i18n; // Import __() from wp.i18n
const { registerBlockType } = wp.blocks; // Import registerBlockType() from wp.blocks

/**
 * Register: aa Gutenberg Block.
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
		// Creates a <p class='wp-block-cgb-block-toast-block'></p>.
	const { attributes: { post_id, type, one_hideauthor, one_format, hide_past }, setAttributes, isSelected } = props;
	
	function setPostID( event ) {
		const selected = event.target.querySelector( '#post_id option:checked' );
		setAttributes( { post_id: selected.value } );		
		event.preventDefault();
	}	
	function setEventType( event ) {
		const selected = event.target.querySelector( '#type option:checked' );
		setAttributes( { type: selected.value } );
		event.preventDefault();
	}
	function setOneFormat( event ) {
		const selected = event.target.querySelector( '#one_format option:checked' );
		setAttributes( { one_format: selected.value } );
		event.preventDefault();
	}	
	function setHideAuthor( event ) {
		const selected = event.target.querySelector( '#one_hideauthor option:checked' );
		setAttributes( { one_hideauthor: selected.value } );
		event.preventDefault();
	}	
	function setHidePast( event ) {
		const selected = event.target.querySelector( '#hide_past option:checked' );
		setAttributes( { hide_past: selected.value } );
		event.preventDefault();
	}
	
		
	function showFormPrompt () {
		return <p><strong>Click here to set options.</strong></p>
	}

	function showForm() {
			return (
				<form onSubmit={ setPostID, setOneFormat, setHideAuthor, setEventType, setHidePast } >
					<p><label>Select Post</label> <select id="post_id"  value={ post_id } onChange={ setPostID }>
						{upcoming.map(function(opt, i){
                    return <option value={ opt.value }>{opt.text}</option>;
                })}
					</select></p>
					<p><label>Format</label> <select id="one_format"  value={ one_format } onChange={ setOneFormat }>
						<option value="">Event with Form</option>
						<option value="button">Event with Button</option>
						<option value="form">Form Only</option>
						<option value="button_only">Button Only</option>
						<option value="compact">Compact (Headline/Date/Button)</option>
						<option value="embed_dateblock">Dates Only</option>
					</select></p>
					<p id="rsvpcontrol-hide-after"><label>Hide After</label> <select id="hide_past"  value={ hide_past } onChange={ setHidePast }>
						<option value="">Not Set</option>
						<option value="1">1 hour</option>
						<option value="2">2 hours</option>
						<option value="3">3 hours</option>
						<option value="4">4 hours</option>
						<option value="5">5 hours</option>
						<option value="6">6 hours</option>
						<option value="7">7 hours</option>
						<option value="8">8 hours</option>
						<option value="12">12 hours</option>
						<option value="18">18 hours</option>
						<option value="24">24 hours</option>
						<option value="48">2 days</option>
						<option value="72">3 days</option>
					</select></p>
					<p id="rsvpcontrol-event-type"><label>Event Type</label> <select id="type" value={ type } onChange={ setEventType }>
					{rsvpmaker_types.map(function(opt, i){
                    return <option value={ opt.value }>{opt.text}</option>;
                })}</select></p>				
					<p><label>Show Author</label> <select id="one_hideauthor"  value={ one_hideauthor } onChange={ setHideAuthor }>
						<option value="1">No</option>
						<option value="0">Yes</option>
					</select></p>
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

//[rsvpmaker_one post_id="0" hideauthor="1" showbutton="0" one_format="compact"]
				  
registerBlockType( 'rsvpmaker/upcoming', {
	// Block name. Block names must be string that contains a namespace prefix. Example: my-plugin/my-custom-block.
	title: __( 'RSVPMaker Upcoming Events' ), // Block title.
	icon: 'calendar-alt', // Block icon from Dashicons → https://developer.wordpress.org/resource/dashicons/.
	category: 'common', // Block category — Group blocks together based on common traits E.g. common, formatting, layout widgets, embed.
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
            no_events: {
                type: 'string',
                default: 'No events listed',
            },
            hideauthor: {
                type: 'boolean',
                default: true,
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
	const { attributes: { calendar, days, posts_per_page, hideauthor, no_events, nav, type }, setAttributes, isSelected } = props;

	function setCalendarDisplay( event ) {
		const selected = event.target.querySelector( '#calendar option:checked' );
		setAttributes( { calendar: selected.value } );
		event.preventDefault();
	}	
	function setNav( event ) {
		const selected = event.target.querySelector( '#nav option:checked' );
		setAttributes( { nav: selected.value } );
		event.preventDefault();
	}	
	function setPostsPerPage( event ) {
		const selected = event.target.querySelector( '#posts_per_page option:checked' );
		setAttributes( { posts_per_page: selected.value } );
		event.preventDefault();
	}	
	function setDays( event ) {
		const selected = event.target.querySelector( '#days option:checked' );
		setAttributes( { days: selected.value } );
		event.preventDefault();
	}	
	function setNoEvents( event ) {
		var no_events = document.getElementById('no_events').value;
		setAttributes( { agenda_note: no_events } );
		event.preventDefault();
	}
	function setEventType( event ) {
		const selected = event.target.querySelector( '#type option:checked' );
		setAttributes( { type: selected.value } );
		event.preventDefault();
	}	

	function showFormPrompt () {
		return <p><strong>Click here to set options.</strong></p>
	}
		
	function showForm() {
			return (
				<form onSubmit={ setCalendarDisplay, setNav, setNoEvents, setEventType } >
					<p><label>Display Calendar</label> <select id="calendar"  value={ calendar } onChange={ setCalendarDisplay }>
						<option value="1">Yes - Calendar plus events listing</option>
						<option value="0">No - Events listing only</option>
						<option value="2">Calendar Only</option>
					</select></p>
					<p><label>Events Per Page</label> <select id="posts_per_page"  value={ posts_per_page } onChange={ setPostsPerPage }>
						<option value="5">5</option>
						<option value="10">10</option>
						<option value="15">15</option>
						<option value="20">15</option>
						<option value="30">15</option>
						<option value="-1">No limit</option>
					</select></p>
					<p><label>Date Range</label> <select id="days" value={ days } onChange={ setDays }>
						<option value="30">30 days</option>
						<option value="60">60 days</option>
						<option value="90">90 days</option>
						<option value="180">180 days</option>
						<option valu="365">1 Year</option>
					</select></p>
				<p id="rsvpcontrol-event-type"><label>Event Type</label> <select id="type" value={ type } onChange={ setEventType }>
					{rsvpmaker_types.map(function(opt, i){
                    return <option value={ opt.value }>{opt.text}</option>;
                })}</select></p>				
					<p><label>Calendar Navigation</label> <select id="nav"  value={ nav } onChange={ setNav }>
						<option value="top">Top</option>
						<option value="bottom">Bottom</option>
						<option value="both">Both</option>
					</select></p>
					<p>Text to show for no events listed<br />
				<input type="text" id="no_events" onChange={setNoEvents} defaultValue={no_events} /> 
				</p>
				
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
