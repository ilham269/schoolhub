<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== DEBUG PPDB EXAMS ===\n\n";

// Check exams
$exams = App\Models\PpdbExam::all();
echo "Total Exams: " . $exams->count() . "\n\n";

foreach ($exams as $exam) {
    echo "ID: {$exam->id}\n";
    echo "Title: {$exam->title}\n";
    echo "Published: " . ($exam->is_published ? 'YES' : 'NO') . "\n";
    echo "Start: " . ($exam->start_at ?? 'NULL') . "\n";
    echo "End: " . ($exam->end_at ?? 'NULL') . "\n";
    echo "Duration: {$exam->duration_minutes} minutes\n";
    echo "Questions: " . $exam->questions()->count() . "\n";
    echo "---\n";
}

echo "\n=== CHECK QUERY (like PpdbController->exams()) ===\n\n";

$now = now();
echo "Current time: {$now}\n\n";

$query = App\Models\PpdbExam::where('is_published', true)
    ->where(function($q) use ($now) {
        $q->whereNull('start_at')
          ->orWhere('start_at', '<=', $now);
    })
    ->where(function($q) use ($now) {
        $q->whereNull('end_at')
          ->orWhere('end_at', '>=', $now);
    })
    ->withCount('questions');

echo "SQL: " . $query->toSql() . "\n\n";

$results = $query->get();
echo "Results: " . $results->count() . " exam(s)\n\n";

foreach ($results as $exam) {
    echo "- {$exam->title} ({$exam->questions_count} questions)\n";
}

echo "\n=== CHECK CALON SISWA ===\n\n";

$calonSiswas = App\Models\CalonSiswa::with('user')->get();
echo "Total Calon Siswa: " . $calonSiswas->count() . "\n\n";

foreach ($calonSiswas as $cs) {
    echo "Name: {$cs->nama}\n";
    echo "Email: {$cs->email}\n";
    echo "Status: {$cs->status}\n";
    echo "User ID: " . ($cs->user_id ?? 'NULL') . "\n";
    if ($cs->user) {
        echo "User Role: {$cs->user->role}\n";
    }
    echo "---\n";
}

echo "\nDone!\n";
