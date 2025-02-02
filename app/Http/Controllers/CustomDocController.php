<?php

namespace App\Http\Controllers;

use App\DataTables\CustomDocDataTable;
use App\Http\Requests;
use App\Http\Requests\CreateCustomDocRequest;
use App\Http\Requests\UpdateCustomDocRequest;
use App\Http\Requests\UpdateCustomDocSlugRequest;
use App\Repositories\CustomDocRepository;
use Flash;
use App\Http\Controllers\AppBaseController;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Response;
use App\Models\CustomDoc;

class CustomDocController extends AppBaseController
{
    /** @var  CustomDocRepository */
    private $customDocRepository;

    //to help with data testing and form settings
    public $link = 'customDocs';
    public $htmlTag = 'custom-docs';
    public $title = 'Custom Docs';
    public $resourceFolder = 'custom_docs';

    public function __construct(CustomDocRepository $customDocRepo)
    {
        $this->customDocRepository = $customDocRepo;
    }

    /**
     * Display a listing of the CustomDoc.
     *
     * @param CustomDocDataTable $customDocDataTable
     * @return Response
     * @throws AuthorizationException
     */
    public function index(CustomDocDataTable $customDocDataTable)
    {
        $this->authorize('viewAny', CustomDoc::class);
        $customDocDataTable->setDrawParams(
            ['link'=>$this->link, 'htmlTag'=>$this->htmlTag,
                'title'=>$this->title, 'resourceFolder'=>$this->resourceFolder]
        );
        return $customDocDataTable->render(
            $this->resourceFolder.'.index',
            ['link'=>$this->link, 'htmlTag'=>$this->htmlTag,
                'title'=>$this->title, 'resourceFolder'=>$this->resourceFolder]
        );
    }

    /**
     * Show the form for creating a new CustomDoc.
     *
     * @return Response
     * @throws AuthorizationException
     */
    public function create()
    {
        $this->authorize('create', CustomDoc::class);

        return view('custom_docs.create')->with(
            ['link'=>$this->link, 'htmlTag'=>$this->htmlTag,
                'title'=>$this->title, 'resourceFolder'=>$this->resourceFolder]
        );
    }

    /**
     * Store a newly created CustomDoc in storage.
     *
     * @param CreateCustomDocRequest $request
     *
     * @return Response
     * @throws AuthorizationException
     */
    public function store(CreateCustomDocRequest $request)
    {
        $this->authorize('create', CustomDoc::class);

        $input = $request->all();

        $customDoc = $this->customDocRepository->create($input);

        Flash::success('Custom Doc saved successfully.');

        if (!empty($input['redirect_url'])) {
            return redirect($input['redirect_url']);
        } else {
            return redirect(route('customDocs.index'));
        }
    }

    /**
     * Display the specified CustomDoc.
     *
     * @param string $slug
     *
     * @return Response
     * @throws AuthorizationException
     */
    public function show($slug)
    {
        $customDoc = $this->customDocRepository->getBySlug($slug);

        $this->authorize('view', $customDoc);

        if (empty($customDoc)) {
            Flash::error('Custom Doc not found');

            return redirect(route('customDocs.index'));
        }

        return view('custom_docs.show')->with(
            ['customDoc'=>$customDoc, 'link'=>$this->link, 'htmlTag'=>$this->htmlTag,
                'title'=>$this->title, 'resourceFolder'=>$this->resourceFolder]
        );
    }

    /**
     * Show the form for editing the specified CustomDoc.
     *
     * @param string $slug
     *
     * @return Response
     * @throws AuthorizationException
     */
    public function edit($slug)
    {
        $customDoc = $this->customDocRepository->getBySlug($slug);

        $this->authorize('update', $customDoc);

        if (empty($customDoc)) {
            Flash::error('Custom Doc not found');

            return redirect(route('customDocs.index'));
        }

        return view('custom_docs.edit')->with(
            ['customDoc'=> $customDoc, 'link'=>$this->link, 'htmlTag'=>$this->htmlTag,
                'title'=>$this->title, 'resourceFolder'=>$this->resourceFolder]
        );
    }

