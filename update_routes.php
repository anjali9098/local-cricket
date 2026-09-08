<?php
$file = 'routes/web.php';
$content = file_get_contents($file);

// Add admin delete routes
$adminRoutes = "Route::post('/admin/tournament/{id}/add-match', [AdminController::class, 'addMatch'])->name('admin.add-match');\n    Route::post('/admin/team/{id}/delete', [AdminController::class, 'deleteTeam'])->name('admin.delete-team');\n    Route::post('/admin/player/{id}/delete', [AdminController::class, 'deletePlayer'])->name('admin.delete-player');\n";
$content = preg_replace("/Route::post\('\/admin\/tournament\/\{id\}\/add-match'.*?;/s", $adminRoutes, $content);

// Add local delete routes
$localRoutes = "Route::post('/local/tournament/{id}/add-match', [LocalController::class, 'addMatch'])->name('local.add-match');\n    Route::post('/local/team/{id}/delete', [LocalController::class, 'deleteTeam'])->name('local.delete-team');\n    Route::post('/local/player/{id}/delete', [LocalController::class, 'deletePlayer'])->name('local.delete-player');\n";
$content = preg_replace("/Route::post\('\/local\/tournament\/\{id\}\/add-match'.*?;/s", $localRoutes, $content);

file_put_contents($file, $content);
echo "Routes updated";
