<?php

use Carbon\Carbon;

function formatDate($date)
{
    return Carbon::parse($date)->format('d F Y');
}

function getLikelihood($criteriaId)
{
    $likelihoods = [
        0.0588, 0.0537, 0.0345,
        0.0183, 0.0375, 0.0264,
        0.0304, 0.0517, 0.0537,
        0.0724, 0.0443, 0.0302,
        0.0432, 0.0400, 0.0520,
        0.0609, 0.0321, 0.0608,
        0.0650, 0.0199, 0.0262,
        0.0629, 0.0251
    ];

    return $likelihoods[$criteriaId - 1];
}

function getNewLikelihood($criteriaId, $monthDifferences)
{
    return getLikelihood($criteriaId) * $monthDifferences;
}

function calculateConsequenceWorkingHours($criteriaId, $workingHours, $completionDelay)
{
    $result = (($completionDelay * $workingHours) / $workingHours) * getLikelihood($criteriaId);

    return $result;
}

function calculateConsequenceMonthDifferences($criteriaId, $monthDifferences, $completionDelay)
{
    $likelihood = getNewLikelihood($criteriaId, $monthDifferences);

    $result = (($completionDelay * $monthDifferences) / $monthDifferences) * $likelihood;

    return $result;
}

function calculateLikelihoodLevel($criteriaId, $monthDifferences)
{
    $result = getNewLikelihood($criteriaId, $monthDifferences);
    $likelihoodLevel = "";
    $colorCode = "";

    switch (true) {
        case $result >= 0 && $result < 1:
            $likelihoodLevel = 'Rare';
            $colorCode = '#99ff99';
            break;
        case $result >= 1 && $result < 5:
            $likelihoodLevel = 'Unlikely';
            $colorCode = '#99ff99';
            break;
        case $result >= 5 && $result < 25:
            $likelihoodLevel = 'Possible';
            $colorCode = '#ffff99';
            break;
        case $result >= 25 && $result < 60:
            $likelihoodLevel = 'Likely';
            $colorCode = '#ffcc99';
            break;
        case $result >= 60:
            $likelihoodLevel = 'Almost Certain';
            $colorCode = '#ff9999';
            break;
        default:
            $likelihoodLevel = "Tidak Diketahui";
            $colorCode = '#888';
    }

    return [
        'likelihoodLevel' => $likelihoodLevel,
        'colorCode' => $colorCode,
    ];
}


function calculateConsequenceLevel($completionDelay)
{
    $consequencesLevel = "";
    $colorCode = ""; // Initialize color code variable

    switch (true) {
        case $completionDelay < 10:
            $consequencesLevel = 'Insignificant';
            $colorCode = '#99ff99'; // Softer green
            break;
        case $completionDelay >= 10 && $completionDelay < 20:
            $consequencesLevel = 'Minor';
            $colorCode = '#ffff99'; // Softer yellow
            break;
        case $completionDelay >= 20 && $completionDelay < 50:
            $consequencesLevel = 'Significant';
            $colorCode = '#ffcc99'; // Softer orange
            break;
        case $completionDelay >= 50 && $completionDelay < 100:
            $consequencesLevel = 'Major';
            $colorCode = '#ff9999'; // Softer red
            break;
        case $completionDelay >= 100:
            $consequencesLevel = 'Catastrophic';
            $colorCode = '#ff6666'; // Softer deep red
            break;
        default:
            $consequencesLevel = 'Unknown';
            $colorCode = '#888'; // Default color
            break;
    }

    return [
        'consequencesLevel' => $consequencesLevel,
        'colorCode' => $colorCode,
    ];
}
