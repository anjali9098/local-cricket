<?php
$file = 'app/Http/Controllers/LocalController.php';
$content = file_get_contents($file);

$append = <<<EOT

    public function markCompleted(\$id)
    {
        \$tournament = Tournament::findOrFail(\$id);
        \$tournament->status = 'completed';
        \$tournament->save();
        return back()->with('success', 'Tournament marked as completed!');
    }
}
EOT;

$content = preg_replace('/}\s*$/', $append, $content);
$content = str_replace("\$tournament->status = 'published';", "\$tournament->status = 'ongoing';", $content);
file_put_contents($file, $content);
echo "Done";
