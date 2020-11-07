/**
 * BLOCK: limited time
 *
 */

const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const { RichText } = wp.blockEditor;
const { Fragment } = wp.element;
const { InnerBlocks, BlockControls } = wp.editor;
const { Component } = wp.element;
const { InspectorControls } = wp.editor;
const { PanelBody, DateTimePicker, SelectControl } = wp.components;

registerBlockType( 'rsvpmaker/limited', {
	title: ( 'Limited Time Content (RSVPMaker)' ), // Block title.
	icon: 'admin-comments', 
	category: 'layout',
	keywords: [
		( 'Expiration' ),
		( 'Start Time' ),
		( 'Wrapper' ),
	],
attributes: {
        content: {
            type: 'array',
            source: 'children',
            selector: 'p',
        },
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
	delete_expired: {
		type: 'string',
		default: '0',
	},
},

    edit: function( props ) {	

	const { attributes, className, setAttributes, isSelected } = props;

	return (
		<Fragment>
		<TimeInspector { ...props } />
<div className={className} >
<div class="limited_border">{__('START Limited time content (click to set start and end times)')}</div>
	<InnerBlocks />
<div class="limited_border">{__('END Limited time content)')}</div>
</div>
		</Fragment>
		);
    },
    save: function( { attributes, className } ) {
		return <div className={className}><InnerBlocks.Content /></div>;
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
				label={ __( 'Delete or Hide Expired Content', 'rsvpmaker' ) }
				value={ attributes.delete_expired }
				onChange={ ( delete_expired ) => setAttributes( { delete_expired } ) }
				options={ [
					{ value: 0, label: __( 'Hide', 'rsvpmaker' ) },
					{ value: 1, label: __( 'Delete', 'rsvpmaker' ) },
				] }
				/>
				</PanelBody>
			</InspectorControls>
		);
	}
}