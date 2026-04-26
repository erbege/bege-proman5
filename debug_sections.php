$sections = App\Models\RabSection::where('project_id', 1)->limit(50)->get();
foreach ($sections as $s) {
echo "ID: {$s->id} | Code: '{$s->code}' | FullCode: '{$s->full_code}' | ParentID: {$s->parent_id} | Name: {$s->name}\n";
}