    /**
     * Update the specified CustomDoc in storage.
     *
     * @param string $slug
     * @param UpdateCustomDocRequest $request
     *
     * @return Response
     * @throws AuthorizationException
     */
    public function update($slug, UpdateCustomDocRequest $request)
    {
        $customDoc = $this->customDocRepository->getBySlug($slug);

        $this->authorize('update', $customDoc);

        if (empty($customDoc)) {
            Flash::error('Custom Doc not found');

            return redirect(route('customDocs.index'));
        }
        $customDocId = $customDoc->id;
        //if this a request to restore....
        if(!empty($input['restore'])){
            return $this->restore($customDocId, $request);
        }

        $input = $request->all();

        $customDoc = $this->customDocRepository->update($input, $customDocId);

        Flash::success('Custom Doc updated successfully.');

        if (!empty($input['redirect_url'])) {
            return redirect($input['redirect_url']);
        } else {
            return redirect(route('customDocs.index'));
        }
    }

    /**
     * Remove the specified CustomDoc from storage.
     *
     * @param string $slug
     *
     * @throws \Exception
     *
     * @return Response
     * @throws AuthorizationException
     */
    public function destroy($slug)
    {
        $customDoc = $this->customDocRepository->getBySlug($slug);

        $this->authorize('delete', $customDoc);

        if (empty($customDoc)) {
            Flash::error('Custom Doc not found');
            return redirect()->back();
        }

        $customDocId = $customDoc->id;

        $this->customDocRepository->delete($customDocId);

        Flash::success('Custom Doc deleted successfully.');

        return redirect()->back();
    }

    /**
     * Restore the a soft deleted it...
     *
     * @param CustomDoc $customDoc
     * @param UpdateCustomDocRequest $request
     *
     * @return Response
     * @throws \Exception
     */
    public function restore($customDoc, UpdateCustomDocRequest $request)
    {

        $this->authorize('update', $customDoc);

        $input = $request->all();

        if(!empty($input['restore'])){
            $this->customDocRepository->makeModel()->withTrashed()->where('id',$customDoc->id)->first()->restore();
        }

        if (empty($customDoc)) {
            \Laracasts\Flash\Flash::error('Error restoring Custom Doc');
            return redirect(route('customDocs.index'));
        }

        Flash::success('CustomDoc restored successfully.');

        if(!empty($input['redirect_url'])){
            return redirect($input['redirect_url']);
        }else{
            return redirect(route('customDocs.index'));
        }

    }


    /**
     * Remove the specified CustomDoc from storage.
     *
     * @param  CustomDoc $customDoc
     *
     * @return Response
     * @throws AuthorizationException
     */
    public function forceDestroy($customDoc)
    {
        $this->authorize('forceDelete', $customDoc);

        if (empty($customDoc)) {
            Flash::error('CustomDoc not found');
            return redirect()->back();
        }


        try {
            $this->customDocRepository->forceDelete($customDoc->id);
            Flash::success('Custom Doc permanently deleted successfully.');
        } catch (\Exception $e) {

            // An error occurred; cancel the transaction...
            Log::error($e);

            //display generic error
            Flash::error('An error occurred - no changes have been made');
            //if admin display a little more info
            if(Auth::user()->hasRole('admin') && (config('lemur.show_detailed_error_messages'))){
                Flash::error($e->getMessage());
            }

        }

        return redirect()->back();


    }

    /**
     * Update the specified CustomDoc in storage.
     *
     * @param  CustomDoc $customDoc
     * @param UpdateCustomDocSlugRequest $request
     *
     * @return Response
     * @throws AuthorizationException
     */
    public function slugUpdate($customDoc, UpdateCustomDocSlugRequest $request)
    {

        $this->authorize('update', $customDoc);

        $inputAll=$request->all();

        $customDocCheck = $this->customDocRepository->getBySlug($inputAll['original_slug']);

        if (empty($customDoc)||empty($customDocCheck)) {
            Flash::error('CustomDoc not found');
            return redirect(route('customDocs.index'));
        }

        if($customDocCheck->id != $customDoc->id){
            Flash::error('CustomDoc slug mismatch');
            return redirect(route('customDocs.index'));
        }


        $input['slug'] = $inputAll['slug'];
        $customDoc = $this->customDocRepository->update($input, $customDoc->id);

        Flash::success('CustomDoc slug updated successfully.');

        return redirect(route('customDocs.index'));



    }
}
