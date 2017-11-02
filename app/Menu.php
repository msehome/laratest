<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Encore\Admin\Form\Field\HasMany;
use Encore\Admin\Traits\AdminBuilder;
use Encore\Admin\Traits\ModelTree;

class Menu extends Model
{
    use ModelTree, AdminBuilder;
    protected $table = 'menu_tree';
    protected $fillable = [
        'title', 'descrption', 'details', 'image','contents'
    ];
}
