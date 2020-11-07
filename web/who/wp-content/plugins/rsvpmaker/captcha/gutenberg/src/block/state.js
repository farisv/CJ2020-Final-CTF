const DEFAULT_STATE = {
	date: '',
};

// This is the reducer
function reducer( state = DEFAULT_STATE, action ) {
  var newstate = state;
  if ( action.type === 'UPDATE_DATE' ) {
    newstate.date = action.date;
  }
  if ( action.type === 'UPDATE_ON' ) {
    newstate.on = action.on;
  }
  return newstate;
}

//actions
//wp.data.select('rsvpevent').getRSVPdate() wp.data.dispatch('rsvpevent').setRSVPdate()
function setRSVPdate( date ) {
  return {
    type: 'UPDATE_DATE',
    date: date,
  };
}

function setRSVPMakerOn( on ) {
  return {
    type: 'UPDATE_ON',
    on: on,
  };
}

function setRsvpMeta( key, value ) {
if(key == '_rsvp_on')
  return {
    type: 'UPDATE_ON',
    on: value,
  };
}

// selectors
function getRSVPdate( state ) {
  return state.date;
}

function getRSVPMakerOn( state ) {
  return state.on;
}

// Now let's register our custom namespace
var myNamespace = 'rsvpevent';
wp.data.registerStore( 'rsvpevent', { 
  reducer: reducer,
  selectors: { getRSVPdate: getRSVPdate, getRSVPMakerOn: getRSVPMakerOn },
  actions: { setRSVPdate: setRSVPdate, setRsvpMeta: setRsvpMeta },
} );
