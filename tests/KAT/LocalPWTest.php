<?php
declare(strict_types=1);
namespace ParagonIE\Paserk\Tests\KAT;

use ParagonIE\ConstantTime\Hex;
use ParagonIE\HiddenString\HiddenString;
use ParagonIE\Paserk\PaserkException;
use ParagonIE\Paserk\Types\LocalPW;
use ParagonIE\Paserk\Tests\KnownAnswers;
use ParagonIE\Paseto\Protocol\{
    Version1,
    Version2,
    Version3,
    Version4
};
use ParagonIE\Paseto\ProtocolInterface;

/**
 * @covers LocalPW
 */
class LocalPWTest extends KnownAnswers
{
    public function testV1()
    {
        $this->doJsonTest(new Version1(), 'k1.local-pw.json');
    }

    public function testV2()
    {
        $this->doJsonTest(new Version2(), 'k2.local-pw.json');
    }

    public function testV3()
    {
        $this->doJsonTest(new Version3(), 'k3.local-pw.json');
    }

    public function testV4()
    {
        $this->doJsonTest(new Version4(), 'k4.local-pw.json');
    }

    /**
     * @param ProtocolInterface $version
     * @param string $name
     * @param array $tests
     *
     * @throws PaserkException
     */
    protected function genericTest(ProtocolInterface $version, string $name, array $tests): void
    {
        foreach ($tests as $test) {
            $wrapper = new LocalPW(
                new HiddenString(Hex::encode($test['password'])),
                $test['options'] ?? []
            );
            $unwrapped = $wrapper->decode($test['paserk']);
            $this->assertSame(
                $test['unwrapped'],
                Hex::encode($unwrapped->raw()),
                $test['name']
            );
        }
    }
}
