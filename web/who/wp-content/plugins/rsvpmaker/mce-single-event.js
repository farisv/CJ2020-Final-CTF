// JavaScript Document
(function() {
	tinymce.PluginManager.add('rsvpmaker_one', function( editor, url ) {
		var shortcode_tag = 'rsvpmaker_one';

		//helper functions 
		function getAttr(s, n) {
			n = new RegExp(n + '=\"([^\"]+)\"', 'g').exec(s);
			return n ?  window.decodeURIComponent(n[1]) : '';
		};

		//add popup
		editor.addCommand('rsvpmaker_one_popup', function(ui, v) {
			//setup defaults
			
			var one_format = '';
			var type = '';
			var hideauthor = '1';
			var showbutton = '0';
			var post_id = [0,'Not Set'];
			var hide_past = '';
			
			if (v.one_format)
				one_format = v.one_format;
			if(v.showbutton)
				one_format = one_format.concat('button');
			if (v.type)
				type = v.type;
			if (v.hideauthor)
				hideauthor = v.hideauthor;
			if (v.post_id)
				post_id = v.post_id;
			if (v.hide_past)
				hide_past = v.hide_past;
						
			editor.windowManager.open( {
				title: 'Embed Single Event in Page',
				width: 600,
				height: 250,
				body: [
					{
						type: 'listbox',
						name: 'post_id',
						label: 'Select Post',
						value: post_id,
						'values': upcoming,
						tooltip: 'Pick an event or display next event'
					},
					{
						type: 'listbox',
						name: 'type',
						label: 'Event Type',
						value: type,
						'values': rsvpmaker_types,
						tooltip: 'If "Next," limit to specified post type'
					},
					{
						type: 'listbox',
						name: 'one_format',
						label: 'Format',
						value: one_format,
						'values': [
							{text: 'Event with Form', value: ''},
							{text: 'Event with Button', value: 'button'},
							{text: 'Form Only', value: 'form'},
							{text: 'Button Only', value: 'button_only'},
							{text: 'Compact (Headline/Date/Button)', value: 'compact'},
							{text: 'Dates Only', value: 'embed_dateblock'}
						],
						tooltip: 'Output format (does not apply to "Next")'
					},
					{
						type: 'listbox',
						name: 'hide_past',
						label: 'Hide After',
						value: hide_past,
						'values': [
							{text: 'None', value: ''},
							{text: '1 hour', value: '1'},
							{text: '2 hours', value: '2'},
							{text: '3 hours', value: '3'},
							{text: '4 hours', value: '4'},
							{text: '5 hours', value: '5'},
							{text: '6 hours', value: '6'},
							{text: '7 hours', value: '7'},
							{text: '8 hours', value: '8'},
							{text: '12 hours', value: '12'},
							{text: '18 hours', value: '18'},
							{text: '24 hours', value: '24'}
						],
						tooltip: 'Prevents display when event is past'
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
					var format = e.data.one_format;
					var post_id = e.data.post_id;
					var showbutton = '0';
					if(format == 'button')
						{
						format = '';
						showbutton = '1';
						}
					if(format == 'button_only')
						showbutton = '1';
					
					var shortcode_str = '[' + shortcode_tag + ' post_id="'+post_id+'"'+ ' hideauthor="'+e.data.hideauthor+'"'+ ' showbutton="'+showbutton+'"';
					if(format)
					shortcode_str = shortcode_str.concat(' one_format="'+format+'"');
					if(e.data.hide_past)
					shortcode_str = shortcode_str.concat(' hide_past="'+e.data.hide_past+'"');
					if(e.data.type)
					shortcode_str = shortcode_str.concat(' type="'+e.data.type+'"');
					shortcode_str = shortcode_str.concat(']');
					editor.insertContent( shortcode_str);
				}
			});
	      	});

		//add button
		editor.addButton('rsvpmaker_one', {
			icon: 'rsvpmaker_one',
			tooltip: 'RSVPMaker Event',
			onclick: function() {
				editor.execCommand('rsvpmaker_one_popup','',{});
			}
		});

	});
})();