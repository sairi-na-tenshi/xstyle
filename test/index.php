<?php

header( 'content-type:text/xml' );

require_once( '../xstyle.php' );

$xstyle= new XStyle( 'test.xs' );
$xsl= $xstyle->compile()->xsl;

echo $xsl->saveXML();
