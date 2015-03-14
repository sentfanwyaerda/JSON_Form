<?php 
require_once(dirname(dirname(__FILE__)).'/JSON_Form.php');

$json = dirname(__FILE__).'/example-'.(isset($_GET['e']) && preg_match("#^[0-9]$#i", $_GET['e']) ? $_GET['e'] : '1').'.json';
$JF = new JSON_FORM();
$JF->load($json, TRUE);


print '<html>'."\n\n";
print '<link rel="stylesheet" href="//code.jquery.com/ui/1.11.1/themes/smoothness/jquery-ui.css">
<script src="//code.jquery.com/jquery-1.10.2.js"></script>
<script src="//code.jquery.com/ui/1.11.1/jquery-ui.js"></script>
<script src="https://jquery-ui.googlecode.com/svn-history/r3982/trunk/ui/i18n/jquery.ui.datepicker-nl.js"></script>
<script src="http://aehlke.github.io/tag-it/js/tag-it.js" type="text/javascript" charset="utf-8"></script>
<link rel="stylesheet" type="text/css" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1/themes/smoothness/jquery-ui.css">
<link href="http://aehlke.github.io/tag-it/css/jquery.tagit.css" rel="stylesheet" type="text/css">'."\n\n";
print '<style>fieldset { display: inline; width: 325px; } span.item { width: 325px; x-text-align: right; display: block; } div.set span.item { display: inline; } span.prefix { width: 175px; display: inline-block; x-float: left; text-align: left; } span.prefix:after { content: \':\' } span.input-tag-it span.prefix, span.input-tag-it span.postfix {display: block; float: none; width: 100%; } ul.tagit {margin: 0; } input { max-width: 150px;} .ui-widget { font-size: 12px; } </style>'."\n\n";

print $JF->generate_html((isset($_GET['l']) ? $_GET['l'] : NULL));

print '<pre>'.file_get_contents($json).'</pre>';

print "\n\n".'<br/><br/>Examples: <a href="?e=1">One</a>, <a href="?e=2">Two</a>, <a href="?e=3">Three</a>; <a href="?'.(isset($_GET['e']) ? 'e='.$_GET['e'].'&' : NULL).'l=nl">(in Dutch)</a>';

print '</html>';
?>
