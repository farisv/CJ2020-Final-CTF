/**
 * BLOCK: stripeformwrapper
 *
 */

const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const { RichText } = wp.blockEditor;
const { Fragment } = wp.element;
const { InnerBlocks, BlockControls } = wp.editor;
const { Component } = wp.element;
const { InspectorControls } = wp.editor;
const { PanelBody, TextControl, SelectControl } = wp.components;

registerBlockType( 'rsvpmaker/stripe-form-wrapper', {
	title: ( 'Stripe Form Wrapper' ), // Block title.
	icon: 'admin-comments', 
	category: 'layout',
	keywords: [
		( 'Stripe' ),
		( 'Form' ),
		( 'Wrapper' ),
	],
attributes: {
        content: {
            type: 'array',
            source: 'children',
            selector: 'p',
        },
	amount: {
		type: 'string',
		default: '',
	},
	paymentType: {
		type: 'string',
		default: '',
	},
	description: {
		type: 'string',
		default: '',
	},
},

    edit: function( props ) {	

	const { attributes, className, setAttributes, isSelected } = props;

	return (
		<Fragment>
		<StripeInspector { ...props } />
<div className={className} >
<div class="stripe-wrapper-border">{__('START Stripe form wrapper')}</div>
	<InnerBlocks template={[
    [ 'rsvpmaker/formfield', { label:'Name',slug:'name',sluglocked: true, guestform:false } ], 
]} />
<div class="stripe-wrapper-border">{__('END Stripe form wrapper')}</div>
</div>
		</Fragment>
		);
    },
    save: function( { attributes, className } ) {
		return <div className={className}><InnerBlocks.Content /></div>;
    }
});

class StripeInspector extends Component {

	render() {
		const { attributes, setAttributes, className } = this.props;
		return (
			<InspectorControls key="inspector">
			<PanelBody title={ __( 'Payment', 'rsvpmaker' ) } >
			<SelectControl
							label={ __( 'Payment Type', 'rsvpmaker' ) }
							value={ attributes.paymentType }
							onChange={ ( paymentType ) => setAttributes( { paymentType } ) }
							options={ [
								{ value: '', label: __( 'One Time', 'rsvpmaker' ) },
								{ value: 'subscription:monthly', label: __( 'Recurring Payment: monthly', 'rsvpmaker' ) },
								{ value: 'subscription:6 months', label: __( 'Recurring Payment: every 6 months', 'rsvpmaker' ) },
								{ value: 'subscription:1 year', label: __( 'Recurring Payment: annual', 'rsvpmaker' ) },
							] }
						/>
					<TextControl
							label={ __( 'Amount', 'rsvpmaker' ) }
							value={ attributes.amount }
							onChange={ ( amount ) => setAttributes( { amount } ) }
						/>
					<TextControl
							label={ __( 'Description', 'rsvpmaker' ) }
							value={ attributes.description }
							onChange={ ( description ) => setAttributes( { description } ) }
						/>
				</PanelBody>
			</InspectorControls>
		);
	}
}