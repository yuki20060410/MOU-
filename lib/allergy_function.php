<?php

function extractAllergies(string $material, array $allergies): array {
    if ($material === '') return [];

    // 文字数の長い順に並べ替え（重要）
    usort($allergies, function ($a, $b) {
        return mb_strlen($b) - mb_strlen($a);
    });

    $found = [];
    $text = $material;

    foreach ($allergies as $allergy) {
        if (mb_strpos($text, $allergy) !== false) {
            $found[] = $allergy;

            // 二重検出防止（削除）
            $text = str_replace($allergy, '', $text);
        }
    }

    return $found;
}



