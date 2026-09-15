<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== FIXING EXAM DATES ===\n\n";

$exams = App\Models\PpdbExam::all();

foreach ($exams as $exam) {
    echo "Updating exam: {$exam->title}\n";
    echo "  Old published: " . ($exam->is_published ? 'YES' : 'NO') . "\n";
    echo "  Old start: {$exam->start_at}\n";
    echo "  Old end: {$exam->end_at}\n";
    
    // Make sure it's published
    $exam->is_published = true;
    
    // Set start date to yesterday, end date to 30 days from now
    $exam->start_at = now()->subDay();
    $exam->end_at = now()->addDays(30);
    $exam->save();
    
    echo "  New published: YES\n";
    echo "  New start: {$exam->start_at}\n";
    echo "  New end: {$exam->end_at}\n";
    echo "  ✓ Updated!\n\n";
}

echo "Done! All exams are now available.\n";
