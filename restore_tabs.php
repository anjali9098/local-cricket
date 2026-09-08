<?php
foreach(['admin', 'local'] as $role) {
    $file = "resources/views/$role/manage-tournament.blade.php";
    if(!file_exists($file)) continue;
    $content = file_get_contents($file);
    
    // Add script back if missing
    if (strpos($content, 'function switchTab(tabId)') === false) {
        $script = '
<script>
    function switchTab(tabId) {
        document.querySelectorAll(".tab-content").forEach(el => el.classList.remove("active"));
        document.getElementById("tab-" + tabId).classList.add("active");
        
        document.querySelectorAll(".tab-btn").forEach(el => el.classList.remove("active"));
        event.currentTarget.classList.add("active");
    }
</script>
';
        $content = str_replace('@endsection', $script . "\n@endsection", $content);
        file_put_contents($file, $content);
    }
}
echo "Done";
