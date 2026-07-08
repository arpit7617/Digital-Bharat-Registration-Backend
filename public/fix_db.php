<?php

// This is a temporary script to create missing database tables.
// Run this by visiting http://localhost:8000/fix_db.php in your browser.

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

echo "<h1>Database Fix Script</h1>";

try {
    // 1. Student Loans
    if (!Schema::hasTable('student_loans')) {
        Schema::create('student_loans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('college_name');
            $table->string('course_name');
            $table->decimal('amount', 15, 2);
            $table->string('status')->default('Pending');
            $table->timestamps();
        });
        echo "<p style='color:green;'>✅ Created 'student_loans' table.</p>";
    } else {
        echo "<p style='color:blue;'>ℹ️ 'student_loans' table already exists.</p>";
    }

    // 2. Business Loans
    if (!Schema::hasTable('business_loans')) {
        Schema::create('business_loans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->decimal('amount', 15, 2);
            $table->string('purpose');
            $table->integer('tenure');
            $table->string('status')->default('Pending');
            $table->timestamps();
        });
        echo "<p style='color:green;'>✅ Created 'business_loans' table.</p>";
    } else {
        echo "<p style='color:blue;'>ℹ️ 'business_loans' table already exists.</p>";
    }

    // 3. Job Postings
    if (!Schema::hasTable('job_postings')) {
        Schema::create('job_postings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('job_title');
            $table->text('description');
            $table->string('salary_range');
            $table->timestamps();
        });
        echo "<p style='color:green;'>✅ Created 'job_postings' table.</p>";
    } else {
        echo "<p style='color:blue;'>ℹ️ 'job_postings' table already exists.</p>";
    }

    // 4. Crop Registrations
    if (!Schema::hasTable('crop_registrations')) {
        Schema::create('crop_registrations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('crop_name');
            $table->decimal('price', 15, 2);
            $table->longText('image_base64')->nullable();
            $table->timestamps();
        });
        echo "<p style='color:green;'>✅ Created 'crop_registrations' table.</p>";
    } else {
        echo "<p style='color:blue;'>ℹ️ 'crop_registrations' table already exists.</p>";
    }

    // 5. Job Applications
    if (!Schema::hasTable('job_applications')) {
        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // Applicant
            $table->unsignedBigInteger('job_id');
            $table->string('status')->default('Pending');
            $table->timestamps();
        });
        echo "<p style='color:green;'>✅ Created 'job_applications' table.</p>";
    } else {
        echo "<p style='color:blue;'>ℹ️ 'job_applications' table already exists.</p>";
    }

    echo "<h3>All missing tables are now ready! You can now submit forms from the app.</h3>";
    echo "<p>Please delete this file (public/fix_db.php) for security.</p>";

} catch (\Exception $e) {
    echo "<p style='color:red;'>❌ Error: " . $e->getMessage() . "</p>";
}
