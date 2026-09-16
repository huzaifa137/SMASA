<?php

namespace Database\Seeders;

use App\Http\Controllers\Helper;
use App\Models\NlscProject;
use App\Models\NlscProjectArea;
use App\Models\NlscProjectCompetencyArea;
use App\Services\NlscSyncService;
use Illuminate\Database\Seeder;

/**
 * php artisan db:seed --class=NlscProjectSeeder
 *
 * Seeds the platform-wide NCDC "Project Work" (Projects) catalogue
 * (Senior 1-4) into the admin side, the same starter set every school
 * automatically pulls in the first time it visits the "Projects" screen
 * for a given Senior/Subject (SchoolNlscProjectController's equivalent
 * of SchoolNlscTopicController::cloneFromAdminIfNeeded()). Nothing
 * needs to be pushed to schools manually here for the Project Areas or
 * Projects themselves — a school not yet synced in picks them up the
 * normal lazy-pull way on its next visit. An already-synced school
 * DOES need each Competency Area pushed to it directly though, since
 * the sync log only tracks whether a school has seen the PROJECT
 * before, not whether a competency area was added to it since — so
 * this calls the same NlscSyncService::propagateNewProjectCompetencyArea()
 * the admin "Add" button itself uses, for every one it creates.
 *
 * Deliberately excludes the source material's 22 "Cross-Curricular"
 * projects (e.g. "School Garden Project" spanning Biology + Geography +
 * Chemistry + Entrepreneurship + Mathematics at once) — nlsc_project_areas
 * requires exactly one senior_class_id + subject_id per area, so a
 * project spanning several subjects has nowhere single to attach to.
 * Left out entirely rather than force-fit into one arbitrary subject.
 *
 * Senior/Subject names are matched case-insensitively against the
 * SECONDARY_OLEVEL_CLASSES / NLSC_SUBJECTS master-data groups, the same
 * lookup NlscTopicBulkImport (and the Subject Achievement / Competency
 * Area seeders) use — with a short alias list for names the catalogue
 * spells differently than master-data commonly does. Any Senior/Subject
 * that can't be resolved is skipped with a warning rather than failing
 * the whole run, since master-data naming can vary between installs.
 *
 * Safe to re-run: an existing Project Area/Project is reused (never
 * duplicated — same firstOrCreate-by-name logic as
 * NlscProjectController::store()), and a Competency Area statement
 * already present with the exact same wording is left alone rather than
 * added again.
 */
class NlscProjectSeeder extends Seeder
{
    /**
     * Extra name(s) to also try, in order, when the catalogue's subject
     * name doesn't match any master-data NLSC_SUBJECTS row exactly.
     * Keyed by the catalogue's own subject name (as spelled in the
     * catalogue below).
     */
    private const SUBJECT_ALIASES = [
        'English Language' => ['English'],
        'History & Political Education' => ['History', 'History and Political Education', 'History & Political Education'],
        'Physical Education' => ['PE'],
    ];

    public function run(): void
    {
        $seniorLookup = Helper::MasterRecords(config('constants.options.SECONDARY_OLEVEL_CLASSES'))
            ->mapWithKeys(fn($m) => [strtolower(trim($m->md_name)) => $m->md_id]);

        $subjectLookup = Helper::MasterRecords(config('constants.options.NLSC_SUBJECTS'))
            ->mapWithKeys(fn($m) => [strtolower(trim($m->md_name)) => $m->md_id]);

        $areasCreated = 0;
        $areasReused = 0;
        $projectsCreated = 0;
        $projectsReused = 0;
        $competencyAreasCreated = 0;
        $competencyAreasSkipped = 0;
        $subjectsSkipped = [];

        foreach ($this->catalogue() as $block) {
            $seniorId = $seniorLookup[strtolower("senior {$block['senior']}")] ?? null;

            $subjectId = $this->resolveSubjectId($block['subject'], $subjectLookup);

            if (!$seniorId || !$subjectId) {
                $subjectsSkipped[] = "Senior {$block['senior']} — {$block['subject']}";
                continue;
            }

            foreach ($block['projects'] as $row) {
                $area = NlscProjectArea::where('senior_class_id', $seniorId)
                    ->where('subject_id', $subjectId)
                    ->where('area_name', $row['area'])
                    ->first();

                if ($area) {
                    $areasReused++;
                } else {
                    $nextAreaOrder = 1 + (int) NlscProjectArea::where('senior_class_id', $seniorId)
                        ->where('subject_id', $subjectId)
                        ->max('sort_order');

                    $area = NlscProjectArea::create([
                        'senior_class_id' => $seniorId,
                        'subject_id' => $subjectId,
                        'area_name' => $row['area'],
                        'sort_order' => $nextAreaOrder,
                    ]);
                    $areasCreated++;
                }

                $project = NlscProject::where('nlsc_project_area_id', $area->id)
                    ->where('project_name', $row['project'])
                    ->first();

                if ($project) {
                    $projectsReused++;
                } else {
                    $nextProjectOrder = 1 + (int) NlscProject::where('nlsc_project_area_id', $area->id)->max('sort_order');

                    $project = NlscProject::create([
                        'nlsc_project_area_id' => $area->id,
                        'project_name' => $row['project'],
                        'description' => $row['description'],
                        'sort_order' => $nextProjectOrder,
                    ]);
                    $projectsCreated++;
                }

                foreach ($row['areas'] as $description) {
                    $alreadyHasIt = NlscProjectCompetencyArea::where('nlsc_project_id', $project->id)
                        ->where('description', $description)
                        ->exists();

                    if ($alreadyHasIt) {
                        $competencyAreasSkipped++;
                        continue;
                    }

                    $nextCaOrder = 1 + (int) NlscProjectCompetencyArea::where('nlsc_project_id', $project->id)
                        ->max('sort_order');

                    $competencyArea = NlscProjectCompetencyArea::create([
                        'nlsc_project_id' => $project->id,
                        'description' => $description,
                        'sort_order' => $nextCaOrder,
                    ]);

                    NlscSyncService::propagateNewProjectCompetencyArea($project, $competencyArea);
                    $competencyAreasCreated++;
                }
            }
        }

        if ($this->command) {
            $this->command->info("Project Areas created: {$areasCreated}, reused: {$areasReused}");
            $this->command->info("Projects created: {$projectsCreated}, reused: {$projectsReused}");
            $this->command->info("Competency areas created: {$competencyAreasCreated}, already present: {$competencyAreasSkipped}");
            if ($subjectsSkipped) {
                $this->command->warn('Skipped (Senior/Subject not found in master data): ' . implode('; ', $subjectsSkipped));
                $this->command->warn('Add these to SECONDARY_OLEVEL_CLASSES / NLSC_SUBJECTS master data (or to SUBJECT_ALIASES above) and re-run — already-seeded rows are left untouched.');
            }
        }
    }

    /**
     * Tries the catalogue's own subject name first, then each alias in
     * SUBJECT_ALIASES, then finally a loose "one name contains the
     * other" match against every NLSC_SUBJECTS row.
     */
    private function resolveSubjectId(string $subjectName, $subjectLookup): ?int
    {
        $key = strtolower(trim($subjectName));
        if (isset($subjectLookup[$key])) {
            return $subjectLookup[$key];
        }

        foreach (self::SUBJECT_ALIASES[$subjectName] ?? [] as $alias) {
            $aliasKey = strtolower(trim($alias));
            if (isset($subjectLookup[$aliasKey])) {
                return $subjectLookup[$aliasKey];
            }
        }

        $normalized = fn($s) => preg_replace('/[^a-z0-9]/', '', strtolower($s));
        $needle = $normalized($subjectName);
        foreach ($subjectLookup as $name => $id) {
            $hay = $normalized($name);
            if ($hay !== '' && (str_contains($needle, $hay) || str_contains($hay, $needle))) {
                return $id;
            }
        }

        return null;
    }

