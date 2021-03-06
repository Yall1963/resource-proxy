<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace FP\Web\Portal\FpOnlineRateTable\src\Utils\CheckedValue;

require_once 'CheckedValue.php';


class TextValue extends CheckedValue {
    
    protected function validate( $value ) {
        return is_string( $value );
    }
    
    protected function sanitize( $value ) {
        return esc_attr( $value );
    }
}