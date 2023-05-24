<?php

namespace App\Http\Controllers;

use App\Models\Classes;
use App\Models\Student;
use Illuminate\Http\Request;
use App\Models\extracurricular;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\StudentCreateRequest;

class StudentsController extends Controller
{
    function Students(Request $request) {
    $Keyword = $request->Keyword;
        $data = Student::where('Name','LIKE','%'.$Keyword.'%')
        ->orWhere('NIS','LIKE','%'.$Keyword.'%')
        //ambil data dari TABLE CLASS UNTUK SEARCH
        ->orWhereHas('Class',function($query) use($Keyword){
            $query->where('Name','LIKE','%'.$Keyword.'%');
        })
        ->paginate(10);
        return view('Students',['data' => $data]);
    }
    public function show($id) {
        $data = Student::with(['Class.Teachers','extracurriculars'])
        ->findOrFail($id);
        return view('student-detail',['dataDetail'=>$data]);

    }
    function create() {
        $data = Classes::select('id', 'Name')->get();
        $extra = extracurricular::get(['Name','id']);
        return view('insert-data', ['class' => $data, 'extra' =>$extra]);
    }                  //EDIT DATA FOR BLADE PHP !!
    function Edit(Request $request , $id) {
        $student = Student::findOrFail($id);
        $extra = extracurricular::get(['Name','id']);
        $class = Classes::where('id','!=' , $student->Class_id)->get(['id','Name']);
        return view('student-edit', ['data' => $student , 'class' => $class , 'extra'=>$extra]);
    }                      //CREATE DATA !!
    public function store(StudentCreateRequest $request) {
        $student = Student::create($request->all());
        $student->extracurriculars()->attach($request->extra);
        if ($student) {
            Session::flash('status','success');
            Session::flash('massage','Data Berhasil Di Tambahkan!');
        }
        return redirect('/students');

    }                      //UPDATE DATA !!
    public function update(Request $request , $id) {
        $student = Student::findOrFail($id)->extracurriculars()->sync($request->extra);
        $student = Student::findOrFail($id)->update($request->all());
        if ($student) {
            Session::flash('status','success');
            Session::flash('massage','Data Berhasil Di Update');
        }
        return redirect('/students');
    }                  //DELETE DATA !!
public function delete($id) {
    $data = Student::findOrFail($id);
    return view('confirm-delete',['delete'=>$data]);
}
public function destroy($id) {
    $delete = Student::findOrFail($id);
    $delete->extracurriculars()->detach();
    $delete->delete();
    if ($delete == true) {
        Session::flash('status','success');
        Session::flash('massage','Data Telah Di Hapus!');
        return redirect('/students');
    }
}
public function softdeletedata(){
    $data =Student::onlyTrashed()->get();
    return view('student-deleted-data',['data'=>$data]);
}
public function restoreDataStudent($id){
    $deletedStudent = Student::withTrashed()->where('id', $id)->restore();
    if($deletedStudent){
        Session::flash('status','success');
        Session::flash('massage','Data Success Restored');
    }
    return redirect('/students');
}


}
