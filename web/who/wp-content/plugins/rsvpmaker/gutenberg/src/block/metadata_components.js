const { __ } = wp.i18n; // Import __() from wp.i18n
//const { registerBlockType } = wp.blocks; // Import registerBlockType() from wp.blocks
const el = wp.element.createElement;
const { DateTimePicker, RadioControl, SelectControl, TextControl, TextareaControl,FormToggle } = wp.components;
const { withSelect, withDispatch } = wp.data;
const { Fragment } = wp.element;
import apiFetch from '@wordpress/api-fetch';

import { __experimentalGetSettings } from '@wordpress/date';

const settings = __experimentalGetSettings();
// To know if the current timezone is a 12 hour time with look for "a" in the time format
// We also make sure this a is not escaped by a "/"

const is12HourTime = /a(?!\\)/i.test(
	settings.formats.time
		.toLowerCase() // Test only the lower case a
		.replace( /\\\\/g, '' ) // Replace "//" with empty strings
		.split( '' )
		.reverse()
		.join( '' ) // Reverse the string and test for "a" not followed by a slash
);	

function HourOptions () {
	
	var hourarray = [];
	
	for(var i=0; i < 24; i++)
		hourarray.push(i);
	return 	hourarray.map(function(hour) {
		var displayhour = '';
		var valuehour = '';
		var ampm = '';
		if(hour < 10)
			valuehour = displayhour = '0'+hour.toString();
		else
			valuehour = displayhour = hour.toString();
		if(is12HourTime) {
			if(hour > 12) {
				displayhour = (hour - 12).toString();
				ampm = 'pm';
			}
			else if(hour == 12) {
				displayhour = hour.toString();
				ampm = 'pm';
			}
			else if(hour == 0) {
				displayhour = __('midnight','rsvpmaker');
			}
			else {
				displayhour = hour.toString();					
				ampm = 'am';	
			}
		}
		return <option value={valuehour}>{displayhour} {ampm}</option>;
	} );
}

function MinutesOptions() {
	return (
		<Fragment>
		<option value='00'>00</option>
		<option value='15'>15</option>
		<option value='30'>30</option>
		<option value='45'>45</option>
		<option value='01'>01</option>
		<option value='02'>02</option>
		<option value='03'>03</option>
		<option value='04'>04</option>
		<option value='05'>05</option>
		<option value='06'>06</option>
		<option value='07'>07</option>
		<option value='08'>08</option>
		<option value='09'>09</option>
		<option value='10'>10</option>
		<option value='11'>11</option>
		<option value='12'>12</option>
		<option value='13'>13</option>
		<option value='14'>14</option>
		<option value='15'>15</option>
		<option value='16'>16</option>
		<option value='17'>17</option>
		<option value='18'>18</option>
		<option value='19'>19</option>
		<option value='20'>20</option>
		<option value='21'>21</option>
		<option value='22'>22</option>
		<option value='23'>23</option>
		<option value='24'>24</option>
		<option value='25'>25</option>
		<option value='26'>26</option>
		<option value='27'>27</option>
		<option value='28'>28</option>
		<option value='29'>29</option>
		<option value='30'>30</option>
		<option value='31'>31</option>
		<option value='32'>32</option>
		<option value='33'>33</option>
		<option value='34'>34</option>
		<option value='35'>35</option>
		<option value='36'>36</option>
		<option value='37'>37</option>
		<option value='38'>38</option>
		<option value='39'>39</option>
		<option value='40'>40</option>
		<option value='41'>41</option>
		<option value='42'>42</option>
		<option value='43'>43</option>
		<option value='44'>44</option>
		<option value='45'>45</option>
		<option value='46'>46</option>
		<option value='47'>47</option>
		<option value='48'>48</option>
		<option value='49'>49</option>
		<option value='50'>50</option>
		<option value='51'>51</option>
		<option value='52'>52</option>
		<option value='53'>53</option>
		<option value='54'>54</option>
		<option value='55'>55</option>
		<option value='56'>56</option>
		<option value='57'>57</option>
		<option value='58'>58</option>
		<option value='59'>59</option>
		</Fragment>
	);
}

