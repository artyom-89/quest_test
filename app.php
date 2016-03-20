#!/usr/bin/env php
<?php

require __DIR__.'/vendor/autoload.php';


require_once __DIR__.'/App.php';
use App\App;
$app = new App();

$options = getopt('', ['without-documents', 'with-documents']);


if (!isset($stdin)) $stdin = fopen('php://stdin', 'r');
if (!isset($stdout)) $stdout = fopen('php://stdout', 'w');

if (empty($options)) {
    fwrite($stdout, "Usage:\n\tphp app.php [options]\n\nOptions:\n\t--with-documents\n\t--without-documents\n");
    exit;
}

fwrite($stdout, "Please enter start date: ");
$dateStart = fgets($stdin);
if (!$app->setDateStart($dateStart)) {
    fwrite($stdout, "Invalid date. Please use format YYYY.MM.DD\n");
    exit;
}

fwrite($stdout, "Please enter end date: ");
$dateFinish = fgets($stdin);
if (!$app->setDateFinish($dateFinish)) {
    fwrite($stdout, "Invalid date. Please use format YYYY.MM.DD\n");
    exit;
}

$app->setSourceOptions($options);
$arrPayments = $app->getPayments();

printf("\n");
$headerPrinted = false;
foreach ($arrPayments as $arrPayment) {
    printf("-----------------------\n");
    if (!$headerPrinted) {
        printf("|%-10s|%-10s|\n", 'count', 'amount');
        printf("-----------------------\n");
        $headerPrinted = true;
    }

    printf("|%-10d|%-10g|\n", $arrPayment['count'], $arrPayment['amount']);
}
printf("-----------------------\n");
exit;


