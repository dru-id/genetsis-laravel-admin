<?php namespace Genetsis\Admin\Controllers;

use Genetsis\Admin\Models\DruidApp;
use Genetsis\Admin\Models\Entrypoint;
use Illuminate\Http\Request;


class AppsController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        \View::share('section', 'apps');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $data = DruidApp::orderBy('name','DESC')->paginate(5);

        return view('genetsis-admin::pages.apps.index',compact('data'))
            ->with('i', ($request->input('page', 1) - 1) * 20);
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('genetsis-admin::pages.apps.create');
    }

    /**
     * Refresh App Entrypoints from Druid
     *
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function refresh($id)
    {
        $druid_app = DruidApp::findOrFail($id);

        $druid_entrypoints = \RestApi::searchEntrypointsBy(['app'=>$id]);

        $entrypoints = [];

        foreach ($druid_entrypoints->getResources('entrypoints') as $druid_entrypoint) {

            $fields = collect($druid_entrypoint->getConfigField())->map(function ($field) {
                return $field->getField()->getKey();
            })->toJson();

            $ids = collect($druid_entrypoint->getConfigId())->map(function ($idfield) {
                return $idfield->getField()->getName();
            })->toJson();

            $entrypoint = new Entrypoint();
            $entrypoint->key = $druid_entrypoint->getKey();
            $entrypoint->name = $druid_entrypoint->getDescription();
            $entrypoint->ids = $ids;
            $entrypoint->fields = $fields;

            array_push($entrypoints, $entrypoint);
        }


        $druid_app->entrypoints()->each(function($relation) use ($entrypoints){
            if (!collect($entrypoints)->contains('key', $relation->key)) {
                return $relation->delete();
            }
        });

        collect($entrypoints)->map(function($entrypoint) use ($druid_app) {
            $druid_app->entrypoints()->updateOrCreate(
                ['key' => $entrypoint->key],
                [
                    'name' => $entrypoint->name,
                    'ids' => json_encode($entrypoint->ids),
                    'fields' => json_encode($entrypoint->fields)
                ]
            );
        });


        return redirect()->route('apps.edit', $id)
            ->with('success','Entrypoints refresh successfully');
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'client_id' => 'required|max:20|unique:druid_apps',
            'secret' => 'required|max:100',
            'name' => 'required|max:100'
        ]);

        $druid_app = DruidApp::create($request->all());

        $druid_entrypoints = \RestApi::searchEntrypointsBy(['app'=>$druid_app->client_id]);

        foreach ($druid_entrypoints->getResources('entrypoints') as $druid_entrypoint) {
            $fields = collect($druid_entrypoint->getConfigField())->map(function ($field) {
                return $field->getField()->getKey();
            })->toJson();

            $ids = collect($druid_entrypoint->getConfigId())->map(function ($idfield) {
                return $idfield->getField()->getName();
            })->toJson();

            Entrypoint::updateOrCreate(
                ['key' => $druid_entrypoint->getKey()],
                [
                    'name' => $druid_entrypoint->getDescription(),
                    'ids' => $ids,
                    'fields' => $fields,
                    'client_id' => $druid_app->client_id
                ]
            );
        }

        return redirect()->route('apps.home')
            ->with('success','Druid App created successfully');
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $app = DruidApp::findOrFail($id);
        return view('genetsis-admin::pages.apps.show', compact('app'));
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $druid_app = DruidApp::findOrFail($id);

        return view('genetsis-admin::pages.apps.edit', compact('druid_app'));
    }


    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            //'client_id' => ['required','max:20', Rule::unique('druid_apps', 'client_id')->ignore($id, 'client_id')],
            'secret' => 'required|max:100',
            'name' => 'required|max:100'
        ]);

        $druid_app = DruidApp::findOrFail($id);

        $druid_app->update($request->all());

        return redirect()->route('apps.home')
            ->with('success','Druid App updated successfully');
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function destroy($id)
    {
        DruidApp::findOrFail($id)->delete();
        return redirect()->route('apps.home')
            ->with('success','Druid App deleted successfully');
    }

}
