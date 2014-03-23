<?php //netteCache[01]000340a:2:{s:4:"time";s:21:"0.07383700 1395594778";s:9:"callbacks";a:2:{i:0;a:3:{i:0;a:2:{i:0;s:6:"NCache";i:1;s:9:"checkFile";}i:1;s:59:"C:\xampp2\htdocs\seznamkaNette2\app\templates\@layout.latte";i:2;i:1395594504;}i:1;a:3:{i:0;a:2:{i:0;s:6:"NCache";i:1;s:10:"checkConst";}i:1;s:20:"NFramework::REVISION";i:2;s:22:"released on 2014-01-01";}}}?><?php

// source file: C:\xampp2\htdocs\seznamkaNette2\app\templates\@layout.latte

?><?php
// prolog NCoreMacros
list($_l, $_g) = NCoreMacros::initRuntime($template, 'k2j09g5gnx')
;
// prolog NUIMacros
//
// block title
//
if (!function_exists($_l->blocks['title'][] = '_lba5f67fe95e_title')) { function _lba5f67fe95e_title($_l, $_args) { foreach ($_args as $__k => $__v) $$__k = $__v
?>Nette Application Skeleton<?php
}}

//
// block head
//
if (!function_exists($_l->blocks['head'][] = '_lb0b877615ca_head')) { function _lb0b877615ca_head($_l, $_args) { foreach ($_args as $__k => $__v) $$__k = $__v
;
}}

//
// block scripts
//
if (!function_exists($_l->blocks['scripts'][] = '_lb6e33c3bc13_scripts')) { function _lb6e33c3bc13_scripts($_l, $_args) { foreach ($_args as $__k => $__v) $$__k = $__v
?>	<script src="<?php echo htmlSpecialChars($basePath) ?>/js/jquery.js"></script>
	<script src="<?php echo htmlSpecialChars($basePath) ?>/js/netteForms.js"></script>
	<script src="<?php echo htmlSpecialChars($basePath) ?>/js/main.js"></script>
<?php
}}

//
// end of blocks
//

// template extending and snippets support

$_l->extends = empty($template->_extended) && isset($_control) && $_control instanceof NPresenter ? $_control->findLayoutTemplateFile() : NULL; $template->_extended = $_extended = TRUE;


if ($_l->extends) {
	ob_start();

} elseif (!empty($_control->snippetMode)) {
	return NUIMacros::renderSnippets($_control, $_l, get_defined_vars());
}

//
// main template
//
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8" />
	<meta name="description" content="" />
<?php if (isset($robots)) { ?>	<meta name="robots" content="<?php echo htmlSpecialChars($robots) ?>" />
<?php } ?>

	<title><?php if ($_l->extends) { ob_end_clean(); return NCoreMacros::includeTemplate($_l->extends, get_defined_vars(), $template)->render(); }
ob_start(); call_user_func(reset($_l->blocks['title']), $_l, get_defined_vars()); echo $template->upper($template->striptags(ob_get_clean()))  ?></title>

	<link rel="stylesheet" media="screen,projection,tv" href="<?php echo htmlSpecialChars($basePath) ?>/css/screen.css" />
	<link rel="stylesheet" media="print" href="<?php echo htmlSpecialChars($basePath) ?>/css/print.css" />
	<link rel="shortcut icon" href="<?php echo htmlSpecialChars($basePath) ?>/favicon.ico" />
	<?php call_user_func(reset($_l->blocks['head']), $_l, get_defined_vars())  ?>

</head>

<body>
	<script> document.documentElement.className+=' js' </script>

<?php $iterations = 0; foreach ($flashes as $flash) { ?>	<div class="flash <?php echo htmlSpecialChars($flash->type) ?>
"><?php echo NTemplateHelpers::escapeHtml($flash->message, ENT_NOQUOTES) ?></div>
<?php $iterations++; } ?>
        <div id='navig'>
        <ul class="navig" >  
            <li <?php try { $_presenter->link("Homepage:"); } catch (NInvalidLinkException $e) {}; if ($_presenter->getLastCreatedRequestFlag("current")) { ?>
class='active'<?php } ?>><a href="<?php echo htmlSpecialChars($_control->link("Homepage:")) ?>
">Články</a></li>
<?php if ($user->loggedIn) { ?>
            <li <?php try { $_presenter->link("Sign:in"); } catch (NInvalidLinkException $e) {}; if ($_presenter->getLastCreatedRequestFlag("current")) { ?>
class='active'<?php } ?>><a href="<?php echo htmlSpecialChars($_control->link("Sign:out")) ?>
">Odhlásit</a></li>
<?php } else { ?>
            <li <?php try { $_presenter->link("Sign:in"); } catch (NInvalidLinkException $e) {}; if ($_presenter->getLastCreatedRequestFlag("current")) { ?>
class='active'<?php } else { ?> class='last' <?php } ?> ><a href="<?php echo htmlSpecialChars($_control->link("Sign:in")) ?>
">Přihlásit</a></li>
<?php } ?>
        </ul>
        </div>
        
<?php NUIMacros::callBlock($_l, 'content', $template->getParameters()) ?>

<?php call_user_func(reset($_l->blocks['scripts']), $_l, get_defined_vars())  ?>
</body>
</html>
