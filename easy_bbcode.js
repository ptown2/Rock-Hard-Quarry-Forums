<!--
function insert_text(open, close)
{
	if (document.forms['<?php echo $bbcode_form ?>'])
		msgfield = document.forms['<?php echo $bbcode_form ?>']['<?php echo $bbcode_field ?>'];
	else if (document.getElementsByName('<?php echo $bbcode_field ?>'))
		msgfield = document.getElementsByName('<?php echo $bbcode_field ?>')[0];
	else
		document.all.req_message;

	// IE support
	if (document.selection && document.selection.createRange)
	{
		msgfield.focus();
		sel = document.selection.createRange();
		sel.text = open + sel.text + close;
	}

	// Moz support
	else if (msgfield.selectionStart || msgfield.selectionStart == '0')
	{
		var startPos = msgfield.selectionStart;
		var endPos = msgfield.selectionEnd;
		var selText = msgfield.value.substring(startPos, endPos);
		
		msgfield.value = msgfield.value.substring(0, startPos) + open + selText + close + msgfield.value.substring(endPos, msgfield.value.length);
		if (selText != '')
		{
			msgfield.selectionStart = endPos + open.length + close.length;
			msgfield.selectionEnd = msgfield.selectionStart;
		}
		else
		{
			msgfield.selectionStart = startPos + open.length;
			msgfield.selectionEnd = msgfield.selectionStart;    
		}
	}

	// Fallback support for other browsers
	else
		msgfield.value += open + close;

	msgfield.focus();
	
	return;
}


/***********
* Functions for mod QuickQuote v1.1 by D.S.Denton
***********/

quote_text = '';
function get_quote_text()
{
	//IE
	if (document.selection && document.selection.createRange())
		quote_text = document.selection.createRange().text;

	//NS,FF,SM
	if (document.getSelection)
		quote_text = document.getSelection();
}

function Quote(user_name, message)
{
	startq = '[quote=' + user_name + ']' + (quote_text != '' ? quote_text : message) + '[/quote]';
	insert_text(startq,'');
}
-->