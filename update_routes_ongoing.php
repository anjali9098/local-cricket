<?php
$file = 'routes/web.php';
$content = file_get_contents($file);

// Add admin route
$adminRoutes = "Route::post('/admin/tournament/{id}/mark-ongoing', [AdminController::class, 'markOngoing'])->name('admin.mark-ongoing');\n    Route::post('/admin/tournament/{id}/mark-completed'";
$content = str_replace("Route::post('/admin/tournament/{id}/mark-completed'", $adminRoutes, $content);

// Add local route
$localRoutes = "Route::post('/local/tournament/{id}/mark-ongoing', [LocalController::class, 'markOngoing'])->name('local.mark-ongoing');\n    Route::post('/local/tournament/{id}/mark-completed'";
$content = str_replace("Route::post('/local/tournament/{id}/mark-completed'", $localRoutes, $content);

file_put_contents($file, $content);
echo "Routes updated";
