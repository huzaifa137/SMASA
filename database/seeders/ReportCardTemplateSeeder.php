<?php

namespace Database\Seeders;

use App\Models\ReportCardTemplate;
use Illuminate\Database\Seeder;

class ReportCardTemplateSeeder extends Seeder
{
    public function run(): void
    {
        ReportCardTemplate::create([
            'school_id' => null, 'is_default' => true, 'is_active' => true,
            'name' => 'Nursery — Playful', 'category' => 'nursery',
            'description' => 'Bright, friendly layout with a big photo and star-style grading — logo centered above the school name.',
            'canvas_width' => 794, 'canvas_height' => 1123,
            'background' => ['color' => '#FFFDF7'],
            'elements' => [
                ['id'=>'logo-1','type'=>'logo','x'=>347,'y'=>30,'w'=>100,'h'=>100,'zIndex'=>2,'props'=>['slot'=>'logo_primary','borderRadius'=>50,'shadow'=>true]],
                ['id'=>'title-1','type'=>'text','x'=>0,'y'=>140,'w'=>794,'h'=>40,'align'=>'center','zIndex'=>2,'props'=>['content'=>'{{school_name}}','fontSize'=>26,'fontWeight'=>700,'color'=>'#FF6B6B']],
                ['id'=>'subtitle-1','type'=>'text','x'=>0,'y'=>182,'w'=>794,'h'=>24,'align'=>'center','zIndex'=>2,'props'=>['content'=>'Nursery Report — {{term}} {{year}}','fontSize'=>14,'color'=>'#666']],
                ['id'=>'photo-1','type'=>'student_photo','x'=>60,'y'=>230,'w'=>140,'h'=>160,'zIndex'=>2,'props'=>['borderRadius'=>16,'border'=>'4px solid #FFD166']],
                ['id'=>'name-1','type'=>'student_field','x'=>230,'y'=>240,'w'=>400,'h'=>28,'zIndex'=>2,'props'=>['field'=>'name','label'=>'Name','fontSize'=>18]],
                ['id'=>'class-1','type'=>'student_field','x'=>230,'y'=>280,'w'=>400,'h'=>24,'zIndex'=>2,'props'=>['field'=>'class','label'=>'Class','fontSize'=>14]],
                ['id'=>'table-1','type'=>'subjects_table','x'=>60,'y'=>430,'w'=>674,'h'=>240,'zIndex'=>2,'props'=>['columns'=>['name','grade','remark'],'zebra'=>true,'headerColor'=>'#FFE8E8','fontSize'=>13]],
                ['id'=>'remarks-1','type'=>'remarks','x'=>60,'y'=>700,'w'=>674,'h'=>80,'zIndex'=>2,'props'=>['role'=>'class_teacher','fontSize'=>13]],
                ['id'=>'sig-1','type'=>'signature','x'=>60,'y'=>820,'w'=>200,'h'=>50,'zIndex'=>2,'props'=>['label'=>'Class Teacher']],
                ['id'=>'sig-2','type'=>'signature','x'=>534,'y'=>820,'w'=>200,'h'=>50,'zIndex'=>2,'props'=>['label'=>'Head of Nursery']],
            ],
        ])->publish();

        ReportCardTemplate::create([
            'school_id' => null, 'is_default' => true, 'is_active' => true,
            'name' => 'Primary — Classic Twin Logo', 'category' => 'primary',
            'description' => 'Traditional format with logos flanking the header, full marks table, attendance and dual signatures.',
            'canvas_width' => 794, 'canvas_height' => 1123,
            'background' => ['color' => '#FFFFFF'],
            'elements' => [
                ['id'=>'logo-l','type'=>'logo','x'=>40,'y'=>30,'w'=>80,'h'=>80,'zIndex'=>2,'props'=>['slot'=>'logo_primary']],
                ['id'=>'logo-r','type'=>'logo','x'=>674,'y'=>30,'w'=>80,'h'=>80,'zIndex'=>2,'props'=>['slot'=>'logo_secondary']],
                ['id'=>'title-1','type'=>'text','x'=>140,'y'=>35,'w'=>514,'h'=>34,'align'=>'center','zIndex'=>2,'props'=>['content'=>'{{school_name}}','fontSize'=>22,'fontWeight'=>700,'color'=>'#1E3A5F']],
                ['id'=>'subtitle-1','type'=>'text','x'=>140,'y'=>72,'w'=>514,'h'=>24,'align'=>'center','zIndex'=>2,'props'=>['content'=>'Primary School Report — {{term}} {{year}}','fontSize'=>13,'color'=>'#444']],
                ['id'=>'divider-1','type'=>'divider','x'=>40,'y'=>130,'w'=>714,'h'=>2,'zIndex'=>2,'props'=>['color'=>'#1E3A5F']],
                ['id'=>'name-1','type'=>'student_field','x'=>40,'y'=>150,'w'=>340,'h'=>24,'zIndex'=>2,'props'=>['field'=>'name','label'=>'Name','fontSize'=>13]],
                ['id'=>'adm-1','type'=>'student_field','x'=>400,'y'=>150,'w'=>340,'h'=>24,'zIndex'=>2,'props'=>['field'=>'admission_no','label'=>'Adm. No','fontSize'=>13]],
                ['id'=>'class-1','type'=>'student_field','x'=>40,'y'=>178,'w'=>340,'h'=>24,'zIndex'=>2,'props'=>['field'=>'class','label'=>'Class','fontSize'=>13]],
                ['id'=>'photo-1','type'=>'student_photo','x'=>640,'y'=>150,'w'=>90,'h'=>100,'zIndex'=>2,'props'=>['borderRadius'=>4]],
                ['id'=>'table-1','type'=>'subjects_table','x'=>40,'y'=>270,'w'=>714,'h'=>320,'zIndex'=>2,'props'=>['columns'=>['name','score','grade','remark'],'zebra'=>true,'fontSize'=>12]],
                ['id'=>'key-1','type'=>'grading_key','x'=>40,'y'=>600,'w'=>714,'h'=>26,'zIndex'=>2,'props'=>['fontSize'=>11]],
                ['id'=>'att-1','type'=>'attendance','x'=>40,'y'=>640,'w'=>300,'h'=>24,'zIndex'=>2,'props'=>['fontSize'=>12]],
                ['id'=>'remarks-1','type'=>'remarks','x'=>40,'y'=>680,'w'=>714,'h'=>70,'zIndex'=>2,'props'=>['role'=>'class_teacher','fontSize'=>12]],
                ['id'=>'remarks-2','type'=>'remarks','x'=>40,'y'=>760,'w'=>714,'h'=>70,'zIndex'=>2,'props'=>['role'=>'head_teacher','fontSize'=>12]],
                ['id'=>'sig-1','type'=>'signature','x'=>40,'y'=>870,'w'=>220,'h'=>50,'zIndex'=>2,'props'=>['label'=>'Class Teacher']],
                ['id'=>'sig-2','type'=>'signature','x'=>534,'y'=>870,'w'=>220,'h'=>50,'zIndex'=>2,'props'=>['label'=>'Head Teacher']],
            ],
        ])->publish();

        ReportCardTemplate::create([
            'school_id' => null, 'is_default' => true, 'is_active' => true,
            'name' => 'Secondary — Formal', 'category' => 'secondary',
            'description' => 'Single centered crest, dense subject table with grade points, formal remarks and signature block.',
            'canvas_width' => 794, 'canvas_height' => 1123,
            'background' => ['color' => '#FFFFFF'],
            'elements' => [
                ['id'=>'logo-1','type'=>'logo','x'=>347,'y'=>25,'w'=>70,'h'=>70,'zIndex'=>2,'props'=>['slot'=>'logo_primary']],
                ['id'=>'title-1','type'=>'text','x'=>0,'y'=>102,'w'=>794,'h'=>30,'align'=>'center','zIndex'=>2,'props'=>['content'=>'{{school_name}}','fontSize'=>20,'fontWeight'=>700,'color'=>'#111']],
                ['id'=>'subtitle-1','type'=>'text','x'=>0,'y'=>134,'w'=>794,'h'=>22,'align'=>'center','zIndex'=>2,'props'=>['content'=>'End of {{term}} Report — {{year}}','fontSize'=>12,'color'=>'#333']],
                ['id'=>'divider-1','type'=>'divider','x'=>40,'y'=>170,'w'=>714,'h'=>1,'zIndex'=>2,'props'=>['color'=>'#000']],
                ['id'=>'name-1','type'=>'student_field','x'=>40,'y'=>190,'w'=>350,'h'=>22,'zIndex'=>2,'props'=>['field'=>'name','label'=>'Name','fontSize'=>12]],
                ['id'=>'class-1','type'=>'student_field','x'=>410,'y'=>190,'w'=>340,'h'=>22,'zIndex'=>2,'props'=>['field'=>'class','label'=>'Class','fontSize'=>12]],
                ['id'=>'adm-1','type'=>'student_field','x'=>40,'y'=>216,'w'=>350,'h'=>22,'zIndex'=>2,'props'=>['field'=>'admission_no','label'=>'Index No','fontSize'=>12]],
                ['id'=>'table-1','type'=>'subjects_table','x'=>40,'y'=>260,'w'=>714,'h'=>420,'zIndex'=>2,'props'=>['columns'=>['name','score','grade','remark'],'zebra'=>false,'headerColor'=>'#e5e5e5','fontSize'=>11]],
                ['id'=>'key-1','type'=>'grading_key','x'=>40,'y'=>700,'w'=>714,'h'=>26,'zIndex'=>2,'props'=>['fontSize'=>10]],
                ['id'=>'remarks-1','type'=>'remarks','x'=>40,'y'=>740,'w'=>714,'h'=>60,'zIndex'=>2,'props'=>['role'=>'class_teacher','fontSize'=>11]],
                ['id'=>'remarks-2','type'=>'remarks','x'=>40,'y'=>810,'w'=>714,'h'=>60,'zIndex'=>2,'props'=>['role'=>'head_teacher','fontSize'=>11]],
                ['id'=>'sig-1','type'=>'signature','x'=>40,'y'=>900,'w'=>220,'h'=>50,'zIndex'=>2,'props'=>['label'=>'Class Teacher']],
                ['id'=>'sig-2','type'=>'signature','x'=>287,'y'=>900,'w'=>220,'h'=>50,'zIndex'=>2,'props'=>['label'=>'Principal']],
                ['id'=>'sig-3','type'=>'signature','x'=>534,'y'=>900,'w'=>220,'h'=>50,'zIndex'=>2,'props'=>['label'=>'Date']],
            ],
        ])->publish();
    }
}