var MetaTextControl = wp.compose.compose(
	withDispatch( function( dispatch, props ) {
		return {
			setMetaValue: function( metaValue ) {
				dispatch( 'core/editor' ).editPost(
					{ meta: { [ props.metaKey ]: metaValue } }
				);
			}
		}
	} ),
	withSelect( function( select, props ) {
		return {
			metaValue: select( 'core/editor' ).getEditedPostAttribute( 'meta' )[ props.metaKey ],
		}
	} ) )( function( props ) {
		return el( TextControl, {
			label: props.label,
			value: props.metaValue,
			onChange: function( content ) {
				props.setMetaValue( content );
			},
		});
	}
);

var MetaRadioControl = wp.compose.compose(
	withDispatch( function( dispatch, props ) {
		return {
			setMetaValue: function( metaValue ) {
				dispatch( 'core/editor' ).editPost(
					{ meta: { [ props.metaKey ]: metaValue } }
				);
			}
		}
	} ),
	withSelect( function( select, props ) {
		return {
			metaValue: select( 'core/editor' ).getEditedPostAttribute( 'meta' )[ props.metaKey ],
		}
	} ) )( function( props ) {
		return el( RadioControl, {
			label: props.label,
			selected: props.metaValue,
			options: props.options,
			onChange: function( content ) {
				props.setMetaValue( content );
			},
		});
	}
);

var MetaSelectControl = wp.compose.compose(
	withDispatch( function( dispatch, props ) {
		return {
			setMetaValue: function( metaValue ) {
				dispatch( 'core/editor' ).editPost(
					{ meta: { [ props.metaKey ]: metaValue } }
				);
			}
		}
	} ),
	withSelect( function( select, props ) {
		return {
			metaValue: select( 'core/editor' ).getEditedPostAttribute( 'meta' )[ props.metaKey ],
		}
	} ) )( function( props ) {
		return el( SelectControl, {
			label: props.label,
			value: props.metaValue,
			options: props.options,
			onChange: function( content ) {
				props.setMetaValue( content );
			},
		});
	}
);

var MetaEndDateControl = wp.compose.compose(

	withDispatch( function( dispatch, props ) {
		return {
			setMetaValue: function( metaValue ) {
				dispatch( 'core/editor' ).editPost(
					{ meta: { [props.timeKey]: metaValue } } //'_endfirsttime'
				);
			},
			setDisplay: function( value ) {
				dispatch( 'core/editor' ).editPost(
					{ meta: { [props.statusKey]: value } } //'_firsttime'
				);
			}
		}
	} ),
	withSelect( function( select, props ) {
		let metaValue = select( 'core/editor' ).getEditedPostAttribute( 'meta' )[props.timeKey];
		console.log('end time meta value');
		console.log(metaValue);
		var hour = '';
		var minutes = '';
		var parts;
		if((typeof metaValue === 'string') && (metaValue.indexOf(':') > 0))
			parts = metaValue.split(':');
		else
			{	
				parts = ['12','00'];
				console.log('props type '+props.type);
				if(props.type == 'date') {
					var time = select( 'core/editor' ).getEditedPostAttribute( 'meta' )['_rsvp_date'];
					console.log('event time');
					console.log(time);
					var p = time.split('/ :/');
					var h = parseInt(p[1])+1;
					if(h < 10)
					hour = '0'+h.toString();
					hour = h.toString();
					parts = [hour,p[2]];
				}
				else {
					hour = select( 'core/editor' ).getEditedPostAttribute( 'meta' )['_sked_hour'];
					minutes = select( 'core/editor' ).getEditedPostAttribute( 'meta' )['_sked_minutes'];
					var h = parseInt(hour)+1;
					if(h < 10)
					hour = '0'+h.toString();
					hour = h.toString();
					parts = [hour,minutes];
					console.log(parts);
				}

				}
		let display = select( 'core/editor' ).getEditedPostAttribute( 'meta' )[props.statusKey];
		console.log('end time display');
		console.log(display);
		return {
			parts: parts,
			display: display,
			//metaValue: select( 'core/editor' ).getEditedPostAttribute( 'meta' )[ '_endfirsttime' ],
		}
	} ) )( function( props ) {
		//inner function to handle change
		function getTimeValues(){
			var hour = document.querySelector( '#endhour option:checked' );
			var minutes = document.querySelector( '#endminutes option:checked' );
			if((typeof hour === 'undefined') || !hour )
				hour = '12';
			if((typeof minutes === 'undefined') || !minutes)
				minutes = '00';
			var newend = hour.value+':'+minutes.value;
			console.log('newend '+newend);
			return newend;
		}

		function handleChange () {
			props.setMetaValue(getTimeValues());
		}

		if(props.display != 'set')
		return <SelectControl
			label="Time Display"
			value={props.display}
			options={ [
				{ label: 'End Time Not Displayed', value: '' },
				{ label: 'Show End Time', value: 'set' },
				{ label: 'Add Day / Do Not Show Time', value: 'allday' },
			] }
			onChange={function( content ) {
				props.setDisplay( content );
			}}
		/> 

		return <div>
		<SelectControl
			label="Time Display"
			value={props.display}
			options={ [
				{ label: 'End Time Not Displayed', value: '' },
				{ label: 'Show End Time', value: 'set' },
				{ label: 'Add Day / Do Not Show Time', value: 'allday' },
			] }
			onChange={function( content ) {
				props.setDisplay( content );
			}}
		/> 
		End Time<br /><select id="endhour" value={props.parts[0]} onChange={ handleChange }>
		<HourOptions />
		</select>	
		<select id="endminutes" value={props.parts[1]} onChange={ handleChange } >
		<MinutesOptions />
		</select>	
		</div>
	}
);

