<?php

class Channel
{
    /** @var string */
    private $sname;

    /** @var string */
    private $sref;

    public function __construct(string $sname, string $sref)
    {
        $this->sname = $sname;
        $this->sref  = $sref;
    }

    public function getSname(): string
    {
        return $this->sname;
    }

    public function getSref(): string
    {
        return $this->sref;
    }
}

class Event
{
    /** @var Channel */
    private $channel;

    /** @var string */
    private $title;

    /** @var DateTimeInterface */
    private $begin;

    /** @var DateTimeInterface */
    private $end;

    /** @var string */
    private $shortdesc;

    /** @var string */
    private $longdesc;

    private function __construct()
    {
    }

    public static function createFromJsonArray(Channel $channel, array $eventData): Event
    {
        $event = new Event();

        $event->channel   = $channel;
        $event->title     = $eventData['title'];
        $event->begin     = DateTimeImmutable::createFromFormat('U', $eventData['begin_timestamp']);
        $event->end       = DateTimeImmutable::createFromFormat('U',
            $eventData['begin_timestamp'] + $eventData['duration_sec']);
        $event->shortdesc = $eventData['shortdesc'];
        $event->longdesc  = $eventData['longdesc'];

        return $event;
    }

    public function getChannel(): Channel
    {
        return $this->channel;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getBegin(): DateTimeInterface
    {
        return $this->begin;
    }

    public function getEnd(): DateTimeInterface
    {
        return $this->end;
    }

    public function getDescription(): string
    {
        if ($this->longdesc !== '') {
            return $this->longdesc;
        }

        return $this->shortdesc;
    }
}

if ($argc !== 2) {
    die(sprintf("Usage: %s <engimaJsonPath>\n", $argv[0]));
}

$jsonFilePath = $argv[1];

$jsonContents = file_get_contents($jsonFilePath);

if ($jsonContents === false) {
    throw new RuntimeException(sprintf('Failed to read "%s"', $jsonFilePath));
}

$json = json_decode($jsonContents, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    throw new RuntimeException(sprintf('Failed to parse "%s": %s', $jsonFilePath, json_last_error_msg()));
}

$rawEvents = $json['events'];

$channels     = [];
$parsedEvents = [];

foreach ($rawEvents as $event) {
    $channel = new Channel($event['sname'], $event['sref']);

    $channels[$event['sref']] = $channel;
    $parsedEvents[]           = Event::createFromJsonArray($channel, $event);
}

$document  = new DOMDocument();
$tvElement = new DOMElement('tv');
$document->appendChild($tvElement);

foreach ($channels as $channel) {
    $channelElement = $document->createElement('channel');
    $channelElement->setAttribute('id', $channel->getSref());
    $displayNameElement = $document->createElement('display-name', $channel->getSname());
    $displayNameElement->setAttribute('lang', '');
    $channelElement->appendChild($displayNameElement);

    $tvElement->appendChild($channelElement);
}

foreach ($parsedEvents as $event) {
    /** @var Event $event */
    $programmeElement = $document->createElement('programme');
    $programmeElement->setAttribute('start', $event->getBegin()->format('YmdHis +0000'));
    $programmeElement->setAttribute('stop', $event->getEnd()->format('YmdHis +0000'));
    $programmeElement->setAttribute('channel', $event->getChannel()->getSref());
    $titleElement = $document->createElement('title', $event->getTitle());
    $titleElement->setAttribute('lang', '');
    $descElement = $document->createElement('desc', $event->getDescription());
    $descElement->setAttribute('lang', '');

    $programmeElement->appendChild($titleElement);
    $programmeElement->appendChild($descElement);

    $tvElement->appendChild($programmeElement);
}

$document->formatOutput = true;
$xml                    = $document->saveXML();

echo $xml;
