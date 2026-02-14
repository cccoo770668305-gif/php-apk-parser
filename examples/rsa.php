<?php

$cert_path = __DIR__ . '/extract_folder/META-INF/CERT.RSA';
$cert = file_get_contents(__DIR__ . '/extract_folder/META-INF/CERT.RSA');

$beginpem = "-----BEGIN CERTIFICATE-----\n";
$endpem = "-----END CERTIFICATE-----\n";
$cert = $beginpem . $cert . $endpem;
$c = openssl_x509_parse($cert);

var_dump($c, openssl_error_string());
