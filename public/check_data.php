<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
use Illuminate\Support\Facades\DB;

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

echo "--- BANKER (ID 1) ---\n";
$banker = DB::table('registrations')->where('id', 1)->first();
echo "City: " . ($banker->city ?? 'NULL') . ", State: " . ($banker->state ?? 'NULL') . "\n";

echo "\n--- FARMER LOANS ---\n";
$farmerLoans = DB::table('farmer_loans')->get();
foreach ($farmerLoans as $loan) {
    $user = DB::table('registrations')->where('id', $loan->user_id)->first();
    echo "ID: {$loan->id}, UserID: {$loan->user_id}, UserCity: " . ($user->city ?? 'NULL') . ", UserState: " . ($user->state ?? 'NULL') . ", Status: {$loan->status}\n";
}

echo "\n--- STUDENT LOANS ---\n";
$studentLoans = DB::table('student_loans')->get();
foreach ($studentLoans as $loan) {
    $user = DB::table('registrations')->where('id', $loan->user_id)->first();
    echo "ID: {$loan->id}, UserID: {$loan->user_id}, UserCity: " . ($user->city ?? 'NULL') . ", UserState: " . ($user->state ?? 'NULL') . ", Status: {$loan->status}\n";
}
