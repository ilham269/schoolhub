<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PpdbQuestion extends Model {protected $fillable=['exam_id','question','type','score','order'];public function options(){return $this->hasMany(PpdbOption::class,'question_id')->orderBy('order');}}
