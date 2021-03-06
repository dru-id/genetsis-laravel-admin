<?php namespace Genetsis\Admin\Controllers;

use App\Models\Action;
use Genetsis\Admin\Models\Role;
use Genetsis\Admin\Models\User;
use Genetsis\Druid\Rest\RestApi;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;

class UserController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        \View::share('section', 'users');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $data = User::orderBy('id','DESC')->paginate(5);
        return view('genetsis-admin::pages.users.index',compact('data'))
            ->with('i', ($request->input('page', 1) - 1) * 20);
    }


    /**
     * Api for Datatable - get Users
     * @return mixed
     * @throws \Exception
     */
    public function get(Request $request) {
        if ($request->ajax()) {
            $users = User::with('roles')->get()->filter(function ($user){
                if (Auth::user()->hasRole('SuperAdmin')) {
                    return true;
                } else {
                    return !$user->hasRole(['SuperAdmin','Admin']);
                }
            })->all();

            return DataTables::of($users)
                ->addColumn('options', function ($user) {
                    return '
                        <div class="actions" style="width:40px">
                        <a class="actions__item zmdi zmdi-edit" href="'.route('users.edit',$user->id).'"></a>
                        </div>                        
                        ';
                })
                ->addColumn('roles', function ($user){
                    $roles = '';
                    if(!empty($user->getRoleNames())) {
                        foreach($user->getRoleNames() as $v) {
                            $roles .= '<label class="badge badge-success">'.$v.'</label>';
                        }
                    }
                    return $roles;
                })
                ->addColumn('delete', function ($user) {
                    return '
                        <div class="actions">                                                
                        <a class="actions__item zmdi zmdi-delete del" data-id="'.$user->id.'"></a>
                        </div>                        
                        ';
                })
                ->rawColumns(['options','delete','roles'])
                ->make(true);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $roles = Role::all()->filter(function($role){
            if (Auth::user()->hasRole('SuperAdmin')) {
                return true;
            } else {
                return !in_array($role->name, ['SuperAdmin', 'Admin']);
            }
        })->pluck('name', 'name')->all();

        return view('genetsis-admin::pages.users.create',compact('roles'));
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|same:confirm-password',
            'roles' => 'required'
        ]);

        $input = $request->all();
        $input['password'] = bcrypt($input['password']);

        $user = User::create($input);
        $user->assignRole($request->input('roles'));


        return redirect()->route('users.home')
            ->with('success','User created successfully');
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::find($id);
        return view('genetsis-admin::pages.users.show',compact('user'));
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = User::find($id);
        $roles = Role::all()->filter(function($role){
            if (Auth::user()->hasRole('SuperAdmin')) {
                return true;
            } else {
                return !in_array($role->name, ['SuperAdmin', 'Admin']);
            }
        })->pluck('name', 'name')->all();

        $userRole = $user->roles->pluck('name','name')->all();

        return view('genetsis-admin::pages.users.edit',compact('user','roles','userRole'));
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:users,email,'.$id,
            'password' => 'same:confirm-password',
            'roles' => 'required'
        ]);

        $input = $request->all();
        if(!empty($input['password'])){
            $input['password'] = bcrypt($input['password']);
        }else{
            $input = array_except($input,array('password'));
        }

        $user = User::find($id);
        $user->syncRoles($request->input('roles'));
        $user->update($input);

        return redirect()->route('users.home')
            ->with('success','User updated successfully');
    }


    /**
     * api delete user
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function destroy(Request $request, $id)
    {
        if ($request->ajax()) {
            User::findOrFail($id)->delete();

            return response()->json(['Status' => 'Ok', 'message' => 'User Deleted']);
        } else {
            return response()->json('Error Deleting', 500);
        }
    }

}
