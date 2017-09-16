<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class FileTest extends TestCase {
    
    public function testNameToPath() {
        $this->assertNameToPath(' FuU   bAr ', 'fuu_bar');
        $this->assertNameToPath(' -/', '_');
        $this->assertNameToPath(' __ _ ', '_');
        $this->assertNameToPath('äüöß', 'aeueoess');
        $this->assertNameToPath('.&`´"\'', '');
        $this->assertNameToPath('Scania 110/111 & 140/141', 'scania_110_111_140_141');
        $this->assertNameToPath('Scania S & new R Serie', 'scania_s_new_r_serie');
    }
    
    private function assertNameToPath(String $pattern, String $result) {
        $this->assertEquals($result, MT_Admin_Model_File::nameToPath($pattern));
    }
}