$sections = App\Models\RabSection::where('project_id', 1)->get()->keyBy('id');

function traceSection($id, $sections, $depth = 0) {
if (!isset($sections[$id])) return;
$s = $sections[$id];
$indent = str_repeat(" ", $depth);

// Manual calculation check
$parts = explode('.', $s->code);
$lastPart = end($parts);

$parentCode = $s->parent_id && isset($sections[$s->parent_id])
? $sections[$s->parent_id]->full_code
: "NULL";

$calculated = $s->full_code;

echo "{$indent}ID:{$s->id} | RawCode:[{$s->code}] | ParentID:{$s->parent_id} | ParentFull:{$parentCode} |
CalcFull:[{$calculated}] | Name: " . substr($s->name, 0, 20) . "\n";

$children = $sections->where('parent_id', $s->id);
foreach ($children as $child) {
traceSection($child->id, $sections, $depth + 1);
}
}

// Find roots
$roots = $sections->where('parent_id', null);
foreach ($roots as $root) {
traceSection($root->id, $sections);
}