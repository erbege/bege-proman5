<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Search for project with specific section
$targetSection = App\Models\RabSection::where('name', 'like', '%PERSIAPAN LAPANGAN%')->first();
if (!$targetSection) {
    echo "Section 'PERSIAPAN LAPANGAN' not found in any project.\n";
    $sections = collect();
} else {
    echo "Found target section in Project ID: {$targetSection->project_id}\n";
    $sections = App\Models\RabSection::where('project_id', $targetSection->project_id)->get()->keyBy('id');
}
$output = [];

function traceSection($id, $sections, &$output, $depth = 0)
{
    if (!isset($sections[$id]))
        return;
    $s = $sections[$id];
    $indent = str_repeat(" ", $depth);

    $parentCode = $s->parent_id && isset($sections[$s->parent_id])
        ? $sections[$s->parent_id]->full_code
        : "NULL";

    // Use fresh instance logic to simulate what the app sees
    $calculated = $s->full_code;

    $output[] = "{$indent}ID:{$s->id} | RawCode:[{$s->code}] | ParentID:{$s->parent_id} | ParentFull:{$parentCode} |
CalcFull:[{$calculated}] | Name: " . substr($s->name, 0, 30);

    $children = $sections->where('parent_id', $s->id);
    foreach ($children as $child) {
        traceSection($child->id, $sections, $output, $depth + 1);
    }
}

// Find roots
$roots = $sections->where('parent_id', null);
foreach ($roots as $root) {
    traceSection($root->id, $sections, $output, 0);
}

// Check items for first root
$firstRoot = $roots->first();
if ($firstRoot) {
    $items = App\Models\RabItem::where('rab_section_id', $firstRoot->id)->take(5)->get();
    foreach ($items as $i) {
        $output[] = " ITEM ID:{$i->id} | RawCode:[{$i->code}] | SectionCode:[{$firstRoot->full_code}] |
CalcFull:[{$i->full_code}] | Name:{$i->work_name}";
    }
}

file_put_contents('debug_output.txt', implode("\n", $output));
echo "Done.";