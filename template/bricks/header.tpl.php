<!DOCTYPE html>
<html>
<head>
<?php $this->brick('head'); ?>
</head>

<body<?=(User::isPremium() ? ' class="premium-logo"' : null); ?>>
<div id="layers"></div>
<?php if ($this->headerLogo):  ?>
<style type="text/css">
    .header-logo {
       background: url(<?=$this->headerLogo; ?>) no-repeat center 0 !important;
       margin-bottom: 1px !important;
    }
</style>
<?php endif; ?>
<div class="layout nosidebar" id="layout">
    <div class="layout-inner" id="layout-inner">
    <div class="header" id="header">
        <div id="header-logo">
            <a class="header-logo" href="."></a>
            <h1><?=Util::htmlEscape($this->name); ?></h1>
        </div>
    </div>
    <div id="wrapper" class="wrapper">
        <div class="toplinks linklist"><?php $this->brick('headerMenu'); ?></div>
        <div class="toptabs" id="toptabs"></div>
        <div class="topbar" id="topbar">
            <div class="topbar-search"><form action="."><a href="javascript:;"></a><input name="search" size="35" id="livesearch-generic" value="<?=Util::htmlEscape($this->search ?? ''); ?>" /></form></div>
            <div class="topbar-browse" id="topbar-browse"></div>
            <div class="topbar-buttons" id="topbar-buttons"></div>
        </div>

        <script type="text/javascript">
<?=$this->writeGlobalVars(); ?>
        </script>
