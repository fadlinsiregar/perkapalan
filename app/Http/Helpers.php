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

function calculateConsequencePerEventWorkingHours($criteriaId, $workingHours, $completionDelay)
{
    $result = (($completionDelay * $workingHours) / $workingHours) * getLikelihood($criteriaId);

    return $result;
}

function calculateConsequencePerEventMonthDifferences($criteriaId, $monthDifferences, $completionDelay)
{
    $likelihood = getNewLikelihood($criteriaId, $monthDifferences);

    $result = (($completionDelay * $monthDifferences) / $monthDifferences) * $likelihood;

    return $result;
}

function calculateLikelihoodLevel($criteriaId, $monthDifferences)
{
    $result = getNewLikelihood($criteriaId, $monthDifferences);
    $likelihoodLevel = "";

    switch (true) {
        case $result >= 0 && $result < 1:
            $likelihoodLevel = '<div style="color: #90EE90">Rare</div>';
            break;
        case $result >= 1 && $result < 5:
            $likelihoodLevel = '<div style="color: #198754">Unlikely</div>';
            break;
        case $result >= 5 && $result < 25:
            $likelihoodLevel = '<div style="color: #EED202">Possible</div>';
            break;
        case $result >= 25 && $result < 60:
            $likelihoodLevel = '<div style="color: #FFBF00">Likely</div>';
            break;
        case $result >= 60:
            $likelihoodLevel = '<div style="color: #991724">Almost Certain</div>';
            break;
        default:
            $likelihoodLevel = "Tidak Diketahui";
    }

    return $likelihoodLevel;
}

function calculateConsequenceLevel($completionDelay)
{
    $consequencesLevel = "";
    switch (true) {
        case $completionDelay < 10:
            $consequencesLevel = '<div style="color: #90EE90">Insignificant</div>';
            break;
        case $completionDelay >= 10 && $completionDelay < 20:
            $consequencesLevel = '<div style="color: #198754">Minor</div>';
            break;
        case $completionDelay >= 20 && $completionDelay < 50:
            $consequencesLevel = '<div style="color: #EED202">Significant</div>';
            break;
        case $completionDelay >= 50 && $completionDelay < 100:
            $consequencesLevel = '<div style="color: #FFBF00">Major</div>';
            break;
        case $completionDelay >= 100:
            $consequencesLevel = '<div style="color: #991724">Catastrophic</div>';
            break;
        default:
            $consequencesLevel = 'Unknown';
            break;
    }

    return $consequencesLevel;
}
