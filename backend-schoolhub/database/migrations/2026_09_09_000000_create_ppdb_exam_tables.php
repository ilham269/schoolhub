<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::create('calon_siswas', function(Blueprint $t){$t->id();$t->foreignId('user_id')->nullable()->unique()->constrained('users')->nullOnDelete();$t->string('nama');$t->string('email')->unique();$t->string('nisn',10)->unique();$t->string('no_hp',20);$t->string('asal_sekolah');$t->string('jurusan');$t->json('documents')->nullable();$t->enum('status',['Menunggu','Terverifikasi','Ditolak'])->default('Menunggu');$t->timestamps();});
  Schema::create('ppdb_exams', function(Blueprint $t){$t->id();$t->foreignId('created_by')->constrained('users')->cascadeOnDelete();$t->string('title');$t->text('description')->nullable();$t->timestamp('start_at')->nullable();$t->timestamp('end_at')->nullable();$t->unsignedInteger('duration_minutes')->default(60);$t->decimal('passing_score',5,2)->nullable();$t->boolean('is_published')->default(false);$t->boolean('show_result')->default(false);$t->timestamps();});
  Schema::create('ppdb_questions', function(Blueprint $t){$t->id();$t->foreignId('exam_id')->constrained('ppdb_exams')->cascadeOnDelete();$t->text('question');$t->enum('type',['multiple_choice','essay'])->default('multiple_choice');$t->decimal('score',6,2)->default(1);$t->unsignedInteger('order')->default(1);$t->timestamps();});
  Schema::create('ppdb_options', function(Blueprint $t){$t->id();$t->foreignId('question_id')->constrained('ppdb_questions')->cascadeOnDelete();$t->text('option_text');$t->boolean('is_correct')->default(false);$t->unsignedInteger('order')->default(1);$t->timestamps();});
  Schema::create('ppdb_exam_attempts', function(Blueprint $t){$t->id();$t->foreignId('exam_id')->constrained('ppdb_exams')->cascadeOnDelete();$t->foreignId('calon_siswa_id')->constrained('calon_siswas')->cascadeOnDelete();$t->timestamp('started_at');$t->timestamp('submitted_at')->nullable();$t->decimal('score',7,2)->nullable();$t->enum('status',['in_progress','submitted'])->default('in_progress');$t->timestamps();$t->unique(['exam_id','calon_siswa_id']);});
  Schema::create('ppdb_exam_answers', function(Blueprint $t){$t->id();$t->foreignId('attempt_id')->constrained('ppdb_exam_attempts')->cascadeOnDelete();$t->foreignId('question_id')->constrained('ppdb_questions')->cascadeOnDelete();$t->foreignId('option_id')->nullable()->constrained('ppdb_options')->nullOnDelete();$t->text('answer_text')->nullable();$t->boolean('is_correct')->nullable();$t->decimal('score',6,2)->nullable();$t->timestamps();$t->unique(['attempt_id','question_id']);});
 }
 public function down(): void {Schema::dropIfExists('ppdb_exam_answers');Schema::dropIfExists('ppdb_exam_attempts');Schema::dropIfExists('ppdb_options');Schema::dropIfExists('ppdb_questions');Schema::dropIfExists('ppdb_exams');Schema::dropIfExists('calon_siswas');}
};
