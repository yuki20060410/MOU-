<?php
require_once __DIR__ . '/../lib/allergy_function.php';

if (!isset($p)) return;

$materialText =
    ($p['material1'] ?? '') .
    ($p['material2'] ?? '');
echo $materialText;
var_dump($materialText);
$matched = extractAllergies($materialText, $allergyMaster);

echo $matched ? implode('、', $matched) : '該当アレルギーなし';
