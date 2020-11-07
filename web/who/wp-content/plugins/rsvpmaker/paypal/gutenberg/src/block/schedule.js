/**
 * BLOCK: limited time
 *
 */

const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const { RichText } = wp.blockEditor;
const { Fragment } = wp.element;
const { Component } = wp.element;
const { InspectorControls } = wp.editor;
const { PanelBody, DateTimePicker, SelectControl } = wp.components;
import apiFetch from '@wordpress/api-fetch';
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

registerBlockType( 'rsvpmaker/schedule', {
	title: ( 'RSVPMaker Schedule' ), // Block title.
    icon: 'admin-comments',
    description: __('Daily schedule of events'),
	category: 'layout',
	keywords: [
		( 'RSVPMaker' ),
		( 'Event' ),
		( 'Schedule' ),
	],
attributes: {
	start: {
		type: 'string',
		default: '',
	},
	start_on: {
		type: 'string',
		default: '0',
	},
	end: {
		type: 'string',
		default: '',
	},
	end_on: {
		type: 'string',
		default: '0',
	},
	type: {
		type: 'string',
		default: '0',
	},
},

    edit: function( props ) {	

	const { attributes, className, setAttributes, isSelected } = props;

	return (
        <Fragment>
        <TimeInspector { ...props } />
		<div className="schedule-placeholder">{__('Daily schedule of events')}</div>
        </Fragment>
		);
    },
    save: function() {
		return null;
    }
});

class TimeInspector extends Component {

	render() {
		const { attributes, setAttributes, className } = this.props;
		return (
			<InspectorControls key="inspector">
			<PanelBody title={ __( 'Start Time', 'rsvpmaker' ) } >
					<SelectControl
							label={ __( 'Set Start Time', 'rsvpmaker' ) }
							value={ attributes.start_on }
							onChange={ ( start_on ) => setAttributes( { start_on } ) }
							options={ [
								{ value: 0, label: __( 'No', 'rsvpmaker' ) },
								{ value: 1, label: __( 'Yes', 'rsvpmaker' ) },
							] }
						/>
		
			{(attributes.start_on > 0) && (
			<DateTimePicker
		    is12Hour={ true }
		    currentDate={ attributes.start }
			onChange={ ( start ) => setAttributes( { start })}
		    />									 
			 )}
				</PanelBody>
			<PanelBody title={ __( 'End Time', 'rsvpmaker' ) } >
					<SelectControl
							label={ __( 'Set End Time', 'rsvpmaker' ) }
							value={ attributes.end_on }
							onChange={ ( end_on ) => setAttributes( { end_on } ) }
							options={ [
								{ value: 0, label: __( 'No', 'rsvpmaker' ) },
								{ value: 1, label: __( 'Yes', 'rsvpmaker' ) },
							] }
						/>
			{(attributes.end_on > 0) && (
			 <div id="endtime">
		<DateTimePicker
		    is12Hour={ true }
		    currentDate={ attributes.end }
			onChange={ ( end ) => setAttributes( { end })}
		    />
			</div>
			 )}

     <SelectControl
        label={__("Event Type",'rsvpmaker')}
        value={ attributes.type }
        options={ rsvptypes }
        onChange={ ( type ) => { setAttributes( { type: type } ) } }
    />		
    </PanelBody>
			</InspectorControls>
		);
	}
}