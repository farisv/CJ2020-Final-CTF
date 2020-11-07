/**
 * BLOCK: form fields
 *
 */

const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const { RichText } = wp.blockEditor;
const { Fragment } = wp.element;
const { InnerBlocks, BlockControls } = wp.editor;
const { Component } = wp.element;
const { InspectorControls } = wp.editor;
const { PanelBody, SelectControl, TextControl, ToggleControl, RadioControl } = wp.components;
if((typeof rsvpmaker_ajax !== 'undefined') && (rsvpmaker_ajax.special == 'RSVP Form'))
registerBlockType( 'rsvpmaker/formfield', {
	// Block name. Block names must be string that contains a namespace prefix. Example: my-plugin/my-custom-block.
	title: __( 'RSVPField Text' ), // Block title.
	icon: 'products', // Block icon from Dashicons → https://developer.wordpress.org/resource/dashicons/.
	category: 'common', // Block category — Group blocks together based on common traits E.g. common, formatting, layout widgets, embed.
	keywords: [
		__( 'RSVPMaker' ),
		__( 'Form' ),
		__( 'Text Field' ),
	],
       attributes: {
            label: {
            type: 'string',
            default: 'Label',
            },
            slug: {
            type: 'string',
            default: '',
            },
            guestform: {
            type: 'boolean',
            default: false,
            },
            sluglocked: {
            type: 'boolean',
            default: false,
            },
            required: {
            type: 'string',
            default: '',
            },
        },
	edit: function( props ) {
	const { attributes: { label, slug, required, guestform }, setAttributes, isSelected } = props;
	var profilename = 'profile['+slug+']';
			return (
			<Fragment>
			<FieldInspector {...props} />
			<div className={ props.className }>
<p><label>{label}:</label> <span className={required}><input className={slug} type="text" name={profilename} id={slug} value="" /></span></p>
{isSelected && (<div><em>{__('Set form label and other properties in sidebar. For use within an RSVPMaker registration form.','rsvpmaker')}</em></div>) }
				</div>
			</Fragment>
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
	save: function(props) {
	return null;
},
} );

if((typeof rsvpmaker_ajax !== 'undefined') && (rsvpmaker_ajax.special == 'RSVP Form'))
registerBlockType( 'rsvpmaker/formtextarea', {
	// Block name. Block names must be string that contains a namespace prefix. Example: my-plugin/my-custom-block.
	title: __( 'RSVPField Text Area' ), // Block title.
	icon: 'products', // Block icon from Dashicons → https://developer.wordpress.org/resource/dashicons/.
	category: 'common', // Block category — Group blocks together based on common traits E.g. common, formatting, layout widgets, embed.
	keywords: [
		__( 'RSVPMaker' ),
		__( 'Form' ),
		__( 'Text Area' ),
	],
       attributes: {
            label: {
            type: 'string',
            default: 'Label',
            },
            slug: {
            type: 'string',
            default: '',
            },
            rows: {
            type: 'string',
            default: '3',
            },
            guestform: {
            type: 'boolean',
            default: false,
            },
        },
	edit: function( props ) {
	const { attributes: { label, slug, rows, guestform }, setAttributes, isSelected } = props;
	var profilename = 'profile['+slug+']';
			return (
			<Fragment>
			<TextAreaInspector {...props} />
			<div className={ props.className }>
<p><label>{label}:</label></p> <p><textarea rows={rows} className={slug} type="text" name={profilename} id={slug}></textarea></p>
<div><em>{__('Set properties in sidebar. Intended for use within an RSVPMaker registration form.','rsvpmaker')}</em></div>
			</div>
			</Fragment>
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
	save: function(props) {
	return null;
	},
} );

if((typeof rsvpmaker_ajax !== 'undefined') && (rsvpmaker_ajax.special == 'RSVP Form'))
registerBlockType( 'rsvpmaker/formnote', {
	// Block name. Block names must be string that contains a namespace prefix. Example: my-plugin/my-custom-block.
	title: __( 'RSVPField Note' ), // Block title.
	icon: 'products', // Block icon from Dashicons → https://developer.wordpress.org/resource/dashicons/.
	category: 'common', // Block category — Group blocks together based on common traits E.g. common, formatting, layout widgets, embed.
	keywords: [
		__( 'RSVPMaker' ),
		__( 'Form' ),
		__( 'Note' ),
	],
       attributes: {
            label: {
            type: 'string',
            default: 'Note',
            },
        },
	edit: function( props ) {
	const { attributes: { label }, setAttributes, isSelected } = props;
			return (
			<Fragment>
			<TextAreaInspector {...props} />
			<div>
			</div><p>Note:<br /><textarea name="note"></textarea></p><div><em>{__('Note for bottom of RSVP form. Only one allowed. Use RSVPField Text Area for any additional text fields. Set properties in sidebar. Intended for use within an RSVPMaker registration form.','rsvpmaker')}</em>
			</div>
			</Fragment>
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
	save: function(props) {
	return null;
	},

} );

class FieldInspector extends Component {
	render() {
	const { attributes, setAttributes, className } = this.props;
	let toggleRequired = (attributes.required == 'required'); //make true/false
	function setLabel(label) {
		const slug = attributes.slug;
		if(attributes.sluglocked)
			{//don't change default required slugs
			setAttributes({label: label});
			return;
			}
		let simpleSlug = label.replace(/[^A-Za-z0-9]+/g,'_');
		simpleSlug = simpleSlug.trim().toLowerCase();
		setAttributes({slug: simpleSlug});
		setAttributes({label: label});
		setAttributes({guestform: true});
	}
	function setRequired(toggleRequired) {
		let required = (toggleRequired) ? 'required' : '';
		setAttributes({required: required});
	}
		return (
			<InspectorControls key="fieldinspector">
			<PanelBody title={ __( 'Field Properties', 'rsvpmaker' ) } >
			<TextControl
				label={ __( 'Label', 'rsvpmaker' ) }
				value={ attributes.label }
				onChange={ ( label ) => setLabel( label  ) }
			/>
			<ToggleControl
				label={ __( 'Required', 'rsvpmaker' ) }
				checked={ toggleRequired }
				help={ attributes.required ? 'Required' : 'Not required' } 
				onChange={ ( toggleRequired ) => {setRequired( toggleRequired ) }}
			/>
			<ToggleControl
				label={ __( 'Include on Guest Form', 'rsvpmaker' ) }
				checked={ attributes.guestform }
				help={ attributes.guestform ? 'Included' : 'Not included' } 
				onChange={ ( guestform ) => {setAttributes( {guestform: guestform} ) }}
			/>
				</PanelBody>
				</InspectorControls>
		);	} }

class TextAreaInspector extends Component {
	render() {
	const { attributes, setAttributes, className } = this.props;
	function setLabel(label) {
		const slug = attributes.slug;
		let simpleSlug = label.replace(/[^A-Za-z0-9]+/g,'_');
		simpleSlug = simpleSlug.trim().toLowerCase();
		setAttributes({slug: simpleSlug});
		setAttributes({label: label});
		setAttributes({guestform: true});
	}
		return (
			<InspectorControls key="fieldinspector">
			<PanelBody title={ __( 'Field Properties', 'rsvpmaker' ) } >
			<TextControl
				label={ __( 'Label', 'rsvpmaker' ) }
				value={ attributes.label }
				onChange={ ( label ) => setLabel( label  ) }
			/>
    <SelectControl
        label="Rows"
        value={ attributes.rows }
        options={ [
            { label: '2', value: '2' },
            { label: '3', value: '3' },
            { label: '4', value: '4' },
            { label: '5', value: '5' },
            { label: '6', value: '6' },
            { label: '7', value: '7' },
            { label: '8', value: '8' },
            { label: '9', value: '9' },
            { label: '10', value: '10' },
        ] }
        onChange={ ( rows ) => { setAttributes( { rows: rows } ) } }
    />
			<ToggleControl
				label={ __( 'Include on Guest Form', 'rsvpmaker' ) }
				checked={ attributes.guestform }
				help={ attributes.required ? 'Included' : 'Not included' } 
				onChange={ ( guestform ) => {setAttributes( {guestform: guestform} ) }}
				 
			/>
				</PanelBody>
				</InspectorControls>
		);	} }
		
if((typeof rsvpmaker_ajax !== 'undefined') && (rsvpmaker_ajax.special == 'RSVP Form'))
	registerBlockType( 'rsvpmaker/formselect', {
	// Block name. Block names must be string that contains a namespace prefix. Example: my-plugin/my-custom-block.
	title: __( 'RSVPField Select' ), // Block title.
	icon: 'products', // Block icon from Dashicons → https://developer.wordpress.org/resource/dashicons/.
	category: 'common', // Block category — Group blocks together based on common traits E.g. common, formatting, layout widgets, embed.
	keywords: [
		__( 'RSVPMaker' ),
		__( 'Form' ),
		__( 'Select' ),
	],
       attributes: {
            label: {
            type: 'string',
            default: 'Label',
            },
            slug: {
            type: 'string',
            default: '',
            },
            choicearray: {
            type: 'array',
            default: ['a','b'],
            },
            guestform: {
            type: 'boolean',
            default: false,
            },
        },
	edit: function( props ) {
		// Creates a <p class='wp-block-cgb-block-toast-block'></p>.
	const { attributes: { label, slug, choicearray, guestform }, setAttributes, isSelected } = props;
	var profilename = 'profile['+slug+']';
			return (
			<Fragment>
			<ChoiceInspector {...props} />
			<div className={ props.className }>
<p><label>{label}:</label> <span><select className={slug} name={profilename} id={slug} >{choicearray.map(function(opt, i){
                    return <option value={ opt }>{opt}</option>;
                })}</select></span></p>
{isSelected && (<div><em>{__('Set form label and other properties in sidebar. For use within an RSVPMaker registration form.','rsvpmaker')}</em></div>) }
			</div>
			</Fragment>
			);
	},

	save: function(props) {
	return null;
	},
} );

if((typeof rsvpmaker_ajax !== 'undefined') && (rsvpmaker_ajax.special == 'RSVP Form'))
registerBlockType( 'rsvpmaker/formradio', {
	// Block name. Block names must be string that contains a namespace prefix. Example: my-plugin/my-custom-block.
	title: __( 'RSVPField Radio Buttons' ), // Block title.
	icon: 'products', // Block icon from Dashicons → https://developer.wordpress.org/resource/dashicons/.
	category: 'common', // Block category — Group blocks together based on common traits E.g. common, formatting, layout widgets, embed.
	keywords: [
		__( 'RSVPMaker' ),
		__( 'Form' ),
		__( 'Radio Buttons' ),
	],
       attributes: {
            label: {
            type: 'string',
            default: 'Label',
            },
            slug: {
            type: 'string',
            default: '',
            },
            choicearray: {
            type: 'array',
            default: ['a','b'],
            },
            guestform: {
            type: 'boolean',
            default: false,
            },
        },
	edit: function( props ) {
		// Creates a <p class='w-p-block-cgb-block-toast-block'></p>.
	const { attributes: { label, slug, choicearray, guestform }, setAttributes, isSelected } = props;
	var profilename = 'profile['+slug+']';
			return (
			<Fragment>
			<ChoiceInspector {...props} />
			<div className={ props.className }>
<p><label>{label}:</label> <span>{choicearray.map(function(opt, i){
                    return <span><input type="radio" className={slug} name={profilename} id={slug} value={opt} /> {opt} </span>;
                })}</span></p>
{isSelected && (<div><em>{__('Set form label and other properties in sidebar. For use within an RSVPMaker registration form.','rsvpmaker')}</em></div>) }
			</div>
			</Fragment>
			);
	},

	save: function(props) {
	return null;
	},
} );

class ChoiceInspector extends Component {
	render() {
	const { attributes, setAttributes, className } = this.props;
	const choices =attributes.choicearray.join(',');
	function setLabel(label) {
		let simpleSlug = label.replace(/[^A-Za-z0-9]+/g,'_');
		simpleSlug = simpleSlug.trim().toLowerCase();
		setAttributes({slug: simpleSlug});
		setAttributes({label: label});
		setAttributes({guestform: true});
	}
		
	function setChoices(choices) {
		setAttributes({choicearray: choices.split(',')});
	}
		return (
			<InspectorControls key="choiceinspector">
			<PanelBody title={ __( 'Field Properties', 'rsvpmaker' ) } >
			<TextControl
				label={ __( 'Label', 'rsvpmaker' ) }
				value={ attributes.label }
				onChange={ ( label ) => setLabel(label) }
			/>
			<TextControl
				label={ __( 'Choices', 'rsvpmaker' ) }
				value={ choices }
				onChange={ ( choices ) => setChoices( choices  ) }
			/>
				<div><em>Separate choices with a comma</em></div>
			<ToggleControl
				label={ __( 'Include on Guest Form', 'rsvpmaker' ) }
				checked={ attributes.guestform }
				help={ attributes.required ? 'Included' : 'Not included' } 
				onChange={ ( guestform ) => {setAttributes( {guestform: guestform} ) }}
			/>
				</PanelBody>
				</InspectorControls>
		);	} }

if((typeof rsvpmaker_ajax !== 'undefined') && (rsvpmaker_ajax.special == 'RSVP Form'))
	registerBlockType( 'rsvpmaker/guests', {
	title: ( 'RSVPField Guests' ), // Block title.
	icon: 'products', 
	category: 'common',
	keywords: [
		( 'RSVPMaker' ),
		( 'Form' ),
		( 'Guests' ),
	],
       attributes: {
            limit: {
            type: 'string',
            default: '',
            },
        },

    edit: function( props ) {	

	const { attributes, className, setAttributes, isSelected } = props;

	return (
<div className={className} >
<h3>{__("Guest Fields",'rsvpmaker')}</h3>
    <SelectControl
        label="Limit (if any)"
        value={ attributes.limit }
        options={ [
             { label: __('No limit','rsvpmaker'), value: '' },
           { label: '1', value: '1' },
            { label: '2', value: '2' },
            { label: '3', value: '3' },
            { label: '4', value: '4' },
            { label: '5', value: '5' },
            { label: '6', value: '6' },
            { label: '7', value: '7' },
            { label: '8', value: '8' },
            { label: '9', value: '9' },
            { label: '10', value: '10' },
        ] }
        onChange={ ( limit ) => { setAttributes( { limit: limit } ) } }
    />
<div className="guestnote">{__('Guests section will include fields you checked off above (such as First Name, Last Name), plus any others you embed below (information to be collected about guests ONLY).','rsvpmaker')}<ul><li>{__('You MUST check "Include on Guest Form"','rsvpmaker')}</li><li>{__('"Required" checkbox does not work in guest fields','rsvpmaker')}</li><li>{__('This block is not intended for use outside of an RSVPMaker RSVP Form document','rsvpmaker')}</li></ul></div>
	<InnerBlocks />
</div>
		);
    },
    save: function( { attributes, className } ) {
		return <div className={className}><InnerBlocks.Content /></div>;
    }
});

if((typeof rsvpmaker_ajax !== 'undefined') && (rsvpmaker_ajax.special == 'RSVP Form'))
registerBlockType( 'rsvpmaker/formchimp', {
	// Block name. Block names must be string that contains a namespace prefix. Example: my-plugin/my-custom-block.
	title: __( 'RSVPField Mailchimp Checkbox' ), // Block title.
	icon: 'products', // Block icon from Dashicons → https://developer.wordpress.org/resource/dashicons/.
	category: 'common', // Block category — Group blocks together based on common traits E.g. common, formatting, layout widgets, embed.
	keywords: [
		__( 'RSVPMaker' ),
		__( 'Form' ),
		__( 'Mailchimp' ),
	],
       attributes: {
            checked: {
            type: 'boolean',
            default: false,
            },
            label: {
            type: 'string',
            default: 'Add me to your email list',
            },
        },
	edit: function( props ) {
	const { attributes: { label, checked }, setAttributes, isSelected } = props;
	let slug = 'email_list_ok';
	let profilename = 'profile['+slug+']';
			return (
			<Fragment>
			<ChimpInspector  {...props} />
			<div className={ props.className }>
<p><input className={slug} type="checkbox" name={profilename} id={slug} value="1" checked={checked} /> {label}</p>
{isSelected && (<div><em>{__('Set form label and other properties in sidebar. For use within an RSVPMaker registration form.','rsvpmaker')}</em></div>) }
			</div>
			</Fragment>
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
	save: function(props) {
	const { attributes: { label, checked } } = props;
	let slug = 'email_list_ok';
	let profilename = 'profile['+slug+']';
	
		// server render
			return (
			<div className={ props.className }>
<p><input className={slug} type="checkbox" name={profilename} id={slug} value="1" checked={checked} /> {label}</p>
			</div>
			);
	},
} );

class ChimpInspector extends Component {
	render() {
	const { attributes, setAttributes, className } = this.props;
		return (
			<InspectorControls key="fieldinspector">
			<PanelBody title={ __( 'Field Properties', 'rsvpmaker' ) } >
			<TextControl
				label={ __( 'Label', 'rsvpmaker' ) }
				value={ attributes.label }
				onChange={ ( label ) => setAttributes( {label: label} ) }
			/>
			<ToggleControl
				label={ __( 'Checked by Default', 'rsvpmaker' ) }
				checked={ attributes.checked }
				help={ attributes.checked ? 'Included' : 'Not included' } 
				onChange={ ( checked ) => {setAttributes( {checked: checked} ) }}
			/>
				</PanelBody>
				</InspectorControls>
		);	} }
