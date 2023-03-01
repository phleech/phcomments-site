<?php

namespace App;

use JsonSerializable;

class Comment implements JsonSerializable
{
    const ATTRIBUTE_BODY = 'body';
    const ATTRIBUTE_TIMESTAMP = 'timestamp';
    const ATTRIBUTE_AUTHOR = 'author';
    const ATTRIBUTE_VOTES = 'votes';

    const DEFAULT_MAX_BODY_LENGTH = 200;
    const DEFAULT_MAX_AUTHOR_LENGTH = 15;

    const ATTRIBUTES = [
        self::ATTRIBUTE_BODY,
        self::ATTRIBUTE_TIMESTAMP,
        self::ATTRIBUTE_AUTHOR,
        self::ATTRIBUTE_VOTES,
    ];

    private array $data = [];

    public function __construct(array $attributes = [])
    {
        foreach (self::ATTRIBUTES as $attribute) {
            $value = null;

            if (array_key_exists($attribute, $attributes)) {
                $value = $attributes[$attribute];
            }

            $this->data[$attribute] = $value;
        }
    }

    public function jsonSerialize(): mixed
    {
        return $this->data;
    }

    public function getBody(): string
    {
        return $this->data[self::ATTRIBUTE_BODY];
    }

    public function setBody(string $body): void
    {
        $this->data[self::ATTRIBUTE_BODY] = $body;
    }

    public function getAuthor(): string
    {
        return $this->data[self::ATTRIBUTE_AUTHOR];
    }

    public function setAuthor(string $author): void
    {
        $this->data[self::ATTRIBUTE_AUTHOR] = $author;
    }
}
