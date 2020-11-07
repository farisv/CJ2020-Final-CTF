const { __ } = wp.i18n; // Import __() from wp.i18n
//const { registerBlockType } = wp.blocks; // Import registerBlockType() from wp.blocks
const el = wp.element.createElement;
const { DateTimePicker, RadioControl, SelectControl, TextControl, TextareaControl,FormToggle } = wp.components;
const { withState } = wp.compose;
const { withSelect, withDispatch } = wp.data;
import './state.js';

var TemplateTextControl = wp.compose.compose(
	withDispatch( function( dispatch, props ) {
    //if(props.key == 'hour')
    return {
			setValue: function( value ) {
        console.log('new value');
        console.log(value);
				dispatch( 'rsvpevent' ).setHour(value);
			}
		}
	} ),
	withSelect( function( select, props ) {
    //if(props.key == 'hour')
		return {
			value: select( 'rsvpevent' ).getHour(),
		}
	} ) )( function( props ) {
    console.log(props);
		return el( TextControl, {
			label: props.label,
			value: props.value,
			onChange: props.setValue,
		});
	}
);

const RSVPTemplate = withState( {
    week: wp.data.select('rsvpevent').getWeek(),
    dow: wp.data.select('rsvpevent').getDOW(),
    hour: wp.data.select('rsvpevent').getHour(),
    minutes: wp.data.select('rsvpevent').getMinutes(),
    end: wp.data.select('rsvpevent').getEnd(),
    stop: wp.data.select('rsvpevent').getStop(),
    duration: wp.data.select('rsvpevent').getDuration(),
    setSked: function (value, key, index) {
		if(key == hour)
			wp.data.dispatch('rsvpevent').setHour(value);
    }
} )( ( props ) => {
	return (
		<div>
			<TextControl label='hour' value={props.hour}  />
		</div>
	);
} ); //onChange={(value) => {setSked(value,'hour',0)}}

export {TemplateTextControl};// default RSVPTemplate;