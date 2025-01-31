<?php
declare(strict_types=1);
namespace ParagonIE\Paserk\Types;

use ParagonIE\HiddenString\HiddenString;
use ParagonIE\Paserk\ConstraintTrait;
use ParagonIE\Paserk\Operations\PBKW;
use ParagonIE\Paserk\PaserkException;
use ParagonIE\Paserk\PaserkTypeInterface;
use ParagonIE\Paserk\Util;
use ParagonIE\Paseto\KeyInterface;
use ParagonIE\Paseto\Keys\SymmetricKey;

/**
 * Class LocalPW
 * @package ParagonIE\Paserk\Types
 */
class LocalPW implements PaserkTypeInterface
{
    use ConstraintTrait;

    /** @var array<string, string> */
    protected $localCache = [];

    /** @var array $options */
    protected $options;

    /** @var HiddenString $password */
    protected $password;

    /**
     * LocalPW constructor.
     * @param HiddenString $password
     * @param array $options
     */
    public function __construct(HiddenString $password, array $options = [])
    {
        $this->password = $password;
        $this->options = $options;
        $this->localCache = [];
    }

    /**
     * @param string $paserk
     * @return KeyInterface
     * @throws PaserkException
     */
    public function decode(string $paserk): KeyInterface
    {
        $pieces = explode('.', $paserk);
        $header = $pieces[0];
        $version = Util::getPasetoVersion($header);
        $this->throwIfInvalidProtocol($version);
        $pbkw = PBKW::forVersion($version);

        return $pbkw->localPwUnwrap($paserk, $this->password);
    }

    /**
     * @param KeyInterface $key
     * @return string
     * @throws PaserkException
     */
    public function encode(KeyInterface $key): string
    {
        if (!($key instanceof SymmetricKey)) {
            throw new PaserkException('Only symmetric keys are allowed here');
        }
        $this->throwIfInvalidProtocol($key->getProtocol());
        $localId = (new Local())->encode($key);
        if (!array_key_exists($localId, $this->localCache)) {
            $this->localCache[$localId] = PBKW::forVersion($key->getProtocol())
                ->localPwWrap($key, $this->password, $this->options);
        }
        return $this->localCache[$localId];
    }

    /**
     * @return string
     */
    public static function getTypeLabel(): string
    {
        return 'local-pw';
    }

    /**
     * @param KeyInterface $key
     * @return string
     *
     * @throws PaserkException
     * @throws \SodiumException
     */
    public function id(KeyInterface $key): string
    {
        return Lid::encode(
            $key->getProtocol(),
            $this->encode($key)
        );
    }
}
