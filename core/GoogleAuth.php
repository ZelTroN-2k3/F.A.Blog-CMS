<?php
class GoogleAuth {
    protected $_codeLength = 6;

    public function createSecret($length = 16) {
        $validChars = $this->_getBase32LookupTable();
        if ($length < 16 || $length > 128) $length = 16;
        $secret = '';
        $rnd = false;
        if (function_exists('random_bytes')) { $rnd = random_bytes($length); }
        elseif (function_exists('openssl_random_pseudo_bytes')) { $rnd = openssl_random_pseudo_bytes($length, $cryptoStrong); if (!$cryptoStrong) { $rnd = false; } }
        if ($rnd !== false) {
            for ($i = 0; $i < $length; ++$i) { $secret .= $validChars[ord($rnd[$i]) & 31]; }
        } else {
            throw new Exception('No source of secure random');
        }
        return $secret;
    }

    public function verifyCode($secret, $code, $discrepancy = 1, $currentTimeSlice = null) {
        if ($currentTimeSlice === null) { $currentTimeSlice = floor(time() / 30); }
        if (strlen($code) != 6) return false;
        for ($i = -$discrepancy; $i <= $discrepancy; ++$i) {
            $calculatedCode = $this->getCode($secret, $currentTimeSlice + $i);
            if ($this->timingSafeEquals($calculatedCode, $code)) { return true; }
        }
        return false;
    }

    public function getCode($secret, $timeSlice = null) {
        if ($timeSlice === null) { $timeSlice = floor(time() / 30); }
        $secretkey = $this->_base32Decode($secret);
        $time = chr(0).chr(0).chr(0).chr(0).pack('N*', $timeSlice);
        $hmac = hash_hmac('sha1', $time, $secretkey, true);
        $offset = ord(substr($hmac, -1)) & 0x0F;
        $hashpart = substr($hmac, $offset, 4);
        $value = unpack('N', $hashpart);
        $value = $value[1];
        $value = $value & 0x7FFFFFFF;
        $modulo = pow(10, $this->_codeLength);
        return str_pad($value % $modulo, $this->_codeLength, '0', STR_PAD_LEFT);
    }

    public function getQRCodeUrl($name, $secret, $title = null) {
        $urlencoded = urlencode('otpauth://totp/' . $name . '?secret=' . $secret . '');
        if (isset($title)) { $urlencoded .= urlencode('&issuer=' . urlencode($title)); }
        return 'https://api.qrserver.com/v1/create-qr-code/?data=' . $urlencoded . '&size=200x200&ecc=M';
    }

    protected function _base32Decode($secret) {
        if (empty($secret)) return '';
        $base32chars = $this->_getBase32LookupTable();
        $base32charsFlipped = array_flip($base32chars);
        $paddingCharCount = substr_count($secret, '=');
        $allowedValues = array(6, 4, 3, 1, 0);
        if (!in_array($paddingCharCount, $allowedValues)) return false;
        for ($i = 0; $i < 4; ++$i) {
            if ($paddingCharCount == $allowedValues[$i] && substr($secret, -($allowedValues[$i])) != str_repeat('=', $allowedValues[$i])) return false;
        }
        $secret = str_replace('=', '', $secret);
        $secret = str_split($secret);
        $binaryString = '';
        for ($i = 0; $i < count($secret); $i = $i + 8) {
            $x = '';
            if (!in_array($secret[$i], $base32chars)) return false;
            for ($j = 0; $j < 8; ++$j) {
                $x .= str_pad(base_convert(@$base32charsFlipped[@$secret[$i + $j]], 10, 2), 5, '0', STR_PAD_LEFT);
            }
            $eightBits = str_split($x, 8);
            for ($z = 0; $z < count($eightBits); ++$z) {
                $binaryString .= (($y = chr(base_convert($eightBits[$z], 2, 10))) || ord($y) == 48) ? $y : '';
            }
        }
        return $binaryString;
    }

    protected function _getBase32LookupTable() {
        return array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '2', '3', '4', '5', '6', '7', '=');
    }

    private function timingSafeEquals($safe, $user) {
        if (function_exists('hash_equals')) { return hash_equals($safe, $user); }
        $safeLen = strlen($safe);
        $userLen = strlen($user);
        if ($userLen != $safeLen) { return false; }
        $result = 0;
        for ($i = 0; $i < $userLen; ++$i) { $result |= (ord($safe[$i]) ^ ord($user[$i])); }
        return $result === 0;
    }
}
?>