<?php class ProtoObject {


static $version= 8;
static $description= 'common object extension';
static $license= 'public domain';

function __toString( ){
    return print_r( $this, true );
}

function __set( $name, $value= null ){
    $this->_aPropertyName( &$name );
    $method= 'set' . $name;
    if( !method_exists( $this, $method ) ) $method= 'set_';
    $value= $this->{ $method }( $value );
    $this->{ $name }= $value;
    return $this;
}
function set_( $val ){
    return $val;
}

function __get( $name ){
    $this->_aPropertyName( &$name );
    $method= 'get' . $name;
    if( !method_exists( $this, $method ) ) $method= 'get_';
    $value= $this->{ $name };
    $value= $this->{ $method }( $value );
    return $value;
}
function get_( $val ){
    return $val;
}

function __call( $name, $args ){
    try {
        $this->_aPropertyName( &$name );
    } catch( Exception $e ){
        return $this->_call( $name, $args );
    }
    switch( count( $args ) ){
        case 0: return $this->__get( $name );
        case 1: return $this->__set( $name, $args[0] );
        default: throw new Exception( 'wrong parameters count' );
    }
}
function _call( $name, $args ){
    throw new Exception( 'method not found' );
}

function _aPropertyName( $val ){
    if( $val[0] !== '_' ) $val= '_' . $val;
    if( !property_exists( $this, $val ) ) throw new Exception( 'property not found' );
    return $val;
}


} return 'ProtoObject'; ?>