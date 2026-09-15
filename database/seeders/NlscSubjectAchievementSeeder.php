<?php

namespace Database\Seeders;

use App\Http\Controllers\Helper;
use App\Models\NlscSubjectAchievement;
use App\Models\NlscTopic;
use App\Services\NlscSyncService;
use Illuminate\Database\Seeder;

/**
 * php artisan db:seed --class=NlscSubjectAchievementSeeder
 *
 * Seeds the platform-wide NCDC "Subject Achievement" catalogue (Senior
 * 1-4) into the admin side, the same starter set every school
 * automatically pulls in the first time it visits either the "Subject
 * Achievement" or "Activities of Integration" screen for a given
 * Senior/Subject (SchoolNlscTopicController::cloneFromAdminIfNeeded()).
 * Nothing needs to be pushed to schools manually here — an already
 * synced school still gets each new statement immediately too, since
 * this calls the same NlscSyncService::propagateNewSubjectAchievement()
 * the admin "Add" button itself uses.
 *
 * Topics are shared with Activities of Integration (one nlsc_topics
 * catalogue, see NlscSubjectAchievementController's docblock) — a topic
 * already created by that feature is reused rather than duplicated; a
 * topic that doesn't exist yet is created here first, exactly the way
 * NlscTopicController::store() creates one (same fields, same
 * next-available sort_order).
 *
 * Senior/Subject names are matched case-insensitively against the
 * SECONDARY_OLEVEL_CLASSES / NLSC_SUBJECTS master-data groups, the same
 * lookup NlscTopicBulkImport uses — with a short list of aliases for
 * names the catalogue spells differently than master-data commonly does
 * (e.g. "English Language" vs "English"). Any Senior/Subject that still
 * can't be resolved is skipped with a warning rather than failing the
 * whole run, since master-data naming can vary between installs.
 *
 * Safe to re-run: an existing topic is reused (never duplicated), and an
 * achievement statement already present with the exact same wording is
 * left alone rather than added again — only genuinely new statements
 * (e.g. after the catalogue below is extended) get created and pushed
 * out on a second run.
 */
class NlscSubjectAchievementSeeder extends Seeder
{
    /**
     * Extra name(s) to also try, in order, when the catalogue's subject
     * name doesn't match any master-data NLSC_SUBJECTS row exactly.
     * Keyed by the catalogue's own uppercase subject name.
     */
    private const SUBJECT_ALIASES = [
        'ENGLISH LANGUAGE' => ['ENGLISH'],
        'RELIGIOUS EDUCATION' => ['IRE', 'ISLAMIC RELIGIOUS EDUCATION', 'CRE', 'CHRISTIAN RELIGIOUS EDUCATION'],
        'HISTORY AND POLITICAL EDUCATION' => ['HISTORY', 'HISTORY & POLITICAL EDUCATION'],
        'PHYSICAL EDUCATION' => ['PE'],
    ];

    public function run(): void
    {
        $seniorLookup = Helper::MasterRecords(config('constants.options.SECONDARY_OLEVEL_CLASSES'))
            ->mapWithKeys(fn($m) => [strtolower(trim($m->md_name)) => $m->md_id]);

        $subjectLookup = Helper::MasterRecords(config('constants.options.NLSC_SUBJECTS'))
            ->mapWithKeys(fn($m) => [strtolower(trim($m->md_name)) => $m->md_id]);

        $topicsCreated = 0;
        $topicsReused = 0;
        $achievementsCreated = 0;
        $achievementsSkipped = 0;
        $subjectsSkipped = [];

        foreach ($this->catalogue() as $block) {
            $seniorId = $seniorLookup[strtolower("senior {$block['senior']}")] ?? null;

            $subjectId = $this->resolveSubjectId($block['subject'], $subjectLookup);

            if (!$seniorId || !$subjectId) {
                $subjectsSkipped[] = "Senior {$block['senior']} — {$block['subject']}";
                continue;
            }

            foreach ($block['topics'] as $row) {
                $topic = NlscTopic::where('senior_class_id', $seniorId)
                    ->where('subject_id', $subjectId)
                    ->where('topic_name', $row['topic'])
                    ->first();

                if ($topic) {
                    $topicsReused++;
                } else {
                    $nextOrder = 1 + (int) NlscTopic::where('senior_class_id', $seniorId)
                        ->where('subject_id', $subjectId)
                        ->max('sort_order');

                    $topic = NlscTopic::create([
                        'senior_class_id' => $seniorId,
                        'subject_id' => $subjectId,
                        'topic_name' => $row['topic'],
                        'sort_order' => $nextOrder,
                    ]);
                    $topicsCreated++;
                }

                $alreadyHasIt = NlscSubjectAchievement::where('nlsc_topic_id', $topic->id)
                    ->where('achievement_text', $row['achievement'])
                    ->exists();

                if ($alreadyHasIt) {
                    $achievementsSkipped++;
                    continue;
                }

                $achievement = NlscSubjectAchievement::create([
                    'nlsc_topic_id' => $topic->id,
                    'achievement_text' => $row['achievement'],
                ]);

                NlscSyncService::propagateNewSubjectAchievement($topic, $achievement);
                $achievementsCreated++;
            }
        }

        if ($this->command) {
            $this->command->info("Topics created: {$topicsCreated}, reused: {$topicsReused}");
            $this->command->info("Achievements created: {$achievementsCreated}, already present: {$achievementsSkipped}");
            if ($subjectsSkipped) {
                $this->command->warn('Skipped (Senior/Subject not found in master data): ' . implode('; ', $subjectsSkipped));
                $this->command->warn('Add these to SECONDARY_OLEVEL_CLASSES / NLSC_SUBJECTS master data (or to SUBJECT_ALIASES above) and re-run — already-seeded rows are left untouched.');
            }
        }
    }