var MetaTemplateEndDateControl = wp.compose.compose(
	withDispatch( function( dispatch, props ) {
		return {
			setMetaValue: function( metaValue ) {
				dispatch( 'core/editor' ).editPost(
					{ meta: { '_sked_end': metaValue } }
				);
			},
			setDisplay: function( value ) {
				dispatch( 'core/editor' ).editPost(
					{ meta: { '_sked_duration': value } }
				);
			}
		}
	} ),
	withSelect( function( select, props ) {
		let metaValue = select( 'core/editor' ).getEditedPostAttribute( 'meta' )[ '_sked_end' ];
		if((typeof metaValue === 'string') && (metaValue.indexOf(':') > 0))
			var parts = metaValue.split(':');
		else
			{
				let hour = select( 'core/editor' ).getEditedPostAttribute( 'meta' )[ '_sked_hour' ];
				let newhour = parseInt(hour)+1;
				if(newhour < 10)
					hour = '0'+newhour.toString();
				else
					hour = newhour.toString();
				let minutes = select( 'core/editor' ).getEditedPostAttribute( 'meta' )[ '_sked_minutes' ];
				var parts = [hour,minutes];
			}
		return {
			parts: parts,
			display: select( 'core/editor' ).getEditedPostAttribute( 'meta' )[ '_sked_duration' ],
			//metaValue: select( 'core/editor' ).getEditedPostAttribute( 'meta' )[ '_endfirsttime' ],
		}
	} ) )( function( props ) {
		//inner function to handle change
		function setHour(hour) {
			var newtime = hour+':'+props.parts[1];
			console.log(newtime);
			setMetaValue(newtime);
		}

		function setMinutes(minutes) {
			var newtime = props.parts[0]+':'+minutes;
			console.log(newtime);
			setMetaValue(newtime);
		}

		if(props.display != 'set')
		return <SelectControl
			label="Time Display"
			value={props.display}
			options={ [
				{ label: 'End Time Not Displayed', value: '' },
				{ label: 'Show End Time', value: 'set' },
				{ label: 'Add Day / Do Not Show Time', value: 'allday' },
			] }
			onChange={function( content ) {
				props.setDisplay( content );
			}}
		/> 

		return <div>
		<SelectControl
			label="Time Display"
			value={props.display}
			options={ [
				{ label: 'End Time Not Displayed', value: '' },
				{ label: 'Show End Time', value: 'set' },
				{ label: 'Add Day / Do Not Show Time', value: 'allday' },
			] }
			onChange={function( content ) {
				props.setDisplay( content );
			}}
		/> 
		End Time: <br /><select id="endhour" value={props.parts[0]} onChange={ (hour) => {setMetaValue(hour+':'+props.parts[1])} }>
		<HourOptions />
		</select>	
		<select id="endminutes" value={props.parts[1]} onChange={ (minutes) => {setMetaValue(props.parts[0]+':'+minutes);console.log(props.parts[0]+':'+minutes) } } >
		<MinutesOptions />
		</select>	
		</div>
	}
);

