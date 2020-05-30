<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Employee;
use Carbon\Carbon;
use Validator;
use Auth;
use DataTables;
use Illuminate\Support\Facades\Redis;

class EmployeeController extends Controller
{
    // List Employee dengan filter modify_by agar hanya user
    // yang menambahkan employee saja yang dapat melihat 
    // data employee nya.

    // Data employee disimpan ke redis selama 5 menit, jika
    // lebih dari 5 menit maka harus request lagi dengan
    // menyertakan bearer token.

    // Hanya berlaku untuk user yang sudah login.

    public function index()
    {
        if ($employee = Redis::get('employee.by_user')) {
            return json_decode($employee);
        }
        $filterId = Auth::user()->id;
        $employee = Employee::where('modify_by', '=', $filterId)->get();
     
        Redis::setex('employee.by_user', 60 * 5, $employee);
    }

    // List Employee dengan filter umur dengan
    // ketentuan paramater $x > age dengan limit 100 Employee.

    // Data employee dengan umur $x disimpan ke redis selama 5 menit, dan
    // dimasukkan ke datatable format json. 

    // Jika lebih dari 5 menit maka harus request lagi dengan
    // menyertakan bearer token.

    // Hanya berlaku untuk user yang sudah login.

    public function filter(Request $request, $x)
    {
        if ($DTemployee = Redis::get('employee.by_age')) {
            return $DTemployee;
        }

        $employee = Employee::where('age', '>', "$x")
            ->limit(100)
            ->get();  
        $DTemployee = DataTables::of($employee)->toJson();
     
        Redis::setex('employee.by_age', 60 * 5, $DTemployee);
          
 
    }

    // Menambahkan data employee baru.
    // Simpan data id user yang sedang menambahkan employee ke kolom modify_by.
    // Simpan data waktu/datetime terkini ke kolom update_at.
    // Hanya berlaku untuk user yang sudah login.

    
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fullname' => 'required',
            'nickname' => 'string|min:2|max:20',
            'age' => 'required|numeric',
            'birthdate' => 'required|date_format:Y-m-d',
            'address' => 'required|string|min:10',
            'mobile' => 'required|digits_between:10,12',
            'avatar' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        $new_employee = new Employee;
        $new_employee->full_name = $request->get('fullname');
        $new_employee->nick_name = $request->get('nickname');
        $new_employee->age = $request->get('age');
        $new_employee->birth_date = $request->get('birthdate');
        $new_employee->address = $request->get('address');
        $new_employee->mobile = $request->get('mobile');

        $avatar = $request->file('avatar');

        if ($avatar) {
            $avatar_path = $avatar->store('avatars', 'public');
            $new_employee->avatar = $avatar_path;
        }

        $new_employee->created_by = Auth::user()->id;
        $new_employee->modify_by = Auth::user()->id;
        $new_employee->created_at = Carbon::now();
        $new_employee->save();

        return response()->json([
            'message' => 'Successfully created a new employee!'
        ],201);

    }

    // Melakukan perubahan data employee.
    // Update kolom modify_by dengan data id user yang sedang login.
    // Update kolom update_at dengan data waktu/datetime saat update.
    // Hanya berlaku untuk user yang sudah login.

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'fullname' => 'required',
            'nickname' => 'string|min:2|max:20',
            'age' => 'required|numeric',
            'birthdate' => 'required|date_format:Y-m-d',
            'address' => 'required|string|min:10',
            'mobile' => 'required|digits_between:10,12',
            'avatar' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        $employee = Employee::findOrFail($id);
        $employee->full_name = $request->get('fullname');
        $employee->nick_name = $request->get('nickname');
        $employee->age = $request->get('age');
        $employee->birth_date = $request->get('birthdate');
        $employee->address = $request->get('address');
        $employee->mobile = $request->get('mobile');

        $new_avatar = $request->file('avatar');

        if ($new_avatar) {
            if ($employee->avatar && file_exists(storage_path('app/public/avatars'.$employee->avatar))) {
                \Storage::delete('public/avatars', $employee->avatar);
            }
            $new_avatar_path = $new_avatar->store('avatars', 'public');
            $employee->avatar = $new_avatar_path;
        }
        $employee->modify_by = Auth::user()->id;
        $employee->updated_at = Carbon::now();
        $employee->save();

        return response()->json([
            'message' => 'Employee successfully updated!'
        ],201);
    }

    // Menghapus sementara data employee.
    // Mengupdate data datetime di kolom deleted_at.
    // pada employee bersangkutan.
   
    public function destroy($id)
    {
        $employee = Employee::findOrFail($id);
        $employee->delete();
        return response()->json([
            'message' => 'Employee successfully deleted!'
        ],201);
    }
}
