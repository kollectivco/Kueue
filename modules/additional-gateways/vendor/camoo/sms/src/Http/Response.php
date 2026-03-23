<?php

declare(strict_types=1);

namespace Camoo\Sms\Http;

use Camoo\Sms\Lib\Utils;
use SimpleXMLElement;
use stdClass;
use Throwable;

/**
 * Class Response
 *
 * @author CamooSarl
 */
class Response
{
    /** @var string */
    public const BAD_STATUS = 'KO';

    /** @var string */
    public const GOOD_STATUS = 'OK';

    private const SUCCESS_HTTP_CODE = 200;

    private const JSON_EXTENSION = 'json';

    private const XML_EXTENSION = 'xml';

    protected array $data;

    public function __construct(
        private readonly string $content = '',
        private readonly int $statusCode = self::SUCCESS_HTTP_CODE,
        private readonly ?string $format = null
    ) {
        $extension = $this->format ?? self::JSON_EXTENSION;
        $this->data = $extension === self::JSON_EXTENSION ? $this->getJson() : [];
    }

    public function getBody(): string
    {
        return $this->content;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getJson(): array
    {
        if ($this->getStatusCode() !== 200) {
            $message = $this->content !== '' ? $this->content : 'request failed!';

            return ['status' => static::BAD_STATUS, 'message' => $message];
        }
        $result = $this->decodeJson($this->content, true) ?? [];

        return array_merge(['status' => static::GOOD_STATUS], $result);
    }

    public function getXml(): ?string
    {
        if ($this->format !== self::XML_EXTENSION) {
            return null;
        }
        if ($this->content === '') {
            return null;
        }

        return $this->decodeXml($this->content);
    }

    protected function decodeJson(string $sJSON, bool $bAsHash = false): stdClass|array|null
    {
        if ($this->content === '') {
            return null;
        }

        return Utils::decodeJson($sJSON, $bAsHash);
    }

    private function decodeXml(string $body): ?string
    {
        $data = null;
        try {
            $xml = new SimpleXMLElement($body);
            $data = $xml->asXML() ?: null;
        } catch (Throwable $exception) {
            echo $exception->getMessage();
        }

        return $data;
    }
}