    /**
     * The NCDC Project Work catalogue, Senior 1-4, single-subject blocks
     * only (subject -> Project Area -> Project + description -> its
     * Competency Area statements). The source material's 22
     * "Cross-Curricular" projects (spanning 4-6 subjects each, no single
     * senior_class_id/subject_id to attach to) are deliberately left out
     * — nlsc_project_areas requires exactly one Senior+Subject per area,
     * so those don't fit this schema and were skipped per a deliberate
     * decision, not an oversight.
     */
    private function catalogue(): array
    {
        return [
            [
                'senior' => 1,
                'subject' => 'English Language',
                'projects' => [
                    [
                        'area' => 'Communication',
                        'project' => 'School Communication Campaign',
                        'description' => 'Learners design a communication campaign addressing an issue affecting learners in their school.',
                        'areas' => [
                            'Identifying a communication problem',
                            'Gathering relevant information',
                            'Conducting interviews and discussions',
                            'Organising ideas logically',
                            'Writing appropriate messages',
                            'Designing posters/notices',
                            'Presenting information to an audience',
                            'Using appropriate language for different audiences',
                        ],
                    ],
                    [
                        'area' => 'Reading and Information',
                        'project' => 'School Reading Promotion Project',
                        'description' => 'Learners investigate reading habits in their school and develop ways of encouraging reading.',
                        'areas' => [
                            'Designing simple questionnaires',
                            'Collecting information',
                            'Summarising findings',
                            'Interpreting responses',
                            'Writing recommendations',
                            'Presenting findings',
                            'Promoting reading culture',
                        ],
                    ],
                    [
                        'area' => 'Oral Communication',
                        'project' => 'Community Interview Project',
                        'description' => 'Learners interview community members about an issue, occupation, tradition or experience and produce a report.',
                        'areas' => [
                            'Preparing interview questions',
                            'Conducting interviews',
                            'Listening actively',
                            'Recording information',
                            'Summarising oral information',
                            'Report writing',
                            'Oral presentation',
                        ],
                    ],
                ],
            ],
            [
                'senior' => 1,
                'subject' => 'Mathematics',
                'projects' => [
                    [
                        'area' => 'Mathematics in Daily Life',
                        'project' => 'Household Budget Project',
                        'description' => 'Learners prepare and analyse a realistic household budget.',
                        'areas' => [
                            'Collecting financial information',
                            'Performing calculations',
                            'Working with percentages',
                            'Comparing expenditure',
                            'Preparing tables',
                            'Representing information graphically',
                            'Drawing conclusions from numerical data',
                        ],
                    ],
                    [
                        'area' => 'Measurement',
                        'project' => 'Measuring Our School',
                        'description' => 'Learners measure selected parts of the school environment and use the measurements to calculate quantities.',
                        'areas' => [
                            'Measuring length and distance',
                            'Selecting appropriate units',
                            'Recording measurements',
                            'Calculating perimeter',
                            'Calculating area',
                            'Estimating quantities',
                            'Presenting measurements systematically',
                        ],
                    ],
                    [
                        'area' => 'Data Handling',
                        'project' => 'School Survey Project',
                        'description' => 'Learners conduct a survey on a selected school issue.',
                        'areas' => [
                            'Designing questions',
                            'Collecting data',
                            'Organising data',
                            'Constructing tables',
                            'Drawing graphs/charts',
                            'Interpreting data',
                            'Communicating conclusions',
                        ],
                    ],
                ],
            ],
            [
                'senior' => 1,
                'subject' => 'History & Political Education',
                'projects' => [
                    [
                        'area' => 'Our Community\'s History',
                        'project' => 'Local History Documentation Project',
                        'description' => 'Learners investigate the history of their community.',
                        'areas' => [
                            'Identifying historical sources',
                            'Conducting oral interviews',
                            'Collecting historical information',
                            'Comparing accounts',
                            'Recording historical evidence',
                            'Constructing a simple timeline',
                            'Preserving local history',
                        ],
                    ],
                    [
                        'area' => 'Cultural Heritage',
                        'project' => 'Local Culture Documentation Project',
                        'description' => 'Learners document selected cultural practices, traditions or institutions.',
                        'areas' => [
                            'Identifying cultural practices',
                            'Conducting research',
                            'Interviewing community members',
                            'Documenting cultural information',
                            'Appreciating cultural diversity',
                            'Presenting historical/cultural evidence',
                        ],
                    ],
                    [
                        'area' => 'Citizenship',
                        'project' => 'Responsible Citizenship Campaign',
                        'description' => 'Learners identify a citizenship issue affecting their school or community and develop an awareness campaign.',
                        'areas' => [
                            'Identifying community issues',
                            'Understanding civic responsibility',
                            'Researching relevant information',
                            'Working collaboratively',
                            'Developing campaign materials',
                            'Communicating civic messages',
                        ],
                    ],
                ],
            ],
            [
                'senior' => 1,
                'subject' => 'Geography',
                'projects' => [
                    [
                        'area' => 'The Local Environment',
                        'project' => 'School Environmental Survey',
                        'description' => 'Learners investigate the physical and human features of their immediate environment.',
                        'areas' => [
                            'Observing geographical features',
                            'Field data collection',
                            'Recording observations',
                            'Classifying physical and human features',
                            'Sketch mapping',
                            'Presenting geographical information',
                        ],
                    ],
                    [
                        'area' => 'Environmental Management',
                        'project' => 'School Waste Management Project',
                        'description' => 'Learners investigate waste generation and management within the school.',
                        'areas' => [
                            'Identifying environmental problems',
                            'Conducting field observations',
                            'Categorising waste',
                            'Analysing causes and effects',
                            'Proposing solutions',
                            'Promoting environmental responsibility',
                        ],
                    ],
                    [
                        'area' => 'Mapping',
                        'project' => 'School Sketch Map Project',
                        'description' => 'Learners produce a detailed sketch map of their school.',
                        'areas' => [
                            'Field observation',
                            'Measuring distances',
                            'Using symbols',
                            'Applying direction',
                            'Estimating scale',
                            'Drawing maps',
                            'Interpreting spatial information',
                        ],
                    ],
                ],
            ],
            [
                'senior' => 1,
                'subject' => 'Physics',
                'projects' => [
                    [
                        'area' => 'Measurement',
                        'project' => 'Measurement Around Us',
                        'description' => 'Learners investigate how measurements are used in everyday activities.',
                        'areas' => [
                            'Identifying measurable quantities',
                            'Selecting measuring instruments',
                            'Taking measurements',
                            'Recording measurements',
                            'Comparing measurements',
                            'Applying measurement to real-life situations',
                        ],
                    ],
                    [
                        'area' => 'Simple Machines',
                        'project' => 'Simple Machines Around the School',
                        'description' => 'Learners identify and investigate simple machines used in their surroundings.',
                        'areas' => [
                            'Identifying simple machines',
                            'Investigating their uses',
                            'Explaining mechanical advantage in simple terms',
                            'Collecting observations',
                            'Demonstrating applications',
                            'Designing simple solutions',
                        ],
                    ],
                    [
                        'area' => 'Energy',
                        'project' => 'Energy Use Investigation',
                        'description' => 'Learners investigate energy use within their school or homes.',
                        'areas' => [
                            'Identifying energy sources',
                            'Recording energy uses',
                            'Comparing energy sources',
                            'Investigating energy-saving practices',
                            'Proposing efficient energy-use strategies',
                        ],
                    ],
                ],
            ],
            [
                'senior' => 1,
                'subject' => 'Biology',
                'projects' => [
                    [
                        'area' => 'Living Things',
                        'project' => 'Biodiversity Survey',
                        'description' => 'Learners conduct a survey of living organisms around the school.',
                        'areas' => [
                            'Observing organisms',
                            'Identifying organisms',
                            'Grouping organisms',
                            'Recording observations',
                            'Appreciating biodiversity',
                            'Presenting biological information',
                        ],
                    ],
                    [
                        'area' => 'Plants',
                        'project' => 'School Plant Survey',
                        'description' => 'Learners investigate plants growing around the school.',
                        'areas' => [
                            'Plant identification',
                            'Observation',
                            'Classification',
                            'Recording data',
                            'Investigating plant uses',
                            'Environmental conservation',
                        ],
                    ],
                    [
                        'area' => 'Health',
                        'project' => 'School Health Awareness Project',
                        'description' => 'Learners investigate a selected health issue affecting their school community.',
                        'areas' => [
                            'Identifying health challenges',
                            'Gathering information',
                            'Analysing causes',
                            'Developing awareness materials',
                            'Communicating health information',
                            'Promoting healthy practices',
                        ],
                    ],
                ],
            ],
            [
                'senior' => 1,
                'subject' => 'Chemistry',
                'projects' => [
                    [
                        'area' => 'Chemistry in Daily Life',
                        'project' => 'Household Substances Investigation',
                        'description' => 'Learners identify and investigate commonly used substances in their homes or school.',
                        'areas' => [
                            'Identifying substances',
                            'Observing physical properties',
                            'Classifying substances',
                            'Recording observations',
                            'Applying safety precautions',
                            'Relating chemistry to daily life',
                        ],
                    ],
                    [
                        'area' => 'Materials',
                        'project' => 'Materials Around the School',
                        'description' => 'Learners investigate materials used to construct or furnish the school.',
                        'areas' => [
                            'Identifying materials',
                            'Classifying materials',
                            'Investigating properties',
                            'Relating properties to uses',
                            'Selecting appropriate materials',
                        ],
                    ],
                    [
                        'area' => 'Laboratory Safety',
                        'project' => 'School Laboratory Safety Project',
                        'description' => 'Learners investigate laboratory safety practices and produce a safety-awareness resource.',
                        'areas' => [
                            'Identifying hazards',
                            'Recognising safety symbols',
                            'Understanding safe handling',
                            'Developing safety guidelines',
                            'Communicating safety information',
                        ],
                    ],
                ],
            ],
            [
                'senior' => 1,
                'subject' => 'Physical Education',
                'projects' => [
                    [
                        'area' => 'Fitness',
                        'project' => 'Personal Fitness Programme',
                        'description' => 'Learners investigate their physical fitness and develop a simple fitness programme.',
                        'areas' => [
                            'Identifying components of fitness',
                            'Assessing personal fitness',
                            'Setting realistic fitness goals',
                            'Planning physical activities',
                            'Monitoring progress',
                            'Maintaining healthy habits',
                        ],
                    ],
                    [
                        'area' => 'Games and Sports',
                        'project' => 'School Games Organisation Project',
                        'description' => 'Learners plan and organise a small sporting activity.',
                        'areas' => [
                            'Planning activities',
                            'Establishing rules',
                            'Teamwork',
                            'Leadership',
                            'Time management',
                            'Fair play',
                            'Evaluation of activities',
                        ],
                    ],
                    [
                        'area' => 'Health and Physical Activity',
                        'project' => 'Active Lifestyle Campaign',
                        'description' => 'Learners investigate physical inactivity among young people and develop an awareness campaign.',
                        'areas' => [
                            'Research',
                            'Health communication',
                            'Teamwork',
                            'Campaign planning',
                            'Presentation',
                            'Promotion of active lifestyles',
                        ],
                    ],
                ],
            ],
            [
                'senior' => 1,
                'subject' => 'Religious Education',
                'projects' => [
                    [
                        'area' => 'Values',
                        'project' => 'Values in Our School Community',
                        'description' => 'Learners investigate how selected religious and moral values are practised within their school.',
                        'areas' => [
                            'Identifying values',
                            'Gathering evidence',
                            'Interviewing',
                            'Reflecting on behaviour',
                            'Connecting values with daily life',
                            'Promoting positive behaviour',
                        ],
                    ],
                    [
                        'area' => 'Community Service',
                        'project' => 'Community Service Project',
                        'description' => 'Learners identify a simple community need and participate in an appropriate service activity.',
                        'areas' => [
                            'Identifying community needs',
                            'Planning service activities',
                            'Cooperation',
                            'Responsibility',
                            'Empathy',
                            'Service to others',
                            'Reflection on experience',
                        ],
                    ],
                    [
                        'area' => 'Religious Heritage',
                        'project' => 'Religious Heritage Documentation',
                        'description' => 'Learners document selected religious practices, institutions or historical sites.',
                        'areas' => [
                            'Research',
                            'Interviewing',
                            'Documentation',
                            'Respect for religious diversity',
                            'Historical awareness',
                            'Presentation',
                        ],
                    ],
                ],
            ],
            [
                'senior' => 1,
                'subject' => 'Entrepreneurship',
                'projects' => [
                    [
                        'area' => 'Business Ideas',
                        'project' => 'Identify a School Business Opportunity',
                        'description' => 'Learners identify a need within the school that could become a small business opportunity.',
                        'areas' => [
                            'Identifying needs',
                            'Generating business ideas',
                            'Market observation',
                            'Problem-solving',
                            'Creativity',
                            'Opportunity identification',
                        ],
                    ],
                    [
                        'area' => 'Product Development',
                        'project' => 'Develop a Simple Product',
                        'description' => 'Learners design and develop a simple product responding to a local need.',
                        'areas' => [
                            'Product design',
                            'Resource identification',
                            'Planning',
                            'Production',
                            'Quality control',
                            'Creativity',
                            'Innovation',
                        ],
                    ],
                    [
                        'area' => 'Small Business',
                        'project' => 'School Enterprise Simulation',
                        'description' => 'Learners plan and operate a simple simulated enterprise.',
                        'areas' => [
                            'Business planning',
                            'Cost calculation',
                            'Pricing',
                            'Record keeping',
                            'Marketing',
                            'Teamwork',
                            'Decision-making',
                            'Evaluation',
                        ],
                    ],
                ],
            ],
            [
                'senior' => 1,
                'subject' => 'Kiswahili',
                'projects' => [
                    [
                        'area' => 'Oral Communication',
                        'project' => 'Kiswahili Community Interview',
                        'description' => 'Learners conduct interviews with selected members of their community and document the information in Kiswahili.',
                        'areas' => [
                            'Preparing questions',
                            'Listening',
                            'Speaking',
                            'Interviewing',
                            'Recording information',
                            'Reporting',
                        ],
                    ],
                    [
                        'area' => 'Culture and Language',
                        'project' => 'Kiswahili Cultural Documentation Project',
                        'description' => 'Learners document selected cultural practices, stories, sayings or traditions using Kiswahili.',
                        'areas' => [
                            'Research',
                            'Oral communication',
                            'Reading',
                            'Writing',
                            'Cultural appreciation',
                            'Documentation',
                        ],
                    ],
                    [
                        'area' => 'Written Communication',
                        'project' => 'Kiswahili School Newsletter',
                        'description' => 'Learners produce a simple Kiswahili newsletter containing school or community information.',
                        'areas' => [
                            'Information gathering',
                            'Writing',
                            'Editing',
                            'Organisation of information',
                            'Communication',
                            'Teamwork',
                            'Publication',
                        ],
                    ],
                ],
            ],
            [
                'senior' => 2,
                'subject' => 'English Language',
                'projects' => [
                    [
                        'area' => 'Research and Communication',
                        'project' => 'Community Issue Investigation',
                        'description' => 'Learners investigate a selected issue affecting their school or community and communicate their findings.',
                        'areas' => [
                            'Identifying a researchable issue',
                            'Developing research questions',
                            'Gathering information',
                            'Conducting interviews',
                            'Taking notes',
                            'Organising information',
                            'Writing a structured report',
                            'Presenting findings',
                        ],
                    ],
                    [
                        'area' => 'Media and Communication',
                        'project' => 'School Media Project',
                        'description' => 'Learners develop a simple school news bulletin, newsletter, podcast script or information campaign.',
                        'areas' => [
                            'Gathering information',
                            'Identifying audiences',
                            'Writing for different purposes',
                            'Editing',
                            'Interviewing',
                            'Organising content',
                            'Team communication',
                            'Presenting information',
                        ],
                    ],
                    [
                        'area' => 'Literature and Society',
                        'project' => 'Community Stories Project',
                        'description' => 'Learners collect and document stories, narratives, sayings or experiences from members of their community.',
                        'areas' => [
                            'Oral communication',
                            'Active listening',
                            'Interviewing',
                            'Story documentation',
                            'Interpretation',
                            'Creative writing',
                            'Cultural appreciation',
                            'Presentation',
                        ],
                    ],
                ],
            ],
            [
                'senior' => 2,
                'subject' => 'Mathematics',
                'projects' => [
                    [
                        'area' => 'Financial Mathematics',
                        'project' => 'Personal/Family Budget Investigation',
                        'description' => 'Learners develop, analyse and evaluate a realistic budget.',
                        'areas' => [
                            'Data collection',
                            'Addition and subtraction',
                            'Percentages',
                            'Ratios',
                            'Budget preparation',
                            'Financial comparison',
                            'Data interpretation',
                            'Decision-making',
                        ],
                    ],
                    [
                        'area' => 'Statistics',
                        'project' => 'School Statistics Survey',
                        'description' => 'Learners investigate a measurable issue affecting learners.',
                        'areas' => [
                            'Designing survey questions',
                            'Sampling',
                            'Data collection',
                            'Frequency tables',
                            'Graphical representation',
                            'Data interpretation',
                            'Drawing conclusions',
                            'Presenting findings',
                        ],
                    ],
                    [
                        'area' => 'Geometry and Measurement',
                        'project' => 'School Infrastructure Measurement Project',
                        'description' => 'Learners measure selected school facilities and calculate relevant dimensions.',
                        'areas' => [
                            'Measurement',
                            'Area',
                            'Perimeter',
                            'Volume',
                            'Estimation',
                            'Scale',
                            'Mathematical modelling',
                            'Applying mathematics to real situations',
                        ],
                    ],
                ],
            ],
            [
                'senior' => 2,
                'subject' => 'History & Political Education',
                'projects' => [
                    [
                        'area' => 'Historical Investigation',
                        'project' => 'Local Historical Research Project',
                        'description' => 'Learners investigate an important historical development in their community.',
                        'areas' => [
                            'Identifying historical questions',
                            'Locating sources',
                            'Oral history',
                            'Comparing sources',
                            'Recording evidence',
                            'Chronological organisation',
                            'Historical interpretation',
                            'Report writing',
                        ],
                    ],
                    [
                        'area' => 'Heritage',
                        'project' => 'Cultural Heritage Preservation Project',
                        'description' => 'Learners document an aspect of local cultural heritage that is at risk of being forgotten.',
                        'areas' => [
                            'Historical research',
                            'Documentation',
                            'Interviewing',
                            'Evidence collection',
                            'Cultural appreciation',
                            'Preservation',
                            'Presentation',
                        ],
                    ],
                    [
                        'area' => 'Governance',
                        'project' => 'School Governance Investigation',
                        'description' => 'Learners investigate how decision-making and leadership operate within their school.',
                        'areas' => [
                            'Understanding leadership structures',
                            'Gathering information',
                            'Interviewing leaders',
                            'Analysing responsibilities',
                            'Identifying challenges',
                            'Civic participation',
                            'Communication',
                        ],
                    ],
                ],
            ],
            [
                'senior' => 2,
                'subject' => 'Geography',
                'projects' => [
                    [
                        'area' => 'Population',
                        'project' => 'School Population Survey',
                        'description' => 'Learners collect and analyse demographic information within their school.',
                        'areas' => [
                            'Data collection',
                            'Classification',
                            'Tabulation',
                            'Statistical representation',
                            'Population analysis',
                            'Interpretation',
                            'Report writing',
                        ],
                    ],
                    [
                        'area' => 'Settlement',
                        'project' => 'Settlement and Land-Use Survey',
                        'description' => 'Learners investigate settlement patterns and land use in their local area.',
                        'areas' => [
                            'Field observation',
                            'Land-use classification',
                            'Mapping',
                            'Data collection',
                            'Environmental analysis',
                            'Identifying human activities',
                            'Drawing conclusions',
                        ],
                    ],
                    [
                        'area' => 'Environmental Management',
                        'project' => 'Local Environmental Problem Investigation',
                        'description' => 'Learners investigate an environmental problem and propose practical solutions.',
                        'areas' => [
                            'Problem identification',
                            'Field investigation',
                            'Evidence collection',
                            'Cause-and-effect analysis',
                            'Solution development',
                            'Environmental responsibility',
                            'Community awareness',
                        ],
                    ],
                ],
            ],
            [
                'senior' => 2,
                'subject' => 'Physics',
                'projects' => [
                    [
                        'area' => 'Forces and Motion',
                        'project' => 'Motion Around Us Investigation',
                        'description' => 'Learners investigate examples of motion in their school or community.',
                        'areas' => [
                            'Observation',
                            'Measurement',
                            'Data recording',
                            'Identifying forces',
                            'Analysing motion',
                            'Graphical representation',
                            'Drawing conclusions',
                        ],
                    ],
                    [
                        'area' => 'Energy',
                        'project' => 'Energy Efficiency Project',
                        'description' => 'Learners investigate energy consumption within the school and recommend ways of reducing wastage.',
                        'areas' => [
                            'Identifying energy sources',
                            'Measuring/estimating energy use',
                            'Data analysis',
                            'Identifying wastage',
                            'Developing solutions',
                            'Energy conservation',
                            'Communication',
                        ],
                    ],
                    [
                        'area' => 'Machines',
                        'project' => 'Simple Machine Design Project',
                        'description' => 'Learners design or construct a simple device that makes a task easier.',
                        'areas' => [
                            'Identifying a practical problem',
                            'Applying scientific principles',
                            'Designing solutions',
                            'Selecting materials',
                            'Construction',
                            'Testing',
                            'Modification',
                            'Evaluation',
                        ],
                    ],
                ],
            ],
            [
                'senior' => 2,
                'subject' => 'Biology',
                'projects' => [
                    [
                        'area' => 'Ecosystems',
                        'project' => 'School Ecosystem Investigation',
                        'description' => 'Learners investigate relationships between organisms and their environment.',
                        'areas' => [
                            'Field observation',
                            'Identification of organisms',
                            'Classification',
                            'Food relationships',
                            'Environmental analysis',
                            'Data recording',
                            'Ecological interpretation',
                        ],
                    ],
                    [
                        'area' => 'Agriculture',
                        'project' => 'School Crop Production Project',
                        'description' => 'Learners establish or investigate a small crop-production activity.',
                        'areas' => [
                            'Crop selection',
                            'Site preparation',
                            'Planting',
                            'Observation',
                            'Growth monitoring',
                            'Data recording',
                            'Pest/disease identification',
                            'Sustainable agriculture',
                        ],
                    ],
                    [
                        'area' => 'Human Health',
                        'project' => 'School Health Investigation',
                        'description' => 'Learners investigate a health challenge affecting their school community.',
                        'areas' => [
                            'Research',
                            'Data collection',
                            'Identifying causes',
                            'Analysing risk factors',
                            'Developing interventions',
                            'Health communication',
                            'Evaluation',
                        ],
                    ],
                ],
            ],
            [
                'senior' => 2,
                'subject' => 'Chemistry',
                'projects' => [
                    [
                        'area' => 'Chemistry in the Home',
                        'project' => 'Household Chemicals Investigation',
                        'description' => 'Learners investigate commonly used chemical substances and their safe applications.',
                        'areas' => [
                            'Identifying substances',
                            'Investigating properties',
                            'Classification',
                            'Safe handling',
                            'Reading labels',
                            'Identifying hazards',
                            'Communicating safety information',
                        ],
                    ],
                    [
                        'area' => 'Separation and Materials',
                        'project' => 'Water Treatment Investigation',
                        'description' => 'Learners investigate practical methods used to obtain cleaner water.',
                        'areas' => [
                            'Identifying impurities',
                            'Observation',
                            'Filtration',
                            'Separation methods',
                            'Comparing methods',
                            'Applying scientific knowledge',
                            'Evaluating results',
                        ],
                    ],
                    [
                        'area' => 'Environmental Chemistry',
                        'project' => 'Pollution Investigation',
                        'description' => 'Learners investigate a form of pollution affecting their community.',
                        'areas' => [
                            'Identifying pollutants',
                            'Gathering evidence',
                            'Classification',
                            'Investigating causes',
                            'Investigating effects',
                            'Proposing solutions',
                            'Environmental responsibility',
                        ],
                    ],
                ],
            ],
            [
                'senior' => 2,
                'subject' => 'Physical Education',
                'projects' => [
                    [
                        'area' => 'Fitness Assessment',
                        'project' => 'School Fitness Survey',
                        'description' => 'Learners conduct a simple fitness assessment among willing participants and analyse the results.',
                        'areas' => [
                            'Planning',
                            'Fitness measurement',
                            'Data recording',
                            'Statistical analysis',
                            'Interpretation',
                            'Fitness planning',
                            'Health awareness',
                        ],
                    ],
                    [
                        'area' => 'Sports Organisation',
                        'project' => 'Inter-Class Sports Event',
                        'description' => 'Learners plan and organise a sporting event.',
                        'areas' => [
                            'Event planning',
                            'Team organisation',
                            'Scheduling',
                            'Rules and officiating',
                            'Leadership',
                            'Communication',
                            'Fair play',
                            'Evaluation',
                        ],
                    ],
                    [
                        'area' => 'Health and Lifestyle',
                        'project' => 'Healthy Lifestyle Campaign',
                        'description' => 'Learners investigate lifestyle factors affecting adolescent health and develop an awareness campaign.',
                        'areas' => [
                            'Research',
                            'Information analysis',
                            'Communication',
                            'Campaign design',
                            'Teamwork',
                            'Health promotion',
                        ],
                    ],
                ],
            ],
            [
                'senior' => 2,
                'subject' => 'Religious Education',
                'projects' => [
                    [
                        'area' => 'Moral Values',
                        'project' => 'Values and Behaviour Investigation',
                        'description' => 'Learners investigate how selected moral/religious values influence behaviour in school or community life.',
                        'areas' => [
                            'Identifying values',
                            'Research',
                            'Interviewing',
                            'Observation',
                            'Analysis',
                            'Reflection',
                            'Communication',
                        ],
                    ],
                    [
                        'area' => 'Community Service',
                        'project' => 'Community Service Initiative',
                        'description' => 'Learners identify a manageable community need and organise a service activity.',
                        'areas' => [
                            'Needs assessment',
                            'Planning',
                            'Resource mobilisation',
                            'Teamwork',
                            'Leadership',
                            'Service',
                            'Reflection',
                            'Evaluation',
                        ],
                    ],
                    [
                        'area' => 'Religious Heritage',
                        'project' => 'Religious Heritage Documentation',
                        'description' => 'Learners research and document a significant religious institution, practice, person or historical development.',
                        'areas' => [
                            'Research',
                            'Source evaluation',
                            'Interviewing',
                            'Documentation',
                            'Historical interpretation',
                            'Respect for diversity',
                            'Presentation',
                        ],
                    ],
                ],
            ],
            [
                'senior' => 2,
                'subject' => 'Entrepreneurship',
                'projects' => [
                    [
                        'area' => 'Business Opportunity',
                        'project' => 'Local Business Opportunity Investigation',
                        'description' => 'Learners identify an unmet need and investigate its potential as a business opportunity.',
                        'areas' => [
                            'Opportunity identification',
                            'Market observation',
                            'Customer identification',
                            'Competitor observation',
                            'Problem-solving',
                            'Creativity',
                            'Decision-making',
                        ],
                    ],
                    [
                        'area' => 'Product Development',
                        'project' => 'Product Design and Production',
                        'description' => 'Learners develop a simple product from idea through production.',
                        'areas' => [
                            'Product ideation',
                            'Planning',
                            'Resource identification',
                            'Costing',
                            'Production',
                            'Quality control',
                            'Packaging',
                            'Evaluation',
                        ],
                    ],
                    [
                        'area' => 'Enterprise Management',
                        'project' => 'Small Enterprise Simulation',
                        'description' => 'Learners operate a small simulated or supervised enterprise.',
                        'areas' => [
                            'Business planning',
                            'Budgeting',
                            'Pricing',
                            'Purchasing',
                            'Sales',
                            'Record keeping',
                            'Customer relations',
                            'Profit/loss analysis',
                        ],
                    ],
                ],
            ],
            [
                'senior' => 2,
                'subject' => 'Kiswahili',
                'projects' => [
                    [
                        'area' => 'Communication',
                        'project' => 'Kiswahili Community Survey',
                        'description' => 'Learners conduct a survey or interview project and present findings in Kiswahili.',
                        'areas' => [
                            'Question development',
                            'Interviewing',
                            'Listening',
                            'Speaking',
                            'Data recording',
                            'Analysis',
                            'Oral presentation',
                        ],
                    ],
                    [
                        'area' => 'Culture',
                        'project' => 'Kiswahili Cultural Heritage Project',
                        'description' => 'Learners research and document a cultural practice, story, proverb, song or tradition.',
                        'areas' => [
                            'Research',
                            'Reading',
                            'Listening',
                            'Writing',
                            'Cultural documentation',
                            'Interpretation',
                            'Presentation',
                        ],
                    ],
                    [
                        'area' => 'Media',
                        'project' => 'Kiswahili Newsletter/Radio Programme',
                        'description' => 'Learners produce a small media product using Kiswahili.',
                        'areas' => [
                            'Research',
                            'Script writing',
                            'Editing',
                            'Speaking',
                            'Information organisation',
                            'Audience awareness',
                            'Teamwork',
                            'Presentation',
                        ],
                    ],
                ],
            ],
            [
                'senior' => 3,
                'subject' => 'English Language',
                'projects' => [
                    [
                        'area' => 'Research and Reporting',
                        'project' => 'Community Research Project',
                        'description' => 'Learners identify a community issue, conduct research and produce a structured research report.',
                        'areas' => [
                            'Identifying a research problem',
                            'Formulating research questions',
                            'Selecting information sources',
                            'Conducting interviews',
                            'Collecting and organising evidence',
                            'Analysing information',
                            'Writing a structured report',
                            'Presenting findings',
                        ],
                    ],
                    [
                        'area' => 'Media',
                        'project' => 'School Media Production Project',
                        'description' => 'Learners investigate a school/community issue and produce a newsletter, magazine, documentary script, podcast or radio programme.',
                        'areas' => [
                            'Research',
                            'Audience identification',
                            'Interviewing',
                            'Script/article writing',
                            'Editing',
                            'Fact checking',
                            'Media production',
                            'Presentation',
                        ],
                    ],
                    [
                        'area' => 'Public Communication',
                        'project' => 'Public Awareness Campaign',
                        'description' => 'Learners develop a communication campaign around an important social, environmental, educational or health issue.',
                        'areas' => [
                            'Problem identification',
                            'Audience analysis',
                            'Message development',
                            'Persuasive communication',
                            'Writing',
                            'Visual communication',
                            'Teamwork',
                            'Campaign evaluation',
                        ],
                    ],
                ],
            ],
            [
                'senior' => 3,
                'subject' => 'Mathematics',
                'projects' => [
                    [
                        'area' => 'Statistical Investigation',
                        'project' => 'Community Data Investigation',
                        'description' => 'Learners identify a measurable community issue and conduct a statistical investigation.',
                        'areas' => [
                            'Developing research questions',
                            'Sampling',
                            'Data collection',
                            'Data classification',
                            'Frequency distribution',
                            'Graphical representation',
                            'Statistical analysis',
                            'Interpretation',
                            'Drawing evidence-based conclusions',
                        ],
                    ],
                    [
                        'area' => 'Financial Mathematics',
                        'project' => 'Small Business Financial Analysis',
                        'description' => 'Learners analyse the financial performance of a small enterprise.',
                        'areas' => [
                            'Data collection',
                            'Cost calculation',
                            'Revenue calculation',
                            'Profit/loss analysis',
                            'Percentages',
                            'Budgeting',
                            'Financial comparison',
                            'Decision-making',
                        ],
                    ],
                    [
                        'area' => 'Mathematical Modelling',
                        'project' => 'Real-Life Measurement and Design Project',
                        'description' => 'Learners use mathematics to solve a practical design or construction problem.',
                        'areas' => [
                            'Problem identification',
                            'Measurement',
                            'Estimation',
                            'Geometry',
                            'Mathematical modelling',
                            'Scale',
                            'Calculation',
                            'Design',
                            'Evaluation',
                        ],
                    ],
                ],
            ],
            [
                'senior' => 3,
                'subject' => 'History & Political Education',
                'projects' => [
                    [
                        'area' => 'Historical Research',
                        'project' => 'Local Historical Investigation',
                        'description' => 'Learners undertake an independent investigation into a significant historical event, development, institution or personality connected to their community.',
                        'areas' => [
                            'Developing historical questions',
                            'Identifying primary and secondary sources',
                            'Oral history',
                            'Source comparison',
                            'Evidence evaluation',
                            'Chronological analysis',
                            'Historical interpretation',
                            'Academic report writing',
                        ],
                    ],
                    [
                        'area' => 'Heritage Preservation',
                        'project' => 'Local Heritage Preservation Project',
                        'description' => 'Learners identify an aspect of local heritage that requires documentation or preservation.',
                        'areas' => [
                            'Heritage identification',
                            'Research',
                            'Documentation',
                            'Interviewing',
                            'Digital/physical preservation',
                            'Cultural appreciation',
                            'Community engagement',
                            'Presentation',
                        ],
                    ],
                    [
                        'area' => 'Governance and Citizenship',
                        'project' => 'Community Governance Investigation',
                        'description' => 'Learners investigate a governance or civic issue affecting their community.',
                        'areas' => [
                            'Identifying civic issues',
                            'Research',
                            'Understanding institutions',
                            'Interviewing stakeholders',
                            'Evidence analysis',
                            'Rights and responsibilities',
                            'Problem-solving',
                            'Civic communication',
                        ],
                    ],
                ],
            ],
            [
                'senior' => 3,
                'subject' => 'Geography',
                'projects' => [
                    [
                        'area' => 'Environmental Investigation',
                        'project' => 'Local Environmental Impact Assessment',
                        'description' => 'Learners investigate a human activity and its effects on the local environment.',
                        'areas' => [
                            'Field investigation',
                            'Environmental observation',
                            'Data collection',
                            'Mapping',
                            'Identifying environmental impacts',
                            'Cause-and-effect analysis',
                            'Evaluating evidence',
                            'Recommending interventions',
                        ],
                    ],
                    [
                        'area' => 'Land Use',
                        'project' => 'Land-Use and Settlement Study',
                        'description' => 'Learners investigate changes in land use and settlement in their local area.',
                        'areas' => [
                            'Fieldwork',
                            'Mapping',
                            'Data collection',
                            'Land-use classification',
                            'Spatial analysis',
                            'Comparing changes',
                            'Interpreting geographical patterns',
                            'Report writing',
                        ],
                    ],
                    [
                        'area' => 'Resource Management',
                        'project' => 'Local Resource Management Project',
                        'description' => 'Learners investigate how a natural resource is being used and develop recommendations for sustainable management.',
                        'areas' => [
                            'Resource identification',
                            'Field investigation',
                            'Data collection',
                            'Resource-use analysis',
                            'Environmental sustainability',
                            'Stakeholder identification',
                            'Solution development',
                            'Presentation',
                        ],
                    ],
                ],
            ],
            [
                'senior' => 3,
                'subject' => 'Physics',
                'projects' => [
                    [
                        'area' => 'Energy',
                        'project' => 'School Energy Audit',
                        'description' => 'Learners investigate energy use within the school and propose practical efficiency measures.',
                        'areas' => [
                            'Identifying energy sources',
                            'Measuring/estimating energy consumption',
                            'Data recording',
                            'Data analysis',
                            'Identifying inefficiencies',
                            'Cost estimation',
                            'Solution development',
                            'Evaluation',
                        ],
                    ],
                    [
                        'area' => 'Electricity',
                        'project' => 'Practical Electrical Solution',
                        'description' => 'Learners identify a simple electrical need and develop a safe prototype or demonstration solution.',
                        'areas' => [
                            'Problem identification',
                            'Circuit design',
                            'Component selection',
                            'Construction',
                            'Testing',
                            'Troubleshooting',
                            'Safety',
                            'Evaluation',
                        ],
                    ],
                    [
                        'area' => 'Mechanics',
                        'project' => 'Mechanical Device Design',
                        'description' => 'Learners design and construct a device that solves a practical problem using mechanical principles.',
                        'areas' => [
                            'Problem identification',
                            'Design',
                            'Application of physical principles',
                            'Material selection',
                            'Construction',
                            'Testing',
                            'Modification',
                            'Evaluation',
                        ],
                    ],
                ],
            ],
            [
                'senior' => 3,
                'subject' => 'Biology',
                'projects' => [
                    [
                        'area' => 'Ecology',
                        'project' => 'Local Ecosystem Investigation',
                        'description' => 'Learners conduct a detailed investigation of an ecosystem or habitat.',
                        'areas' => [
                            'Field sampling',
                            'Species identification',
                            'Data recording',
                            'Population observation',
                            'Environmental analysis',
                            'Food relationships',
                            'Human impact assessment',
                            'Conservation recommendations',
                        ],
                    ],
                    [
                        'area' => 'Agriculture',
                        'project' => 'Sustainable Agriculture Project',
                        'description' => 'Learners investigate and implement a sustainable agricultural practice.',
                        'areas' => [
                            'Problem identification',
                            'Agricultural research',
                            'Planning',
                            'Resource management',
                            'Practical implementation',
                            'Monitoring',
                            'Data collection',
                            'Evaluation',
                        ],
                    ],
                    [
                        'area' => 'Health',
                        'project' => 'Community Health Investigation',
                        'description' => 'Learners investigate a significant health issue affecting their community and develop an evidence-based intervention.',
                        'areas' => [
                            'Health research',
                            'Data collection',
                            'Risk-factor analysis',
                            'Evidence interpretation',
                            'Intervention planning',
                            'Health communication',
                            'Implementation',
                            'Evaluation',
                        ],
                    ],
                ],
            ],
            [
                'senior' => 3,
                'subject' => 'Chemistry',
                'projects' => [
                    [
                        'area' => 'Environmental Chemistry',
                        'project' => 'Water Quality Investigation',
                        'description' => 'Learners investigate the quality and possible contamination of a local water source using appropriate observations/tests available to them.',
                        'areas' => [
                            'Sampling',
                            'Observation',
                            'Recording results',
                            'Identifying contaminants',
                            'Comparing samples',
                            'Interpreting results',
                            'Health/environmental implications',
                            'Recommendations',
                        ],
                    ],
                    [
                        'area' => 'Materials',
                        'project' => 'Materials and Their Properties',
                        'description' => 'Learners investigate materials used in a practical application and determine why particular materials are suitable.',
                        'areas' => [
                            'Material identification',
                            'Property investigation',
                            'Comparison',
                            'Data collection',
                            'Application of chemical knowledge',
                            'Material selection',
                            'Evaluation',
                        ],
                    ],
                    [
                        'area' => 'Household/Community Chemistry',
                        'project' => 'Household Chemical Safety Project',
                        'description' => 'Learners investigate chemical products commonly used in homes and develop a safety-awareness intervention.',
                        'areas' => [
                            'Chemical identification',
                            'Hazard identification',
                            'Label interpretation',
                            'Safe handling',
                            'Risk assessment',
                            'Information analysis',
                            'Safety communication',
                        ],
                    ],
                ],
            ],
            [
                'senior' => 3,
                'subject' => 'Physical Education',
                'projects' => [
                    [
                        'area' => 'Fitness Research',
                        'project' => 'Physical Fitness Investigation',
                        'description' => 'Learners investigate selected fitness indicators among a group of participants and analyse the results.',
                        'areas' => [
                            'Research planning',
                            'Fitness assessment',
                            'Data collection',
                            'Statistical analysis',
                            'Interpretation',
                            'Programme design',
                            'Evaluation',
                        ],
                    ],
                    [
                        'area' => 'Sports Management',
                        'project' => 'School Sports Management Project',
                        'description' => 'Learners plan and execute a structured sports event.',
                        'areas' => [
                            'Event planning',
                            'Budgeting',
                            'Scheduling',
                            'Team management',
                            'Officiating',
                            'Leadership',
                            'Risk management',
                            'Evaluation',
                        ],
                    ],
                    [
                        'area' => 'Health Promotion',
                        'project' => 'Adolescent Health and Physical Activity Campaign',
                        'description' => 'Learners investigate barriers to physical activity and develop an intervention.',
                        'areas' => [
                            'Research',
                            'Problem identification',
                            'Data collection',
                            'Analysis',
                            'Campaign design',
                            'Communication',
                            'Implementation',
                            'Evaluation',
                        ],
                    ],
                ],
            ],
            [
                'senior' => 3,
                'subject' => 'Religious Education',
                'projects' => [
                    [
                        'area' => 'Religion and Society',
                        'project' => 'Religion and Community Values Investigation',
                        'description' => 'Learners investigate how religious values influence behaviour and social relationships within their community.',
                        'areas' => [
                            'Research',
                            'Interviewing',
                            'Observation',
                            'Evidence collection',
                            'Comparative analysis',
                            'Reflection',
                            'Ethical reasoning',
                            'Presentation',
                        ],
                    ],
                    [
                        'area' => 'Community Service',
                        'project' => 'Community Service Intervention',
                        'description' => 'Learners identify a community need, plan a response and implement a service activity.',
                        'areas' => [
                            'Needs assessment',
                            'Planning',
                            'Resource mobilisation',
                            'Leadership',
                            'Teamwork',
                            'Service',
                            'Monitoring',
                            'Reflection and evaluation',
                        ],
                    ],
                    [
                        'area' => 'Religious Heritage',
                        'project' => 'Religious Heritage Research Project',
                        'description' => 'Learners conduct an independent investigation into an important religious historical or cultural subject.',
                        'areas' => [
                            'Research',
                            'Source evaluation',
                            'Interviewing',
                            'Historical interpretation',
                            'Documentation',
                            'Respect for diversity',
                            'Report writing',
                            'Presentation',
                        ],
                    ],
                ],
            ],
            [
                'senior' => 3,
                'subject' => 'Entrepreneurship',
                'projects' => [
                    [
                        'area' => 'Business Opportunity',
                        'project' => 'Market Research Project',
                        'description' => 'Learners identify a potential business opportunity and conduct market research.',
                        'areas' => [
                            'Identifying a market problem',
                            'Customer research',
                            'Market segmentation',
                            'Competitor analysis',
                            'Data collection',
                            'Data interpretation',
                            'Opportunity evaluation',
                        ],
                    ],
                    [
                        'area' => 'Enterprise Development',
                        'project' => 'Student Enterprise Project',
                        'description' => 'Learners establish and manage a small supervised enterprise.',
                        'areas' => [
                            'Business planning',
                            'Resource mobilisation',
                            'Costing',
                            'Pricing',
                            'Production',
                            'Marketing',
                            'Sales',
                            'Record keeping',
                            'Financial analysis',
                        ],
                    ],
                    [
                        'area' => 'Innovation',
                        'project' => 'Product Innovation Project',
                        'description' => 'Learners identify an existing product/service and develop an improved version.',
                        'areas' => [
                            'Problem identification',
                            'Creativity',
                            'Product research',
                            'Design',
                            'Prototyping',
                            'Testing',
                            'Customer feedback',
                            'Product improvement',
                        ],
                    ],
                ],
            ],
            [
                'senior' => 3,
                'subject' => 'Kiswahili',
                'projects' => [
                    [
                        'area' => 'Research',
                        'project' => 'Kiswahili Community Research Project',
                        'description' => 'Learners conduct an independent research project and communicate the findings in Kiswahili.',
                        'areas' => [
                            'Research planning',
                            'Interviewing',
                            'Information gathering',
                            'Note-taking',
                            'Analysis',
                            'Report writing',
                            'Oral presentation',
                        ],
                    ],
                    [
                        'area' => 'Culture and Literature',
                        'project' => 'Oral Literature Documentation Project',
                        'description' => 'Learners document selected oral literature from their community.',
                        'areas' => [
                            'Field research',
                            'Listening',
                            'Interviewing',
                            'Transcription',
                            'Interpretation',
                            'Cultural documentation',
                            'Writing',
                            'Presentation',
                        ],
                    ],
                    [
                        'area' => 'Media',
                        'project' => 'Kiswahili Media Production',
                        'description' => 'Learners produce a substantial Kiswahili media product.',
                        'areas' => [
                            'Research',
                            'Script development',
                            'Writing',
                            'Editing',
                            'Communication',
                            'Production',
                            'Audience awareness',
                            'Evaluation',
                        ],
                    ],
                ],
            ],
            [
                'senior' => 4,
                'subject' => 'English Language',
                'projects' => [
                    [
                        'area' => 'Independent Research',
                        'project' => 'Community Research and Advocacy Project',
                        'description' => 'Learners independently investigate a significant community issue and develop an evidence-based advocacy response.',
                        'areas' => [
                            'Identifying a significant research problem',
                            'Formulating research questions',
                            'Selecting appropriate sources',
                            'Conducting interviews/surveys',
                            'Evaluating information',
                            'Analysing evidence',
                            'Writing a formal report',
                            'Developing recommendations',
                            'Presenting and defending findings',
                        ],
                    ],
                    [
                        'area' => 'Media Production',
                        'project' => 'Investigative Media Project',
                        'description' => 'Learners investigate a real issue and produce a substantial media product such as a documentary script, feature article, newsletter, podcast or radio programme.',
                        'areas' => [
                            'Investigative research',
                            'Source verification',
                            'Interviewing',
                            'Script/article development',
                            'Editing',
                            'Audience analysis',
                            'Ethical communication',
                            'Media production',
                            'Evaluation',
                        ],
                    ],
                    [
                        'area' => 'Public Communication',
                        'project' => 'Social Advocacy Campaign',
                        'description' => 'Learners develop, implement and evaluate a public-awareness campaign.',
                        'areas' => [
                            'Problem analysis',
                            'Audience identification',
                            'Message development',
                            'Persuasive communication',
                            'Campaign planning',
                            'Production of communication materials',
                            'Implementation',
                            'Monitoring',
                            'Impact evaluation',
                        ],
                    ],
                ],
            ],
            [
                'senior' => 4,
                'subject' => 'Mathematics',
                'projects' => [
                    [
                        'area' => 'Statistical Research',
                        'project' => 'Community Statistical Investigation',
                        'description' => 'Learners undertake a substantial statistical investigation into a real community issue.',
                        'areas' => [
                            'Research design',
                            'Sampling',
                            'Data collection',
                            'Data classification',
                            'Statistical calculations',
                            'Graphical representation',
                            'Data interpretation',
                            'Drawing evidence-based conclusions',
                            'Communicating statistical findings',
                        ],
                    ],
                    [
                        'area' => 'Financial Planning',
                        'project' => 'Business Financial Feasibility Project',
                        'description' => 'Learners analyse whether a proposed small business is financially viable.',
                        'areas' => [
                            'Market data collection',
                            'Cost estimation',
                            'Revenue projection',
                            'Budget preparation',
                            'Profit/loss analysis',
                            'Break-even analysis',
                            'Financial comparison',
                            'Risk identification',
                            'Decision-making',
                        ],
                    ],
                    [
                        'area' => 'Mathematical Modelling',
                        'project' => 'Real-Life Design and Modelling Project',
                        'description' => 'Learners use mathematical concepts to design a solution to a practical problem.',
                        'areas' => [
                            'Problem identification',
                            'Mathematical modelling',
                            'Measurement',
                            'Geometry',
                            'Estimation',
                            'Calculation',
                            'Scale',
                            'Design',
                            'Testing',
                            'Evaluation',
                        ],
                    ],
                ],
            ],
            [
                'senior' => 4,
                'subject' => 'History & Political Education',
                'projects' => [
                    [
                        'area' => 'Historical Research',
                        'project' => 'Independent Historical Research Project',
                        'description' => 'Learners conduct an independent historical investigation into a significant event, institution, development or personality.',
                        'areas' => [
                            'Developing historical questions',
                            'Source identification',
                            'Primary-source research',
                            'Secondary-source research',
                            'Source criticism',
                            'Evidence evaluation',
                            'Historical interpretation',
                            'Chronological reasoning',
                            'Report writing',
                            'Presentation/defence',
                        ],
                    ],
                    [
                        'area' => 'Heritage',
                        'project' => 'Community Heritage Preservation Project',
                        'description' => 'Learners identify a heritage resource and develop a practical preservation or documentation initiative.',
                        'areas' => [
                            'Heritage identification',
                            'Historical research',
                            'Documentation',
                            'Oral history',
                            'Community engagement',
                            'Preservation planning',
                            'Resource mobilisation',
                            'Implementation',
                            'Evaluation',
                        ],
                    ],
                    [
                        'area' => 'Citizenship and Governance',
                        'project' => 'Community Governance Research Project',
                        'description' => 'Learners investigate a significant governance or citizenship challenge and propose evidence-based solutions.',
                        'areas' => [
                            'Problem identification',
                            'Civic research',
                            'Stakeholder identification',
                            'Evidence collection',
                            'Analysis',
                            'Rights and responsibilities',
                            'Policy/solution development',
                            'Civic communication',
                            'Evaluation',
                        ],
                    ],
                ],
            ],
            [
                'senior' => 4,
                'subject' => 'Geography',
                'projects' => [
                    [
                        'area' => 'Environmental Impact',
                        'project' => 'Environmental Impact Investigation',
                        'description' => 'Learners investigate a major environmental issue and develop a practical intervention.',
                        'areas' => [
                            'Field investigation',
                            'Environmental data collection',
                            'Mapping',
                            'Impact assessment',
                            'Cause-and-effect analysis',
                            'Stakeholder identification',
                            'Intervention design',
                            'Implementation',
                            'Evaluation',
                        ],
                    ],
                    [
                        'area' => 'Urbanisation and Settlement',
                        'project' => 'Settlement and Urban Development Study',
                        'description' => 'Learners investigate settlement growth and its social, economic and environmental effects.',
                        'areas' => [
                            'Fieldwork',
                            'Mapping',
                            'Data collection',
                            'Spatial analysis',
                            'Population analysis',
                            'Land-use investigation',
                            'Environmental assessment',
                            'Interpretation',
                            'Recommendation development',
                        ],
                    ],
                    [
                        'area' => 'Resource Management',
                        'project' => 'Sustainable Resource Management Project',
                        'description' => 'Learners investigate the use of a natural resource and develop a sustainable management plan.',
                        'areas' => [
                            'Resource assessment',
                            'Field research',
                            'Data analysis',
                            'Stakeholder consultation',
                            'Sustainability planning',
                            'Resource conservation',
                            'Implementation',
                            'Monitoring',
                            'Evaluation',
                        ],
                    ],
                ],
            ],
            [
                'senior' => 4,
                'subject' => 'Physics',
                'projects' => [
                    [
                        'area' => 'Energy Solutions',
                        'project' => 'Renewable Energy Solution Project',
                        'description' => 'Learners investigate an energy challenge and develop a practical renewable-energy solution or prototype.',
                        'areas' => [
                            'Problem identification',
                            'Energy research',
                            'Scientific design',
                            'Mathematical calculation',
                            'Component/material selection',
                            'Prototype development',
                            'Testing',
                            'Modification',
                            'Efficiency evaluation',
                            'Cost analysis',
                        ],
                    ],
                    [
                        'area' => 'Electricity and Technology',
                        'project' => 'Practical Electrical/Electronic Solution',
                        'description' => 'Learners design and develop a safe electrical/electronic system that addresses a practical need.',
                        'areas' => [
                            'Problem analysis',
                            'Circuit design',
                            'Component selection',
                            'Construction',
                            'Testing',
                            'Troubleshooting',
                            'Safety',
                            'Documentation',
                            'Evaluation',
                        ],
                    ],
                    [
                        'area' => 'Mechanics and Engineering',
                        'project' => 'Engineering Design Project',
                        'description' => 'Learners design and construct a mechanical device or structure that solves a defined problem.',
                        'areas' => [
                            'Problem identification',
                            'Research',
                            'Engineering design',
                            'Material selection',
                            'Measurement',
                            'Construction',
                            'Testing',
                            'Modification',
                            'Performance evaluation',
                            'Technical presentation',
                        ],
                    ],
                ],
            ],
            [
                'senior' => 4,
                'subject' => 'Biology',
                'projects' => [
                    [
                        'area' => 'Environmental Biology',
                        'project' => 'Biodiversity and Conservation Project',
                        'description' => 'Learners conduct a detailed biodiversity investigation and develop a conservation intervention.',
                        'areas' => [
                            'Field sampling',
                            'Species identification',
                            'Data collection',
                            'Biodiversity analysis',
                            'Environmental assessment',
                            'Conservation planning',
                            'Implementation',
                            'Monitoring',
                            'Evaluation',
                        ],
                    ],
                    [
                        'area' => 'Agriculture',
                        'project' => 'Sustainable Agricultural Production Project',
                        'description' => 'Learners develop and implement a sustainable agricultural production project.',
                        'areas' => [
                            'Problem identification',
                            'Agricultural research',
                            'Production planning',
                            'Resource management',
                            'Practical implementation',
                            'Growth monitoring',
                            'Data collection',
                            'Productivity analysis',
                            'Cost analysis',
                            'Evaluation',
                        ],
                    ],
                    [
                        'area' => 'Human Health',
                        'project' => 'Community Health Intervention Project',
                        'description' => 'Learners investigate a major health concern and design, implement and evaluate an appropriate awareness/prevention intervention.',
                        'areas' => [
                            'Health research',
                            'Needs assessment',
                            'Data collection',
                            'Risk analysis',
                            'Intervention design',
                            'Communication',
                            'Implementation',
                            'Monitoring',
                            'Impact evaluation',
                        ],
                    ],
                ],
            ],
            [
                'senior' => 4,
                'subject' => 'Chemistry',
                'projects' => [
                    [
                        'area' => 'Water and Environmental Chemistry',
                        'project' => 'Water Quality and Treatment Project',
                        'description' => 'Learners investigate a local water-quality challenge and evaluate an appropriate treatment approach.',
                        'areas' => [
                            'Sampling',
                            'Observation/testing',
                            'Identifying contaminants',
                            'Data recording',
                            'Analysis',
                            'Treatment methods',
                            'Comparing results',
                            'Safety',
                            'Environmental recommendations',
                        ],
                    ],
                    [
                        'area' => 'Materials Chemistry',
                        'project' => 'Materials Development Project',
                        'description' => 'Learners investigate materials and develop/test a material or product for a practical application.',
                        'areas' => [
                            'Material selection',
                            'Property investigation',
                            'Experimental design',
                            'Testing',
                            'Data collection',
                            'Analysis',
                            'Product development',
                            'Safety',
                            'Evaluation',
                        ],
                    ],
                    [
                        'area' => 'Environmental Pollution',
                        'project' => 'Pollution Reduction Project',
                        'description' => 'Learners investigate a local pollution problem and implement a practical reduction/reuse/recycling intervention.',
                        'areas' => [
                            'Pollution identification',
                            'Scientific investigation',
                            'Data collection',
                            'Cause-and-effect analysis',
                            'Solution design',
                            'Implementation',
                            'Environmental responsibility',
                            'Monitoring',
                            'Evaluation',
                        ],
                    ],
                ],
            ],
            [
                'senior' => 4,
                'subject' => 'Physical Education',
                'projects' => [
                    [
                        'area' => 'Fitness Research',
                        'project' => 'School Fitness and Wellness Study',
                        'description' => 'Learners conduct a structured fitness/wellness investigation and develop an intervention.',
                        'areas' => [
                            'Research planning',
                            'Fitness assessment',
                            'Data collection',
                            'Statistical analysis',
                            'Interpretation',
                            'Programme design',
                            'Implementation',
                            'Monitoring',
                            'Evaluation',
                        ],
                    ],
                    [
                        'area' => 'Sports Management',
                        'project' => 'Major School Sports Event Project',
                        'description' => 'Learners independently plan, manage and evaluate a substantial sporting event.',
                        'areas' => [
                            'Event planning',
                            'Budgeting',
                            'Scheduling',
                            'Team management',
                            'Leadership',
                            'Officiating',
                            'Safety/risk management',
                            'Communication',
                            'Evaluation',
                        ],
                    ],
                    [
                        'area' => 'Health Promotion',
                        'project' => 'Active Lifestyle Intervention',
                        'description' => 'Learners investigate barriers to physical activity and implement a practical intervention.',
                        'areas' => [
                            'Research',
                            'Needs assessment',
                            'Intervention planning',
                            'Communication',
                            'Programme implementation',
                            'Participation monitoring',
                            'Data analysis',
                            'Impact evaluation',
                        ],
                    ],
                ],
            ],
            [
                'senior' => 4,
                'subject' => 'Religious Education',
                'projects' => [
                    [
                        'area' => 'Religion and Contemporary Society',
                        'project' => 'Religion and Social Issues Research Project',
                        'description' => 'Learners investigate the relationship between religious values and a contemporary social issue.',
                        'areas' => [
                            'Independent research',
                            'Source evaluation',
                            'Interviewing',
                            'Ethical reasoning',
                            'Evidence analysis',
                            'Reflection',
                            'Argument development',
                            'Presentation',
                        ],
                    ],
                    [
                        'area' => 'Community Service',
                        'project' => 'Community Service Intervention',
                        'description' => 'Learners identify a significant community need and develop, implement and evaluate a service intervention.',
                        'areas' => [
                            'Needs assessment',
                            'Project planning',
                            'Resource mobilisation',
                            'Leadership',
                            'Teamwork',
                            'Service',
                            'Monitoring',
                            'Reflection',
                            'Impact evaluation',
                        ],
                    ],
                    [
                        'area' => 'Religious Heritage',
                        'project' => 'Religious Heritage Preservation Project',
                        'description' => 'Learners document and preserve a significant religious historical or cultural resource.',
                        'areas' => [
                            'Research',
                            'Source evaluation',
                            'Documentation',
                            'Oral history',
                            'Preservation',
                            'Community engagement',
                            'Ethical consideration',
                            'Presentation',
                        ],
                    ],
                ],
            ],
            [
                'senior' => 4,
                'subject' => 'Entrepreneurship',
                'projects' => [
                    [
                        'area' => 'Enterprise Development',
                        'project' => 'Business Start-Up Project',
                        'description' => 'Learners develop a complete business concept and, where feasible, implement it.',
                        'areas' => [
                            'Opportunity identification',
                            'Market research',
                            'Business planning',
                            'Resource mobilisation',
                            'Costing',
                            'Pricing',
                            'Production',
                            'Marketing',
                            'Sales',
                            'Record keeping',
                            'Financial evaluation',
                        ],
                    ],
                    [
                        'area' => 'Innovation',
                        'project' => 'Product/Service Innovation Project',
                        'description' => 'Learners identify an existing problem and develop an innovative product or service.',
                        'areas' => [
                            'Problem identification',
                            'Market research',
                            'Ideation',
                            'Product/service design',
                            'Prototyping',
                            'Customer feedback',
                            'Testing',
                            'Refinement',
                            'Cost analysis',
                            'Commercial evaluation',
                        ],
                    ],
                    [
                        'area' => 'Social Entrepreneurship',
                        'project' => 'Social Enterprise Project',
                        'description' => 'Learners develop an enterprise designed to address a community problem while remaining economically sustainable.',
                        'areas' => [
                            'Community needs assessment',
                            'Opportunity identification',
                            'Social-impact planning',
                            'Business planning',
                            'Resource mobilisation',
                            'Implementation',
                            'Financial management',
                            'Impact measurement',
                            'Sustainability evaluation',
                        ],
                    ],
                ],
            ],
            [
                'senior' => 4,
                'subject' => 'Kiswahili',
                'projects' => [
                    [
                        'area' => 'Independent Research',
                        'project' => 'Kiswahili Research and Presentation Project',
                        'description' => 'Learners undertake independent research and communicate their findings in Kiswahili.',
                        'areas' => [
                            'Research planning',
                            'Information gathering',
                            'Interviewing',
                            'Source evaluation',
                            'Analysis',
                            'Report writing',
                            'Oral presentation',
                            'Defence of findings',
                        ],
                    ],
                    [
                        'area' => 'Oral Literature and Heritage',
                        'project' => 'Kiswahili Oral Heritage Preservation Project',
                        'description' => 'Learners collect, document and interpret oral literature from their community.',
                        'areas' => [
                            'Field research',
                            'Interviewing',
                            'Listening',
                            'Transcription',
                            'Interpretation',
                            'Cultural documentation',
                            'Preservation',
                            'Presentation',
                        ],
                    ],
                    [
                        'area' => 'Media',
                        'project' => 'Kiswahili Media Production Project',
                        'description' => 'Learners produce and publish/present a substantial Kiswahili media product.',
                        'areas' => [
                            'Research',
                            'Script development',
                            'Writing',
                            'Editing',
                            'Production',
                            'Audience analysis',
                            'Communication',
                            'Team management',
                            'Evaluation',
                        ],
                    ],
                ],
            ],
        ];
    }
}
