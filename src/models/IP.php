<?php

namespace Phroses;

class IP {
    public $address;
    public $subnet;
    public $mask;

    const DEFAULT_NETMASK = 32;
    const NETMASK_SEPARATOR = "/";
    const MASK_CHARS = [
        1 => '8',
        2 => 'c',
        3 => 'e'
    ];

    public function __construct($cidr) {
        $address = strstr($cidr, self::NETMASK_SEPARATOR, true);
        $this->address = inet_pton(($address) ? $address : $cidr);
        $this->subnet = (!$address) ? self::DEFAULT_NETMASK : substr(strstr($cidr, self::NETMASK_SEPARATOR), strlen(self::NETMASK_SEPARATOR));

        $this->generateMask();
    }

    private function generateMask() {
        $length = 8 * strlen($this->address);
        if($this->subnet > $length) $this->subnet = $length;

        $mask = str_repeat('f', $this->subnet >> 2);
        $mask .= self::MASK_CHARS[$this->subnet & 3] ?? "";
        $mask = str_pad($mask, $length >> 2, '0');
        $this->mask = pack('H*', $mask);
    }

    public function inRange($cidr) {
        $netAddr = new self($cidr);
        return ($this->address & $netAddr->mask) == ($netAddr->address & $netAddr->mask);
    }
}