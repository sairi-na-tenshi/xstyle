<?php

require_once( 'proto.php' );

class XStyle extends ProtoObject {

	function __construct( $xsPath ){
		$this->xsPath= $xsPath;
	}

	protected $_dir;

	protected $_xsPath;
	function set_xsPath( $path ){
		$this->xslPath= $path . 'l';
		return $path;
	}

	protected $_xslPath;
	function set_xslPath( $path ){
		$this->xsPath= substr( $path, 0, -1 );
		$this->dir= dirname( $path );
		return $path;
	}

	protected $_xs;
	function get_xs( ){
		$doc= $this->_xs;
		if( $doc ) return $doc;
		$doc= new DOMDocument( );
		$xsPath= $this->xsPath;
		if( file_exists( $xsPath ) ) $doc->load( $xsPath, LIBXML_COMPACT );
		return $this->_xs= $doc;
	}
	function set_xs( $doc ){
		$this->aDocument( &$doc );
		$path= $this->xsPath;
		if( file_exists( $path ) ) unlink( $path );
		$doc->save( $path );
		return null;
	}

	protected $_xsl;
	function get_xsl( ){
		$doc= $this->_xsl;
		if( $doc ) return $doc;
		$doc= new DOMDocument( );
		$xslPath= $this->xslPath;
		if( file_exists( $this->xsPath ) ):
			if( file_exists( $xslPath ) ) $this->sync();
			else $this->compile();
		endif;
		$source= file_get_contents( $xslPath );
		$doc->loadXML( $source, LIBXML_COMPACT );
		return $this->_xsl= $doc;
	}
	function set_xsl( $doc ){
		$this->aDocument( &$doc );
		$path= $this->xslPath;
		if( file_exists( $path ) ) unlink( $path );
		$doc->save( $path );
		return null;
	}

	protected $_processor;
	function get_processor( ){
		$proc= $this->_processor;
		if( $proc ) return $proc;
		$proc= new XSLTProcessor( );
		$proc->importStyleSheet( $this->xsl );
		return $this->_processor= $proc;
	}
	function set_processor( $doc ){
		throw new Exception( 'can not redefine processor' );
	}

	function aDocument( $val ){
		if( is_string( $val ) ):
			$val= DOMDocument::loadXML( $val );
		elseif( is_array( $val ) ):
			$val= $this->_array2doc( $val );
		endif;
		if( is_object( $val ) ):
			if( $val instanceof SimpleXMLElement ) $val= dom_import_simplexml( $val );
			if( $val->ownerDocument ) $val= $val->ownerDocument;
		endif;
		if(!( $val instanceof DOMDocument )) throw new Exception( 'unsupported type' );
		return $val;
	}

	function _value2node( $array, $parent ){
		foreach( $array as $key => $value ):
			if( !$value ) continue;
			$node= $parent;
			if( !is_numeric( $key ) ):
				$node= $parent->addChild( $key, is_scalar( $value ) ? (string)$value : '' );
			endif;
			if( is_array( $value ) ):
				$this->_value2node( $value, $node );
			elseif( is_object( $value ) ):
				if( $value instanceof SimpleXMLElement ) $value= dom_import_simplexml( $value );
				if( $value instanceof DOMNode ):
					$nodeDOM= dom_import_simplexml( $node );
					$nodeDOM->appendChild( $nodeDOM->ownerDocument->importNode( $value ) );
				endif;
			endif;
		endforeach;
	}
	function _array2doc( $array ){
		foreach( $array as $root => $data ):
			$node= new SimpleXMLElement( "<{$root}/>" );
			$this->_value2node( $data, $node );
			return $node;
		endforeach;
	}

	function process( $doc ){
		$this->aDocument( &$doc );
		$res= $this->processor->transformToDoc( $doc );
		return $res;
	}

	function sync( ){
		$xsPath= $this->xsPath;
		$xslPath= $this->xslPath;
		if( filemtime( $xsPath ) !== fileatime( $xsPath ) ):
			$this->compile();
		endif;
		return $this;
	}

	function compile( ){
		$xs= $this->xs;
		$dir= getcwd();
		chdir( $this->dir );
		$xs2xsl= new $this( __DIR__ . '/xs2xsl.xs' );
		$xsl= $xs2xsl->process( $xs );
		chdir( $dir );
		$this->xs= $xs;
		$this->xsl= $xsl;
		return $this;
	}

}

return 'XStyle';

?>