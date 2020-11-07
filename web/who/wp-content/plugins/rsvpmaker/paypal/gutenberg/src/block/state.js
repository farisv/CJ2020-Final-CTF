const DEFAULT_STATE = {
	week: [],
  dayofweek: [],
  hour: '12',
  minutes: '00',
  end: '13:00',
  stop: '',
  duration: '',
};

// This is the reducer
function reducer( state = DEFAULT_STATE, action ) {
  var newstate = state;
  if ( action.type === 'UPDATE_WEEK' ) {
    newstate.week = action.week;
  }
  if ( action.type === 'UPDATE_DOW' ) {
    newstate.dayofweek = action.dayofweek;
  }
  if ( action.type === 'UPDATE_HOUR' ) {
    newstate.hour = action.hour;
  }
  if ( action.type === 'UPDATE_MINUTES' ) {
    newstate.minutes = action.minutes;
  }
  if ( action.type === 'UPDATE_END_TIME' ) {
    newstate.end = action.end;
  }
  if ( action.type === 'UPDATE_STOP' ) {
    newstate.stop = action.stop;
  }
  if ( action.type === 'UPDATE_DURATION' ) {
    newstate.stop = action.stop;
  }
  return newstate;
}

function setWeek( week ) {
  return {
    type: 'UPDATE_WEEK',
    week: week,
  };
}
function getWeek( state ) {
  return state.week;
}
function setDOW( dow ) {
  return {
    type: 'UPDATE_DOW',
    dow: dow,
  };
}
function getDOW( state ) {
  return state.dow;
}
function setHour( hour ) {
  return {
    type: 'UPDATE_HOUR',
    hour: hour,
  };
}
function getHour( state ) {
  return state.hour;
}
function setMinutes( minutes ) {
  return {
    type: 'UPDATE_MINUTES',
    minutes: minutes,
  };
}
function getMinutes( state ) {
  return state.minutes;
}
function setEnd( end ) {
  return {
    type: 'UPDATE_END_TIME',
    end: end,
  };
}
function getEnd( state ) {
  return state.end;
}
function setStop( stop ) {
  return {
    type: 'UPDATE_STOP',
    stop: stop,
  };
}
function getStop( state ) {
  return state.stop;
}
function setDuration( duration ) {
  return {
    type: 'UPDATE_DURATION',
    duration: duration,
  };
}
function getDuration( state ) {
  return state.duration;
}

// Now let's register our custom namespace
var myNamespace = 'rsvpevent';
wp.data.registerStore( 'rsvpevent', { 
  reducer: reducer,
  selectors: { getWeek: getWeek, getDOW: getDOW, getHour: getHour, getMinutes: getMinutes, getEnd: getEnd, getStop: getStop, getDuration: getDuration},
  actions: { setWeek: setWeek, setDOW: setDOW, setHour: setHour, setMinutes: setMinutes, setEnd: setEnd, setStop: setStop, setDuration: setDuration},
} );
