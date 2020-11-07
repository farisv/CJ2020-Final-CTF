import { TextControl } from '@wordpress/components';
import { withState } from '@wordpress/compose';

const RSVPMakerEmail = withState( {
    rsvpto: '',
} )( ( { rsvpto, setState } ) => ( 
    <TextControl
        label="RSVPMaker Email"
        value={ rsvpto }
        onChange={ ( rsvpto ) => setState( { rsvpto } ) }
    />
) );

export default RSVPMakerEmail;