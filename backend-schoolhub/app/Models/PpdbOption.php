<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PpdbOption extends Model {protected $fillable=['question_id','option_text','is_correct','order'];protected function casts():array{return ['is_correct'=>'boolean'];}}
