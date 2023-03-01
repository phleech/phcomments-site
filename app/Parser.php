<?php

namespace App;

use App\Comment;
use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;

class Parser
{
    const URL = 'https://www.pornhub.com/video/random';

    const COMMENT_DOM_LOCATION = 'div#cmtWrapper > div#cmtContent > div.commentBlock > div.topCommentBlock';
    const BODY_DOM_LOCATION = 'div.commentMessage > span';
    const TIMESTAMP_DOM_LOCATION = 'div.userWrap > div.date';
    const AUTHOR_DOM_LOCATION = 'div.userWrap > div.usernameWrap .usernameLink';
    const VOTES_DOM_LOCATION = 'div.commentMessage > div.actionButtonsBlock > span.voteTotal';

    const DEFAULT_STRIP_NON_ALPHA_CHARS = true;
    const DEFAULT_MAX_ATTEMPTS = 10;

    private Client $client;
    private bool $stripNonAlphaChars;
    private int $maxRetryAttempts;
    private int $maxCommentBodyLength;
    private int $maxCommentAuthorLength;
    private array $comments;

    public function __construct(
        $stripNonAlphaChars = self::DEFAULT_STRIP_NON_ALPHA_CHARS,
        $maxRetryAttempts = self::DEFAULT_MAX_ATTEMPTS,
        $maxCommentBodyLength = Comment::DEFAULT_MAX_BODY_LENGTH,
        $maxCommentAuthorLength = Comment::DEFAULT_MAX_AUTHOR_LENGTH
    ) {
        $this->client = new Client();
        $this->stripNonAlphaChars = $stripNonAlphaChars;
        $this->maxRetryAttempts = $maxRetryAttempts;
        $this->maxCommentBodyLength = $maxCommentBodyLength;
        $this->maxCommentAuthorLength = $maxCommentAuthorLength;

        $this->parse();
    }

    private function parse()
    {
        $attemptCounter = 0;
        do {
            $crawler = $this->makeRequest();
            $this->comments = $crawler->filter(self::COMMENT_DOM_LOCATION)->each(function ($node) {
                return new Comment([
                    Comment::ATTRIBUTE_BODY => $node->filter(self::BODY_DOM_LOCATION)->text(),
                    Comment::ATTRIBUTE_TIMESTAMP => $node->filter(self::TIMESTAMP_DOM_LOCATION)->text(),
                    Comment::ATTRIBUTE_AUTHOR => $node->filter(self::AUTHOR_DOM_LOCATION)->text(),
                    Comment::ATTRIBUTE_VOTES => $node->filter(self::VOTES_DOM_LOCATION)->text()
                ]);
            });
        } while ($attemptCounter++ < $this->maxRetryAttempts && empty($this->comments));

        $this->filterMaxCommentBodyLength();
        $this->filterMaxCommentAuthorLength();
        $this->filterAlphaChars();
    }

    private function filterMaxCommentBodyLength(): void
    {
        $this->comments = array_values(
            array_filter($this->comments, function (Comment $comment) {
                return strlen($comment->getBody()) <= $this->maxCommentBodyLength;
            })
        );
    }

    private function filterMaxCommentAuthorLength(): void
    {
        $this->comments = array_values(
            array_filter($this->comments, function (Comment $comment) {
                return strlen($comment->getAuthor()) <= $this->maxCommentAuthorLength;
            })
        );
    }

    private function filterAlphaChars(): void
    {
        if (!$this->stripNonAlphaChars) {
            return;
        }

        $this->comments = array_map(function (Comment $comment) {
            $comment->setAuthor(
                preg_replace("/[^A-Za-z0-9 !?'.,\/\$£:;]/", '', $comment->getAuthor())
            );

            $comment->setBody(
                preg_replace("/[^A-Za-z0-9 !?'.,\/\$£:;]/", '', $comment->getBody())
            );
            return $comment;
        }, $this->comments);
    }

    private function makeRequest(): Crawler
    {
        return $this->client->request('GET', self::URL);
    }

    public function getComments(): array
    {
        return $this->comments;
    }

    public function getMaxCommentBodyLength(): int
    {
        return $this->maxCommentBodyLength;
    }

    public function getMaxCommentAuthorLength(): int
    {
        return $this->maxCommentAuthorLength;
    }
}
