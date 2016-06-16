<?php
/**************************************************************************

 �Modern BBcode� PunBB Modification by neutral (anatolian.online@gmail.com)

***************************************************************************/

// Make sure no one attempts to run this script "directly"
if (!defined('PUN'))
	exit;

if (isset($modern_bbcode_enabled) && $modern_bbcode_enabled)
{

	if (!isset($bbcode_form))
		$bbcode_form = 'post';
	if (!isset($bbcode_field))
		$bbcode_field = 'req_message';

	require PUN_ROOT.'lang/'.$pun_user['language'].'/modern_bbcode.php';

	?>
						<script type="text/javascript">
							var list_prompt = <?php echo '"'.$lang_modern_bbcode['List tag prompt'].'"' ?>;
							
							function insert_text(open, close)
							{
								msgfield = (document.all) ? document.all.req_message : document.forms['<?php echo $bbcode_form ?>']['<?php echo $bbcode_field ?>'];

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
								{
									msgfield.value += open + close;
									msgfield.focus();
								}

								hide_poped_menu();

								return;
							}

							function resize_text_area(dpixels)
							{
								msgfield = (document.all) ? document.all.req_message : document.forms['<?php echo $bbcode_form ?>']['<?php echo $bbcode_field ?>'];

								var box = msgfield;	
								var cur_height = parseInt( box.style.height ) ? parseInt( box.style.height ) : 210;
								var new_height = cur_height + dpixels;

								if (new_height > 0) 
								{
									box.style.height = new_height + "px";
								}

							}

							function show_smilies_window()
							{
								msgfield = (document.all) ? document.all.req_message : document.forms['<?php echo $bbcode_form ?>']['<?php echo $bbcode_field ?>'];
								msgfield.focus();

								var width = 360;
								var height = 500;

								window.open('smilies.php', 'smilies', 'alwaysRaised=yes, dependent=yes, resizable=yes, location=no, width=' + width + ', height=' + height + ', menubar=no, scrollbars=yes');
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
						</script>
						<div id="bbcode_adv" class="toolbar" style="margin-top: 4px;">
							<div class="draghandle"></div>
							<a id="right" class="tool_btn" title=<?php echo '"'.$lang_modern_bbcode['Right title'].'"' ?> href="javascript:insert_text('[align=right]','[/align]')"><?php echo $lang_modern_bbcode['Right'] ?></a>
							<a id="center" class="tool_btn" title=<?php echo '"'.$lang_modern_bbcode['Center title'].'"' ?> href="javascript:insert_text('[align=center]','[/align]')"><?php echo $lang_modern_bbcode['Center'] ?></a>
							<a id="justify" class="tool_btn" title="Justify" href="javascript:insert_text('[align=justify]','[/align]')">Justify</a>
							<div class="separator"></div>
							<a id="size" title=<?php echo '"'.$lang_modern_bbcode['Fontsize title'].'"' ?> class="dropdown" href="javascript:popup_menu(2)" onMouseOver="javascript:mouseover_menu(2)"><?php echo $lang_modern_bbcode['Fontsize'] ?></a>
							<?php if ($pun_config['p_message_img_tag'] == '1') 
							{ ?>
							<a id="img" title=<?php echo '"'.$lang_modern_bbcode['Image title'].'"' ?> class="dropdown" href="javascript:popup_menu(3)" onMouseOver="javascript:mouseover_menu(3)"><?php echo $lang_modern_bbcode['Image'] ?></a>
							<?php 
							} ?>
							<a id="list" title=<?php echo '"'.$lang_modern_bbcode['List title'].'"' ?> class="dropdown" href="javascript:popup_menu(4)" onMouseOver="javascript:mouseover_menu(4)"><?php echo $lang_modern_bbcode['List'] ?></a>
							<div class="separator"></div>
							<a id="spoiler" class="tool_btn" title=<?php echo '"'.$lang_modern_bbcode['Spoiler title'].'"' ?> href="javascript:insert_text('[spoiler]','[/spoiler]')"><?php echo $lang_modern_bbcode['Spoiler'] ?></a>
							<a id="help" class="tool_btn" title=<?php echo '"'.$lang_modern_bbcode['Help title'].'"' ?> href="help.php" onclick="window.open(this.href, 'Help', 'width=800, height=600, resizable=yes, scrollbars=yes'); return false;">?</a>
						</div>
						<div id="bbcode" class="toolbar">
							<div class="draghandle"></div>
							<a id="topic" class="tool_btn" title="Topic" href="javascript:insert_text('[topic]','[/topic]')">Topic</a>
							<a id="post" class="tool_btn" title="Post" href="javascript:insert_text('[post]','[/post]')">Post</a>
							<a id="forum" class="tool_btn" title="Forum" href="javascript:insert_text('[forum]','[/forum]')">Forum</a>
							<a id="user" class="tool_btn" title="User" href="javascript:insert_text('[user]','[/user]')">User</a>
						</div>
						<div id="bbcode" class="toolbar">
							<div class="draghandle"></div>
							<a id="ytube" class="tool_btn" title="YouTube" href="javascript:insert_text('[youtube]','[/youtube]')">YouTube</a>
							<a id="vimeo" class="tool_btn" title="Vimeo" href="javascript:insert_text('[vimeo]','[/vimeo]')">Vimeo</a>
							<a id="vine" class="tool_btn" title="Vine" href="javascript:insert_text('[vine]','[/vine]')">Vine</a>
							<a id="video" class="tool_btn" title="Video" href="javascript:insert_text('[video]','[/video]')">Video</a>
							<div class="separator"></div>
							<a id="sndcld" class="tool_btn" title="SoundCloud" href="javascript:insert_text('[soundcloud]','[/soundcloud]')">SoundCloud</a>
							<a id="bndcmp" class="tool_btn" title="BandCamp" href="javascript:insert_text('[bandcamp]','[/bandcamp]')">BandCamp</a>
						</div>
						<div id="bbcode" class="toolbar">
							<div class="draghandle"></div>
							<a id="bold" class="tool_btn" title=<?php echo '"'.$lang_modern_bbcode['Bold title'].'"' ?> href="javascript:insert_text('[b]','[/b]')"><?php echo $lang_modern_bbcode['Bold'] ?></a>
							<a id="italic" class="tool_btn" title=<?php echo '"'.$lang_modern_bbcode['Italic title'].'"' ?> href="javascript:insert_text('[i]','[/i]')"><?php echo $lang_modern_bbcode['Italic'] ?></a>
							<a id="underline" class="tool_btn" title=<?php echo '"'.$lang_modern_bbcode['Underline title'].'"' ?> href="javascript:insert_text('[u]','[/u]')"><?php echo $lang_modern_bbcode['Underline'] ?></a>
							<a id="strikeout" class="tool_btn" title=<?php echo '"'.$lang_modern_bbcode['Strikeout title'].'"' ?> href="javascript:insert_text('[s]','[/s]')"><?php echo $lang_modern_bbcode['Strikeout'] ?></a>
							<a id="heading" class="tool_btn" title="Header" href="javascript:insert_text('[h]','[/h]')">H</a>
							<div class="separator"></div>
							<a id="url" class="tool_btn" title=<?php echo '"'.$lang_modern_bbcode['URL title'].'"' ?> href="javascript:insert_text('[url]','[/url]')"><?php echo $lang_modern_bbcode['URL'] ?></a>
							<a id="mail" class="tool_btn" title=<?php echo '"'.$lang_modern_bbcode['Mail title'].'"' ?> href="javascript:insert_text('[email]','[/email]')"><?php echo $lang_modern_bbcode['Mail'] ?></a>
							<div class="separator"></div>
							<a id="code" class="tool_btn" title=<?php echo '"'.$lang_modern_bbcode['Code title'].'"' ?> href="javascript:insert_text('[code]','[/code]')"><?php echo $lang_modern_bbcode['Code'] ?></a>
							<a id="quote" class="tool_btn" title=<?php echo '"'.$lang_modern_bbcode['Quote title'].'"' ?> href="javascript:insert_text('[quote]','[/quote]')"><?php echo $lang_modern_bbcode['Quote'] ?></a>
							<div class="separator"></div>
							<a id="color" title=<?php echo '"'.$lang_modern_bbcode['Color title'].'"' ?> class="dropdown" href="javascript:popup_menu(0)" onMouseOver="javascript:mouseover_menu(0)"><?php echo $lang_modern_bbcode['Color'] ?></a>
							<a id="smiley" title=<?php echo '"'.$lang_modern_bbcode['Smiley title'].'"' ?> class="dropdown" href="javascript:popup_menu(1)" onMouseOver="javascript:mouseover_menu(1)"><?php echo $lang_modern_bbcode['Smiley'] ?></a>
							<a id="dectxt" class="tool_btn" title=<?php echo '"'.$lang_modern_bbcode['Dec text field height title'].'"' ?> href="javascript:resize_text_area(-100)"><?php echo $lang_modern_bbcode['Dec text field height'] ?></a>
							<a id="inctxt" class="tool_btn" title=<?php echo '"'.$lang_modern_bbcode['Inc text field height title'].'"' ?> href="javascript:resize_text_area(100)"><?php echo $lang_modern_bbcode['Inc text field height'] ?></a>
						</div>
						<div id="colorpalette">
							<a id="colorbtn" class="tool_btn_opened" href="javascript:hide_poped_menu();"><?php echo $lang_modern_bbcode['Color'] ?></a>
							<div class="clearer"></div>							
							<div id="colorcontent">
								<a id="colorblack" class="abtn" title="Black" href="javascript:insert_text('[color=black]','[/color]');"></a>
								<a id="colorbrown" class="abtn" title="Brown"  href="javascript:insert_text('[color=brown]','[/color]');"></a>
								<a id="colorolive" class="abtn" title="Olive Green"  href="javascript:insert_text('[color=#333300]','[/color]');"></a>
								<a id="colordarkgreen" class="abtn" title="Dark Green"  href="javascript:insert_text('[color=#003300]','[/color]');"></a>
								<a id="colordarkteal" class="abtn" title="Dark Teal"  href="javascript:insert_text('[color=#003366]','[/color]');"></a>
								<a id="colordarkblue" class="abtn" title="Dark Blue"  href="javascript:insert_text('[color=#000080]','[/color]');"></a>
								<a id="colorindigo" class="abtn" title="Indigo"  href="javascript:insert_text('[color=#333399]','[/color]');"></a>
								<a id="colorgray80" class="abtn" title="Gray-80%"  href="javascript:insert_text('[color=#333333]','[/color]');"></a>
								<a id="colordarkred" class="abtn" title="Dark Red"  href="javascript:insert_text('[color=#800000]','[/color]');"></a>
								<a id="colororange" class="abtn" title="Orange"  href="javascript:insert_text('[color=#FF6600]','[/color]');"></a>
								<a id="colordarkyellow" class="abtn" title="Dark Yellow"  href="javascript:insert_text('[color=#808000]','[/color]');"></a>
								<a id="colorgreen" class="abtn" title="Green"  href="javascript:insert_text('[color=green]','[/color]');"></a>
								<a id="colorteal" class="abtn" title="Teal"  href="javascript:insert_text('[color=#008080]','[/color]');"></a>
								<a id="colorblue" class="abtn" title="Blue"  href="javascript:insert_text('[color=blue]','[/color]');"></a>
								<a id="colorbluegray" class="abtn" title="Blue-Gray"  href="javascript:insert_text('[color=#666699]','[/color]');"></a>
								<a id="colorgray50" class="abtn" title="Gray-50%"  href="javascript:insert_text('[color=#808080]','[/color]');"></a>
								<a id="colorred" class="abtn" title="Red"  href="javascript:insert_text('[color=red]','[/color]');"></a>
								<a id="colorlightorange" class="abtn" title="Light Orange"  href="javascript:insert_text('[color=#FF9900]','[/color]');"></a>
								<a id="colorlime" class="abtn" title="Lime"  href="javascript:insert_text('[color=#99CC00]','[/color]');"></a>
								<a id="colorseagreen" class="abtn" title="Sea Green"  href="javascript:insert_text('[color=#339966]','[/color]');"></a>
								<a id="coloraqua" class="abtn" title="Aqua"  href="javascript:insert_text('[color=#33CCCC]','[/color]');"></a>
								<a id="colorlightblue" class="abtn" title="Light Blue"  href="javascript:insert_text('[color=#3366FF]','[/color]');"></a>
								<a id="colorviolet" class="abtn" title="Violet"  href="javascript:insert_text('[color=#800080]','[/color]');"></a>
								<a id="colorgray40" class="abtn" title="Gray-40%"  href="javascript:insert_text('[color=#969696]','[/color]');"></a>
								<a id="colorpink" class="abtn" title="Pink"  href="javascript:insert_text('[color=#FF00FF]','[/color]');"></a>
								<a id="colorgold" class="abtn" title="Gold"  href="javascript:insert_text('[color=#FFCC00]','[/color]');"></a>
								<a id="coloryellow" class="abtn" title="Yellow"  href="javascript:insert_text('[color=#FFFF00]','[/color]');"></a>
								<a id="colorbrightgreen" class="abtn" title="Bright Green"  href="javascript:insert_text('[color=#00FF00]','[/color]');"></a>
								<a id="colorturquoise" class="abtn" title="Turquoise"  href="javascript:insert_text('[color=#00FFFF]','[/color]');"></a>
								<a id="colorskyblue" class="abtn" title="Sky Blue"  href="javascript:insert_text('[color=#00CCFF]','[/color]');"></a>
								<a id="colorplum" class="abtn" title="Plum"  href="javascript:insert_text('[color=#993366]','[/color]');"></a>
								<a id="colorgray25" class="abtn" title="Gray-25%"  href="javascript:insert_text('[color=#C0C0C0]','[/color]');"></a>
								<a id="colorrose" class="abtn" title="Rose"  href="javascript:insert_text('[color=#FF99CC]','[/color]');"></a>
								<a id="colortan" class="abtn" title="Tan"  href="javascript:insert_text('[color=#FFCC99]','[/color]');"></a>
								<a id="colorlightyellow" class="abtn" title="Light Yellow"  href="javascript:insert_text('[color=#FFFF99]','[/color]');"></a>
								<a id="colorlightgreen" class="abtn" title="Light Green"  href="javascript:insert_text('[color=#CCFFCC]','[/color]');"></a>
								<a id="colorlightturquoise" class="abtn" title="Light Turquoise"  href="javascript:insert_text('[color=#CCFFFF]','[/color]');"></a>
								<a id="colorpaleblue" class="abtn" title="Pale Blue"  href="javascript:insert_text('[color=#99CCFF]','[/color]');"></a>
								<a id="colorlavender" class="abtn" title="Lavender"  href="javascript:insert_text('[color=#CC99FF]','[/color]');"></a>
								<a id="colorwhite" class="abtn" title="White"  href="javascript:insert_text('[color=white]','[/color]');"></a>
								<div class="clearer"></div>
							</div>
						</div>
						<div id="sizepanel">
							<a id="sizebtn" class="tool_btn_opened" href="javascript:hide_poped_menu();;"><?php echo $lang_modern_bbcode['Fontsize'] ?></a>
							<div class="clearer"></div>							
							<div id="sizecontent">
								<a class="abtn" href="javascript:insert_text('[size=6]','[/size]');"><?php echo $lang_modern_bbcode['Very small'] ?></a>
								<a class="abtn" href="javascript:insert_text('[size=10]','[/size]');"><?php echo $lang_modern_bbcode['Small'] ?></a>
								<a class="abtn" href="javascript:insert_text('[size=18]','[/size]');"><?php echo $lang_modern_bbcode['Big'] ?></a>
								<a class="abtn" href="javascript:insert_text('[size=24]','[/size]');"><?php echo $lang_modern_bbcode['Large'] ?></a>
								<div class="clearer"></div>
							</div>
						</div>
						<?php if ($pun_config['p_message_img_tag'] == '1') { ?>
						<div id="imgpanel">
							<a id="imgbtn" class="tool_btn_opened" href="javascript:hide_poped_menu();"><?php echo $lang_modern_bbcode['Image'] ?></a>
							<div class="clearer"></div>							
							<div id="imgcontent">
								<a class="abtn" title=<?php echo '"'.$lang_modern_bbcode['No float title'].'"' ?> href="javascript:insert_text('[img]','[/img]');"><?php echo $lang_modern_bbcode['No float'] ?></a>
								<a class="abtn" title=<?php echo '"'.$lang_modern_bbcode['Float to left title'].'"' ?> href="javascript:insert_text('[imgl]','[/imgl]');"><?php echo $lang_modern_bbcode['Float to left'] ?></a>
								<a class="abtn" title=<?php echo '"'.$lang_modern_bbcode['Float to right title'].'"' ?> href="javascript:insert_text('[imgr]','[/imgr]');"><?php echo $lang_modern_bbcode['Float to right'] ?></a>
								<div class="clearer"></div>
							</div>
						</div>
						<?php } ?>
						<div id="listpanel">
							<a id="listbtn" class="tool_btn_opened" href="javascript:hide_poped_menu();"><?php echo $lang_modern_bbcode['List'] ?></a>
							<div class="clearer"></div>							
							<div id="listcontent">
								<a class="abtn" title=<?php echo '"'.$lang_modern_bbcode['Unordered title'].'"' ?> href="javascript:hide_poped_menu();tag_list('');"><?php echo $lang_modern_bbcode['Unordered'] ?></a>
								<a class="abtn" title=<?php echo '"'.$lang_modern_bbcode['Ordered title'].'"' ?> href="javascript:hide_poped_menu();tag_list('ordered');"><?php echo $lang_modern_bbcode['Ordered'] ?></a>
								<div class="clearer"></div>
							</div>
						</div>
						<div id="smilespanel">
							<a id="smilesbtn" class="tool_btn_opened" href="javascript:hide_poped_menu();"><?php echo $lang_modern_bbcode['Smiley'] ?></a>
							<div class="clearer"></div>							
							<div id="smilescontent">
								<div style="padding: 4px 0px 0px 7px;">
									<?php
									require_once PUN_ROOT.'include/parser.php';
									echo "\n";

									$smiley_dups = array();
									foreach ($smilies as $smiley_text => $smiley_img)
									{
										if (!in_array($smiley_img, $smiley_dups))
										{
											echo "\t\t\t\t\t\t\t\t".'<a class="abtn" href="javascript:insert_text(\''.$smiley_text.' \', \'\');"><img class="abtn" src="img/smilies/'.$smiley_img.'" alt="'.$smiley_text.'" /></a>'."\n";
										}

										$smiley_dups[] = $smiley_img;
										if (count($smiley_dups) > 8) break;
									}
									?>
								</div>
								<div id="adv"><a class="abtn" href="javascript:show_smilies_window();hide_poped_menu();"><?php echo $lang_modern_bbcode['More text'] ?></a></div>
								<div class="clearer"></div>
							</div>
						</div>
<?php

}
