<?php

require_once(__DIR__.'/../core.php');

$hock = new Hockeyunlimited();
$hock->category = 'https://www.hockeyunlimited.fi/epages/hockeyunlimited.sf/fi_FI/?ObjectPath=/Shops/2014061601/Categories/Kypaeraet';
$hock->name = 'Jääkiekkokypärät';
$hock->csv = 'attachments';
$hock->increment = 'attachments';
$hock->images = 'attachments';
//$hock->size = 5;
$hock->start();