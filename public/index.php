<?php

require __DIR__.'/../vendor/autoload.php';
use PHComments\Parser;

$parser = new Parser();
$comments = $parser->randomVideo()->getComments(translateEmojis: !empty($_GET['translateEmoji']));

$response = json_encode($comments);

header('Content-Type: application/json');
header('Content-Length: '.strlen($response));
header('X-Comment-Count: '.count($comments));
header('X-Max-Comment-Body-Length: '.$parser->maxCommentBodyLength);
header('X-Max-Comment-Author-Length: '.$parser->maxCommentAuthorLength);
header('X-Video-URL: '.$parser->getPageUrl());


echo $response;
