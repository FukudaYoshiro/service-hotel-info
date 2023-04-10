function SetFocus() {
  if (document.forms.length > 0) {
    isNotAdminLanguage:
    for (f=0; f<document.forms.length; f++) {
      if (document.forms[f].name != "adminlanguage") {
        var field = document.forms[f];
        for (i=0; i<field.length; i++) {
          if ( (field.elements[i].type != "image") &&
               (field.elements[i].type != "hidden") &&
               (field.elements[i].type != "reset") &&
               (field.elements[i].type != "button") &&
               (field.elements[i].type != "submit") ) {

            document.forms[f].elements[i].focus();

            if ( (field.elements[i].type == "text") ||
                 (field.elements[i].type == "password") )
              document.forms[f].elements[i].select();

            break isNotAdminLanguage;
          }
        }
      }
    }
  }
}

function rowOverEffect(object) {
  if (object.className == 'dataTableRow') object.className = 'dataTableRowOver';
}

function rowOutEffect(object) {
  if (object.className == 'dataTableRowOver') object.className = 'dataTableRow';
}

function toggleDivBlock(id) {
  if (document.getElementById) {
    itm = document.getElementById(id);
  } else if (document.all){
    itm = document.all[id];
  } else if (document.layers){
    itm = document.layers[id];
  }

  if (itm) {
    if (itm.style.display != "none") {
      itm.style.display = "none";
    } else {
      itm.style.display = "block";
    }
  }
}

//Document Ready JS

jQuery(document).ready(function()
	{					
		//IMAGE FUNCTIONS
		// Image preview
		$('.image_activate_preview').on('change', function() {
			var image_key = $(this).attr('id').substr(7);			
			$("#imagex_preview_"+image_key).show();
			$("#imagex_preview_"+image_key).html('<img src="images/loader.gif" alt="Uploading...."/>');
			$(".current_form").ajaxSubmit({
				url:       "zajax_upload_preview.php",
				type:      "POST",  
				dataType:  'json' ,
				global : false,
				data: { image_number: image_key },
				success:   function(data) {
					if (data.error == true) {
						$('#error_photo').html(data.error_image_message);	
						$("#imagex_preview_"+image_key).html('');
						$("#imagex_preview_"+image_key).hide();
					} else {
						$("#imagex_preview_"+image_key).html(data.image_response[image_key]);	
						$("#image_delete_"+image_key).show();	
						$("#previous_picture_"+image_key).val(data.filename[image_key]);	
					}
				}
			});
		});
		
		$('.image_upload_delete_button').on('click', function() {
			var image_key = $(this).attr('id').substr(22);
			$("#imagex_preview_"+image_key).html('<img src="images/loader.gif" alt="Uploading...."/>');
			$("#imagex_preview_"+image_key).html();
			$("#imagex_preview_"+image_key).hide();
			$("#image_delete_"+image_key).hide();
			$("#imagex_"+image_key).val('');
			$("#previous_picture_"+image_key).val('');	
		});
		
		// Editor for textareas
		/*
		$('.editor').jqte({center: false,
						   format: false,
						   indent: false,
						   left: false,
						   ol: false,
						   outdent: false,
						   right: false,
						   rule: false,
						   sub: false,
						   strike: false,
						   sup: false,
						   ul: false,
						   p: false
						  });;
		*/
	});