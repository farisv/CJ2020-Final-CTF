jQuery(document).ready(function($) {

    $.ajaxSetup({
        headers: {
            'X-WP-Nonce': rsvpmaker_rest.nonce,
        }
    });
    
	$('.timezone_on').click( function () {

		$('.timezone_hint').each( function () {
		
		var utc = $(this).attr('utc');
		var target = $(this).attr('target');
		var localdate = new Date(utc);
		localstring = localdate.toString();
		$('#'+target).html('<div>'+localstring+'<div>');
		var data = {
			'action': 'rsvpmaker_localstring',
			'localstring': localstring
		};
		jQuery.post(ajaxurl, data, function(response) {
		$('#'+target).html('<div>'+response+'</div>');
		});
		
		});
	});

$('.signed_up_ajax').each( function () {

var post = $(this).attr('post');
var data = {
	'event': post,
};
jQuery.get(rsvpmaker_rest.rest_url+'rsvpmaker/v1/signed_up', data, function(response) {
$('#signed_up_'+post).html(response);
});

});

var guestlist = '';

function format_guestlist(guest) {
if(!guest.first)
    return;
guestlist = guestlist.concat('<h3>'+guest.first);
if(guest.last)
    guestlist = guestlist.concat(' '+guest.last);
guestlist = guestlist.concat('</h3>\n');
if(guest.note)
    guestlist = guestlist.concat('<p>'+guest.note+'</p>');
}

function display_guestlist (post_id) {
    var url = rsvpmaker_json_url+'guestlist/'+post_id;
    fetch(url)
    .then(response => {
      return response.json()
    })
    .then(data => {
        if(Array.isArray(data))
        {
            data.forEach(format_guestlist);
            if(guestlist == '')
                guestlist = '<div>?</div>';
            $('#attendees-'+post_id).html(guestlist);
        }
    })
    .catch(err => {
        console.log(err);
        $('#attendees-'+post_id).html('Error fetching guestlist from '+url);
  });
  
}

$( ".rsvpmaker_show_attendees" ).click(function( event ) {
    var post_id = $(this).attr('post_id');
    guestlist = '';
    display_guestlist(post_id);//,nonce);
  });

});
//end jquery

class RSVPJsonWidget {
    constructor(divid, url, limit, morelink = '') {
        this.el = document.getElementById(divid);
        this.url = url;
        this.limit = limit;
        this.morelink = morelink;
        let eventslist = '';
        //this.showEvent = ;

  fetch(url)
  .then(response => {
    return response.json()
  })
  .then(data => {
    var showmorelink = false;
    if(Array.isArray(data))
        {
        if(limit && (data.length >= limit)) {
            data = data.slice(0,limit);
            showmorelink = true;
        }
        data.forEach(function (value, index, data) {
    if(!value.datetime)
        return '';
    var d = new Date(value.datetime);
    console.log('event '+ index);
    console.log(d);
    eventslist = eventslist.concat('<li><a href="' + value.guid + '">' + value.post_title + ' - ' + value.date + '</a></li>');
    });
        }
    else
        {
            this.el.innerHTML = 'None found: '+data.code;
            console.log(data);
        }
    if(eventslist == '')
       this.el.innerHTML = 'No event listings found';
    else
        {
            if(showmorelink && (morelink != ''))
                eventslist = eventslist.concat('<li><a href="'+morelink+'">More events</a></li>');
            this.el.innerHTML = '<ul class="eventslist rsvpmakerjson">'+eventslist+'</ul>';
        }
  })
  .catch(err => {
    this.el.innerHTML = 'Error fetching events from '+this.url;
    console.log(err);
});

    }
}
