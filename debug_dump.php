$sections = App\Models\RabSection::where('project_id', 1)->take(20)->get();
dump("Found sections: " . $sections->count());
foreach ($sections as $s) {
dump("ID: {$s->id} | Code: {$s->code} | Parent: {$s->parent_id} | Name: {$s->name}");
}
// Get items for the section named 'PEKERJAAN PERSIAPAN'
$sec = $sections->first(fn($s) => str_contains($s->name, 'PERSIAPAN'));
if ($sec) {
$items = App\Models\RabItem::where('rab_section_id', $sec->id)->take(5)->get();
dump("Items for section {$sec->name}:");
foreach($items as $i) {
dump("ItemID: {$i->id} | Code: {$i->code} | Name: {$i->work_name}");
}
}