var MetaTemplateStartTimeControl = wp.compose.compose(
	withDispatch( function( dispatch, props ) {
		return {
			setHour: function( metaValue ) {
				dispatch( 'core/editor' ).editPost(
					{ meta: { '_sked_hour': metaValue } }
				);
			},
			setMinutes: function( value ) {
				dispatch( 'core/editor' ).editPost(
					{ meta: { '_sked_minutes': value } }
				);
			}
		}
	} ),
	withSelect( function( select, props ) {
		let hour = select( 'core/editor' ).getEditedPostAttribute( 'meta' )[ '_sked_hour' ];
		let minutes = select( 'core/editor' ).getEditedPostAttribute( 'meta' )[ '_sked_minutes' ];
		return {
			hour: hour,
			minutes: minutes,
		}
	} ) )( function( props ) {
		//inner function to handle change

		return <div>
		Start Time:<br /><select id="starthour" value={props.hour} onChange={ (hour) => {setHour(hour)} }>
		<HourOptions />
		</select>
		<select id="startminutes" value={props.minutes} onChange={ (minutes) => {setMinutes(minutes)} } >
		<MinutesOptions />
		</select>	
		</div>
	}
);

var MetaDateControl = wp.compose.compose(
	withDispatch( function( dispatch, props ) {
		return {
			setMetaValue: function( metaValue ) {
				metaValue = metaValue.replace('T',' ');
				apiFetch({path: rsvpmaker_json_url+'clearcache/'+rsvpmaker_ajax.event_id});
				dispatch( 'core/editor' ).editPost(
					{ meta: { [ props.metaKey ]: metaValue } }
				);
			}
		}
	} ),
	withSelect( function( select, props ) {
		return {
			metaValue: select( 'core/editor' ).getEditedPostAttribute( 'meta' )[ props.metaKey ],
		}
	} ) )( function( props ) {

		const settings = __experimentalGetSettings();
		// To know if the current timezone is a 12 hour time with look for "a" in the time format
		// We also make sure this a is not escaped by a "/"
		const is12HourTime = /a(?!\\)/i.test(
			settings.formats.time
				.toLowerCase() // Test only the lower case a
				.replace( /\\\\/g, '' ) // Replace "//" with empty strings
				.split( '' )
				.reverse()
				.join( '' ) // Reverse the string and test for "a" not followed by a slash
		);	

		return el( DateTimePicker, {
			label: props.label,
			is12Hour: is12HourTime,
			currentDate: props.metaValue,
			options: props.options,
			onChange: function( content ) {
				props.setMetaValue( content );
			},
		});
	}
);

var MetaTextareaControl = wp.compose.compose(
	withDispatch( function( dispatch, props ) {
		return {
			setMetaValue: function( metaValue ) {
				dispatch( 'core/editor' ).editPost(
					{ meta: { [ props.metaKey ]: metaValue } }
				);
			}
		}
	} ),
	withSelect( function( select, props ) {
		return {
			metaValue: select( 'core/editor' ).getEditedPostAttribute( 'meta' )[ props.metaKey ],
		}
	} ) )( function( props ) {
		return el( TextareaControl, {
			label: props.label,
			value: props.metaValue,
			onChange: function( content ) {
				props.setMetaValue( content );
			},
		});
	}
);

var MetaFormToggle = wp.compose.compose(
	withDispatch( function( dispatch, props ) {
		return {
			setMetaValue: function( metaValue ) {
				if(metaValue == null)
						{
						metaValue = false; //never submit a null value
						}
					dispatch( 'core/editor' ).editPost(
					{ meta: { [ props.metaKey ]: metaValue } }
				);
				//todo trigger change in week components for template
			}
		}
	} ),
	withSelect( function( select, props ) {
		let value = select( 'core/editor' ).getEditedPostAttribute( 'meta' )[ props.metaKey ];//boolvalue,
		if(value == null)
			value = false;
		return {
			metaValue: value,
		}
	} ) )( function( props ) {
		return <div class="rsvpmaker_toggles"><FormToggle checked={props.metaValue} 
		onChange={ function(  ) {
				props.setMetaValue( !props.metaValue );
			} }	
		/>&nbsp;{props.label} </div>
	}
);

export {MetaEndDateControl, MetaDateControl, MetaTextControl, MetaSelectControl, MetaRadioControl, MetaFormToggle, MetaTextareaControl};