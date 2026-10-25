<?php

/*
| UCE "Result" on Secondary pass slips / report cards (UNEB new lower-secondary curriculum).
|
|   Result 1  the candidate qualifies for the certificate: at least a D in one or more
|             subjects, and the entry requirements below are met.
|   Result 2  entry requirements NOT met: a compulsory subject was not sat, or fewer than
|             the minimum number of subjects were sat.
|   Result 3  the candidate got grade E in EVERY subject sat.
|
| The label printed for each outcome is set here. To print a different wording or
| swap the numbers, change only the values on the right.
*/
return [
    'labels' => [
        'qualified' => 'Result 1',
        'incomplete' => 'Result 2',
        'all_e' => 'Result 3',
    ],

    // Entry requirements are only enforced for the UCE class (Senior 4).
    'min_subjects' => 8,

    // Compulsory = every O-Level master subject NOT tagged "Elective" in
    // Master Data -> Secondary O-Level Subjects, that the class-stream offers.
    'check_compulsory' => true,
];
