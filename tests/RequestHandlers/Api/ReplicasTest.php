<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Tests\RequestHandlers\Api;

use FediE2EE\PKDServer\ActivityPub\{
    ActivityStream,
    WebFinger
};
use FediE2EE\PKDServer\Dependency\{
    EasyDBHandler,
    WrappedEncryptedRow
};
use FediE2EE\PKD\Crypto\AttributeEncryption\AttributeKeyMap;
use FediE2EE\PKD\Crypto\Exceptions\CryptoException;
use FediE2EE\PKD\Crypto\Exceptions\NotImplementedException;
use FediE2EE\PKD\Crypto\Protocol\Actions\AddKey;
use FediE2EE\PKD\Crypto\Protocol\Handler;
use FediE2EE\PKD\Crypto\SecretKey;
use FediE2EE\PKD\Crypto\SymmetricKey;
use GuzzleHttp\Psr7\Response;
use FediE2EE\PKDServer\Protocol\{
    KeyWrapping,
    Payload,
    RewrapConfig
};
use FediE2EE\PKDServer\{
    AppCache,
    Math,
    Protocol,
    ServerConfig,
    Table,
    TableCache,
    Tests\HttpTestTrait,
    Traits\ConfigTrait
};
use FediE2EE\PKDServer\Exceptions\{
    CacheException,
    DependencyException,
    TableException,
};
use FediE2EE\PKDServer\RequestHandlers\Api\Replicas;
use FediE2EE\PKDServer\Tables\{
    Actors,
    MerkleState,
    Peers,
    PublicKeys,
    TOTP
};
use FediE2EE\PKDServer\Tables\Records\{
    Actor,
    ActorKey,
    MerkleLeaf,
    Peer
};
use PHPUnit\Framework\Attributes\{
    CoversClass,
    UsesClass
};
use ParagonIE\Certainty\Exception\CertaintyException;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\InvalidArgumentException;
use Random\RandomException;
use SodiumException;

#[CoversClass(Replicas::class)]
#[UsesClass(ActivityStream::class)]
#[UsesClass(AppCache::class)]
#[UsesClass(WebFinger::class)]
#[UsesClass(EasyDBHandler::class)]
#[UsesClass(WrappedEncryptedRow::class)]
#[UsesClass(Protocol::class)]
#[UsesClass(KeyWrapping::class)]
#[UsesClass(Peers::class)]
#[UsesClass(Payload::class)]
#[UsesClass(ServerConfig::class)]
#[UsesClass(Table::class)]
#[UsesClass(TableCache::class)]
#[UsesClass(Actors::class)]
#[UsesClass(MerkleState::class)]
#[UsesClass(PublicKeys::class)]
#[UsesClass(TOTP::class)]
#[UsesClass(Actor::class)]
#[UsesClass(ActorKey::class)]
#[UsesClass(MerkleLeaf::class)]
#[UsesClass(Math::class)]
#[UsesClass(RewrapConfig::class)]
#[UsesClass(Peer::class)]
class ReplicasTest extends TestCase
{
    use ConfigTrait;
    use HttpTestTrait;

    /**
     * @throws CacheException
     * @throws DependencyException
     * @throws TableException
     */
    public function testHandle(): void
    {
        $hpke = $this->config()->getHPKE();

        // First, create a new peer that we replicate.
        // For simplicity, we replicate ourselves!
        $peersTable = $this->table('Peers');
        if (!($peersTable instanceof Peers)) {
            $this->fail('peers table is not the right type');
        }
        /** @var Peer $newPeer */
        $newPeer = $peersTable->create(
            $this->config->getSigningKeys()->publicKey,
            'localhost',
            false,
            true,
            RewrapConfig::from($hpke->cs, $hpke->encapsKey)
        );

        // Now that we have a replicated peer, let's make sure they show up:
        $request = $this->makeGetRequest('/api/replicas');
        $replicaHandler = new Replicas($this->config);
        $response = $replicaHandler->handle($request);

        $this->assertSame(200, $response->getStatusCode());
        $contents = $response->getBody()->getContents();
        $decoded = json_decode($contents, true);

        $this->assertArrayHasKey('!pkd-context', $decoded);
        $this->assertArrayHasKey('time', $decoded);
        $this->assertArrayHasKey('replicas', $decoded);
        $this->assertIsString($decoded['!pkd-context']);
        $this->assertIsString($decoded['time']);
        $this->assertIsArray($decoded['replicas']);
        $this->assertSame('fedi-e2ee:v1/api/replicas', $decoded['!pkd-context']);
        $found = false;
        foreach ($decoded['replicas'] as $replica) {
            $this->assertArrayHasKey('id', $replica);
            $this->assertArrayHasKey('ref', $replica);
            $this->assertStringStartsWith('https://', $replica['ref']);
            $this->assertNotSame('https://', $replica['ref']);
            if ($replica['id'] === $newPeer->uniqueId) {
                $found = true;
            }
        }
        $this->assertTrue($found, 'peer not found in replicas list');
    }
}
