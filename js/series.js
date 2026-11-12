jQuery(document).ready( function($) {
	var myConfirm = function() { return '' !== $('#newseries').val(); };

	var l10n = $.extend( {
		noResults: 'No series found.',
		oneResult: '1 series found.',
		results: '%d series found.',
		emptyName: 'Enter a name for the new series.'
	}, window.ppSeriesMetabox || {} );

	function speak( message, politeness ) {
		if ( window.wp && wp.a11y && typeof wp.a11y.speak === 'function' ) {
			wp.a11y.speak( message, politeness || 'polite' );
		}
	}

	/*$('#serieschecklist').wpList( { alt: '', response: 'series-ajax-response', confirm: myConfirm } ); /**/

  var searchTimer = null,
      lastSearch = null;

  $('.editor-series-search-input').bind('keydown keypress keyup change', function () {

    // Declare variables
    var input, filter, ul, li, a, i, txtValue, span, visible = 0;
    input = document.getElementById('editor-series-search-input');
    filter = input.value.toUpperCase();
    ul = document.getElementById("serieschecklist");
    li = ul.getElementsByTagName('li');

    // Loop through all list items, and hide those who don't match the search query
    for (i = 0; i < li.length; i++) {
      span = li[i].getElementsByTagName("span")[0];
      txtValue = span ? (span.textContent || span.innerText) : '';
      if (txtValue.toUpperCase().indexOf(filter) > -1) {
        li[i].style.display = "";
        if (i > 0) {
          visible++;
        }
      } else {
        li[i].style.display = "none";
      }
    }
    li[0].style.display = "";

    // Announce the result count once typing pauses (first item, "Not part of a series", is always shown).
    if (filter === lastSearch) {
      return;
    }
    clearTimeout(searchTimer);
    searchTimer = setTimeout(function () {
      lastSearch = filter;
      if (filter === '') {
        return;
      }
      if (visible === 0) {
        speak(l10n.noResults);
      } else if (visible === 1) {
        speak(l10n.oneResult);
      } else {
        speak(l10n.results.replace('%d', visible));
      }
    }, 500);

  });

	function clearNewSeriesError() {
		$('#newseries').removeAttr('aria-invalid aria-describedby');
	}

	function showNewSeriesError( message ) {
		$('#series-ajax-response').text( message );
		$('#newseries')
			.attr( 'aria-invalid', 'true' )
			.attr( 'aria-describedby', 'series-ajax-response' )
			.trigger( 'focus' );
	}

	function addSeries() {
		if ( ! myConfirm() ) {
			showNewSeriesError( l10n.emptyName );
			return;
		}

		$('input','#serieschecklist').removeProp('checked');

		var data = {
			action: 'add_series',
			newseries: $('#newseries').val(),
			addnonce: $('#add-series-nonce').text()
		}
		$.post(ajaxurl, data, function(response) {
			var resp = ( typeof response === 'string' ) ? $.parseJSON(response) : response;
			if ( !resp.error ) {
				clearNewSeriesError();
				$('#newseries').val('');
				$('ul#serieschecklist li:first').after(resp.html);
				$('#series-'+resp.id).animate({backgroundColor: "transparent"}, 3000);
				$('#add-series-nonce').text(resp.new_nonce);
				$('#series-ajax-response').text(resp.message || '');
				if ( resp.id ) {
					$('#series_part').attr('name', 'series_part['+resp.id+']');
				}
			} else {
				showNewSeriesError( resp.error );
			}
		});
	}

	$('#seriesadd').click( function() {
		addSeries();
	});

	// Enter in the new series field adds the series instead of submitting the post form.
	$('#newseries').on( 'keydown', function( event ) {
		if ( event.key === 'Enter' || event.keyCode === 13 ) {
			event.preventDefault();
			addSeries();
		}
	}).on( 'input', function() {
		if ( $(this).attr('aria-invalid') ) {
			clearNewSeriesError();
		}
	});

	$('#seriesadd').mouseup( function() {
		setTimeout( function() {
			var new_id = $('input:checked', '#serieschecklist').val();
			if ( new_id != null )
				$('#series_part').attr('name', 'series_part['+new_id+']')
		}, 1000);
	});

	// Delegated so series added via Add New also update the part field name.
	$('#serieschecklist').on( 'click', 'input[name|="post_series"]', function() {
		var ser_id = $(this).attr('id').replace('in-series-', '');
		$('#series_part').attr('name', 'series_part['+ser_id+']')
  });

} );
