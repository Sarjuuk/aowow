<!DOCTYPE html>
<html>
<head>
<?php $this->brick('head'); ?>
</head>

<body<?php echo User::isPremium() ? ' class="premium-logo"' : null; ?>>
<div id="layers"></div>
<div class="layout nosidebar" id="layout">
    <div class="layout-inner" id="layout-inner">
    <div class="header" id="header">
        <div id="header-logo">
            <a class="header-logo" href="."></a>
            <h1><?php echo htmlentities($this->name); ?></h1>
        </div>
    </div>
    <div id="wrapper" class="wrapper">
        <div class="toplinks linklist"><?php $this->brick('headerMenu'); ?></div>
        <div class="toptabs" id="toptabs"></div>
        <div class="topbar" id="topbar">
            <div class="topbar-search"><form action="."><a href="javascript:;"></a><input name="search" size="35" value="" id="livesearch-generic" /></form></div>
            <div class="topbar-browse" id="topbar-browse"></div>
            <div class="topbar-buttons" id="topbar-buttons"></div>
        </div>

        <script type="text/javascript">
<?php echo $this->writeGlobalVars(); ?>
        </script>