    /**
     * Tries the catalogue's own subject name first, then each alias in
     * SUBJECT_ALIASES, then finally a loose "one name contains the
     * other" match against every NLSC_SUBJECTS row — catches spacing/
     * wording differences (e.g. "History And Political Education" vs
     * "History & Political Education") without needing every variant
     * listed explicitly above.
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
     * The full NCDC Subject Achievement catalogue, Senior 1-4, exactly as
     * supplied (subject -> topic -> one achievement statement per topic).
     * Topic names are matched/reused against whatever Activities of
     * Integration has already created for that Senior/Subject (same
     * nlsc_topics rows both features share), and a fresh topic is created
     * here first for any that don't exist yet.
     */
    private function catalogue(): array
    {
        return [
            [
                'senior' => 1,
                'subject' => 'ENGLISH LANGUAGE',
                'topics' => [
                    [
                        'topic' => 'Personal Life and Family',
                        'achievement' => 'Communicates confidently about personal identity, family members, relationships, routines and responsibilities using appropriate spoken and written English.',
                    ],
                    [
                        'topic' => 'Finding Information',
                        'achievement' => 'Locates, selects, interprets and presents relevant information from spoken, written and visual sources for educational and everyday purposes.',
                    ],
                    [
                        'topic' => 'Food',
                        'achievement' => 'Uses appropriate vocabulary and language structures to discuss food, eating habits, preparation, preferences and healthy choices in different situations.',
                    ],
                    [
                        'topic' => 'At the Market',
                        'achievement' => 'Communicates effectively in buying and selling situations, using appropriate vocabulary, expressions, questions and descriptions related to goods, prices and transactions.',
                    ],
                    [
                        'topic' => 'Children at Work',
                        'achievement' => 'Discusses children\'s work and related social issues, expresses opinions and supports views using appropriate spoken and written language.',
                    ],
                    [
                        'topic' => 'Environment and Pollution',
                        'achievement' => 'Describes environmental problems and pollution, explains their causes and effects, and communicates practical ways of protecting the environment.',
                    ],
                    [
                        'topic' => 'Urban and Rural Life',
                        'achievement' => 'Compares urban and rural life, describing differences in lifestyles, services, opportunities and challenges using coherent spoken and written English.',
                    ],
                    [
                        'topic' => 'Travel',
                        'achievement' => 'Uses English effectively to discuss travel, journeys, destinations, transport and travel experiences and to communicate information in practical travel situations.',
                    ],
                    [
                        'topic' => 'Experience of Secondary School',
                        'achievement' => 'Reflects on and communicates experiences of secondary school life, expressing opinions, describing experiences and producing appropriate spoken and written texts.',
                    ],
                ],
            ],
            [
                'senior' => 1,
                'subject' => 'MATHEMATICS',
                'topics' => [
                    [
                        'topic' => 'Number Bases',
                        'achievement' => 'Converts, represents and performs operations with numbers in different bases, relating these representations to decimal place value.',
                    ],
                    [
                        'topic' => 'Working with Integers',
                        'achievement' => 'Performs calculations involving positive and negative integers and applies integer operations to solve practical mathematical problems.',
                    ],
                    [
                        'topic' => 'Fractions, Percentages and Decimals',
                        'achievement' => 'Converts between fractions, decimals and percentages and applies them accurately in calculations and everyday problem-solving.',
                    ],
                    [
                        'topic' => 'Rectangular Cartesian Coordinates in 2 Dimensions',
                        'achievement' => 'Plots, reads and interprets coordinates and uses coordinate grids to represent and solve problems involving points and shapes.',
                    ],
                    [
                        'topic' => 'Geometric Construction Skills',
                        'achievement' => 'Uses appropriate geometric instruments and construction techniques to accurately construct angles, lines and regular geometric figures.',
                    ],
                    [
                        'topic' => 'Sequences and Patterns',
                        'achievement' => 'Identifies, describes, extends and generates numerical patterns and sequences and explains the rules governing them.',
                    ],
                    [
                        'topic' => 'Bearings',
                        'achievement' => 'Uses compass directions, bearings and scale drawings to describe positions, directions and distances in practical situations.',
                    ],
                    [
                        'topic' => 'General and Angle Properties of Geometric Figures',
                        'achievement' => 'Applies properties of angles, lines, polygons and geometric figures to determine unknown quantities and solve geometric problems.',
                    ],
                    [
                        'topic' => 'Data Collection and Presentation',
                        'achievement' => 'Collects, organises, represents and interprets data using appropriate tables, charts and graphs.',
                    ],
                    [
                        'topic' => 'Reflection',
                        'achievement' => 'Identifies lines of symmetry and performs and interprets reflections of shapes in different contexts, including the Cartesian plane.',
                    ],
                    [
                        'topic' => 'Equations of Lines and Curves',
                        'achievement' => 'Forms and interprets linear equations and represents straight-line relationships graphically.',
                    ],
                    [
                        'topic' => 'Algebra 1',
                        'achievement' => 'Uses algebraic notation to form, simplify and evaluate expressions and solves simple equations in one variable.',
                    ],
                    [
                        'topic' => 'Business Arithmetic',
                        'achievement' => 'Applies arithmetic involving profit, loss, discount, commission, interest, insurance and related percentages to real-life financial situations.',
                    ],
                    [
                        'topic' => 'Time and Time Tables',
                        'achievement' => 'Reads, calculates and interprets time and timetables and applies time calculations to practical situations.',
                    ],
                ],
            ],
            [
                'senior' => 1,
                'subject' => 'HISTORY AND POLITICAL EDUCATION',
                'topics' => [
                    [
                        'topic' => 'Finding Out About Our Past',
                        'achievement' => 'Uses different historical sources and methods to investigate the past and explains the importance of preserving historical sites and evidence.',
                    ],
                    [
                        'topic' => 'The Origin of Man',
                        'achievement' => 'Examines and compares different accounts and theories concerning the origin of humankind and evaluates them within their historical and cultural contexts.',
                    ],
                    [
                        'topic' => 'Migration and Settlement in East Africa Since 1000 AD',
                        'achievement' => 'Explains the origins, movements, settlement patterns and effects of major migrant groups in East Africa using historical evidence and maps.',
                    ],
                    [
                        'topic' => 'Culture and Key Ethnic Groups in East Africa',
                        'achievement' => 'Explains cultural practices, institutions and ethnic diversity in East Africa and demonstrates respect for different cultures and mechanisms of social harmony.',
                    ],
                    [
                        'topic' => 'State Formation in East Africa',
                        'achievement' => 'Explains the organisation, leadership and development of centralised and non-centralised societies in pre-colonial East Africa and compares their strengths and limitations.',
                    ],
                    [
                        'topic' => 'Religions in East Africa',
                        'achievement' => 'Explains indigenous religious practices and the arrival, spread and influence of Christianity and Islam in East Africa.',
                    ],
                    [
                        'topic' => 'Local and External Trade Contacts with East African Communities',
                        'achievement' => 'Explains the development, participants, goods, routes and effects of local and external trade on East African communities.',
                    ],
                    [
                        'topic' => 'Scramble, Partition and Colonisation of East Africa',
                        'achievement' => 'Explains the causes, processes, methods and consequences of the scramble, partition and colonisation of East Africa.',
                    ],
                    [
                        'topic' => 'Response to the Establishment of Colonial Rule in East Africa',
                        'achievement' => 'Analyses the reasons for and forms of African collaboration and resistance to colonial rule and evaluates their consequences.',
                    ],
                    [
                        'topic' => 'Colonial Administrative Systems in East Africa',
                        'achievement' => 'Explains the major colonial administrative systems and assesses how colonial governments exercised political control over East African societies.',
                    ],
                    [
                        'topic' => 'Colonial Economy in East Africa',
                        'achievement' => 'Explains the organisation and effects of the colonial economy, including labour, taxation, cash crops, infrastructure and trade.',
                    ],
                    [
                        'topic' => 'World Wars and Their Impact on East Africa',
                        'achievement' => 'Explains East African involvement in the World Wars and assesses their political, social and economic effects on the region.',
                    ],
                    [
                        'topic' => 'Struggle for Independence in East Africa',
                        'achievement' => 'Explains the causes, processes, personalities and organisations involved in the struggle for independence and evaluates their contribution to national liberation.',
                    ],
                    [
                        'topic' => 'Post-Independence Socio-Economic Challenges in East Africa',
                        'achievement' => 'Identifies and analyses major social, political and economic challenges faced by East African states after independence.',
                    ],
                    [
                        'topic' => 'Civil Society and Non-Governmental Organisations',
                        'achievement' => 'Explains the role of civil society and NGOs in addressing social, economic and community challenges in East Africa.',
                    ],
                    [
                        'topic' => 'Changing Land Tenure Systems in East Africa',
                        'achievement' => 'Explains major changes in land ownership and tenure systems and assesses their effects on communities and economic development.',
                    ],
                    [
                        'topic' => 'Key Personalities in the History of East Africa Before Independence',
                        'achievement' => 'Identifies significant historical personalities and evaluates their roles and contributions to the development and transformation of East African societies.',
                    ],
                ],
            ],
            [
                'senior' => 1,
                'subject' => 'GEOGRAPHY',
                'topics' => [
                    [
                        'topic' => 'Introduction to Geography',
                        'achievement' => 'Explains the meaning, branches, scope and importance of Geography and relates geographical knowledge to everyday life and the environment.',
                    ],
                    [
                        'topic' => 'Showing the Local Area on a Map',
                        'achievement' => 'Produces and interprets sketch maps showing important physical and human features of the local environment using appropriate symbols and scale.',
                    ],
                    [
                        'topic' => 'Maps and Their Uses',
                        'achievement' => 'Uses maps, symbols, scale, direction and other map elements to locate, interpret and communicate geographical information.',
                    ],
                    [
                        'topic' => 'Ways of Studying Geography: Fieldwork, Photographs, Statistics, Charts and Graphs',
                        'achievement' => 'Uses fieldwork, photographs, statistics, charts and graphs to collect, analyse and communicate geographical information.',
                    ],
                    [
                        'topic' => 'The Earth and Its Movements',
                        'achievement' => 'Explains the Earth\'s rotation and revolution and relates these movements to phenomena such as day and night, seasons and differences in time.',
                    ],
                    [
                        'topic' => 'Weather and Climate',
                        'achievement' => 'Distinguishes weather from climate, observes and records weather elements, uses weather instruments and explains their influence on human activities.',
                    ],
                    [
                        'topic' => 'Location, Size and Relief Regions of East Africa',
                        'achievement' => 'Locates East Africa geographically and describes its size, major relief regions and associated physical characteristics.',
                    ],
                    [
                        'topic' => 'Formation of Major Landforms and Drainage in East Africa',
                        'achievement' => 'Explains the formation and distribution of major landforms and drainage features in East Africa and interprets their effects on human activities.',
                    ],
                    [
                        'topic' => 'Climate and Natural Vegetation of East Africa',
                        'achievement' => 'Explains the relationship between climate and natural vegetation in East Africa and describes their distribution and significance to human activities.',
                    ],
                    [
                        'topic' => 'Climate Change in East Africa and the World',
                        'achievement' => 'Explains the causes, evidence and effects of climate change and evaluates measures that communities and governments can take to reduce its impacts.',
                    ],
                    [
                        'topic' => 'Major Climatic Zones of the World',
                        'achievement' => 'Identifies and explains the major climatic zones of the world and relates their characteristics to human activities and natural vegetation.',
                    ],
                    [
                        'topic' => 'Geographical Regions of North America',
                        'achievement' => 'Identifies and describes major geographical regions of North America and explains their physical and human characteristics.',
                    ],
                    [
                        'topic' => 'Development of Agriculture in East Africa',
                        'achievement' => 'Explains the development, types, distribution, importance and challenges of agriculture in East Africa and evaluates ways of improving agricultural productivity.',
                    ],
                    [
                        'topic' => 'Some Agricultural Areas of North America',
                        'achievement' => 'Describes major agricultural areas of North America and explains how climate, relief, soils, technology and markets influence agricultural production.',
                    ],
                ],
            ],
            [
                'senior' => 1,
                'subject' => 'PHYSICS',
                'topics' => [
                    [
                        'topic' => 'Introduction to Physics',
                        'achievement' => 'Explains what Physics is, identifies its major areas and recognises its applications and importance in everyday life and technology.',
                    ],
                    [
                        'topic' => 'Measurements in Physics',
                        'achievement' => 'Uses appropriate instruments and units to measure physical quantities, records measurements accurately and interprets measurement results.',
                    ],
                    [
                        'topic' => 'States of Matter',
                        'achievement' => 'Explains the properties of solids, liquids and gases using the particle model and relates particle behaviour to changes of state.',
                    ],
                    [
                        'topic' => 'Effects of Forces',
                        'achievement' => 'Identifies and describes forces and explains their effects on objects, including changes in motion, shape and direction.',
                    ],
                    [
                        'topic' => 'Temperature Measurements',
                        'achievement' => 'Measures and compares temperature using appropriate instruments and scales and explains temperature changes in practical situations.',
                    ],
                    [
                        'topic' => 'Heat Transfer',
                        'achievement' => 'Explains conduction, convection and radiation and applies knowledge of heat transfer to everyday situations and practical problems.',
                    ],
                    [
                        'topic' => 'Expansion of Solids, Liquids and Gases',
                        'achievement' => 'Explains thermal expansion in solids, liquids and gases and applies the concept to practical applications and everyday phenomena.',
                    ],
                    [
                        'topic' => 'Nature of Light and Reflection at Plane Surfaces',
                        'achievement' => 'Explains basic properties of light and applies the laws of reflection to predict and explain the behaviour of light at plane surfaces.',
                    ],
                ],
            ],
            [
                'senior' => 1,
                'subject' => 'BIOLOGY',
                'topics' => [
                    [
                        'topic' => 'Introduction to Biology',
                        'achievement' => 'Explains Biology as the study of living organisms, identifies its major branches and applies biological methods and knowledge to everyday life.',
                    ],
                    [
                        'topic' => 'Cells',
                        'achievement' => 'Describes the structure and functions of plant and animal cells, compares different cell types and relates cell structures to their functions.',
                    ],
                    [
                        'topic' => 'Classification',
                        'achievement' => 'Explains the purpose and principles of biological classification and uses observable characteristics to classify organisms.',
                    ],
                    [
                        'topic' => 'The Five Kingdoms of Living Organisms',
                        'achievement' => 'Identifies the major characteristics and representative organisms of the five kingdoms and uses those characteristics to distinguish between groups.',
                    ],
                    [
                        'topic' => 'Viruses',
                        'achievement' => 'Describes the characteristics, transmission, infection and effects of viruses and explains appropriate measures for preventing viral diseases.',
                    ],
                    [
                        'topic' => 'Insects',
                        'achievement' => 'Identifies major insect features, explains insect classification, life cycles, adaptations and importance, and uses appropriate biological methods to identify insects.',
                    ],
                    [
                        'topic' => 'Flowering Plants',
                        'achievement' => 'Identifies major features of flowering plants and explains their structures, functions, reproduction and importance to humans and the environment.',
                    ],
                ],
            ],
            [
                'senior' => 1,
                'subject' => 'CHEMISTRY',
                'topics' => [
                    [
                        'topic' => 'Chemistry and Society',
                        'achievement' => 'Explains the nature, importance and applications of Chemistry and relates chemical knowledge to everyday life, industry, health and national development.',
                    ],
                    [
                        'topic' => 'Experimental Chemistry',
                        'achievement' => 'Uses laboratory equipment and procedures safely, makes observations and measurements, records results and draws appropriate conclusions from experiments.',
                    ],
                    [
                        'topic' => 'States and Changes of States of Matter',
                        'achievement' => 'Uses the particle model to explain the properties of solids, liquids and gases and describes processes involved in changes of state.',
                    ],
                    [
                        'topic' => 'Using Materials',
                        'achievement' => 'Investigates common materials, relates their properties to their uses and evaluates appropriate methods of reuse, recycling and disposal.',
                    ],
                    [
                        'topic' => 'Temporary and Permanent Changes',
                        'achievement' => 'Distinguishes physical and chemical changes and uses observable evidence to identify and explain temporary and permanent changes in materials.',
                    ],
                    [
                        'topic' => 'Mixtures, Elements and Compounds',
                        'achievement' => 'Distinguishes elements, compounds and mixtures and selects appropriate methods for separating and purifying substances.',
                    ],
                    [
                        'topic' => 'Air',
                        'achievement' => 'Explains the composition and importance of air, investigates oxygen and other components and relates human activities to changes in air quality.',
                    ],
                    [
                        'topic' => 'Water',
                        'achievement' => 'Explains the physical and chemical properties and importance of water and describes evaporation, condensation, the water cycle and methods of water purification.',
                    ],
                    [
                        'topic' => 'Rocks and Minerals',
                        'achievement' => 'Identifies common rocks and minerals, explains how mineral composition affects rock properties and relates rocks and minerals to their uses and importance.',
                    ],
                ],
            ],
            [
                'senior' => 1,
                'subject' => 'PHYSICAL EDUCATION',
                'topics' => [
                    [
                        'topic' => 'Introduction to Physical Education',
                        'achievement' => 'Explains the meaning, importance and scope of Physical Education and demonstrates a positive attitude towards participation in physical activities.',
                    ],
                    [
                        'topic' => 'Safety and First Aid',
                        'achievement' => 'Identifies risks in physical activities, follows safety procedures and applies basic first-aid measures to common sports and physical-activity injuries.',
                    ],
                    [
                        'topic' => 'Body Conditioning',
                        'achievement' => 'Performs appropriate conditioning exercises and demonstrates understanding of how physical activities develop strength, endurance, flexibility and general fitness.',
                    ],
                    [
                        'topic' => 'Movement Concepts',
                        'achievement' => 'Demonstrates fundamental movement concepts and applies control, coordination, balance, space and body awareness during physical activities.',
                    ],
                    [
                        'topic' => 'Exercise, Rest and Hygiene',
                        'achievement' => 'Explains the relationship between exercise, rest and personal hygiene and applies healthy practices to support physical wellbeing.',
                    ],
                    [
                        'topic' => 'Basic Running Skills',
                        'achievement' => 'Demonstrates fundamental running techniques and applies appropriate starting, running and finishing skills in athletics activities.',
                    ],
                    [
                        'topic' => 'Basic Skills in Rounders',
                        'achievement' => 'Demonstrates basic rounders skills and applies appropriate techniques, rules, teamwork and safety practices during play.',
                    ],
                    [
                        'topic' => 'Skills Development and Diet',
                        'achievement' => 'Explains the relationship between nutrition and physical performance and makes appropriate dietary choices for healthy growth and activity.',
                    ],
                    [
                        'topic' => 'Basic Skills in Netball',
                        'achievement' => 'Demonstrates fundamental netball skills and applies basic rules, teamwork, positioning and safe play during games.',
                    ],
                    [
                        'topic' => 'Basic Skills in Volleyball',
                        'achievement' => 'Demonstrates fundamental volleyball skills and applies basic rules, teamwork, positioning and safe participation during games.',
                    ],
                ],
            ],
            [
                'senior' => 1,
                'subject' => 'RELIGIOUS EDUCATION',
                'topics' => [
                    [
                        'topic' => 'Worship',
                        'achievement' => 'Explains the meaning, purpose and forms of worship in Islam and demonstrates understanding of how worship develops personal discipline, spirituality and responsible living.',
                    ],
                    [
                        'topic' => 'Islamic Rituals and Celebrations',
                        'achievement' => 'Explains major Islamic rituals and celebrations, their significance and the values they promote in individual and community life.',
                    ],
                    [
                        'topic' => 'Islam and Values in Christianity and African Traditional Religion',
                        'achievement' => 'Compares selected Islamic values with values found in Christianity and African Traditional Religion and demonstrates respect for religious diversity and peaceful coexistence.',
                    ],
                ],
            ],
            [
                'senior' => 1,
                'subject' => 'ENTREPRENEURSHIP',
                'topics' => [
                    [
                        'topic' => 'Introduction to Entrepreneurship Education',
                        'achievement' => 'Explains the meaning, importance and characteristics of entrepreneurship and identifies entrepreneurial qualities and opportunities in everyday life.',
                    ],
                    [
                        'topic' => 'Businesses in Uganda',
                        'achievement' => 'Identifies and classifies different types of businesses in Uganda and explains their contribution to employment, income generation and economic development.',
                    ],
                    [
                        'topic' => 'Business Ideas and Business Opportunities',
                        'achievement' => 'Distinguishes business ideas from business opportunities and identifies, evaluates and develops viable opportunities from the surrounding environment.',
                    ],
                    [
                        'topic' => 'Business Start-up Process',
                        'achievement' => 'Explains and applies the major steps involved in starting a small business, including identifying an opportunity, planning, mobilising resources and initiating operations.',
                    ],
                    [
                        'topic' => 'Introduction to Government Revenue',
                        'achievement' => 'Explains the meaning, sources and importance of government revenue and relates taxation and other revenue sources to public services and national development.',
                    ],
                ],
            ],
            [
                'senior' => 1,
                'subject' => 'KISWAHILI',
                'topics' => [
                    [
                        'topic' => 'Watu wa Nyumbani — People at Home',
                        'achievement' => 'Uses appropriate Kiswahili vocabulary, greetings, polite expressions and grammatical structures to communicate about family members, household roles and responsibilities.',
                    ],
                    [
                        'topic' => 'Jamii — Community',
                        'achievement' => 'Communicates about important places and activities in the community, gives directions using a compass and applies appropriate vocabulary and grammatical structures.',
                    ],
                    [
                        'topic' => 'Hesabu — Numbers/Arithmetic',
                        'achievement' => 'Uses Kiswahili vocabulary for numbers, calculations, days, months, dates and time and communicates schedules and everyday numerical information accurately.',
                    ],
                    [
                        'topic' => 'Wanyama na Ndege — Animals and Birds',
                        'achievement' => 'Identifies and describes domestic and wild animals and birds, discusses their importance and uses appropriate Kiswahili vocabulary and grammar in speech and writing.',
                    ],
                    [
                        'topic' => 'Mimea na Matunda — Plants and Fruits',
                        'achievement' => 'Uses Kiswahili vocabulary to identify plants, fruits and foods, discusses their importance and communicates appropriately in everyday contexts.',
                    ],
                    [
                        'topic' => 'Biashara — Business/Trade',
                        'achievement' => 'Uses Kiswahili appropriately in buying and selling situations, discusses goods and transactions and applies relevant vocabulary and grammatical structures.',
                    ],
                    [
                        'topic' => 'Nyumba — House/Home',
                        'achievement' => 'Describes parts of a house, household objects and their uses and communicates about the home using appropriate vocabulary and grammatical structures.',
                    ],
                    [
                        'topic' => 'Shuleni — At School',
                        'achievement' => 'Communicates about people, places, activities and objects at school and uses appropriate grammatical structures to describe school experiences.',
                    ],
                    [
                        'topic' => 'Michezo — Sports/Games',
                        'achievement' => 'Discusses different sports, sporting equipment, players and benefits of sports and communicates appropriately about participation and safety in games.',
                    ],
                ],
            ],
            [
                'senior' => 2,
                'subject' => 'ENGLISH LANGUAGE',
                'topics' => [
                    [
                        'topic' => 'Modern Communication Technology',
                        'achievement' => 'Communicates effectively about modern communication technologies, explains their uses, advantages and limitations, and uses appropriate language to discuss technology in everyday life.',
                    ],
                    [
                        'topic' => 'Celebrations',
                        'achievement' => 'Talks and writes about different celebrations and produces appropriate plans, invitations, descriptions and accounts related to celebrations.',
                    ],
                    [
                        'topic' => 'Parents and Children',
                        'achievement' => 'Discusses parent-child relationships, responsibilities and challenges and communicates ideas and opinions appropriately in speech and writing.',
                    ],
                    [
                        'topic' => 'Anti-Corruption',
                        'achievement' => 'Identifies different forms and causes of corruption, explains their dangers and communicates practical ways of promoting integrity and combating corruption.',
                    ],
                    [
                        'topic' => 'Human Rights, Gender and Responsibilities',
                        'achievement' => 'Discusses human rights, gender and responsibilities, understands spoken and written arguments on related issues and expresses informed opinions respectfully.',
                    ],
                    [
                        'topic' => 'Tourism, Maps and Giving Directions',
                        'achievement' => 'Gives and follows directions to tourist sites, researches and communicates information about tourism and explains the importance of tourism.',
                    ],
                    [
                        'topic' => 'Leisure',
                        'achievement' => 'Communicates effectively about leisure activities, discusses preferences and experiences and uses appropriate vocabulary and language structures in different contexts.',
                    ],
                    [
                        'topic' => 'Appearance and Grooming',
                        'achievement' => 'Describes personal appearance and grooming practices and communicates appropriately about hygiene, presentation and responsible personal care.',
                    ],
                ],
            ],
            [
                'senior' => 2,
                'subject' => 'MATHEMATICS',
                'topics' => [
                    [
                        'topic' => 'Mappings and Relations',
                        'achievement' => 'Represents and interprets relations and functions using mappings and arrow diagrams and determines whether mappings represent functions.',
                    ],
                    [
                        'topic' => 'Vectors and Translation',
                        'achievement' => 'Represents and manipulates vectors and uses them to describe translations and solve problems involving magnitude, direction and movement.',
                    ],
                    [
                        'topic' => 'Graphs',
                        'achievement' => 'Constructs, reads and interprets graphs and uses graphical representations to describe relationships and solve practical problems.',
                    ],
                    [
                        'topic' => 'Numerical Concept 1 — Indices and Logarithms',
                        'achievement' => 'Applies laws of indices and basic logarithmic relationships to simplify expressions and solve numerical problems.',
                    ],
                    [
                        'topic' => 'Inequalities and Regions',
                        'achievement' => 'Represents and solves inequalities and uses number lines and graphical regions to communicate sets of possible solutions.',
                    ],
                    [
                        'topic' => 'Algebra 2',
                        'achievement' => 'Manipulates algebraic expressions, including quadratic expressions, factorises appropriate expressions and applies algebraic techniques to solve problems.',
                    ],
                    [
                        'topic' => 'Similarities and Enlargement',
                        'achievement' => 'Identifies similar figures, determines scale factors and applies similarity and enlargement to solve problems involving lengths, areas and volumes.',
                    ],
                    [
                        'topic' => 'Circle',
                        'achievement' => 'Applies properties and formulae associated with circles to determine circumference, area and related quantities in mathematical and practical situations.',
                    ],
                    [
                        'topic' => 'Rotation',
                        'achievement' => 'Performs and interprets rotations of geometrical figures and determines centres, angles and coordinates of rotated images.',
                    ],
                    [
                        'topic' => 'Length and Area Properties of Two-Dimensional Geometrical Figures',
                        'achievement' => 'Calculates and applies perimeter, length and area properties of two-dimensional figures to solve practical problems.',
                    ],
                    [
                        'topic' => 'Nets, Areas and Volumes of Solids',
                        'achievement' => 'Constructs and interprets nets and calculates surface areas and volumes of common three-dimensional solids.',
                    ],
                    [
                        'topic' => 'Numerical Concept 2 — Indices, Logarithms and Surds',
                        'achievement' => 'Manipulates indices, logarithms and surds and applies their properties to simplify expressions and solve numerical problems.',
                    ],
                    [
                        'topic' => 'Set Theory',
                        'achievement' => 'Represents sets using appropriate notation and diagrams and applies set operations to solve mathematical and real-life problems.',
                    ],
                ],
            ],
            [
                'senior' => 2,
                'subject' => 'HISTORY AND POLITICAL EDUCATION',
                'topics' => [
                    [
                        'topic' => 'Colonial Administrative Systems in East Africa',
                        'achievement' => 'Explains the major colonial administrative systems used in East Africa, why they were adopted and their effects on East African societies.',
                    ],
                    [
                        'topic' => 'Colonial Economy in East Africa',
                        'achievement' => 'Explains the organisation and effects of the colonial economy, including taxation, labour, transport, agriculture, trade and infrastructure.',
                    ],
                    [
                        'topic' => 'World Wars and Their Impact in East Africa',
                        'achievement' => 'Explains East African involvement in the World Wars and evaluates their political, social and economic effects on East African societies.',
                    ],
                    [
                        'topic' => 'Struggle for Independence in East Africa',
                        'achievement' => 'Explains the causes, processes, personalities and organisations involved in the struggle for independence and compares the experiences of East African territories.',
                    ],
                    [
                        'topic' => 'Post-Independence Socio-Economic Challenges in East Africa',
                        'achievement' => 'Identifies and evaluates major political, social and economic challenges faced by East African states after independence and proposes appropriate responses.',
                    ],
                    [
                        'topic' => 'Civil Society and Non-Governmental Organisations in East Africa',
                        'achievement' => 'Explains the role of civil society and NGOs in East African development and evaluates their contributions and challenges.',
                    ],
                    [
                        'topic' => 'Changing Land Tenure Systems in East Africa',
                        'achievement' => 'Explains changes in land ownership and management systems and evaluates their historical and contemporary effects on communities in East Africa.',
                    ],
                ],
            ],
            [
                'senior' => 2,
                'subject' => 'GEOGRAPHY',
                'topics' => [
                    [
                        'topic' => 'Mining in East Africa',
                        'achievement' => 'Identifies major minerals and mining areas in East Africa and explains mining methods, importance, problems and environmental effects.',
                    ],
                    [
                        'topic' => 'Development of Manufacturing Industries in East Africa',
                        'achievement' => 'Explains factors influencing industrial development in East Africa and evaluates the contribution, challenges and future potential of manufacturing.',
                    ],
                    [
                        'topic' => 'Mining and Manufacturing Industries in North America',
                        'achievement' => 'Explains the distribution, development and importance of mining and manufacturing industries in North America and compares them with East African industries.',
                    ],
                    [
                        'topic' => 'Sustainable Use of Fisheries Resources in East Africa',
                        'achievement' => 'Explains the distribution, development and importance of fishing in East Africa and evaluates methods of conserving and sustainably using fisheries resources.',
                    ],
                    [
                        'topic' => 'Tourism in East Africa',
                        'achievement' => 'Explains the distribution and importance of tourist attractions, factors affecting tourism and measures for promoting sustainable tourism in East Africa.',
                    ],
                    [
                        'topic' => 'Wildlife Conservation, Forests and Fishing',
                        'achievement' => 'Explains the importance of wildlife and forests, identifies threats to natural resources and evaluates conservation and sustainable-use strategies.',
                    ],
                    [
                        'topic' => 'Population and Urbanisation in East Africa',
                        'achievement' => 'Explains population distribution and growth, factors influencing urbanisation and the social, economic and environmental effects of population growth and urban development.',
                    ],
                    [
                        'topic' => 'Population and Urbanisation in North America',
                        'achievement' => 'Examines population patterns and urban development in North America and compares them with population and urbanisation patterns in East Africa.',
                    ],
                    [
                        'topic' => 'Transport and Communication in East Africa',
                        'achievement' => 'Explains major transport and communication systems, factors influencing their development and their contribution to regional economic and social development.',
                    ],
                    [
                        'topic' => 'Trade Within and Outside East Africa',
                        'achievement' => 'Explains types, patterns and importance of internal and external trade and evaluates factors affecting East African trade and possible ways of improving it.',
                    ],
                ],
            ],
            [
                'senior' => 2,
                'subject' => 'PHYSICS',
                'topics' => [
                    [
                        'topic' => 'Work, Energy and Power',
                        'achievement' => 'Explains and applies relationships between work, energy and power and uses these concepts to explain the operation and efficiency of simple machines.',
                    ],
                    [
                        'topic' => 'Turning Effect of Forces, Centre of Gravity and Stability',
                        'achievement' => 'Explains turning effects, determines centre of gravity and applies principles of moments and stability to practical situations.',
                    ],
                    [
                        'topic' => 'Pressure in Solids and Fluids',
                        'achievement' => 'Explains pressure in solids and fluids, performs appropriate calculations and applies pressure principles to everyday devices and situations.',
                    ],
                    [
                        'topic' => 'Mechanical Properties of Materials and Hooke\'s Law',
                        'achievement' => 'Investigates mechanical properties of materials and applies Hooke\'s law to describe and calculate deformation caused by forces.',
                    ],
                    [
                        'topic' => 'Reflection of Light by Curved Surfaces',
                        'achievement' => 'Explains image formation by concave and convex mirrors and applies principles of reflection to practical optical devices.',
                    ],
                    [
                        'topic' => 'Magnets and Magnetic Fields',
                        'achievement' => 'Investigates magnetic materials, magnetic poles and fields and explains how the Earth behaves as a magnet and how magnets are used for navigation.',
                    ],
                    [
                        'topic' => 'Electrostatics',
                        'achievement' => 'Explains static electrical charge, charging processes and everyday effects of electrostatics and applies this knowledge to phenomena such as lightning and lightning conductors.',
                    ],
                    [
                        'topic' => 'The Solar System',
                        'achievement' => 'Explains the relative movements of the Sun, Earth and Moon and relates these movements to phenomena such as phases, eclipses and other effects observed on Earth.',
                    ],
                ],
            ],
            [
                'senior' => 2,
                'subject' => 'BIOLOGY',
                'topics' => [
                    [
                        'topic' => 'Physical and Chemical Properties of Soil',
                        'achievement' => 'Investigates and explains the physical and chemical properties of soil and relates these properties to soil fertility and plant growth.',
                    ],
                    [
                        'topic' => 'Soil Erosion and Conservation: Causes, Effects and Prevention',
                        'achievement' => 'Explains causes and effects of soil erosion and evaluates practical methods of maintaining soil fertility and conserving soil resources.',
                    ],
                    [
                        'topic' => 'Nutrition Types and Nutrient Compounds',
                        'achievement' => 'Distinguishes nutritional modes and identifies major nutrient compounds, their sources, functions and importance to organisms.',
                    ],
                    [
                        'topic' => 'Nutrition in Green Plants',
                        'achievement' => 'Explains photosynthesis and investigates how plants use light, water and carbon dioxide to manufacture food.',
                    ],
                    [
                        'topic' => 'Nutrition in Mammals',
                        'achievement' => 'Explains the processes of feeding and digestion in mammals and relates nutrients and digestive processes to growth, health and energy supply.',
                    ],
                    [
                        'topic' => 'Transport in Plants',
                        'achievement' => 'Explains how water, mineral salts and manufactured food move through plants and applies knowledge of diffusion, osmosis and active transport.',
                    ],
                    [
                        'topic' => 'Transport in Animals',
                        'achievement' => 'Explains the need for transport systems in animals and describes how the circulatory system transports substances efficiently around the body.',
                    ],
                ],
            ],
            [
                'senior' => 2,
                'subject' => 'CHEMISTRY',
                'topics' => [
                    [
                        'topic' => 'Acids and Alkalis',
                        'achievement' => 'Identifies and explains properties of acids and alkalis, uses indicators appropriately and applies knowledge of acidity and alkalinity to practical situations.',
                    ],
                    [
                        'topic' => 'Salts',
                        'achievement' => 'Explains how salts are formed and prepares and identifies appropriate salts using suitable chemical reactions and laboratory techniques.',
                    ],
                    [
                        'topic' => 'The Periodic Table',
                        'achievement' => 'Uses the Periodic Table to identify elements, understand their organisation and relate position to selected properties and patterns.',
                    ],
                    [
                        'topic' => 'Carbon in the Environment',
                        'achievement' => 'Explains the importance and movement of carbon in the environment, including carbon compounds, the carbon cycle and environmental implications.',
                    ],
                    [
                        'topic' => 'The Reactivity Series',
                        'achievement' => 'Arranges metals according to their reactivity, predicts reactions and applies the reactivity series to extraction, displacement and practical uses of metals.',
                    ],
                ],
            ],
            [
                'senior' => 2,
                'subject' => 'PHYSICAL EDUCATION',
                'topics' => [
                    [
                        'topic' => 'Factors in Performance of Physical Activities',
                        'achievement' => 'Identifies and evaluates personal and environmental factors that influence performance and uses this understanding to improve participation.',
                    ],
                    [
                        'topic' => 'Physical Fitness',
                        'achievement' => 'Performs appropriate fitness exercises and develops a basic personal fitness programme supporting lifelong physical wellbeing.',
                    ],
                    [
                        'topic' => 'Basic Skills in Educational Gymnastics',
                        'achievement' => 'Demonstrates rolling, balancing and related gymnastic skills safely and with appropriate coordination and control.',
                    ],
                    [
                        'topic' => 'Agreeable and Disagreeable Behaviour',
                        'achievement' => 'Distinguishes positive and negative behaviour in society and sport and demonstrates appropriate conduct, cooperation, respect and fair play.',
                    ],
                    [
                        'topic' => 'Substance Abuse and Sports',
                        'achievement' => 'Explains the effects and dangers of substance abuse in sport and identifies appropriate prevention and support measures.',
                    ],
                    [
                        'topic' => 'Basic Jumping and Throwing Skills',
                        'achievement' => 'Demonstrates fundamental athletics jumping and throwing techniques while observing appropriate safety procedures.',
                    ],
                    [
                        'topic' => 'Leisure and Recreation',
                        'achievement' => 'Explains the value of active leisure and recreation and makes appropriate choices that promote physical, mental and social wellbeing.',
                    ],
                    [
                        'topic' => 'Basic Skills in Handball',
                        'achievement' => 'Improvises appropriate equipment, demonstrates fundamental handball skills and applies them effectively and safely in game situations.',
                    ],
                    [
                        'topic' => 'Basic Skills in Soccer',
                        'achievement' => 'Improvises equipment where appropriate, demonstrates fundamental soccer skills and applies acquired techniques and rules during games.',
                    ],
                ],
            ],
            [
                'senior' => 2,
                'subject' => 'RELIGIOUS EDUCATION',
                'topics' => [
                    [
                        'topic' => 'Respect for Human Life',
                        'achievement' => 'Explains Islamic teachings concerning the sanctity, dignity and protection of human life and applies these principles to relationships and responsible community living.',
                    ],
                    [
                        'topic' => 'Marriage',
                        'achievement' => 'Explains the Islamic concept, purposes, responsibilities and values of marriage and relates them to family and community wellbeing.',
                    ],
                    [
                        'topic' => 'Family',
                        'achievement' => 'Explains the structure, responsibilities and importance of the family in Islam and demonstrates understanding of values that promote healthy family relationships.',
                    ],
                ],
            ],
            [
                'senior' => 2,
                'subject' => 'ENTREPRENEURSHIP',
                'topics' => [
                    [
                        'topic' => 'Legal Forms of Business Ownership',
                        'achievement' => 'Identifies and compares different legal forms of business ownership and evaluates their characteristics, advantages, disadvantages and suitability for different enterprises.',
                    ],
                    [
                        'topic' => 'Production in Business',
                        'achievement' => 'Explains the production process, distinguishes needs from wants, and evaluates specialisation, division of labour, diversification, raw materials, tools, machinery and equipment used in production.',
                    ],
                    [
                        'topic' => 'Marketing in a Small and Medium Business Enterprise (SME)',
                        'achievement' => 'Applies basic marketing principles, conducts simple market research, identifies distribution channels, develops promotional strategies and explains consumer protection.',
                    ],
                    [
                        'topic' => 'Money and Financial Institutions',
                        'achievement' => 'Explains the functions of money and financial institutions, including central banks, commercial banks, SACCOs, microfinance institutions and electronic banking.',
                    ],
                    [
                        'topic' => 'Taxation',
                        'achievement' => 'Explains the meaning, purpose and basic principles of taxation and relates taxation to government revenue, business operations and national development.',
                    ],
                ],
            ],
            [
                'senior' => 2,
                'subject' => 'KISWAHILI',
                'topics' => [
                    [
                        'topic' => 'Uongozi katika Jamii — Leadership in Society',
                        'achievement' => 'Uses Kiswahili vocabulary related to leadership and democracy, explains responsibilities and qualities of leaders and communicates appropriately about elections and leadership.',
                    ],
                    [
                        'topic' => 'Afya na Usafi — Health and Hygiene',
                        'achievement' => 'Uses appropriate Kiswahili vocabulary to discuss health, hygiene, disease prevention and personal cleanliness and communicates relevant information clearly.',
                    ],
                    [
                        'topic' => 'Sherehe katika Familia — Family Celebrations',
                        'achievement' => 'Describes different family and community celebrations, explains their importance and communicates appropriately about cultural practices and social responsibilities.',
                    ],
                    [
                        'topic' => 'Salamu na Adabu — Greetings and Courtesy',
                        'achievement' => 'Uses a wider range of greetings and courteous expressions appropriately and applies relevant grammatical structures in spoken and written communication.',
                    ],
                    [
                        'topic' => 'Hesabu — Numbers and Arithmetic',
                        'achievement' => 'Uses Kiswahili accurately to communicate numbers, calculations, dates, fractions and shapes and applies relevant vocabulary and grammatical structures.',
                    ],
                    [
                        'topic' => 'Usafiri — Transport',
                        'achievement' => 'Uses Kiswahili vocabulary to describe means of transport, road safety and transport systems and communicates information about safe travel effectively.',
                    ],
                ],
            ],
            [
                'senior' => 3,
                'subject' => 'ENGLISH LANGUAGE',
                'topics' => [
                    [
                        'topic' => 'Childhood Memories',
                        'achievement' => 'Narrates and writes about childhood experiences using appropriate habitual-past structures, descriptive language, similes, metaphors and coherent sequencing.',
                    ],
                    [
                        'topic' => 'School Clubs',
                        'achievement' => 'Explains the purpose, organisation and benefits of school clubs and communicates persuasively about club activities, roles and membership.',
                    ],
                    [
                        'topic' => 'Integrity',
                        'achievement' => 'Identifies and discusses behaviours that demonstrate honesty and integrity and communicates reasoned views about ethical behaviour in personal and public life.',
                    ],
                    [
                        'topic' => 'Identity Crisis',
                        'achievement' => 'Reflects on personal identity, cultural background and personal qualities, communicates sensitively about differences and demonstrates respect for individual uniqueness.',
                    ],
                    [
                        'topic' => 'Relationships and Emotions',
                        'achievement' => 'Describes different relationships, expresses emotions appropriately and communicates advice, opinions and personal experiences using suitable spoken and written English.',
                    ],
                    [
                        'topic' => 'Patriotism',
                        'achievement' => 'Discusses the meaning, symbols and practices of patriotism and communicates persuasive ideas about contributing positively to the development of Uganda.',
                    ],
                    [
                        'topic' => 'Further Education',
                        'achievement' => 'Researches further and higher education opportunities, explains admission requirements and communicates informed choices about education and career pathways.',
                    ],
                    [
                        'topic' => 'Banking and Money',
                        'achievement' => 'Uses appropriate English to communicate about banking, saving, borrowing, money management and financial transactions in practical situations.',
                    ],
                ],
            ],
            [
                'senior' => 3,
                'subject' => 'MATHEMATICS',
                'topics' => [
                    [
                        'topic' => 'Equation of a Straight Line',
                        'achievement' => 'Determines and interprets equations of straight lines and uses gradients, intercepts and coordinate relationships to solve practical problems.',
                    ],
                    [
                        'topic' => 'Trigonometry 1',
                        'achievement' => 'Derives and applies the sine, cosine and tangent ratios to solve problems involving right-angled triangles, angles of elevation and depression.',
                    ],
                    [
                        'topic' => 'Data Collection / Display',
                        'achievement' => 'Collects, organises, represents and interprets data using measures of central tendency, measures of spread, frequency tables, histograms and cumulative-frequency curves.',
                    ],
                    [
                        'topic' => 'Vectors',
                        'achievement' => 'Represents and operates with vectors and applies vector methods to determine direction, magnitude, ratios, collinearity and geometric relationships.',
                    ],
                    [
                        'topic' => 'Ratios and Proportions',
                        'achievement' => 'Applies ratios, rates, direct and inverse proportion and related relationships to solve mathematical and real-life problems.',
                    ],
                    [
                        'topic' => 'Business Mathematics',
                        'achievement' => 'Applies percentages, interest, taxation, currency exchange and other financial mathematics to solve practical business and personal-finance problems.',
                    ],
                    [
                        'topic' => 'Trigonometry 2',
                        'achievement' => 'Applies trigonometric functions beyond basic right-angled triangles, including angles greater than 90° and the sine and cosine rules, to solve real-life problems.',
                    ],
                    [
                        'topic' => 'Matrices',
                        'achievement' => 'Represents and manipulates matrices, performs appropriate matrix operations, finds determinants and inverses and applies matrices to solve problems.',
                    ],
                    [
                        'topic' => 'Matrix Transformations',
                        'achievement' => 'Uses transformation matrices to represent reflections, rotations and enlargements, determines images and inverse transformations and interprets determinant/area relationships.',
                    ],
                    [
                        'topic' => 'Simultaneous Equations',
                        'achievement' => 'Forms and solves simultaneous equations using substitution, elimination, graphical and matrix methods and interprets solutions in real-life contexts.',
                    ],
                    [
                        'topic' => 'Probability',
                        'achievement' => 'Determines theoretical and experimental probabilities and applies probability spaces, probability trees and Venn diagrams to solve problems involving events.',
                    ],
                    [
                        'topic' => 'Quadratic Equations',
                        'achievement' => 'Solves quadratic equations using factorisation, completing the square and the quadratic formula, forms equations from roots and relates equations to their graphs.',
                    ],
                ],
            ],
            [
                'senior' => 3,
                'subject' => 'HISTORY AND POLITICAL EDUCATION',
                'topics' => [
                    [
                        'topic' => 'The Structure of Government in Uganda',
                        'achievement' => 'Explains the structure, levels and functions of government in Uganda and evaluates how different institutions contribute to governance.',
                    ],
                    [
                        'topic' => 'Local Government Systems in Uganda',
                        'achievement' => 'Explains the structure, responsibilities and operation of local government and evaluates its contribution and challenges in service delivery and community development.',
                    ],
                    [
                        'topic' => 'Constitutionalism in Uganda',
                        'achievement' => 'Explains the importance of the Constitution, national symbols, citizenship, Parliament, judiciary, elections and other constitutional institutions and evaluates their roles in democratic governance.',
                    ],
                    [
                        'topic' => 'Democracy and Leadership in Uganda',
                        'achievement' => 'Explains democratic principles and leadership responsibilities and evaluates how different leaders contribute to community development, governance and conflict resolution.',
                    ],
                    [
                        'topic' => 'The United Nations Organisation and Its Impact on Uganda',
                        'achievement' => 'Explains the history, structure and functions of the United Nations and evaluates its contribution to peace, development and Uganda\'s international relations.',
                    ],
                    [
                        'topic' => 'The Evolution of Human Rights in Uganda',
                        'achievement' => 'Explains human rights, responsibilities and the rule of law, examines historical violations and evaluates measures for protecting rights and promoting peaceful society.',
                    ],
                    [
                        'topic' => 'The Post-Independence Liberation Struggles in Uganda',
                        'achievement' => 'Explains the major political crises and liberation struggles after independence and evaluates their causes, key events, personalities and consequences for Uganda.',
                    ],
                    [
                        'topic' => 'Patriotism in Uganda',
                        'achievement' => 'Explains the meaning and importance of patriotism, identifies patriotic personalities and events and evaluates the contribution of patriotism to Uganda\'s development.',
                    ],
                    [
                        'topic' => 'Key Contributors to Nation Building in Post-Colonial Uganda',
                        'achievement' => 'Identifies significant Ugandans who contributed to political, economic, social, educational, health, religious and cultural development and evaluates their contributions to nation building.',
                    ],
                ],
            ],
            [
                'senior' => 3,
                'subject' => 'GEOGRAPHY',
                'topics' => [
                    [
                        'topic' => 'Further Skills in Map Reading',
                        'achievement' => 'Uses advanced map-reading skills, including interpretation of physical and human features, scale, direction, grid references and other map information to solve geographical problems.',
                    ],
                    [
                        'topic' => 'Location and Size of Africa',
                        'achievement' => 'Locates Africa geographically and describes its position, size, shape and relationship with other continents and major geographical features.',
                    ],
                    [
                        'topic' => 'Relief Regions and Drainage of Africa',
                        'achievement' => 'Identifies and explains the major relief regions and drainage systems of Africa and relates them to human activities and environmental conditions.',
                    ],
                    [
                        'topic' => 'Climate and Vegetation of Africa',
                        'achievement' => 'Explains the major climatic regions and vegetation zones of Africa and analyses the relationship between climate, vegetation and human activities.',
                    ],
                    [
                        'topic' => 'Europe: The Rhine Lands — Location, Relief, Drainage and Climate',
                        'achievement' => 'Describes the geographical characteristics of the Rhine lands and explains how relief, drainage and climate influence human activities in the region.',
                    ],
                    [
                        'topic' => 'Introduction to China — Location, Size, Relief, Drainage and Climate',
                        'achievement' => 'Locates China and explains its size, relief, drainage and climate and their influence on population and economic activities.',
                    ],
                    [
                        'topic' => 'Development of Agriculture in Africa',
                        'achievement' => 'Explains the development, types, distribution and changing patterns of African agriculture and evaluates factors affecting agricultural productivity and improvement.',
                    ],
                    [
                        'topic' => 'Forests, Forest Resources and Forestry in Africa',
                        'achievement' => 'Explains the distribution, importance and uses of African forests and evaluates problems of deforestation and measures for sustainable forest management.',
                    ],
                    [
                        'topic' => 'Irrigation Farming in Africa',
                        'achievement' => 'Explains the need, methods, distribution, benefits and challenges of irrigation farming in Africa and evaluates ways of improving its sustainability.',
                    ],
                    [
                        'topic' => 'Irrigation Farming in China',
                        'achievement' => 'Explains why irrigation is important in China, identifies major irrigated areas and methods and evaluates the role of technology, including hydroponics, in agricultural production.',
                    ],
                    [
                        'topic' => 'Agriculture in the Rhine Lands — Reclaimed Land in the Netherlands and Cattle in Switzerland',
                        'achievement' => 'Explains specialised agricultural practices in the Rhine lands and evaluates how physical conditions and technology have been overcome to support productive agriculture.',
                    ],
                    [
                        'topic' => 'Tourism in Switzerland',
                        'achievement' => 'Explains the development, attractions, importance and challenges of tourism in Switzerland and evaluates factors that support sustainable tourism.',
                    ],
                ],
            ],
            [
                'senior' => 3,
                'subject' => 'PHYSICS',
                'topics' => [
                    [
                        'topic' => 'Linear and Non-Linear Motion',
                        'achievement' => 'Investigates motion and applies distance, time, speed, acceleration, momentum, resultant forces and Newton\'s laws to explain and solve problems involving moving bodies.',
                    ],
                    [
                        'topic' => 'Refraction, Dispersion and Colour',
                        'achievement' => 'Explains refraction and dispersion of light, investigates how light changes direction between media and relates colour to the behaviour of light.',
                    ],
                    [
                        'topic' => 'Lenses and Optical Instruments',
                        'achievement' => 'Explains the behaviour of converging and diverging lenses and applies knowledge of lenses to vision correction and common optical instruments.',
                    ],
                    [
                        'topic' => 'General Wave Properties',
                        'achievement' => 'Explains how waves transfer energy, distinguishes transverse and longitudinal waves and applies relationships among velocity, wavelength and frequency.',
                    ],
                    [
                        'topic' => 'Sound Waves',
                        'achievement' => 'Explains sound as a mechanical wave, investigates its production and transmission and determines the velocity of sound using appropriate methods.',
                    ],
                    [
                        'topic' => 'Heat Quantities and Vapours',
                        'achievement' => 'Applies concepts of heat capacity, specific heat capacity, latent heat, vapour pressure, evaporation and changes of state to explain and solve practical problems.',
                    ],
                    [
                        'topic' => 'Stars and Galaxies',
                        'achievement' => 'Explains the sources of stellar energy, variation in stars and the life cycle of stars and relates stellar development to phenomena such as supernovae, neutron stars and black holes.',
                    ],
                ],
            ],
            [
                'senior' => 3,
                'subject' => 'BIOLOGY',
                'topics' => [
                    [
                        'topic' => 'Gaseous Exchange',
                        'achievement' => 'Explains the process and importance of gaseous exchange in plants and animals and relates specialised structures to efficient exchange of respiratory gases.',
                    ],
                    [
                        'topic' => 'Aerobic and Anaerobic Respiration',
                        'achievement' => 'Explains aerobic and anaerobic respiration, compares their energy yields and relates respiration to everyday biological and practical processes.',
                    ],
                    [
                        'topic' => 'Excretion in Animals',
                        'achievement' => 'Explains the need for excretion and relates major excretory organs and processes in humans to the removal of metabolic wastes and maintenance of internal balance.',
                    ],
                    [
                        'topic' => 'Chemical Coordination in Humans',
                        'achievement' => 'Explains the role of hormones in coordinating body functions, identifies major endocrine glands and relates hormonal imbalance to selected disorders and their management.',
                    ],
                    [
                        'topic' => 'Nervous Coordination in Humans',
                        'achievement' => 'Explains the organisation and functions of the nervous system, reflex actions and nerve communication and evaluates factors that can affect nervous coordination.',
                    ],
                    [
                        'topic' => 'Receptor Organs in Man',
                        'achievement' => 'Explains how the eye and ear detect stimuli, relates their structures to their functions and describes common visual problems and appropriate correction.',
                    ],
                    [
                        'topic' => 'Locomotion in Mammals',
                        'achievement' => 'Explains how the skeleton, joints and muscles interact to produce movement and relates body structures and muscle action to locomotion and physical activity.',
                    ],
                    [
                        'topic' => 'Growth in Plants and Animals',
                        'achievement' => 'Explains growth in plants and animals, identifies factors affecting growth and uses measurements and observations to investigate growth patterns.',
                    ],
                    [
                        'topic' => 'Development in Plants and Animals',
                        'achievement' => 'Explains how organisms develop specialised cells, tissues and organs and relates development to changes in structure and function throughout the life cycle.',
                    ],
                ],
            ],
            [
                'senior' => 3,
                'subject' => 'CHEMISTRY',
                'topics' => [
                    [
                        'topic' => 'Carbon in Life',
                        'achievement' => 'Explains the importance of carbon and carbon compounds in living organisms and everyday materials and relates carbon chemistry to biological and environmental processes.',
                    ],
                    [
                        'topic' => 'Structures and Bonds',
                        'achievement' => 'Explains how atoms combine through different types of chemical bonding and relates bonding and molecular structure to the properties of substances.',
                    ],
                    [
                        'topic' => 'Formulae, Stoichiometry and Mole Concept',
                        'achievement' => 'Uses chemical formulae, equations, relative masses and the mole concept to calculate quantities of substances involved in chemical reactions.',
                    ],
                    [
                        'topic' => 'Properties and Structures of Substances',
                        'achievement' => 'Relates the structure and bonding of substances to their physical and chemical properties and uses particle-level explanations to account for observed behaviour.',
                    ],
                    [
                        'topic' => 'Fossil Fuels',
                        'achievement' => 'Explains the formation, extraction, processing and uses of fossil fuels and evaluates their contribution to energy supply and their environmental impacts.',
                    ],
                    [
                        'topic' => 'Chemical Reactions',
                        'achievement' => 'Represents chemical reactions using balanced equations, identifies reaction patterns and applies chemical principles to explain and predict reactions.',
                    ],
                ],
            ],
            [
                'senior' => 4,
                'subject' => 'ENGLISH LANGUAGE',
                'topics' => [
                    [
                        'topic' => 'Leadership',
                        'achievement' => 'Communicates effectively about leadership in different settings, analyses leadership styles and qualities, and expresses informed views about responsible and effective leadership.',
                    ],
                    [
                        'topic' => 'The Media',
                        'achievement' => 'Interprets information from different media sources, discusses the role and influence of the media and evaluates information critically for reliability, purpose and effect.',
                    ],
                    [
                        'topic' => 'Culture',
                        'achievement' => 'Discusses cultural practices, traditions, beliefs and identity, compares cultural experiences and communicates respectfully about cultural diversity.',
                    ],
                    [
                        'topic' => 'Culture (Continued)',
                        'achievement' => 'Analyses cultural change and its effects on individuals and society and communicates informed views about preserving valuable cultural practices while responding to social change.',
                    ],
                    [
                        'topic' => 'Choosing a Career',
                        'achievement' => 'Researches career options, gathers information through appropriate questioning and communication, evaluates personal interests and abilities and makes informed career choices.',
                    ],
                    [
                        'topic' => 'Applying for a Job',
                        'achievement' => 'Interprets job advertisements, identifies employment requirements, prepares appropriate application documents and demonstrates effective communication and presentation skills for interviews.',
                    ],
                    [
                        'topic' => 'Globalisation',
                        'achievement' => 'Explains and discusses globalisation and its effects on individuals, communities and Uganda, interprets information from different sources and presents evidence-based opinions.',
                    ],
                ],
            ],
            [
                'senior' => 4,
                'subject' => 'MATHEMATICS',
                'topics' => [
                    [
                        'topic' => 'Composite Functions',
                        'achievement' => 'Uses function notation, forms and evaluates composite functions, determines inverse functions and interprets the graphical relationship between a function and its inverse.',
                    ],
                    [
                        'topic' => 'Equations and Inequalities',
                        'achievement' => 'Forms formulae from statements, changes the subject of a formula and solves equations and inequalities, including representing solutions numerically and graphically.',
                    ],
                    [
                        'topic' => 'Linear Programming',
                        'achievement' => 'Formulates linear inequalities from real-life situations, represents feasible regions graphically and uses linear programming to determine optimal solutions.',
                    ],
                    [
                        'topic' => 'Loci',
                        'achievement' => 'Constructs, describes and determines loci satisfying given geometric conditions and applies loci to solve practical and mathematical problems.',
                    ],
                    [
                        'topic' => 'Lines and Planes in Three Dimensions',
                        'achievement' => 'Applies geometric relationships and Pythagoras\' theorem in three dimensions to determine distances and angles involving lines and planes.',
                    ],
                ],
            ],
            [
                'senior' => 4,
                'subject' => 'HISTORY AND POLITICAL EDUCATION',
                'topics' => [
                    [
                        'topic' => 'Lessons from World Economic Transformations',
                        'achievement' => 'Explains major economic transformations associated with capitalism and socialism, analyses factors behind the development of selected countries and evaluates lessons Uganda can apply to its own development.',
                    ],
                    [
                        'topic' => 'Pan-Africanism, Political and Economic Federation in Africa',
                        'achievement' => 'Explains the origins, principles and objectives of Pan-Africanism and evaluates regional and continental organisations and efforts towards African political and economic cooperation.',
                    ],
                    [
                        'topic' => 'Neo-Colonialism in East Africa',
                        'achievement' => 'Explains the concept and manifestations of neo-colonialism in East Africa and evaluates its political, economic, social and developmental effects.',
                    ],
                    [
                        'topic' => 'Globalisation',
                        'achievement' => 'Explains globalisation and its relationship with foreign aid, imports, exports and economic development and evaluates its advantages, disadvantages and impact on Uganda and East Africa.',
                    ],
                    [
                        'topic' => 'Lessons from Liberation Struggles in South Africa',
                        'achievement' => 'Explains apartheid, nationalism and the liberation struggle in South Africa, evaluates resistance strategies and assesses the contributions of major organisations, countries and personalities.',
                    ],
                    [
                        'topic' => 'Peace, Conflicts and Resolution in East Africa',
                        'achievement' => 'Identifies causes and forms of conflict in East Africa, analyses their effects and evaluates traditional and modern mechanisms for conflict prevention, resolution and peace building.',
                    ],
                    [
                        'topic' => 'Topical Review and Final Revision',
                        'achievement' => 'Integrates and applies knowledge from the four-year History and Political Education programme to analyse historical, political and contemporary issues and demonstrate the importance of historical understanding.',
                    ],
                ],
            ],
            [
                'senior' => 4,
                'subject' => 'GEOGRAPHY',
                'topics' => [
                    [
                        'topic' => 'Industrial Development in Germany',
                        'achievement' => 'Explains the distribution, development and importance of industries in Germany, analyses factors responsible for industrial development and evaluates challenges and lessons relevant to industrialisation.',
                    ],
                    [
                        'topic' => 'Reclamation and Agricultural Development in the Netherlands',
                        'achievement' => 'Explains how land has been reclaimed in the Netherlands and evaluates how physical and human factors have supported agricultural development on reclaimed land.',
                    ],
                    [
                        'topic' => 'The Rest of Africa — Location, Position, Size and Political Units',
                        'achievement' => 'Locates Africa accurately, describes its geographical position and size and identifies and interprets its major political regions and countries.',
                    ],
                    [
                        'topic' => 'Relief and Major Landforms and Processes in Africa',
                        'achievement' => 'Explains the formation, distribution and characteristics of major African landforms and relates geomorphic processes to human activities and settlement.',
                    ],
                    [
                        'topic' => 'Climate and Vegetation of Africa',
                        'achievement' => 'Explains the major climatic and vegetation regions of Africa and evaluates the relationship between climate, vegetation and human activities.',
                    ],
                    [
                        'topic' => 'Major Agricultural Practices in Africa',
                        'achievement' => 'Identifies and explains major agricultural practices in Africa and evaluates factors affecting agricultural productivity, sustainability and development.',
                    ],
                    [
                        'topic' => 'Livestock Farming in Africa — Traditional Nomadic Pastoralism and Modern Livestock Ranching',
                        'achievement' => 'Compares traditional pastoralism and modern ranching, explains their distribution and evaluates their advantages, challenges and contribution to African economies.',
                    ],
                    [
                        'topic' => 'Development of Mining and Manufacturing Industries in Africa',
                        'achievement' => 'Explains the distribution, development and importance of mining and manufacturing in Africa and evaluates factors affecting industrial development and environmental sustainability.',
                    ],
                    [
                        'topic' => 'Development of Transport and Communication in Africa',
                        'achievement' => 'Explains the major transport and communication systems in Africa, analyses factors affecting their development and evaluates their contribution to economic integration and development.',
                    ],
                    [
                        'topic' => 'Problems and Prospects of Transport and Communication in Africa',
                        'achievement' => 'Analyses challenges affecting African transport and communication networks and evaluates practical measures for improving connectivity and supporting development.',
                    ],
                ],
            ],
            [
                'senior' => 4,
                'subject' => 'PHYSICS',
                'topics' => [
                    [
                        'topic' => 'Introduction to Current Electricity',
                        'achievement' => 'Explains electric current as the transfer of charge, describes how cells produce electrical energy and constructs and investigates simple series and parallel circuits safely.',
                    ],
                    [
                        'topic' => 'Voltage, Resistance and Ohm\'s Law',
                        'achievement' => 'Explains electrical resistance, investigates the relationship between voltage and current, applies Ohm\'s law and solves practical circuit problems involving resistance.',
                    ],
                    [
                        'topic' => 'Electromagnetic Effects',
                        'achievement' => 'Explains the relationship between electric current and magnetic fields and applies electromagnetic principles to devices such as motors, bells, generators and electromagnets.',
                    ],
                    [
                        'topic' => 'Electric Energy Distribution and Consumption',
                        'achievement' => 'Explains how electrical energy is generated, transmitted and distributed, interprets domestic electrical systems and applies principles of safe and efficient electricity consumption.',
                    ],
                    [
                        'topic' => 'Atomic Models',
                        'achievement' => 'Describes atomic structure, compares major atomic models and uses atomic number, mass number and isotopes to represent and explain atomic systems.',
                    ],
                    [
                        'topic' => 'Nuclear Processes',
                        'achievement' => 'Explains radioactive decay, nuclear fission and fusion, evaluates their applications and assesses the risks associated with radiation and nuclear processes.',
                    ],
                    [
                        'topic' => 'Digital Electronics',
                        'achievement' => 'Explains the principles of digital electronics and applies logic gates, electronic components and digital systems to interpret and construct simple electronic circuits.',
                    ],
                ],
            ],
            [
                'senior' => 4,
                'subject' => 'BIOLOGY',
                'topics' => [
                    [
                        'topic' => 'Asexual Reproduction in Lower Organisms',
                        'achievement' => 'Explains different forms of asexual reproduction in lower organisms and relates their reproductive methods to their survival and multiplication.',
                    ],
                    [
                        'topic' => 'Asexual Reproduction in Plants — Vegetative Reproduction',
                        'achievement' => 'Explains vegetative propagation, demonstrates appropriate propagation methods and evaluates its commercial importance, advantages and limitations.',
                    ],
                    [
                        'topic' => 'Sexual Reproduction in Humans',
                        'achievement' => 'Explains the structure and functions of the human reproductive systems, describes fertilisation and reproductive cycles and relates reproductive processes to responsible health practices.',
                    ],
                    [
                        'topic' => 'Sexual Reproduction in Plants',
                        'achievement' => 'Explains the structure and function of flowers and the processes of pollination, fertilisation, fruit and seed formation and dispersal.',
                    ],
                    [
                        'topic' => 'Meiosis and Its Importance',
                        'achievement' => 'Explains meiosis, identifies its stages and evaluates its significance in maintaining chromosome number and generating variation.',
                    ],
                    [
                        'topic' => 'Genetics and Monohybrid Inheritance',
                        'achievement' => 'Explains heredity and genetic terminology and uses genetic diagrams to predict and interpret monohybrid inheritance patterns.',
                    ],
                    [
                        'topic' => 'Applied Genetics',
                        'achievement' => 'Explains applications of genetics in agriculture, medicine and other areas and evaluates the benefits, limitations and ethical considerations of genetic applications.',
                    ],
                    [
                        'topic' => 'Mutation and Variation',
                        'achievement' => 'Explains mutation and variation, identifies causes and types of variation and evaluates their significance to organisms and populations.',
                    ],
                    [
                        'topic' => 'Evolution',
                        'achievement' => 'Explains major ideas and evidence concerning evolution and evaluates how variation, natural selection and environmental pressures contribute to evolutionary change.',
                    ],
                    [
                        'topic' => 'Concept of Ecology',
                        'achievement' => 'Explains ecological organisation and the relationships between organisms and their physical environment and applies ecological concepts to local environments.',
                    ],
                    [
                        'topic' => 'Food Chains and Food Webs',
                        'achievement' => 'Constructs and interprets food chains, food webs and ecological pyramids and explains interdependence and energy flow within ecosystems.',
                    ],
                    [
                        'topic' => 'Techniques for Sampling Living Organisms',
                        'achievement' => 'Applies appropriate sampling techniques to investigate living organisms, records observations accurately and interprets collected ecological data.',
                    ],
                    [
                        'topic' => 'Changes in Population',
                        'achievement' => 'Explains factors affecting population size, distribution and growth and interprets population changes using appropriate data and ecological concepts.',
                    ],
                    [
                        'topic' => 'Associations in Biological Communities',
                        'achievement' => 'Explains relationships among organisms in communities, including competition, predation and symbiosis, and evaluates their effects on ecosystem balance.',
                    ],
                    [
                        'topic' => 'Humans and the Natural Environment',
                        'achievement' => 'Explains human interactions with the natural environment, evaluates environmental impacts of human activities and proposes sustainable conservation and management strategies.',
                    ],
                ],
            ],
            [
                'senior' => 4,
                'subject' => 'CHEMISTRY',
                'topics' => [
                    [
                        'topic' => 'Oxidation and Reduction Reactions',
                        'achievement' => 'Explains oxidation and reduction in terms of oxygen/hydrogen transfer and electron transfer, identifies redox processes and applies redox principles to chemical and industrial processes.',
                    ],
                    [
                        'topic' => 'Industrial Processes',
                        'achievement' => 'Explains selected industrial chemical processes, identifies raw materials and operating conditions and evaluates their economic, technological and environmental significance.',
                    ],
                    [
                        'topic' => 'Trends in the Periodic Table',
                        'achievement' => 'Identifies and explains trends in physical and chemical properties across periods and groups and relates these trends to atomic structure and electron arrangement.',
                    ],
                    [
                        'topic' => 'Energy Changes During Chemical Reactions',
                        'achievement' => 'Explains energy changes in chemical reactions, distinguishes exothermic and endothermic processes and interprets energy changes in practical and industrial contexts.',
                    ],
                    [
                        'topic' => 'Chemicals for Consumers',
                        'achievement' => 'Evaluates everyday chemical products including soaps, detergents, food additives and medicines and explains their uses, benefits, risks and environmental implications.',
                    ],
                ],
            ],
        ];
    }
}
