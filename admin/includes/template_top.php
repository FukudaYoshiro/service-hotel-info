<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<meta name="robots" content="noindex,nofollow">
<title><?php echo TITLE; ?></title>
<base href="<?php echo HTTP_SERVER . DIR_WS_ADMIN; ?>" />
<link rel="stylesheet" type="text/css" href="<?php echo tep_catalog_href_link('ext/jquery/ui/redmond/jquery-ui-1.8.22-1.css'); ?>">
<script type="text/javascript" src="../jquery/jqueryadmin.js"></script>
<?php
  if (tep_not_null(JQUERY_DATEPICKER_I18N_CODE)) {
?>
<script type="text/javascript" src="<?php echo tep_catalog_href_link('ext/jquery/ui/i18n/jquery.ui.datepicker-' . JQUERY_DATEPICKER_I18N_CODE . '.js'); ?>"></script>
<script type="text/javascript">
$.datepicker.setDefaults($.datepicker.regional['<?php echo JQUERY_DATEPICKER_I18N_CODE; ?>']);
</script>
<?php
  }
?>
<link rel="stylesheet" type="text/css" href="includes/stylesheet1.1.css">
<link rel="stylesheet" href="../css/redactor.css" />
<script type="text/javascript" src="includes/general.js"></script>
<!-- <script type="text/javascript" src="ckeditor/ckeditor.js"></script> -->
<script src="../jquery/redactor.min.js"></script>
<!-- Redactor's plugin -->
<script src="../jquery/fontsize.js"></script>
<script src="../jquery/fontfamily.js"></script>
<script type="text/javascript">
$(function()
{
	$('.ckeditor').redactor({ focus: false, // If redactor gets focused on load or not
							linebreaks: false, // If true, then it uses <br>, otherwise it uses <p>
							boldTag: 'b', // If this is not set, then for bold <strong> is used
							italicTag: 'i', // If this is not set, then for bold <em> is used
							buttons: ['html', '|', 'formatting', '|', 'bold', 'italic', 'deleted', 'underline', '|', 'unorderedlist', 'orderedlist', '|' ,
'image', 'video', 'file', 'table', 'link', '|' , 'fontcolor', '|', 'alignment', '|', 'horizontalrule'], // these are all the default buttons, remove what is not necessary
							// 'underline', 'alignleft', 'aligncenter', 'alignright', 'justify' - these are some aditional possible buttons
							formattingTags: ['p', 'h2', 'h3', '<br>'], // List what tags we allow for formating
							//buttonSource: false, // Show or not show the source button (for editing HTML)
							cleanup: true, // Auto cleanup of copy/pasted text
							paragraphy: false, // Not sure what this is
							convertLinks: true, // automatically convert links to hyperlinks
							convertDivs: false, // convert divs to p
							minHeight: 250, // pixels
						    removeEmptyTags: false, //remove empty tags?
							pastePlaintText: false,
							plugins: ['fontsize', 'fontfamily']
 		  
	});
});
</script>
</head>
<body>
<?php //echo '<pre>';print_r($_SESSION);echo '</pre>';?>
<?php //echo '<pre>';print_r($_POST);echo '</pre>';?>
<?php //echo '<pre>';print_r($_FILES);echo '</pre>';?>
<?php
	if ($messageStack->size > 0) {
    	echo $messageStack->output();
  	}
?>

<table border="0" width="100%" cellspacing="0" cellpadding="0">
  	<tr>
    	<td colspan="2"><?php echo '<a href="' . tep_href_link(FILENAME_DEFAULT, '', 'NONSSL') . '"><img src="../images/logo.png" alt="Logo"></a>'; ?></td>
  	</tr>
  	<tr class="headerBar">
    	<td class="headerBarContent">&nbsp;&nbsp;<?php echo '<a href="' . tep_href_link(FILENAME_DEFAULT, '', 'NONSSL') . '" class="headerLink">' . HEADER_TITLE_ADMINISTRATION . '</a> &nbsp;|&nbsp; <a href="' . tep_catalog_href_link() . '" class="headerLink"> FrontEnd </a>'; ?></td>
    	<td class="headerBarContent" align="right"><?php echo (tep_session_is_registered('admin') ? 'Logged in as: ' . $admin['username']  . ' (<a href="' . tep_href_link(FILENAME_LOGIN, 'action=logoff') . '" class="headerLink">Logoff</a>)' : ''); ?>&nbsp;&nbsp;</td>
  	</tr>
</table>
<?php
	if (tep_session_is_registered('admin')) {
    	include(DIR_WS_INCLUDES . 'column_left.php');
  	} else {
?>
<style>
	#contentText {
 		 margin-left: 0;
	}
</style>
<?php
	}
?>
<div id="contentText">