<?php

$pun_bbcode = array (
'1' => '','2' => '','3' => 'soundcloud','4' => 'align','5' => 'font','6' => 'rot','7' => 'video','8' => 'bandcamp','9' => 'vimeo','10' => 'vine',);

$pun_bbcode2 = array (
'1' => '','2' => '','3' => '<object height="81" width="100%">
<param name="movie" value="https://player.soundcloud.com/player.swf?url=$1&g=bb"></param>
<param name="allowscriptaccess" value="always"></param>
<embed allowscriptaccess="always" height="81" src="https://player.soundcloud.com/player.swf?url=$1" type="application/x-shockwave-flash" width="100%"></embed>
</object>
<a href="$1">Source</a>','4' => '<div style="width: 100%; text-align: $1;">$2</div>','5' => '<span style="font-family: $1;">$2</span>','6' => '<div style="position: relative;  transform-origin: top left; -webkit-transform-origin: top left; -ms-transform-origin: top left; transform:rotate($1deg);  -ms-transform:rotate($1deg); -webkit-transform:rotate($1deg); overflow: visible; white-space: nowrap;">$2</div>','7' => '<video src="$1" controls="true" preload="metadata">
Your browser does not support HTML5 Videos.
</video>','8' => '<iframe width="400" height="100" style="position: relative; display: block; width: 400px; height: 100px;" src="https://bandcamp.com/EmbeddedPlayer/v=2/album=$1/size=venti/bgcol=FFFFFF/linkcol=4a4a4a/" allowtransparency="true" frameborder="1"></iframe>','9' => '<iframe src="//player.vimeo.com/video/$1" width="600" height="337" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>','10' => '<iframe class="vine-embed" src="$1/embed/simple" width="600" height="600" frameborder="0"></iframe><script async src="//platform.vine.co/static/scripts/embed.js" charset="utf-8"></script>',);

$pun_bbcode3 = array (
'1' => '0','2' => '0','3' => '1','4' => '2','5' => '2','6' => '2','7' => '1','8' => '1','9' => '1','10' => '1',);

?>