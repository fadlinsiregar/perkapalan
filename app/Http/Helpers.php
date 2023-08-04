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
            $likelihoodLevel = 'Rare';
            break;
        case $result >= 1 && $result < 5:
            $likelihoodLevel = 'Unlikely';
            break;
        case $result >= 5 && $result < 25:
            $likelihoodLevel = 'Possible';
            break;
        case $result >= 25 && $result < 60:
            $likelihoodLevel = 'Likely';
            break;
        case $result >= 60:
            $likelihoodLevel = 'Almost Certain';
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
            $consequencesLevel = 'Insignificant';
            break;
        case $completionDelay >= 10 && $completionDelay < 20:
            $consequencesLevel = 'Minor';
            break;
        case $completionDelay >= 20 && $completionDelay < 50:
            $consequencesLevel = 'Significant';
            break;
        case $completionDelay >= 50 && $completionDelay < 100:
            $consequencesLevel = 'Major';
            break;
        case $completionDelay >= 100:
            $consequencesLevel = 'Catastrophic';
            break;
        default:
            $consequencesLevel = 'Unknown';
            break;
    }

    return $consequencesLevel;
}

function checkRiskMatrix($likelihoodLevel, $consequencesLevel)
{
    $riskMatrix = [
        'Rare' => ['Insignificant' => 'bg-success', 'Minor' => 'bg-success', 'Significant' => 'bg-warning', 'Major' => 'bg-warning', 'Catastrophic' => 'bg-danger'],
        'Unlikely' => ['Insignificant' => 'bg-success', 'Minor' => 'bg-warning', 'Significant' => 'bg-warning', 'Major' => 'bg-danger', 'Catastrophic' => 'bg-danger'],
        'Possible' => ['Insignificant' => 'bg-warning', 'Minor' => 'bg-warning', 'Significant' => 'bg-danger', 'Major' => 'bg-danger', 'Catastrophic' => 'bg-danger'],
        'Likely' => ['Insignificant' => 'bg-warning', 'Minor' => 'bg-danger', 'Significant' => 'bg-danger', 'Major' => 'bg-danger', 'Catastrophic' => 'bg-danger'],
        'Almost Certain' => ['Insignificant' => 'bg-danger', 'Minor' => 'bg-danger', 'Significant' => 'bg-danger', 'Major' => 'bg-danger', 'Catastrophic' => 'bg-danger'],
    ];

    return isset($riskMatrix[$likelihoodLevel][$consequencesLevel]) ? $riskMatrix[$likelihoodLevel][$consequencesLevel] : 'bg-secondary';
}

function checkRiskMatrixLabel($likelihoodLevel, $consequencesLevel)
{
    $riskMatrix = [
        'Rare' => ['Insignificant' => 'Low', 'Minor' => 'Low', 'Significant' => 'Medium', 'Major' => 'Medium', 'Catastrophic' => 'High', ],
        'Unlikely' => ['Insignificant' => 'Low', 'Minor' => 'Medium', 'Significant' => 'Medium', 'Major' => 'High', 'Catastrophic' => 'High', ],
        'Possible' => ['Insignificant' => 'Low', 'Minor' => 'Medium', 'Significant' => 'High', 'Major' => 'High', 'Catastrophic' => 'Extreme', ],
        'Likely' => ['Insignificant' => 'Medium', 'Minor' => 'Medium', 'Significant' => 'High', 'Major' => 'Extreme', 'Catastrophic' => 'Extreme', ],
        'Almost Certain' => ['Insignificant' => 'Medium', 'Minor' => 'High', 'Significant' => 'High', 'Major' => 'Extreme', 'Catastrophic' => 'Extreme', ]
    ];
    

    return isset($riskMatrix[$likelihoodLevel][$consequencesLevel]) ? $riskMatrix[$likelihoodLevel][$consequencesLevel] : 'Unknown';
}