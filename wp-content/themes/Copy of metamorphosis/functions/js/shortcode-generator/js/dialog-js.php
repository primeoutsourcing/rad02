<?php
	header("Content-Type:text/javascript");
	
	// Get the path to the root.
	$full_path = __FILE__;
	
	$path_bits = explode( 'wp-content', $full_path );
	
	$url = $path_bits[0];
	
	// Require WordPress bootstrap.
	require_once( $url . '/wp-load.php' );
	
	$path_bits = explode( 'wp-content', dirname(__FILE__) );
	
	$prime_framework_path = trailingslashit( '../wp-content' . substr( $path_bits[1], 0, -3 ) );
	
	$prime_framework_url = get_template_directory_uri() . '/functions/';
	
	// Check if this is a Windows server or not.
	$_is_windows = false;
	$delimiter = '/';
	$dirname = dirname( __FILE__ );
	$_has_forwardslash = strpos( $dirname, $delimiter );
	
	if ( $_has_forwardslash === false ) {
	
		$_is_windows = true;
		$delimiter = '\\';
	
	} // End IF Statement
	
	$prime_framework_functions_path = str_replace( 'js' . $delimiter . 'shortcode-generator' . $delimiter . 'js', '', dirname( __FILE__ ) );

	// Require admin functions.
	require_once( $prime_framework_functions_path . $delimiter . 'admin-functions.php' );

	global $google_fonts;

	$fonts = '';

	// Build array of usabel typefaces.
	$fonts_whitelist = array( 
						'Arial, Helvetica, sans-serif', 
						'Verdana, Geneva, sans-serif', 
						'|Trebuchet MS|, Tahoma, sans-serif', 
						'Georgia, |Times New Roman|, serif', 
						'Tahoma, Geneva, Verdana, sans-serif', 
						'Palatino, |Palatino Linotype|, serif', 
						'|Helvetica Neue|, Helvetica, sans-serif', 
						'Calibri, Candara, Segoe, Optima, sans-serif', 
						'|Myriad Pro|, Myriad, sans-serif', 
						'|Lucida Grande|, |Lucida Sans Unicode|, |Lucida Sans|, sans-serif', 
						'|Arial Black|, sans-serif', 
						'|Gill Sans|, |Gill Sans MT|, Calibri, sans-serif', 
						'Geneva, Tahoma, Verdana, sans-serif', 
						'Impact, Charcoal, sans-serif'
						);
	
	$fonts_whitelist = array(); // Temporarily remove the default fonts.

	// Get just the names of the Google fonts.
	$google_font_names = array();
	
	if ( count( $google_fonts ) ) {
	
		foreach ( $google_fonts as $g ) {
		
			$google_font_names[] = $g['name'];
		
		} // End FOREACH Loop
	
		$fonts_whitelist = array_merge( $fonts_whitelist, $google_font_names );
	
	} // End IF Statement
	
	foreach ( $fonts_whitelist as $k => $v ) {
	
		$fonts_whitelist[$k] = str_replace( '|', '\"', $v );
	
	} // End FOREACH Loop
	
	$fonts = join( '|', $fonts_whitelist );
?>

var framework_url = '<?php echo dirname( __FILE__ ); ?>';

var shortcode_generator_path = '<?php echo $prime_framework_path; ?>';
var shortcode_generator_url = '<?php echo $prime_framework_url; ?>' + 'js/shortcode-generator/';

