<?php namespace Yaim\Mitter;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Routing\Controller;

class BaseController extends Controller {

	protected $structure;
	protected $nodeModel;
	protected $apiController;
	protected $view;
	protected $paginate;

	public function getStructure()
	{
		return $this->structure;
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @param $model
	 * @return View
	 */
	public function index($model)
	{
		// get model instance
		$model =  $this->getModel($model);
		// render table
		$table = view('mitter::layouts.table', $model->renderTable())->render();
		// view file
		$viewFile = $model->indexView ?: config('mitter.views.index');

		return view($viewFile, compact('table'));
	}


	/**
	 * Show the form for creating a new resource.
	 *
	 * @param $model
	 * @return View
	 */
	public function create($model)
	{
		return $this->edit($model, null);
	}


	/**
	 * Store a newly created resource in storage.
	 *
	 * @param Model $model
	 * @return Redirect
	 */
	public function store($model)
	{
		$request = request();
		$model = $this->getModel($model);
		$model = new FormSaver($model->structure, $request->all(), $model->nodeModel);
		return redirect($model->getEditAction());

	}


	/**
	 * Display the specified resource.
	 *
	 * @param $model
	 * @param  int $id
	 * @return Redirect
	 */
	public function show($model, $id)
	{
		$model = $this->getModel($model, $id);
		return redirect($model->getEditAction());
	}


	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param $model
	 * @param  int $id
	 * @return View
	 */
	public function edit($model, $id)
	{
		$model = getMitterModelByAliasesName($model);

		$relations = array();
		if (isset($model->structure['relations'])) {
			foreach ($model->structure['relations'] as $key => $value) {
				if (isset($value['type'])) {
					if ($value['type'] == 'divider') {
						continue;
					}
				}
				$relations[] = $key;
			}
		}

		$model = $model::withTrashed()->with($relations)->find($id);
		$modelData = (isset($model))? $modelData = array_filter($model->revealHidden()->toArray(), 'mitterNullFilter') : null;
		if(!isset($modelData)) {
			return \Response::view('errors.missing', array(), 404);
		}

		$html = new FormBuilder($model->structure, null, $modelData, $id);
		$form = $html->get();

		$viewFile = $model->FormView ?: config('mitter.views.form');
		return view($viewFile, compact('form'));
	}


	/**
	 * Update the specified resource in storage.
	 *
	 * @param $model
	 * @param  int $id
	 * @return Redirect
	 */
	public function update($model, $id)
	{
		$model = $this->getModel($model, $id);
		new FormSaver($model->structure, \Input::all(), $model->nodeModel);
		return redirect()->back();
	}


	/**
	 * Remove the specified resource from storage.
	 * @param Model $model
	 * @return mixed
	 * @throws \Exception
	 */
	public function destroy($model, $id)
	{
		$model = $this->getModel($model, $id);
		if ($model->delete()) {
			return redirect($model->getIndexAction());
		}
		return back()->withErrors(['Sorry, but you are unable to delete the this entity.']);
	}

	/**
	 * @param $model
	 * @param null $id
	 * @return mixed
	 */
	private function getModel($model, $id = null)
	{
		if (!$model instanceof Model) {
			if (hasMitterModelAliases($model)) {
				$model = getMitterModelByAliasesName($model);
			}
		}
		if ($model instanceof Model) {
			if ($id) {
				$model = $model->withTrashed()->where('id',$id)->first();
			}
			return $model;
		}
		return abort(404);
	}

}
