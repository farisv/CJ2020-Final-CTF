// JavaScript Document
(function() {
	tinymce.PluginManager.add('rsvpmaker_upcoming', function( editor, url ) {
		var sh_tag = 'rsvpmaker_upcoming';

		//helper functions 
		function getAttr(s, n) {
			n = new RegExp(n + '=\"([^\"]+)\"', 'g').exec(s);
			return n ?  window.decodeURIComponent(n[1]) : '';
		};

		function html( cls, data) {
		
		var urlparts = url.split("wp-content");
		var baseurl = urlparts[0] + '?rsvpmaker_placeholder=1';
		
		var placeholder = baseurl;
		var one = getAttr(data,'one');
		if(one && (one > 0))
			{
			placeholder = placeholder.concat('&single_post='+one);
			}
		else
			{
			placeholder = placeholder.concat('&calendar=' +  getAttr(data,'calendar') + '&events_per_page=' +  getAttr(data,'posts_per_page'));
			var type = getAttr(data,'type');
			if(type)
				{
				placeholder = placeholder.concat('&event_type=' +  getAttr(data,'type'));
				}
			}
			data = window.encodeURIComponent( data );
			return '\n\n<img src="' + placeholder + '" class="mceItem ' + cls + '" ' + 'data-tm-attr="' + data + '"' + 'alt="' + data + '" data-mce-resize="false" data-mce-placeholder="1" />\n\n';
		}

		function replaceShortcodes( content ) {
			return content.replace( /\[rsvpmaker_upcoming([^\]]*)\]/g, function( all,attr) {
				return html( 'wp-rsvpmaker_upcoming', attr);
			});
		}

		function restoreShortcodes( content ) {
			return content.replace( /(?:<p(?: [^>]+)?>)*(<img [^>]+>)(?:<\/p>)*/g, function( match, image ) {
				var data = getAttr( image, 'data-tm-attr' );

				if ( data ) {
					return '<p>[' + sh_tag + data + ']</p>'+'\n\n';
				}
				return match;
			});
		}

		//add popup
		editor.addCommand('rsvpmaker_upcoming_popup', function(ui, v) {
			//setup defaults
			
			var calendar = '1';
			var posts_per_page = '10';
			var days = '180';
			var type = '';
			var hideauthor = '1';
			var past = '0';
			var no_events = 'No events currently listed';
			var nav = 'bottom';
			var one_post = [0,'Not Set'];
			
			if (v.calendar)
				calendar = v.calendar;
			if (v.days)
				days = v.days;
			if (v.posts_per_page)
				posts_per_page = v.posts_per_page;
			if (v.type)
				type = v.type;
			if (v.hideauthor)
				hideauthor = v.hideauthor;
			if (v.past)
				past = v.past;
			if (typeof v.no_events != 'undefined')
				no_events = v.no_events;
			if (v.nav)
				nav = v.nav;
						
			editor.windowManager.open( {
				title: 'Events Listing/Calendar',
				body: [
					{
						type: 'listbox',
						name: 'calendar',
						label: 'Display Calendar',
						value: calendar,
						'values': [
							{text: 'No', value: '0'},
							{text: 'Yes', value: '1'}
						],
						tooltip: 'Include the calendar table at the top of the listings?'
					},
					{
						type: 'listbox',
						name: 'nav',
						label: 'Calendar Navigation Links',
						value: nav,
						'values': [
							{text: 'Top', value: 'top'},
							{text: 'Bottom', value: 'bottom'},
							{text: 'Both', value: 'both'}
						],
						tooltip: 'Where should the next month / previous month links be displayed?'
					},
					{
						type: 'listbox',
						name: 'posts_per_page',
						label: 'Number of Listings',
						value: posts_per_page,
						'values': [
							{text: 'Limit by date only', value: '-1'},
							{text: '1', value: '1'},
							{text: '2', value: '2'},
							{text: '3', value: '3'},
							{text: '4', value: '4'},
							{text: '5', value: '5'},
							{text: '6', value: '6'},
							{text: '7', value: '7'},
							{text: '8', value: '8'},
							{text: '9', value: '9'},
							{text: '10', value: '10'},
							{text: '15', value: '15'},
							{text: '20', value: '20'},
							{text: '25', value: '25'},
							{text: '30', value: '30'},
							{text: '35', value: '35'},
							{text: '40', value: '40'}
						],
						tooltip: 'Maximum number per page'
					},
					{
						type: 'listbox',
						name: 'days',
						label: 'Date Range',
						value: days,
						'values': [
							{text: '3 Days', value: '3'},
							{text: '4 Days', value: '4'},
							{text: '5 Days', value: '5'},
							{text: '1 Week', value: '7'},
							{text: '2 Weeks', value: '14'},
							{text: '3 Weeks', value: '21'},
							{text: '4 Weeks', value: '28'},
							{text: '5 Weeks', value: '35'},
							{text: '6 Weeks', value: '42'},
							{text: '90 Days', value: '90'},
							{text: '180 Days', value: '180'},
							{text: '1 Year', value: '365'}
						],
						tooltip: 'Date range of listing'
					},
					{
						type: 'textbox',
						name: 'no_events',
						label: 'No Events Message',
						value: no_events,
						tooltip: 'Message displayed when no future listings'
					},
					{
						type: 'listbox',
						name: 'type',
						label: 'Event Type',
						value: type,
						'values': rsvpmaker_types,
						tooltip: 'Limit to a given post type'
					},
					{
						type: 'listbox',
						name: 'past',
						label: 'Upcoming Events?',
						value: past,
						'values': [
							{text: 'Upcoming', value: '0'},
							{text: 'Past Events', value: '1'}
						],
						tooltip: 'Upcoming or past (reverse chronology)'
					},
					{
						type: 'listbox',
						name: 'hideauthor',
						label: 'Show Author/Post Date',
						value: hideauthor,
						'values': [
							{text: 'Yes', value: '0'},
							{text: 'No', value: '1'}
						],
						tooltip: 'Include the author and date of the post at the bottom?'
					}
				],
				onsubmit: function( e ) {
					var shortcode_str = '[' + sh_tag + ' calendar="'+e.data.calendar+'"' + ' days="'+e.data.days+'"' + ' posts_per_page="'+e.data.posts_per_page+'"'+ ' type="'+e.data.type+'"'+ ' hideauthor="'+e.data.hideauthor+'"'+ ' past="'+e.data.past+'"'+ ' no_events="'+e.data.no_events+'"'+ ' nav="'+e.data.nav+'"]'+'\n\n';
					//insert shortcode to tinymce
					editor.insertContent( shortcode_str);
				}
			});
	      	});

		//add button
		editor.addButton('rsvpmaker_upcoming', {
			icon: 'rsvpmaker_upcoming',
			tooltip: 'RSVPMaker Calendar',
			onclick: function() {
				editor.execCommand('rsvpmaker_upcoming_popup','',{
					count   : '1',
					role: ''
				});
			}
		});

		//replace from shortcode to an image placeholder
		editor.on('BeforeSetcontent', function(event){ 
			event.content = replaceShortcodes( event.content );
		});

		//replace from image placeholder to shortcode
		editor.on('GetContent', function(event){
			event.content = restoreShortcodes(event.content);
		});

		//open popup on placeholder double click
		editor.on('DblClick',function(e) {
			var cls  = e.target.className.indexOf('wp-rsvpmaker_upcoming');
			if ( e.target.nodeName == 'IMG' && e.target.className.indexOf('wp-rsvpmaker_upcoming') > -1 ) {
				var title = e.target.attributes['data-tm-attr'].value;
				title = window.decodeURIComponent(title);
				console.log(title);
				editor.execCommand('rsvpmaker_upcoming_popup','',{
					calendar   : getAttr(title,'calendar'),
					posts_per_page   : getAttr(title,'posts_per_page'),
					days   : getAttr(title,'days'),
					type   : getAttr(title,'type'),
					hideauthor   : getAttr(title,'hideauthor'),
					past   : getAttr(title,'past'),
					no_events   : getAttr(title,'no_events'),
					nav   : getAttr(title,'nav')
				});
			}
		});
	});
})();