var primeDialogHelper = {

    needsPreview: false,
    setUpButtons: function () {
        var a = this;
        jQuery("#prime-btn-cancel").click(function () {
            a.closeDialog()
        });
        jQuery("#prime-btn-insert").click(function () {
            a.insertAction()
        });
        jQuery("#prime-btn-preview").click(function () {
            a.previewAction()
        })
    },
    
    setUpColourPicker: function () {

		var startingColour = '000000';

    	jQuery('.prime-marker-colourpicker-control div.colorSelector').each ( function () {
    	
    		var colourPicker = jQuery(this).ColorPicker({
    	
	    	color: startingColour,
			onShow: function (colpkr) {
				jQuery(colpkr).fadeIn(500);
				return false;
			},
			onHide: function (colpkr) {
				jQuery(colpkr).fadeOut(500);
				
				primeDialogHelper.previewAction();
				
				return false;
			},
			onChange: function (hsb, hex, rgb) {
				jQuery(colourPicker).children('div').css('backgroundColor', '#' + hex);
				jQuery(colourPicker).next('input').attr('value','#' + hex);
			}
	    	
	    	});
	    	
	    	// jQuery(colourPicker).children('div').css('backgroundColor', '#' + startingColour);
			// jQuery(colourPicker).next('input').attr('value','#' + startingColour);
	
	    	
    	});
    	   	
    	jQuery('.colorpicker').css('position', 'absolute').css('z-index', '9999');
		
    }, 

    loadShortcodeDetails: function () {
        if (primeSelectedShortcodeType) {

            var a = this;
            jQuery.getScript(shortcode_generator_url + "shortcodes/" + primeSelectedShortcodeType + ".js", function () {
                a.initializeDialog();
                
                // Set the default content to the highlighted text, for certain shortcode types.
                switch ( primeSelectedShortcodeType ) {
				
					case 'box':
					case 'ilink':
					case 'quote':
					case 'button':
					case 'abbr':
					case 'unordered_list':
					case 'ordered_list':
					case 'typography':
					
						jQuery('input#prime-value-content').val( selectedText );
						
					case 'toggle':
					
						jQuery('textarea#prime-value-content').val( selectedText );
					
					break;
				
				} // End SWITCH Statement
                
                // Automatic preview generation on load.
                a.previewAction();
            })

        }

    },
    initializeDialog: function () {

        if (typeof primeShortcodeMeta == "undefined") {
            jQuery("#prime-options").append("<p>Error loading details for shortcode: " + primeSelectedShortcodeType + "</p>");
        } else {
            if (primeShortcodeMeta.disablePreview) {
                jQuery("#prime-preview").remove();
                jQuery("#prime-btn-preview").remove()
            }
            var a = primeShortcodeMeta.attributes,
                b = jQuery("#prime-options-table");

            for (var c in a) {
                var f = "prime-value-" + a[c].id,
                    d = a[c].isRequired ? "prime-required" : "",
                    g = jQuery('<th valign="top" scope="row"></th>');

                var requiredSpan = '<span class="optional"></span>';

                if (a[c].isRequired) {

                    requiredSpan = '<span class="required">*</span>';

                } // End IF Statement
                jQuery("<label/>").attr("for", f).attr("class", d).html(a[c].label).append(requiredSpan).appendTo(g);
                f = jQuery("<td/>");

                d = (d = a[c].controlType) ? d : "text-control";

                switch (d) {

                case "column-control":

                    this.createColumnControl(a[c], f, c == 0);

                    break;
                    
                case "tab-control":

                    this.createTabControl(a[c], f, c == 0);

                    break;

                case "icon-control":
                case "color-control":
                case "link-control":
                case "text-control":

                    this.createTextControl(a[c], f, c == 0);

                    break;
                    
                case "textarea-control":

                    this.createTextAreaControl(a[c], f, c == 0);

                    break;

                case "select-control":

                    this.createSelectControl(a[c], f, c == 0);

                    break;
                    
                case "font-control":

                    this.createFontControl(a[c], f, c == 0);

                    break;
                    
                 case "range-control":

                    this.createRangeControl(a[c], f, c == 0);

                    break;
                    
                 case "colourpicker-control":
                 
                 	this.createColourPickerControl(a[c], f, c == 0);
                 
                 	break;

                }

                jQuery("<tr/>").append(g).append(f).appendTo(b)
            }
            jQuery(".prime-focus-here:first").focus()

			// Add additional wrappers, etc, to each select box.
			
			jQuery('#prime-options select').wrap( '<div class="select_wrapper"></div>' ).before('<span></span>');
			
			jQuery('#prime-options select option:selected').each( function () {
			
				jQuery(this).parents('.select_wrapper').find('span').text( jQuery(this).text() );
			
			});
			
			// Setup the colourpicker.
            this.setUpColourPicker();

        } // End IF Statement
    },

    /* Column Generator Element */

    createColumnControl: function (a, b, c) {
        new primeColumnMaker(b, 6, c ? "prime-focus-here" : null);
        b.addClass("prime-marker-column-control")
    },
    
     /* Tab Generator Element */

    createTabControl: function (a, b, c) {
        new primeTabMaker(b, 10, c ? "prime-focus-here" : null);
        b.addClass("prime-marker-tab-control")
    },

	/* Colour Picker Element */

    createColourPickerControl: function (a, b, c) {

        var f = a.validateLink ? "prime-validation-marker" : "",
            d = a.isRequired ? "prime-required" : "",
            g = "prime-value-" + a.id;

		b.attr('id', 'prime-marker-colourpicker-control').addClass("prime-marker-colourpicker-control");

		jQuery('<div class="colorSelector"><div></div></div>').appendTo(b);

        jQuery('<input type="text">').attr("id", g).attr("name", g).addClass(f).addClass(d).addClass('txt input-text input-colourpicker').addClass(c ? "prime-focus-here" : "").appendTo(b);

        if (a = a.help) {
            jQuery("<br/>").appendTo(b);
            jQuery("<span/>").addClass("prime-input-help").html(a).appendTo(b)
        }

        var h = this;
        b.find("#" + g).bind("keydown focusout", function (e) {
            if (e.type == "keydown" && e.which != 13 && e.which != 9 && !e.shiftKey) h.needsPreview = true;
            else if (h.needsPreview && (e.type == "focusout" || e.which == 13)) {
                h.previewAction(e.target);
                h.needsPreview = false
            }
        })

    },

    /* Generic Text Element */

    createTextControl: function (a, b, c) {

        var f = a.validateLink ? "prime-validation-marker" : "",
            d = a.isRequired ? "prime-required" : "",
            g = "prime-value-" + a.id;

        jQuery('<input type="text">').attr("id", g).attr("name", g).addClass(f).addClass(d).addClass('txt input-text').addClass(c ? "prime-focus-here" : "").appendTo(b);

        if (a = a.help) {
            jQuery("<br/>").appendTo(b);
            jQuery("<span/>").addClass("prime-input-help").html(a).appendTo(b)
        }

        var h = this;
        b.find("#" + g).bind("keydown focusout", function (e) {
            if (e.type == "keydown" && e.which != 13 && e.which != 9 && !e.shiftKey) h.needsPreview = true;
            else if (h.needsPreview && (e.type == "focusout" || e.which == 13)) {
                h.previewAction(e.target);
                h.needsPreview = false
            }
        })

    },
    
    /* Generic TextArea Element */

    createTextAreaControl: function (a, b, c) {

        var f = a.validateLink ? "prime-validation-marker" : "",
            d = a.isRequired ? "prime-required" : "",
            g = "prime-value-" + a.id;

        jQuery('<textarea>').attr("id", g).attr("name", g).attr("rows", 10).attr("cols", 30).addClass(f).addClass(d).addClass('txt input-textarea').addClass(c ? "prime-focus-here" : "").appendTo(b);
        b.addClass("prime-marker-textarea-control");

        if (a = a.help) {
            jQuery("<br/>").appendTo(b);
            jQuery("<span/>").addClass("prime-input-help").html(a).appendTo(b)
        }

        var h = this;
        b.find("#" + g).bind("keydown focusout", function (e) {
            if (e.type == "keydown" && e.which != 13 && e.which != 9 && !e.shiftKey) h.needsPreview = true;
            else if (h.needsPreview && (e.type == "focusout" || e.which == 13)) {
                h.previewAction(e.target);
                h.needsPreview = false
            }
        })

    },

    /* Select Box Element */

    createSelectControl: function (a, b, c) {

        var f = a.validateLink ? "prime-validation-marker" : "",
            d = a.isRequired ? "prime-required" : "",
            g = "prime-value-" + a.id;

        var selectNode = jQuery('<select>').attr("id", g).attr("name", g).addClass(f).addClass(d).addClass('select input-select').addClass(c ? "prime-focus-here" : "");

        b.addClass('prime-marker-select-control');

        var selectBoxValues = a.selectValues;
        
        var labelValues = a.selectValues;

        for (v in selectBoxValues) {

            var value = selectBoxValues[v];
            var label = labelValues[v];
            var selected = '';

            if (value == '') {

                if (a.defaultValue == value) {

                    label = a.defaultText;

                } // End IF Statement
            } else {

                if (value == a.defaultValue) {
                    label = a.defaultText;
                } // End IF Statement
            } // End IF Statement
            if (value == a.defaultValue) {
                selected = ' selected="selected"';
            } // End IF Statement
            
            selectNode.append('<option value="' + value + '"' + selected + '>' + label + '</option>');

        } // End FOREACH Loop
        
        selectNode.appendTo(b);

        if (a = a.help) {
            jQuery("<br/>").appendTo(b);
            jQuery("<span/>").addClass("prime-input-help").html(a).appendTo(b)
        }

        var h = this;

        b.find("#" + g).bind("change", function (e) {

            if ((e.type == "change" || e.type == "focusout") || e.which == 9) {

                h.needsPreview = true;

            }

            if (h.needsPreview) {

                h.previewAction(e.target);

                h.needsPreview = false
            }
            
            // Update the text in the appropriate span tag.
            var newText = jQuery(this).children('option:selected').text();
            
            jQuery(this).parents('.select_wrapper').find('span').text( newText );
        })

    },
    
    /* Range Select Box Element */

    createRangeControl: function (a, b, c) {

        var f = a.validateLink ? "prime-validation-marker" : "",
            d = a.isRequired ? "prime-required" : "",
            g = "prime-value-" + a.id;

        var selectNode = jQuery('<select>').attr("id", g).attr("name", g).addClass(f).addClass(d).addClass('select input-select input-select-range').addClass(c ? "prime-focus-here" : "");

        b.addClass('prime-marker-select-control');

        // var selectBoxValues = a.selectValues;
        
        var rangeStart = a.rangeValues[0];
        var rangeEnd = a.rangeValues[1];
		var defaultValue = 0;
		if ( a.defaultValue ) {
		
			defaultValue = a.defaultValue;
		
		} // End IF Statement
		
		for ( var i = rangeStart; i <= rangeEnd; i++ ) {
		
			var selected = '';
			
			if ( i == defaultValue ) { selected = ' selected="selected"'; } // End IF Statement
		
			selectNode.append('<option value="' + i + '"' + selected + '>' + i + '</option>');
		
		} // End FOR Loop
        
        selectNode.appendTo(b);

        if (a = a.help) {
            jQuery("<br/>").appendTo(b);
            jQuery("<span/>").addClass("prime-input-help").html(a).appendTo(b)
        }

        var h = this;

        b.find("#" + g).bind("change", function (e) {

            if ((e.type == "change" || e.type == "focusout") || e.which == 9) {

                h.needsPreview = true;

            }

            if (h.needsPreview) {

                h.previewAction(e.target);

                h.needsPreview = false
            }
            
            // Update the text in the appropriate span tag.
            var newText = jQuery(this).children('option:selected').text();
            
            jQuery(this).parents('.select_wrapper').find('span').text( newText );
        })

    },
    
    /* Fonts Select Box Element */

    createFontControl: function (a, b, c) {

        var f = a.validateLink ? "prime-validation-marker" : "",
            d = a.isRequired ? "prime-required" : "",
            g = "prime-value-" + a.id;

        var selectNode = jQuery('<select>').attr("id", g).attr("name", g).addClass(f).addClass(d).addClass('select input-select input-select-font').addClass(c ? "prime-focus-here" : "");

        b.addClass('prime-marker-select-control').addClass('prime-marker-font-control');

        var selectBoxValues = '<?php echo $fonts; ?>';
        selectBoxValues = selectBoxValues.split('|');

        for (v in selectBoxValues) {

            var value = selectBoxValues[v];
            var label = selectBoxValues[v];
            var selected = '';

            if (value == '') {

                if (a.defaultValue == value) {

                    label = a.defaultText;

                } // End IF Statement
            } else {

                if (value == a.defaultValue) {
                    label = a.defaultText;
                } // End IF Statement
            } // End IF Statement
            if (value == a.defaultValue) {
                selected = ' selected="selected"';
            } // End IF Statement
            
            selectNode.append('<option value=\'' + value + '\'' + selected + '>' + label + '</option>');

        } // End FOREACH Loop
        
        selectNode.appendTo(b);

        if (a = a.help) {
            jQuery("<br/>").appendTo(b);
            jQuery("<span/>").addClass("prime-input-help").html(a).appendTo(b)
        }

        var h = this;

        b.find("#" + g).bind("change", function (e) {

            if ((e.type == "change" || e.type == "focusout") || e.which == 9) {

                h.needsPreview = true;

            }

            if (h.needsPreview) {

                h.previewAction(e.target);

                h.needsPreview = false
            }
            
            // Update the text in the appropriate span tag.
            var newText = jQuery(this).children('option:selected').text();
            
            jQuery(this).parents('.select_wrapper').find('span').text( newText );
        })

    },

   getTextKeyValue: function (a) {
        var b = a.find("input");
        if (!b.length) return null;
        a = b.attr("id").substring(10);
        b = b.val();
        return {
            key: a,
            value: b
        }
    },

	getTextAreaKeyValue: function (a) {
        var b = a.find("textarea");
        if (!b.length) return null;
        a = b.attr("id").substring(10);
        b = b.val();
        return {
            key: a,
            value: b
        }
    },

    getColumnKeyValue: function (a) {
        var b = a.find("#prime-column-text").text();
        if (a = Number(a.find("select option:selected").val())) return {
            key: "data",
            value: {
                content: b,
                numColumns: a
            }
        }
    },
    
    getTabKeyValue: function (a) {
        var b = a.find("#prime-tab-text").text();
        if (a = Number(a.find("select option:selected").val())) return {
            key: "data",
            value: {
                content: b,
                numTabs: a
            }
        }
    },

    makeShortcode: function () {

        var a = {},
            b = this;

        jQuery("#prime-options-table td").each(function () {

            var h = jQuery(this),
                e = null;

            if (e = h.hasClass("prime-marker-column-control") ? b.getColumnKeyValue(h) : b.getTextKeyValue(h)) a[e.key] = e.value
            if (e = h.hasClass("prime-marker-select-control") ? b.getSelectKeyValue(h) : b.getTextKeyValue(h)) a[e.key] = e.value
            if (e = h.hasClass("prime-marker-tab-control") ? b.getTabKeyValue(h) : b.getTextKeyValue(h)) a[e.key] = e.value
            if (e = h.hasClass("prime-marker-textarea-control") ? b.getTextAreaKeyValue(h) : b.getTextKeyValue(h)) a[e.key] = e.value

        });

        if (primeShortcodeMeta.customMakeShortcode) return primeShortcodeMeta.customMakeShortcode(a);
        var c = a.content ? a.content : primeShortcodeMeta.defaultContent,
            f = "";
        for (var d in a) {
            var g = a[d];
            if (g && d != "content") f += " " + d + '="' + g + '"'
        }
        
        // Customise the shortcode output for various shortcode types.
        
        switch ( primeShortcodeMeta.shortcodeType ) {
        
        	case 'text-replace':
        	
        		var shortcode = "[" + primeShortcodeMeta.shortcode + f + "]" + (c ? c + "[/" + primeShortcodeMeta.shortcode + "]" : " ")
        	
        	break;
        	
        	default:
        	
        		var shortcode = "[" + primeShortcodeMeta.shortcode + f + "]" + (c ? c + "[/" + primeShortcodeMeta.shortcode + "] " : " ")
        	
        	break;
        
        } // End SWITCH Statement
        
        return shortcode;
    },

    getSelectKeyValue: function (a) {
        var b = a.find("select");
        if (!b.length) return null;
        a = b.attr("id").substring(10);
        b = b.val();
        return {
            key: a,
            value: b
        }
    },

    insertAction: function () {
        if (typeof primeShortcodeMeta != "undefined") {
            var a = this.makeShortcode();
            tinyMCE.activeEditor.execCommand("mceInsertContent", false, a);
            this.closeDialog()
        }
    },

    closeDialog: function () {
        this.needsPreview = false;
        tb_remove();
        jQuery("#prime-dialog").remove()
    },

    previewAction: function (a) {
    
    	var fontValue = '';
    	
    	jQuery('#prime-options-table').find('select.input-select-font').each ( function () {
    	
    		fontValue = jQuery(this).val();
    	
    	});
    
        jQuery(a).hasClass("prime-validation-marker") && this.validateLinkFor(a);
        jQuery("#prime-preview h3:first").addClass("prime-loading");
        jQuery("#prime-preview-iframe").attr("src", shortcode_generator_url + "preview-shortcode-external.php?shortcode=" + encodeURIComponent(this.makeShortcode()) + "&font=" + fontValue )
    },

    validateLinkFor: function (a) {
        var b = jQuery(a);
        b.removeClass("prime-validation-error");
        b.removeClass("prime-validated");
        if (a = b.val()) {
            b.addClass("prime-validating");
            jQuery.ajax({
                url: ajaxurl,
                dataType: "json",
                data: {
                    action: "prime_check_url_action",
                    url: a
                },
                error: function () {
                    b.removeClass("prime-validating")
                },
                success: function (c) {
                    b.removeClass("prime-validating");
                    c.error || b.addClass(c.exists ? "prime-validated" : "prime-validation-error")
                }
            })
        }
    }

};

primeDialogHelper.setUpButtons();
primeDialogHelper.loadShortcodeDetails();