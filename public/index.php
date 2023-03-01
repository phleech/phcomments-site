<?php

require __DIR__ . '/../vendor/autoload.php';
use App\Parser;

$parser = new Parser();
$comments = $parser->getComments();
$response = json_encode($comments);

header("Content-Type: application/json");
header("Content-Length: " . strlen($response));
header("X-Comment-Count: " . count($comments));
header("X-Max-Comment-Body-Length: " . $parser->getMaxCommentBodyLength());
header("X-Max-Comment-Author-Length: " . $parser->getMaxCommentAuthorLength());

echo $response;
