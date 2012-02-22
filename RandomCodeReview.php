<?php

namespace GithubRandomCodeReview;

require 'config/bootstrap.php';

use GithubApi_v3\Api;
use JsonDB\JsonDB;

$api = new Api();
$db = new JsonDB();
$db->setdb('RandomCodeReview');

if (false === $api->login($config['username'], $config['password'])) {
    die('Bad User Credidentials');
}

$commits = $api->getCommits($config['repository']['user'], $config['repository']['repo']);

// user don't have access to this repo
if (isset($commits['message'])) {
    die($commits['message']);
}

// get the last commit sha analysed by cron
$last_sha = false !== $db->get('sha') ? $db->get('sha') : 0 ;
$history = false !== $db->get('history') ? $db->get('history') : array() ;

// create a simple sha array
foreach ($commits as $key => $value) {
    $sha_array[$value['sha']] = $key;
}

$from = isset($sha_array[$last_sha]) ? $sha_array[$last_sha] : sizeof($sha_array)-1;
$from--;
$count = 0;
$headers = 'From: noreply@githubrandomcodereview.com' . "\r\n" .
     'Reply-To: noreply@githubrandomcodereview.com' . "\r\n" .
     'X-Mailer: PHP/' . phpversion();

// loop through commits array from oldest to newest
for ($i = $from; $i >= 0; $i--) {
    if (0 === $i % $config['commits_interval']) {
        $author = $commits[$i]['author']['login'];
        $sha = $commits[$i]['sha'];
        $url = 'https://github.com/'.$config['repository']['user'].'/'.$config['repository']['repo'].'/commit/'.$sha;
        $reviewer = getRandomReviewerEmail($author);

        if (true === mail($reviewer, "[GithubRandomCodeReview] Please review $author's commit!", $url, $headers)) {
            $db->set('sha', $sha);
            $hist = array('author' => $author, 'reviewer' => $reviewer, 'commit' => $url);
            echo "\n" . json_encode($hist);
            $history[] = $hist;
            $count++;
            $db->set('history', $history);
        } else {
            echo "\n ERROR: Unable to send email";
        }
    }
}

$db->save();
echo "\n******\n Total review asked: $count \n******\n";

function getRandomReviewerEmail($author) {
    global $config;

    do {
        $rand_key = rand(0, sizeof($config['reviewers'])-1);
    } while ($config['reviewers'][$rand_key]['username'] == $author);

    return $config['reviewers'][$rand_key]['email'];
}