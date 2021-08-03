<?php
declare(strict_types=1);
namespace ParagonIE\Paserk\Tests\KAT;

use ParagonIE\ConstantTime\Hex;
use ParagonIE\Paserk\Tests\KnownAnswers;
use ParagonIE\Paserk\Types\Pid;
use ParagonIE\Paseto\Keys\AsymmetricPublicKey;
use ParagonIE\Paseto\ProtocolInterface;
use ParagonIE\Paseto\Protocol\{
    Version1,
    Version2,
    Version3,
    Version4
};

/**
 * @covers Pid
 */
class PidTest extends KnownAnswers
{
    public function testV1()
    {
        $this->doJsonTest(new Version1(), 'k1.pid.json');
    }

    public function testV2()
    {
        $this->doJsonTest(new Version2(), 'k2.pid.json');
    }

    public function testV3()
    {
        $this->doJsonTest(new Version3(), 'k3.pid.json');
    }

    public function testV4()
    {
        $this->doJsonTest(new Version4(), 'k4.pid.json');
    }

    protected function genericTest(ProtocolInterface $version, string $name, array $tests): void
    {
        foreach ($tests as $test) {
            if ($version instanceof Version1) {
                $publickey = new AsymmetricPublicKey($test['key'], $version);
            } else {
                $publickey = new AsymmetricPublicKey(Hex::decode($test['key']), $version);
            }
            $this->assertSame($test['paserk'], Pid::encodePublic($publickey), $test['name']);
        }
    }
}
