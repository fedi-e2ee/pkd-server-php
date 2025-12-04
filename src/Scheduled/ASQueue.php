<?php
declare(strict_types=1);
namespace FediE2EE\PKDServer\Scheduled;

use FediE2EE\PKDServer\ActivityPub\{
    ActivityStream,
    WebFinger
};
use FediE2EE\PKDServer\Exceptions\{
    DependencyException,
    ProtocolException
};
use FediE2EE\PKDServer\{
    Protocol,
    ServerConfig
};
use GuzzleHttp\{
    Client,
    Psr7\Request
};
use ParagonIE\Certainty\Exception\CertaintyException;
use ParagonIE\EasyDB\EasyDB;
use SodiumException;
use Throwable;

class ASQueue
{
    private EasyDB $db;
    private Client $http;
    protected ?WebFinger $webFinger = null;

    /**
     * @throws CertaintyException
     * @throws DependencyException
     * @throws SodiumException
     */
    public function __construct(private readonly ServerConfig $config)
    {
        $this->db = $config->getDB();
        $this->http = $this->config->getGuzzle();
        $this->webFinger = new WebFinger($this->http, $this->config->getCaCertFetch());
    }

    /**
     * ASQueue::run() is a very dumb method.
     *
     * All this method does is grab the unprocessed messages, order them, decode them, and then pass them onto
     * Protocol::process().
     *
     * The logic is entirely contained to Protocol and the Table classes.
     */
    public function run(): void
    {
        $workload = $this->db->run(
            "SELECT * FROM pkd_activitystream_queue
            WHERE NOT processed
            ORDER BY queueid ASC"
        );
        $protocol = new Protocol($this->config);
        foreach ($workload as $queue) {
            $success = false;
            try {
                $decoded = json_decode($queue['message'], false, 512, JSON_THROW_ON_ERROR);
                $enqueued = ActivityStream::fromDecoded($decoded);
                try {
                    $results = $protocol->process($enqueued);
                    $success = true;
                    $this->replySuccess($enqueued, $results);
                } catch (ProtocolException $ex) {
                    $this->replyFailure($enqueued, $ex);
                }
            } catch (Throwable $ex) {
                $this->config->getLogger()->error($ex->getMessage());
                echo $ex->getMessage(), PHP_EOL;
            }

            // Update the database to mark this field as processed and whether it was successful.
            $this->db->update(
                'pkd_activitystream_queue',
                [
                    'processed' => true,
                    'successful' => $success,
                ],
                ['queueid' => $queue['queueid']]
            );
        }
    }

    protected function replySuccess(ActivityStream $enqueued, array $results): void
    {
        // TODO: Let this be customized.
        $message = match ($results['action']) {
            'AddKey' => 'Key added successfully.',
            'AddAuxData' => 'Auxiliary data added successfully.',
            'BurnDown' => 'The fire has been lit. Now all of Fedi knows your keys are revoked.',
            'Checkpoint' => 'Checkpoint acknowledged.',
            'Fireproof' => 'You are now immune from BurnDown.',
            'RevokeKey' => 'Key revoked successfully.',
            'RevokeAuxData' => 'Auxiliary data revoked successfully.',
            'UndoFireproof' => 'You are no longer immune from BurnDown.',
            default => '',
        };
        if (empty($message)) {
            return;
        }

        // Append latest Merkle root
        $message .= "\nLatest Merkle Root: {$results['latest-root']}";

        $this->sendDM($enqueued->actor, $enqueued->id, [
            'content' => $message
        ]);
    }

    protected function replyFailure(ActivityStream $enqueued, Throwable $ex): void
    {
        $this->sendDM($enqueued->actor, $enqueued->id, [
            'content' => defined('PKD_SERVER_DEBUG')
                ? $ex->getMessage()
                : 'An unexpected error has occurred.',
        ]);
    }

    protected function sendDM(string $actor, string $inReplyTo, array $object): void
    {
        $params = $this->config->getParams();
        // Format the object:
        $data = [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'type' => 'Create',
            'actor' => 'https://' . $params->hostname . '/users/' . $params->actorUsername,
            'inReplyTo' => $inReplyTo,
            'to' => [$actor],
            'object' => $object,
        ];
        $data['object']['type'] = 'Note';
        $encoded = json_encode(
            $data,
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );

        // Get the actor's inbox URL:
        $actorInbox = $this->webFinger->getInboxUrl($actor);

        // Create and send a request:
        $request = new Request(
            'POST',
            $actorInbox,
            ['Accept' => 'application/activity+json'],
            $encoded
        );
        $this->http->send($request);
    }
}
