<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class CalonSiswa extends Model {protected $fillable=['user_id','nama','email','nisn','no_hp','asal_sekolah','jurusan','documents','status'];protected function casts():array{return ['documents'=>'array'];}public function user(){return $this->belongsTo(User::class);}}
