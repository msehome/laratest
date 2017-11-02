<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Menu;
use Encore\Admin\Form;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Tree;
use Encore\Admin\Widgets\Box;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Row;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    use ModelForm;

    public function index()
    {
        return Admin::content(function (Content $content) {
            $content->header('منوی روبات');
            $content->description(  'منوی روبات ');
           // $content->body(Menu::tree());
            $content->row(function (Row $row) {
                $row->column(6, $this->treeView()->render());
                $row->column(6, function (Column $column) {
                    $form = new \Encore\Admin\Widgets\Form();
                    $form->action(admin_url('/menu'));
                    $form->select('parent_id', 'منوی والد')->options(Menu::selectOptions());
                    $form->text('title','عنوان')->rules('required');;
                    $form->textarea('description','شرح');
                    $form->textarea('details','جزئیات');
                    $form->image('image','تصویر')->uniqueName()->move('/images/food');
                    $column->append((new Box(trans(''), $form))->style('success'));
                });
            });
        });
    }

    public function edit($id)
    {
        return Admin::content(function (Content $content) use ($id) {
            $content->header('منو');
            $content->description('اصلاح یک آیتم منو');
            $content->row($this->form()->edit($id));
        });
    }

    public function show($id)
    {
        return redirect()->action(
            '\Encore\Admin\Controllers\MenuController@edit', ['id' => $id]
        );
    }

    public function saveTree($serialize)
    {
        $tree = json_decode($serialize, true);
        if (json_last_error() != JSON_ERROR_NONE) {
            throw new \InvalidArgumentException(json_last_error_msg());
        }
        $this->model->saveTree($tree);
        return true;
    }

     protected function treeView()
    {
        return Menu::tree(function (Tree $tree) {
            $tree->disableCreate();
            $tree->branch(function ($branch) {
                $photos = $branch['image'];
                $logo = '';
                if (isset($branch['image'])) {
                    //foreach ($photos as $photo) {
                        $src = config('admin.upload.host') . '/' .$branch['image'];
                        $logo .= "<img src='$src' style='max-width:30px;max-height:30px' class='img'/>";
                    //}
                }
                return "{$branch['id']} - {$branch['title']} $logo";

            });

        });
    }

    protected function form()
    {
        return Menu::form(function (Form $form) {

            $form->display('id', 'شناسه');

            $form->select('parent_id')->options(Menu::selectOptions());

            $form->text('title','عنوان')->rules('required');;
            $form->textarea('description','شرح');
            $form->textarea('details','جزئیات');
            $form->image('image','تصویر')->uniqueName()->move('/images');;
            $form->display('created_at', 'Created At');
            $form->display('updated_at', 'Updated At');
        });
    }

}
